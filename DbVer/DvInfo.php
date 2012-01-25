<?php
/**
 * class MyApp_AppInfo Contains all the Database Version Functions
*/
class DbVer_DvInfo {

    protected $_db;

    protected $_database;

    protected $_name = 'schema_build_log';

    protected $build_id;

    protected $build_list = array();

    protected $_defaultbuild = 1;

    public function __construct($database)
    {
        $this->_database = $database;
        $this->setDatabase($database);
        $this->setDefaultProperty($database);

    }

    private function _enable_migration_mode($active=true)
    {

        //prevents missing tables from crashing script
        if(!defined('MIGRATION_IN_PROGRESS')){
            define('MIGRATION_IN_PROGRESS',true);
        }
    }
    public function adminDbClass()
    {

        $this->_enable_migration_mode();

        print('STARTING - $this->adminDbClass() at '. date('H:i:s' , time() ).'<br/><br/>');
        //$this->controller_log('STARTING - $this->admin_db_class() at '. date('H:i:s' , time() ) );

        //	Check for presence of schema_infos table
        $arrResult = $this->checkSchema();

        $result = $arrResult;

        if (!empty($result['Msg_type']) && $result['Msg_type'] == 'Error')
        {
            print("Table schema_build_log not present.Creating schema_build_log table. <br/><br/>");
            $this->startDbClass($this->_defaultbuild);

        }

        $row = $this->getLatestDbSchema();
        if(empty($row['build_num']))
        {
            $build = $this->_defaultbuild;
        }
        else
        {
            $build = $row['build_num'];
            if ($this->_defaultbuild != $build)
                $build--;
        }


        /**
         * Build files Directory LOcation
        */
        $builddir = $this->_database['buildPath'];
        $list = scandir($builddir);

        foreach($list as $file)
        {
            $number = preg_replace("/[^0-9]/", '', $file); // ditch anything that is not a number
            if (is_numeric($number)) {
                if($number >= $build) {
                    require_once($builddir.$file);
                    $classname = 'build'.$number;
                    $class = new $classname($this->_database);
                    $class->executeBuild();
                }
            }
        }

        print("All updates have ran, you are now on build ".$class->getBuildId()." <br/><br/>");
        print('FINISHING - $this->adminDbClass() at '. date('H:i:s' , time() )." <br/><br/>");
        //$this->controller_log('FINISHING - $this->admin_db_class() at '. date('H:i:s' , time() ) );
        //$this->exit_flow();
        exit;

    }

    public function setDatabase($database)

    {

        if (!empty($database)) {
            $this->_db = Zend_Db::factory($database['adapter'], array(
                'host' => $database['host'],
                'username' => $database['username'],
                'password' => $database['password'],
                'dbname' => $database['dbname']
            ));
            Zend_Db_Table_Abstract::setDefaultAdapter($this->_db);
        }
        return $this;
    }

    public function setDefaultProperty($database)
    {

        if (!empty($database)) {
             $this->_defaultbuild = $database['defaultBuild'];
        }
        return $this;
    }


    /**
    * This is the control method that will decide which db to connect
    * @param void
    * @return void
    * @author Prasad Chaugule
    */
    private function _select_db_conn() {
        if(empty($this->conn)) {
                $this->conn = 'default';
        }
    }

    /**
    * This is the check if the Schema table is present.
    * @return void $result Databse result
    */
    public function checkSchema()
    {

        $qry = "CHECK TABLE $this->_name FAST QUICK";
        $result = $this->_db->fetchRow($qry);
        return $result;
    }

    /**
    * If the Schema table is present, then create one.
    * @param int $defaultbuild default database version
    */
    public function startDbClass($defaultbuild)
    {
        $this->_db->query("CREATE TABLE $this->_name (
                                `build_id` INT(10) NOT NULL AUTO_INCREMENT,
                                `build_function` VARCHAR(255) NOT NULL,
                                `build_num` MEDIUMINT NOT NULL,
                                PRIMARY KEY (`build_id`),
                                UNIQUE INDEX `build_function_build_num_UQ` (`build_function`, `build_num`)
                                )
                                COMMENT='This is the new schema log for all db updates'
                                COLLATE='utf8_general_ci'
                                ENGINE=InnoDB
                                ROW_FORMAT=DEFAULT;");

        /**
         * Build files Directory LOcation
        */
        $builddir = $this->_database['buildPath'];
        // try{
        include_once($builddir.'build'.$defaultbuild.'.php');
        $buildclass = "build".$defaultbuild;
        $class = new $buildclass($this->_database);

        $functions = $class->getBuildList();


        if(!empty($functions))
        {
            foreach($functions as $i)
            {
                    $class->executeBuild();
            }
        }
        //            } catch(Zend_Exception $e){
        //                echo $e->getMessage();
        //            }

    }

    /**
    * Insert  Schema table record.
    * @param text $build_function Build Function Name
    * @param int $build_num Build Id
    * @return bool $result true if success else false
    */
    private function insertDbSchema($build_function, $build_num){
        $data =array("build_function" => $build_function, "build_num" => $build_num);
        if($this->_db->insert($this->_name, $data))
             return true;
        return false;
    }

    /**
    * Get the Latest Build Id from Schema Table.
    * @return void $result database Result
    */
    public function getLatestDbSchema(){

        $qry = $this->_db->select()
                    ->from(array('d' => $this->_name), array('d.build_num'))
                    ->order("d.build_num DESC")
                    ->limit(1);
                    //echo $qry; exit;
        $result = $this->_db->fetchRow($qry);
        return $result;
    }

    /**
    * This is the control method that will actually execute the code to update the db
    * @param void
    * @return void
    * @author Jason Ball
    */
    public function executeBuild() {

        print("Starting Build <b>".$this->build_id."</b><br/><br/>");
        if (count($this->build_list)>0) {
                //Find all the functions that already ran and remove them from the list
            try {
                $qry = $this->_db->select()
                            ->from(array('d' => $this->_name), array('lower(d.build_function) as build_function'))
                            ->where("d.build_num = $this->build_id");
            } catch(Zend_Db_Exception $ed) {
                echo $ed->getMessage();
            }
             $row = $this->_db->fetchAll($qry);

            if (!empty($row)) {
                foreach ($row as $v) {
                        $this->findElement($v['build_function']);
                }
            }
            //Run any build items that are left
            if (!empty($this->build_list)) {
                foreach ($this->build_list as $i) {
                    if (method_exists($this, $i)) {

                        print "<b>Build Function Name:".$i.":</b> <br/><br/>";
                        $return = $this->$i();
                        if ($return) {
                                $m = "Method <b>".$i."</b> ran fine, recording now";
                                print " $m <br/><br/>";
                                $this->insertDbSchema($i, $this->build_id);

                        } else if ($return == false) {
                                $m = "Function <b>".$i."</b> failed in build ".$this->build_id.", system has stopped running";

                                print($m);
                                //$this->controller_log($m);
                                //cleanBackTrace();
                                exit;
                        } else {
                            $m = " Function <b>".$i."</b> did not return boolean, will continue as if nothing wrong happend ";
                            print("$m <Br/><br/>");

                        }
                    }
                }
            }
        }
    }

    /**
    * This method is just to make sure we won't execute any methods that might have already run
    * @param string $want
    * @author Jason Ball
    */
    private function findElement($want)
    {
        if(!empty($this->build_list))
        {
            foreach($this->build_list as $k => $i)
            {

                if(strtolower($i) == $want)
                {

                    unset($this->build_list[$k]);
                    return true;
                }
            }
        }
        return false;

    }

    /**
    * This method lets you know how many functions are in the build list
    * @param void
    * @return integer
    * @author Jason Ball
    */
    public function getFunctionCount()
    {
        return count($this->build_list);

    }

    /**
    * This method actually executes the code
    *
    * @param string $sql
    * @return boolean
    * @author Jason Ball
    */
    protected function runSQL($sql)
    {
        try{
            if($this->_db->query($sql)) {
                return true;
            }
        } catch(Zend_Db_Exception $e) {
            echo $e->getMessage();

        }
        return false;
    }

    /**
    * This method takes an sql file and parses it.
    * @param string $filename
    * @param string $separator
    * @author Kunal Somaya
    */
    /**
    protected function import_sql_file($filename,$separator=";")
    {
        $this->_select_db_conn();
        $db = ConnectionManager::getDataSource($this->conn);
        $filename = ROOT."/SQL/builds/".$this->build_id."/".$filename;
        if(file_exists($filename))
        {

                $sql_data = file_get_contents($filename);
                if(!empty($sql_data))
                {
                        $queries = explode($separator,$sql_data);

                        $db->begin($this);
                        foreach($queries as $query)
                        {
                                $query = trim($query);
                                if(!empty($query))
                                {
                                        $result = $this->db->execute($query);
                                        if (!$result) {
                                                //something went wrong, revert back entire SQL file
                                                $db->rollback($this);
                                                return false;
                                        }
                                }
                        }
                        //All's well, commit the transaction
                        $db->commit($this);
                        return true;
                }
                else
                {
                        print "<br/>Please execute baseDIR/SQL/".$filename."<br/>";
                }
        }
        else
        {
                print "<br/>Please execute baseDIR/SQL/".$filename."<br/>";
        }
        return false;
    }
    **/

    /**
    * This Method will return the build id
    *
    * @param void
    * @return mixed (integer or print error message and kill process
    * @author Jason Ball
    */
    public function getBuildId()
    {
        if(is_numeric($this->build_id))
        {
                return $this->build_id;
        }

        print("Invalid Build Class");
        exit;
    }

    /**
    * This method is used mostly to populate build 13 data functions without running them
    * @param void
    * @return Array
    * @author Jason Ball
    */
    public function getBuildList()
    {
        return $this->build_list;
    }    

}



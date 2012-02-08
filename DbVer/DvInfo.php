<?php
/**
 * class MyApp_AppInfo Contains all the Database Version Functions
*/
class DbVer_DvInfo {

    /** database properties */		
    protected $_db;

    /** other data info */
    protected $_datainfo;

    /** table name for schema if not defined */
    protected $_tablename = 'schema_build_log';
    
    /** build id defined */
    protected $build_id;

    /** build list */	
    protected $build_list = array();

    /** default build id */
    protected $_defaultbuild = 1;

    /** if schema table present set to 0 */
    protected $_newschematable = 0;

    public function __construct($data)
    {
        $this->_datainfo = $data;
        $this->setDefaultProperty($data);

    }

    /**
    * This is the set the migration mode to active/deactivate
    * @param boolean $active true or false 
    */	
    private function _enable_migration_mode($active=true)
    {

        //prevents missing tables from crashing script
        if(!defined('MIGRATION_IN_PROGRESS')){
            define('MIGRATION_IN_PROGRESS',true);
        }
    }

    /**
    * This is the starting point of database versioning process 
    */	
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
	    $this->_newschematable = 1;
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
        $builddir = $this->_datainfo['buildPath'];
        $list = scandir($builddir);

        foreach($list as $file)
        {
            $number = preg_replace("/[^0-9]/", '', $file); // ditch anything that is not a number
            if (is_numeric($number)) {
                if($number >= $build) {
                    require_once($builddir.$file);
                    $classname = 'build'.$number;
                    $class = new $classname($this->_datainfo);
	            /** If the Schema table is newly created then the default build is already executed i.e. executeBuild so skip this call for default build number **/		
		    if(!$this->_newschematable || $this->_defaultbuild != $number) {
                    	$class->executeBuild();
		    }
                }
            }
        }

        print("All updates have ran, you are now on build ".$class->getBuildId()." <br/><br/>");
        print('FINISHING - $this->adminDbClass() at '. date('H:i:s' , time() )." <br/><br/>");        
    }

    /**
    * This is to set the properties 
    */	
    public function setDefaultProperty($data)
    {

        if (!empty($data)) {
            $this->_db = Zend_Db::factory($data['adapter'], array(
                'host' => $data['host'],
                'username' => $data['username'],
                'password' => $data['password'],
                'dbname' => $data['dbname']
            ));
            Zend_Db_Table_Abstract::setDefaultAdapter($this->_db);

	    if ($data['tablename']) {
            	$this->_tablename = $data['tablename'];
	    }
	
	    if ($data['defaultBuild']) {
		$this->_defaultbuild = $data['defaultBuild'];
	    }
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

        $qry = "CHECK TABLE $this->_tablename FAST QUICK";
        $result = $this->_db->fetchRow($qry);
        return $result;
    }

    /**
    * If the Schema table is present, then create one.
    * @param int $defaultbuild default database version
    */
    public function startDbClass($defaultbuild)
    {
        $this->_db->query("CREATE TABLE $this->_tablename (
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
        $builddir = $this->_datainfo['buildPath'];
        // try{
        include_once($builddir.'build'.$defaultbuild.'.php');
        $buildclass = "build".$defaultbuild;
        $class = new $buildclass($this->_datainfo);
	
	$class->executeBuild();

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
        if($this->_db->insert($this->_tablename, $data))
             return true;
        return false;
    }

    /**
    * Get the Latest Build Id from Schema Table.
    * @return void $result database Result
    */
    public function getLatestDbSchema(){

        $qry = $this->_db->select()
                    ->from(array('d' => $this->_tablename), array('d.build_num'))
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
    */
    public function executeBuild() {

        print("Starting Build <b>".$this->build_id."</b><br/><br/>");
        if (count($this->build_list)>0) {
                //Find all the functions that already ran and remove them from the list
            try {
                $qry = $this->_db->select()
                            ->from(array('d' => $this->_tablename), array('lower(d.build_function) as build_function'))
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
    * @return mixed (integer or print error message and kill process)
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
    */
    public function getBuildList()
    {
        return $this->build_list;
    }    

}



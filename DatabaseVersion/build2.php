<?php
/**
 * @param string $dir Model file Directory Location
*/
$dir = $this->_database['appPath'];
require_once($dir."DvInfo.php");
class build2 extends DbVer_DvInfo
{

	public function __construct($db)
	{

            parent::__construct($db);

            $this->build_id = 2;

            $this->build_list[] = "testdemo2";
            $this->build_list[] = "testdemo2_1";
            $this->build_list[] = "testdemo2_2";
            $this->build_list[] = "testdemo2_3";

	}

	protected function testdemo2()
	{
		$sql = "
		CREATE TABLE testdemo2
		(
		`id` int(10) NOT NULL AUTO_INCREMENT,
		`test_name`  VARCHAR(255) DEFAULT '',
		`is_deleted` TINYINT(1) DEFAULT 0,
		`created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
		`created_by` INT(11) NOT NULL,
		`modified` TIMESTAMP NULL DEFAULT NULL,
		`modified_by` INT(11) NOT NULL,
                PRIMARY KEY (`id`)
		)
		COMMENT='testdemo2 table';";

		return $this->runSQL($sql);

	}

        protected function testdemo2_1()
	{
		$sql = "
		CREATE TABLE testdemo2_1
		(
		`id` int(10) NOT NULL AUTO_INCREMENT,
		`test_name`  VARCHAR(255) DEFAULT '',
		`is_deleted` TINYINT(1) DEFAULT 0,
		`created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
		`created_by` INT(11) NOT NULL,
		`modified` TIMESTAMP NULL DEFAULT NULL,
		`modified_by` INT(11) NOT NULL,
                PRIMARY KEY (`id`)
		)
		COMMENT='testdemo2_1 table';";

		return $this->runSQL($sql);

	}
        protected function testdemo2_2()
	{
		$sql = "
		CREATE TABLE testdemo2_2
		(
		`id` int(10) NOT NULL AUTO_INCREMENT,
		`test_name`  VARCHAR(255) DEFAULT '',
		`is_deleted` TINYINT(1) DEFAULT 0,
		`created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
		`created_by` INT(11) NOT NULL,
		`modified` TIMESTAMP NULL DEFAULT NULL,
		`modified_by` INT(11) NOT NULL,
                PRIMARY KEY (`id`)
		)
		COMMENT='testdemo2_2 table';";

		return $this->runSQL($sql);

	}

         protected function testdemo2_3()
	{
		$sql = "
		CREATE TABLE testdemo2_3
		(
		`id` int(10) NOT NULL AUTO_INCREMENT,
		`test_name`  VARCHAR(255) DEFAULT '',
		`is_deleted` TINYINT(1) DEFAULT 0,
		`created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
		`created_by` INT(11) NOT NULL,
		`modified` TIMESTAMP NULL DEFAULT NULL,
		`modified_by` INT(11) NOT NULL,
                PRIMARY KEY (`id`)
		)
		COMMENT='testdemo2_3 table';";

		return $this->runSQL($sql);

	}

}
?>

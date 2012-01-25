<?php
/**
 * @param string $dir Model file Directory Location
*/
$dir = $this->_database['appPath'];
require_once($dir."DvInfo.php");
class build3 extends DbVer_DvInfo
{

	public function __construct($db)
	{

            parent::__construct($db);

            $this->build_id = 3;

            $this->build_list[] = "testdemo3";
            $this->build_list[] = "testdemo3_1";
            $this->build_list[] = "testdemo3_2";

	}

	protected function testdemo3()
	{
		$sql = "
		CREATE TABLE testdemo3
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
		COMMENT='testdemo3 table';";

		return $this->runSQL($sql);

	}

        protected function testdemo3_1()
	{
		$sql = "
		CREATE TABLE testdemo3_1
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
		COMMENT='testdemo3_1 table';";

		return $this->runSQL($sql);

	}
        protected function testdemo3_2()
	{
		$sql = "
		CREATE TABLE testdemo3_2
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
		COMMENT='testdemo3_2 table';";

		return $this->runSQL($sql);

	}

}
?>

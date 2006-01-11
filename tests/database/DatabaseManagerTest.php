<?php
require_once dirname(__FILE__) . '/../test_environment.php';

class DatabaseManagerTest extends UnitTestCase
{
	public function testInitialization()
	{
		$DBM = new DatabaseManager();
		$this->assertIsA($DBM, 'DatabaseManager');
		$this->assertTrue(file_exists(AG_CONFIG_DIR . '/databases.ini'));
		/*
			$database = new PDODatabase();
			$database->initialize(null);
			$this->databases['default'] = $database;
		*/
			
		$this->assertTrue($DBM->initialize());
	}

}
?>
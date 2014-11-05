<?php

/**
 * @runTestsInSeparateProcesses
 * @agaviIsolationEnvironment testing-use_database_on
 */
class AgaviDatabaseManagerTest extends AgaviUnitTestCase
{
	private $_dbm = null;
	
	public function setUp()
	{
		$this->_dbm = $this->getContext()->getDatabaseManager();
	}

	public function tearDown()
	{
		$this->_dbm = null;
	}

	public function testInitialization()
	{
		$this->assertInstanceOf('AgaviDatabaseManager', $this->_dbm);
	}

}
?>
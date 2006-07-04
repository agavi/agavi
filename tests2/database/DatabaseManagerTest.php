<?php

class DatabaseManagerTest extends AgaviTestCase
{
	private $_dbm = null,
					$_context = null;
	
	public function setUp()
	{
		$this->_context = AgaviContext::getInstance();
		
		$this->_dbm = $this->_context->getDatabaseManager();
	}

	public function tearDown()
	{
		$this->_dbm = null;
		$this->_context = null;
	}

	public function testInitialization()
	{
		$this->assertType('AgaviDatabaseManager', $this->_dbm);
		$this->assertType('AgaviContext', $this->_dbm->getContext());
		$this->assertReference($this->_context, $this->_dbm->getContext());
	}

}
?>
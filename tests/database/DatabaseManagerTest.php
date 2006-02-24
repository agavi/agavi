<?php
require_once dirname(__FILE__) . '/../test_environment.php';

class DatabaseManagerTest extends UnitTestCase
{
	private $_dbm = null,
					$_context = null;
	
	public function setUp()
	{
		$this->_context = Context::getInstance();
		
		$this->_dbm = $this->_context->getDatabaseManager();
	}

	public function tearDown()
	{
		$this->_dbm = null;
		$this->_context = null;
	}

	public function testWhatever()
	{
		$this->assertIsA($this->_dbm, 'DatabaseManager');
		$this->assertIsA($this->_dbm->getContext(), 'Context');
	}

}
?>
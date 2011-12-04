<?php

class DatabaseManagerTest extends AgaviTestCase
{
	private $_dbm = null,
					$_context = null;
	
	public function setUp()
	{
		unlink(AgaviConfigCache::getCacheName(AgaviConfig::get('core.config_dir') . '/factories.xml', AgaviConfig::get('core.default_context')));
		AgaviConfig::set('core.use_database', true);
		AgaviContext::getInstance('test')->initialize();
		
		$this->_context = AgaviContext::getInstance('test');
		
		$this->_dbm = $this->_context->getDatabaseManager();
	}

	public function tearDown()
	{
		$this->_dbm = null;
		$this->_context = null;
	}

	public function testInitialization()
	{
		$this->assertInstanceOf('AgaviDatabaseManager', $this->_dbm);
		$this->assertInstanceOf('AgaviContext', $this->_dbm->getContext());
		$ctx_test = $this->_dbm->getContext();
		$this->assertReference($this->_context, $ctx_test);
	}

}
?>
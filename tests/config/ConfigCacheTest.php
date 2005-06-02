<?php
require_once dirname(__FILE__) . '/../mockContext.php';

class ConfigCacheTest extends UnitTestCase
{
/*
	private	$_controller = null,
					$_context = null;

	public function setUp()
	{
		$this->_controller = new MockController($this);
		$this->_controller->dispatch();
		$this->_context = $this->_controller->getContext();
	}

	public function tearDown() 
	{
		$this->_controller = null;
		$this->_context->cleanSlate();
		$this->_context = null;
	}

*/

	public function testCheckConfig()
	{
		try {
			$filename = ConfigCache::checkConfig('config/factories.ini');
			$this->assertIdentical(AG_CACHE_DIR . '/config_factories.ini.php', $filename);
			$this->assertTrue( file_exists($filename) );
		} catch (ConfigurationException $e) {
			$this->fail($e->getMessage());
		}
		
		try {
			ConfigCache::checkConfig('a file that doesnt exist');
		} catch (ConfigurationException $e) {
			$this->pass('Successfully caught configuration exception');
		}
		$this->assertTrue($e, 'Did not get expected ConfigurationException?');

	}


	public function testgetCacheName()
	{
		$name = 'bleh/blah.ini';	
		$this->assertIdentical(AG_CACHE_DIR . '/bleh_blah.ini.php', ConfigCache::getCacheName($name) );
		
		$name = 'bleh\blah.ini';	
		$this->assertIdentical(AG_CACHE_DIR . '/bleh_blah.ini.php', ConfigCache::getCacheName($name) );
	}
	
	public function testimport()
	{
		$this->assertFalse( defined('Sompn_loaded') );
		ConfigCache::import('config/sompn.ini');
		$this->assertTrue( defined('Sompn_loaded') );
			
	}
	
	public function testclear()
	{
		$dummyfile = AG_CACHE_DIR . '/dummyfile.ini.php';
		file_put_contents($dummyfile, 'Dummy file created in ' . __FILE__ . ' - ' . date('Y/m/d'));
		$this->assertTrue( file_exists($dummyfile) );
		ConfigCache::clear();
		$this->assertFalse( file_exists($dummyfile) );
	}
}

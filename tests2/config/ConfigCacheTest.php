<?php

class ConfigCacheTest extends AgaviTestCase
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
			$filename = AgaviConfigCache::checkConfig('config/factories.ini');
			$this->assertSame(AgaviConfig::get('core.cache_dir') . DIRECTORY_SEPARATOR . AgaviConfigCache::CACHE_SUBDIR . DIRECTORY_SEPARATOR . 'config_factories.ini.php', $filename);
			$this->assertTrue( file_exists($filename) );
		} catch (AgaviConfigurationException $e) {
			$this->fail($e->getMessage());
		}
		
		try {
			AgaviConfigCache::checkConfig('a file that doesnt exist');
		} catch (AgaviConfigurationException $e) {
			return;
		}
		$this->fail('Did not get expected ConfigurationException?');

	}


	public function testgetCacheName()
	{
		$name = 'bleh/blah.ini';	
		$this->assertEquals(AgaviConfig::get('core.cache_dir') . DIRECTORY_SEPARATOR . AgaviConfigCache::CACHE_SUBDIR . DIRECTORY_SEPARATOR . 'bleh_blah.ini.php', AgaviConfigCache::getCacheName($name) );
		
		$name = 'bleh\blah.ini';	
		$this->assertEquals(AgaviConfig::get('core.cache_dir') . DIRECTORY_SEPARATOR . AgaviConfigCache::CACHE_SUBDIR . DIRECTORY_SEPARATOR . 'bleh_blah.ini.php', AgaviConfigCache::getCacheName($name) );
	}
	
	public function testimport()
	{
		$this->assertFalse( defined('Sompn_loaded') );
		AgaviConfigCache::import('config/sompn.ini');
		$this->assertTrue( defined('Sompn_loaded') );
			
	}
	
	public function testclear()
	{
		$dummyfile = AgaviConfig::get('core.cache_dir') . '/' . AgaviConfigCache::CACHE_SUBDIR . '/dummyfile.ini.php';
		file_put_contents($dummyfile, 'Dummy file created in ' . __FILE__ . ' - ' . date('Y/m/d'));
		$this->assertTrue( file_exists($dummyfile) );
		AgaviConfigCache::clear();
		$this->assertFalse( file_exists($dummyfile) );
	}
}

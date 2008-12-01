<?php

class AgaviConfigCacheTest extends AgaviPhpUnitTestCase
{
	/**
	 * @dataProvider dataGenerateCacheName 
	 * 
	 */
	public function testGenerateCacheName($configname, $context, $expected)
	{
		$cachename = AgaviConfigCache::getCacheName($configname, $context);
		$expected = AgaviConfig::get('core.cache_dir').'/config/'.$expected; 
		$this->assertEquals($expected, $cachename);
	}
	
	public function dataGenerateCacheName()
	{
		return array(	'slashes_null' =>     array('foo/bar/back\\slash.xml', 
													null, 
													'foo_bar_back_slash.xml_'.AgaviConfig::get('core.environment').'_.php',
													),
						'<contextname>' =>    array('foo/bar/back\\slash.xml',
													'<contextname>',
													'foo_bar_back_slash.xml_'.AgaviConfig::get('core.environment').'_<contextname>.php',
													),
					);
	}
	
	public function testCheckConfig()
	{
		$config = AgaviConfig::get('core.config_dir').'/autoload.xml';
		$expected = AgaviConfigCache::getCacheName($config);
		if(file_exists($expected)) {
			unlink($expected);
		}
		$cacheName = AgaviConfigCache::checkConfig($config);
		$this->assertEquals($expected, $cacheName);
		$this->assertFileExists($cacheName);
	}
	
	public function testModified()
	{
		$config = AgaviConfig::get('core.config_dir').'/autoload.xml';
		$cacheName = AgaviConfigCache::getCacheName($config);
		if(!file_exists($cacheName)) {
			$cacheName = AgaviConfigCache::checkConfig($config);
		}	
		sleep(1);
		touch($config);
		clearstatcache(); // make shure we don't get fooled by the stat cache
		$this->assertTrue(AgaviConfigCache::isModified($config, $cacheName), 'Failed asserting that the config file has been modified.');
	}
	
	public function testTicket931()
	{
		$config = 'project/foo.xml';
		$cachename = AgaviConfigCache::getCacheName($config, 'with/slash');
		$expected = AgaviConfig::get('core.cache_dir').'/config/project_foo.xml_'.AgaviConfig::get('core.environment').'_with_slash.php'; 
		$this->assertEquals($expected, $cachename);
	}
	
	public function testTicket932()
	{
		$config1 = 'project/foo.xml';
		$config2 = 'project_foo.xml';
		
		$this->assertNotEquals(AgaviConfigCache::getCacheName($config1), AgaviConfigCache::getCacheName($config2));
	}
	
	
}
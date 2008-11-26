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
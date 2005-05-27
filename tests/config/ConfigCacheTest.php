<?php
require_once 'core/AgaviObject.class.php';
require_once 'config/ConfigCache.class.php';
require_once 'util/Toolkit.class.php';
require_once 'exception/AgaviException.class.php';
require_once 'exception/ConfigurationException.class.php';

if (!defined('AG_WEBAPP_DIR')) {
	define('AG_WEBAPP_DIR', dirname(__FILE__) . '/sandbox');
}

if (!defined('AG_CACHE_DIR')) {
	define('AG_CACHE_DIR', AG_WEBAPP_DIR . '/cache');
}
	
class ConfigCacheTest extends UnitTestCase
{

	public function setup() 
	{
		// TODO: create an ini file on the fly.. 
		$inifile = '';
		
	}


	public function testCheckConfig()
	{
		// We should be throwing exceptions if the config is bunk/unreadable
		try {
			ConfigCache::checkConfig('a file that doesnt exist');
		} catch (ConfigurationException $e) {
			$this->pass('Successfully caught configuration exception');
		}
		// the string passed into checkConfig maybe an absolute path or relative
		// regardless, an absolute path is ascertained and internally knowns as the $filename
		// the name of the cache file is determined
		
	}

	public function testclear()
	{
	
		$this->fail('incomplete Test');
	}

	public function testgetCacheName()
	{
		
		$this->fail('incomplete Test');
	}
	
	public function testimport()
	{
		$this->fail('incomplete Test');
	}
}

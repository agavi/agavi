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

	private static function makeCacheName($config, $environment, $context = '')
	{
		return sprintf(
			'%1$s_%2$s.php',
			preg_replace(
				'/[^\w-_.]/i', 
				'_', 
				sprintf(
					'%1$s_%2$s_%3$s', 
					basename(str_replace('\\', '/', $config)), 
					$environment, 
					$context
				)
			),
			sha1(
				sprintf(
					'%1$s_%2$s_%3$s',
					$config,
					$environment,
					$context
				)
			)
		);
	}

	public function testCheckConfig()
	{
		$filename = AgaviConfigCache::checkConfig(AgaviConfig::get('core.config_dir') . '/factories.xml');

		// TODO: do we need such a test at all?
		// $this->assertSame(AgaviConfig::get('core.cache_dir') . DIRECTORY_SEPARATOR . AgaviConfigCache::CACHE_SUBDIR . DIRECTORY_SEPARATOR . 'config_factories.xml.php', $filename);
		$cd = realpath(AgaviConfig::get('core.cache_dir'));
		$fn = realpath($filename);
		$this->assertSame($cd, substr($fn, 0, strlen($cd)));

		$this->assertTrue( file_exists($filename) );

		try {
			AgaviConfigCache::checkConfig('a file that doesnt exist');
		} catch (AgaviConfigurationException $e) {
			return;
		}
		$this->fail('Did not get expected ConfigurationException?');

	}


	public function testgetCacheName()
	{
		$env = AgaviConfig::get('core.environment');
		$ctx = 'testContext';
		$csd = AgaviConfig::get('core.cache_dir') . DIRECTORY_SEPARATOR . AgaviConfigCache::CACHE_SUBDIR . DIRECTORY_SEPARATOR;


		$name = 'bleh/blah.xml';
		$this->assertEquals($csd . self::makeCacheName('bleh/blah.xml', $env), AgaviConfigCache::getCacheName($name));

		$name = 'bleh/blah.xml';
		$this->assertEquals($csd . self::makeCacheName('bleh/blah.xml', $env, $ctx), AgaviConfigCache::getCacheName($name, $ctx));
	}

	public function testgetCacheNameWin()
	{
		if(strtoupper(substr(PHP_OS, 0, 3)) != 'WIN') {
			$this->markTestSkipped('Skipping AgaviConfigCache::getCacheName() tests for Windows.');
		}
		
		$env = AgaviConfig::get('core.environment');
		$ctx = 'testContext';
		$csd = AgaviConfig::get('core.cache_dir') . DIRECTORY_SEPARATOR . AgaviConfigCache::CACHE_SUBDIR . DIRECTORY_SEPARATOR;

		$name = 'bleh\\blah.xml';
		$this->assertEquals($csd . self::makeCacheName('bleh\\blah.xml', $env), AgaviConfigCache::getCacheName($name));
	}

	public function testload()
	{
		$this->assertFalse( defined('ConfigCacheImportTest_included') );
		AgaviConfigCache::load(AgaviConfig::get('core.config_dir') . '/tests/importtest.xml');
		$this->assertTrue( defined('ConfigCacheImportTest_included') );

		$GLOBALS["ConfigCacheImportTestOnce_included"] = false;
		AgaviConfigCache::load(AgaviConfig::get('core.config_dir') . '/tests/importtest_once.xml', true);
		$this->assertTrue( $GLOBALS["ConfigCacheImportTestOnce_included"] );

		$GLOBALS["ConfigCacheImportTestOnce_included"] = false;
		AgaviConfigCache::load(AgaviConfig::get('core.config_dir') . '/tests/importtest_once.xml', true);
		$this->assertFalse( $GLOBALS["ConfigCacheImportTestOnce_included"] );

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

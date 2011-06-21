<?php

class AgaviConfigCacheTest extends AgaviPhpUnitTestCase
{
	/**
	 * Constructs a test case with the given name.
	 *
	 * @param  string $name
	 * @param  array  $data
	 * @param  string $dataName
	 */
	public function __construct($name = NULL, array $data = array(), $dataName = '')
	{
		parent::__construct($name, $data, $dataName);
		$this->setRunTestInSeparateProcess(true);
	}
	
	/**
	 * @dataProvider dataGenerateCacheName 
	 * 
	 */
	public function testGenerateCacheName($configname, $context, $expected)
	{
		$cachename = AgaviConfigCache::getCacheName($configname, $context);
		$expected = AgaviConfig::get('core.cache_dir').DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.$expected; 
		$this->assertEquals($expected, $cachename);
	}
	
	public function dataGenerateCacheName()
	{
		return array(
			'slashes_null' => array(
				'foo/bar/hash#bang.xml',
				null,
				'hash_bang.xml_'.AgaviConfig::get('core.environment').'__'.sha1('foo/bar/hash#bang.xml_'.AgaviConfig::get('core.environment').'_').'.php',
			),
			'<contextname>' => array(
				'foo/bar/hash#bang.xml',
				'<contextname>',
				'hash_bang.xml_'.AgaviConfig::get('core.environment').'__contextname__'.sha1('foo/bar/hash#bang.xml_'.AgaviConfig::get('core.environment').'_<contextname>').'.php',
			),
		);
	}
	
	public function testCheckConfig()
	{
		$config = AgaviConfig::get('core.config_dir').DIRECTORY_SEPARATOR.'autoload.xml';
		$config = AgaviToolkit::normalizePath($config);
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
		$config = AgaviConfig::get('core.config_dir').DIRECTORY_SEPARATOR.'autoload.xml';
		$cacheName = AgaviConfigCache::getCacheName($config);
		if(!file_exists($cacheName)) {
			$cacheName = AgaviConfigCache::checkConfig($config);
		}	
		sleep(1);
		touch($config);
		clearstatcache(); // make shure we don't get fooled by the stat cache
		$this->assertTrue(AgaviConfigCache::isModified($config, $cacheName), 'Failed asserting that the config file has been modified.');
	}

	public function testModifiedNonexistantFile()
	{
		$config = AgaviConfig::get('core.config_dir').DIRECTORY_SEPARATOR.'autoload.xml';
		$cacheName = AgaviConfigCache::getCacheName($config);
		if(file_exists($cacheName)) {
			unlink($cacheName);
		}	
		$this->assertTrue(AgaviConfigCache::isModified($config, $cacheName), 'Failed asserting that the config file has been modified.');
	}
	
	public function testWriteCacheFile()
	{
		$expected = 'This is a config cache test.';
		$config = AgaviConfig::get('core.config_dir').DIRECTORY_SEPARATOR.'foo.xml';
		$cacheName = AgaviConfigCache::getCacheName($config);
		if(file_exists($cacheName)) {
			unlink($cacheName);
		}
		AgaviConfigCache::writeCacheFile($config, $cacheName, $expected);
		$this->assertFileExists($cacheName);
		$content = file_get_contents($cacheName);
		$this->assertEquals($expected, $content);
		
		$append = "\nAnd a second line appended.";
		AgaviConfigCache::writeCacheFile($config, $cacheName, $append, true);
		$content = file_get_contents($cacheName);
		$this->assertEquals($expected.$append, $content);
	}
	
	public function testClear()
	{
		$cacheDir = AgaviConfig::get('core.cache_dir').DIRECTORY_SEPARATOR.'config';
		AgaviConfigCache::clear();
		$directory = new DirectoryIterator($cacheDir);
		foreach($directory as $item) {
			if($directory->current()->isDot()) {
				continue;
			}
			$this->fail(sprintf('Failed asserting that the cache dir "%1$s" is empty, it contains at least "%2$s"', $cacheDir, $item->getFileName()));
		}
	}
	
	/**
	 * @expectedException AgaviUnreadableException
	 * this does not seem to work in isolation
	 */
	public function testAddNonexistantConfigHandlersFile()
	{
		$this->setExpectedException('AgaviUnreadableException');
		AgaviConfigCache::addConfigHandlersFile('does/not/exist');
	}
	
	public function testAddConfigHandlersFile()
	{
		$config = AgaviConfig::get('core.module_dir').'/Default/config/config_handlers.xml';
		AgaviTestingConfigCache::addConfigHandlersFile($config);
		$this->assertTrue(AgaviTestingConfigCache::handlersDirty(), 'Failed asserting that the handlersDirty flag is set after adding a config handlers file.');
		$handlerFiles = AgaviTestingConfigCache::getHandlerFiles();
		$this->assertFalse($handlerFiles[$config], sprintf('Failed asserting that the config file "%1$s" has not been loaded.', $config));
	}
	
	public function testCallHandlers()
	{
		$this->markTestIncomplete();
	}
	
	public function testSetupHandlers()
	{	
		// this is not possible to test with the agavi unit tests as this needs
		// a really clean env with no framework bootstrapped. Need to think about that.
		//$this->markTestIncomplete();
		AgaviTestingConfigCache::resetHandlers();
		$this->assertEquals(null, AgaviTestingConfigCache::getHandlers());
		AgaviTestingConfigCache::setUpHandlers();
		$handlers = AgaviTestingConfigCache::getHandlers();
		$this->assertNotEquals(null, $handlers);
	}
	
	public function testGetHandlerInfo()
	{
		$handlerInfo = AgaviTestingConfigCache::getHandlerInfo('notregistered');
		$this->assertEquals(null, $handlerInfo);
		
		$expected = array(
			'class' => 'AgaviReturnArrayConfigHandler',
			'parameters' => array(),
			'transformations' => array(
				'single' => array('confighandler-testing.xsl',),
				'compilation' => array(),
			),
			'validations' => array(
				'single' => array(
					'transformations_before' => array(
						'relax_ng' => array(),
						'schematron' => array(),
						'xml_schema' => array(),
					),
					'transformations_after' => array(
						'relax_ng' => array('confighandler-testing.rng'),
						'schematron' => array(),
						'xml_schema' => array(),
					),
				),
				'compilation' => array(
					'transformations_before' => array(
						'relax_ng' => array(),
						'schematron' => array(),
						'xml_schema' => array(),
					),
					'transformations_after' => array(
						'relax_ng' => array(),
						'schematron' => array(),
						'xml_schema' => array(),
					),
				),
			),
		);
		$handlerInfo = AgaviTestingConfigCache::getHandlerInfo('confighandler-testing');
		$this->assertEquals($expected, $handlerInfo);
	}
	
	public function testTicket931()
	{
		$config = 'project/foo.xml';
		$context = 'with/slash';
		$cachename = AgaviConfigCache::getCacheName($config, $context);
		
		$expected = AgaviConfig::get('core.cache_dir').DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR;
		$expected .= 'foo.xml';
		$expected .= '_'.preg_replace('/[^\w-_]/i', '_', AgaviConfig::get('core.environment'));
		$expected .= '_'.preg_replace('/[^\w-_]/i', '_', $context).'_';
		$expected .= sha1($config.'_'.AgaviConfig::get('core.environment').'_'.$context).'.php'; 
		
		$this->assertEquals($expected, $cachename);
	}
	
	public function testTicket932()
	{
		$config1 = 'project/foo.xml';
		$config2 = 'project_foo.xml';
		
		$this->assertNotEquals(AgaviConfigCache::getCacheName($config1), AgaviConfigCache::getCacheName($config2));
	}
	
	public function testTicket941()
	{
		if(!extension_loaded('xdebug')) {
			$this->markTestSkipped('This test check for an infinite loop, you need xdebug as protection.');
		}
		
		$config = AgaviConfig::get('core.module_dir').'/Default/config/config_handlers.xml';
		AgaviTestingConfigCache::addConfigHandlersFile($config);
		AgaviConfigCache::checkConfig(AgaviConfig::get('core.module_dir').'/Default/config/autoload.xml');
	}
}
<?php
require_once dirname(__FILE__) . '/../mockContext.php';

class ConfigCacheTest extends UnitTestCase
{
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


	public function testCheckConfig()
	{
		// We should be throwing exceptions if the config is bunk/unreadable
		try {
			ConfigCache::checkConfig('a file that doesnt exist');
		} catch (ConfigurationException $e) {
			$this->pass('Successfully caught configuration exception');
		}
		$this->assertTrue($e, 'Did not get expected ConfigurationException?');
	}

	public function testclear()
	{
		$dummyfile = AG_CACHE_DIR . '/dummyfile.ini.php';
		file_put_contents($dummyfile, 'Dummy file created in ' . __FILE__ . ' - ' . date('Y/m/d'));
		$this->assertTrue( file_exists($dummyfile) );
		ConfigCache::clear();
		$this->assertFalse( file_exists($dummyfile) );
		
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

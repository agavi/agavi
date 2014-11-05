<?php
require_once(__DIR__ . '/ConfigHandlerTestBase.php');

class MyAutoloader {
	public static $classes;
	public static $namespaces;
	
	public static function addClasses($classes) {
		static::$classes = $classes;
	}
	
	public static function addNamespaces($namespaces) {
		static::$namespaces = $namespaces;
	}
}

class AgaviAutoloadConfigHandlerTest extends ConfigHandlerTestBase
{
	protected function runHandler($environment = null) {
		$ACH = new AgaviAutoloadConfigHandler();

		$document = $this->parseConfiguration(
			AgaviConfig::get('core.config_dir') . '/tests/autoload_simple.xml',
			AgaviConfig::get('core.agavi_dir') . '/config/xsl/autoload.xsl',
			$environment
		);
		// AgaviAutoloader will have all of Agavi's as well, so let's replace it with our "mock"
		$code = str_replace('AgaviAutoloader::', 'MyAutoloader::', $ACH->execute($document));
		$this->includeCode($code);
	}
	
	public function testBasic()
	{
		$this->runHandler();
		$expected = array(
			'AgaviConfigAutoloadClass1' => AgaviConfig::get('core.app_dir') . '/lib/config/autoload/AgaviConfigAutoloadClass1.class.php',
			'AgaviConfigAutoloadClass2' => AgaviConfig::get('core.app_dir') . '/lib/config/autoload/AgaviConfigAutoloadClass2.class.php',
			'AgaviConfigAutoloadClass3' => AgaviConfig::get('core.app_dir') . '/lib/config/autoload/AgaviConfigAutoloadClass3.class.php',
		);

		$this->assertEquals($expected, MyAutoloader::$classes);
		
		$expected = array(
			'Agavi\TestAbsolute' => AgaviConfig::get('core.app_dir') . '/lib/config/autoload',
			'Agavi\TestRelative' => AgaviConfig::get('core.app_dir') . '/lib/config/autoload',
		);

		$this->assertEquals($expected, MyAutoloader::$namespaces);
	}

	public function testOverwrite()
	{
		$this->runHandler('test-overwrite');
		$expected = array(
			'AgaviConfigAutoloadClass1' => AgaviConfig::get('core.app_dir') . '/lib/config/autoload/AgaviConfigAutoloadClass1.class.php',
			'AgaviConfigAutoloadClass2' => AgaviConfig::get('core.app_dir') . '/lib/config/autoload/AgaviConfigAutoloadClass3.class.php',
			'AgaviConfigAutoloadClass3' => AgaviConfig::get('core.app_dir') . '/lib/config/autoload/AgaviConfigAutoloadClass3.class.php',
		);

		$this->assertEquals($expected, MyAutoloader::$classes);
		
		$expected = array(
			'Agavi\TestAbsolute' => AgaviConfig::get('core.app_dir') . '/lib/config',
			'Agavi\TestRelative' => AgaviConfig::get('core.app_dir') . '/lib/config/autoload',
		);

		$this->assertEquals($expected, MyAutoloader::$namespaces);
	}

	/**
	 * @expectedException AgaviParseException
	 */
	public function testClassMissing() {
		$this->runHandler('test-class-missing');
	}

	/**
	 * @expectedException AgaviParseException
	 */
	public function testNamespaceMissing() {
		$this->runHandler('test-namespace-missing');
	}

}
?>
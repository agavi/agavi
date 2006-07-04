<?php
require_once(dirname(__FILE__) . '/ConfigHandlerTestBase.php');

class AutoloadConfigHandlerTest extends ConfigHandlerTestBase
{
	public function testAutoloadHandler()
	{
		$ACH = new AgaviAutoloadConfigHandler();

		$oldAl = Agavi::$autoloads;
		Agavi::$autoloads = array();
		$this->includeCode($ACH->execute(AgaviConfig::get('core.config_dir') . '/tests/autoload_simple.xml'));
		$expected = array(
			'TestClass1' => AgaviConfig::get('core.webapp_dir') . '/lib/config/autoload/test/Class1.class.php',
			'TestClass2' => AgaviConfig::get('core.webapp_dir') . '/lib/config/autoload/Test2.class.php',
			'TestClass3' => AgaviConfig::get('core.webapp_dir') . '/AutoloadHandlerTestClass.class.php',
		);
		$got = Agavi::$autoloads;

		Agavi::$autoloads = $oldAl;

		$this->assertEquals($expected, $got);
	}


}
?>
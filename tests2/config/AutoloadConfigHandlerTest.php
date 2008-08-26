<?php
require_once(dirname(__FILE__) . '/ConfigHandlerTestBase.php');

class AutoloadConfigHandlerTest extends ConfigHandlerTestBase
{
	public function testAutoloadHandler()
	{
		$ACH = new AgaviAutoloadConfigHandler();

		$got = $this->includeCode($ACH->execute(AgaviConfig::get('core.config_dir') . '/tests/autoload_simple.xml'));
		$expected = array(
			'TestClass1' => AgaviConfig::get('core.app_dir') . '/lib/config/autoload/test/Class1.class.php',
			'TestClass2' => AgaviConfig::get('core.app_dir') . '/lib/config/autoload/Test2.class.php',
			'TestClass3' => AgaviConfig::get('core.app_dir') . '/AutoloadHandlerTestClass.class.php',
		);
		
		$this->assertEquals($expected, $got);
	}


}
?>
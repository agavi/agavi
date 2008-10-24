<?php
require_once(dirname(__FILE__) . '/ConfigHandlerTestBase.php');

class AutoloadConfigHandlerTest extends ConfigHandlerTestBase
{
	public function testAutoloadHandler()
	{
		$ACH = new AgaviAutoloadConfigHandler();

		$document = AgaviXmlConfigParser::run(
			AgaviConfig::get('core.config_dir') . '/tests/autoload_simple.xml',
			'',
			'',
			array(
				AgaviXmlConfigParser::STAGE_SINGLE => array(
					AgaviConfig::get('core.agavi_dir') . '/config/xsl/autoload.xsl',
				),
				AgaviXmlConfigParser::STAGE_COMPILATION => array(
				),
			),
			array(
				AgaviXmlConfigParser::STAGE_SINGLE => array(
					AgaviXmlConfigParser::STEP_TRANSFORMATIONS_BEFORE => array(
					),
					AgaviXmlConfigParser::STEP_TRANSFORMATIONS_AFTER => array(
						AgaviXmlConfigParser::VALIDATION_TYPE_RELAXNG => array(
							AgaviConfig::get('core.agavi_dir') . '/config/rng/autoload.rng',
						),
					),
				),
				AgaviXmlConfigParser::STAGE_COMPILATION => array(
					AgaviXmlConfigParser::STEP_TRANSFORMATIONS_BEFORE => array(),
					AgaviXmlConfigParser::STEP_TRANSFORMATIONS_AFTER => array()
				),
			)
		);

		$got = $this->includeCode($ACH->execute($document));
		$expected = array(
			'TestClass1' => AgaviConfig::get('core.app_dir') . '/lib/config/autoload/test/Class1.class.php',
			'TestClass2' => AgaviConfig::get('core.app_dir') . '/lib/config/autoload/Test2.class.php',
			'TestClass3' => AgaviConfig::get('core.app_dir') . '/AutoloadHandlerTestClass.class.php',
		);

		$this->assertEquals($expected, $got);
	}


}
?>
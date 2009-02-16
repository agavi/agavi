<?php
require_once(dirname(__FILE__) . '/ConfigHandlerTestBase.php');

class CHCHTestHandler extends AgaviConfigHandler
{
	public	$validationFile,
					$parser,
					$parameters;

	public function initialize($vf = null, $parser = null, $params = array())
	{
		$this->validationFile = $vf;
		$this->parser = $parser;
		$this->parameters = $params;
	}

	public function execute($config, $context = null)
	{
	}
}

class ConfigHandlersConfigHandlerTest extends ConfigHandlerTestBase
{

	public function testConfigHandlersConfigHandler()
	{
		$hf = AgaviToolkit::normalizePath(AgaviConfig::get('core.config_dir') . '/routing.xml');
		$CHCH = new AgaviConfigHandlersConfigHandler();

		$document = AgaviXmlConfigParser::run(
			AgaviConfig::get('core.config_dir') . '/tests/config_handlers.xml',
			'',
			'',
			array(
				AgaviXmlConfigParser::STAGE_SINGLE => array(
					AgaviConfig::get('core.agavi_dir') . '/config/xsl/config_handlers.xsl',
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
							AgaviConfig::get('core.agavi_dir') . '/config/rng/config_handlers.rng',
						),
					),
				),
				AgaviXmlConfigParser::STAGE_COMPILATION => array(
					AgaviXmlConfigParser::STEP_TRANSFORMATIONS_BEFORE => array(),
					AgaviXmlConfigParser::STEP_TRANSFORMATIONS_AFTER => array()
				),
			)
		);
		$file = $this->getIncludeFile($CHCH->execute($document));
		$handlers = include($file);
		unlink($file);

		$this->assertSame(1, count($handlers));
		$this->assertTrue(isset($handlers[$hf]));
		$this->assertSame('CHCHTestHandler', $handlers[$hf]['class']);
		$this->assertSame(AgaviConfig::get('core.agavi_dir') . '/config/xsd/routing.xsd', $handlers[$hf]['validations']['single']['transformations_before']['xml_schema'][0]);
		$this->assertSame(array('foo' => 'bar', 'dir' => AgaviConfig::get('core.agavi_dir')) , $handlers[$hf]['parameters']);
	}

}
?>
<?php
require_once(__DIR__ . '/ConfigHandlerTestBase.php');

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

class AgaviConfigHandlersConfigHandlerTest extends ConfigHandlerTestBase
{

	public function testConfigHandlersConfigHandler()
	{
		$hf = AgaviToolkit::normalizePath(AgaviConfig::get('core.config_dir') . '/routing.xml');
		$CHCH = new AgaviConfigHandlersConfigHandler();

		$document = $this->parseConfiguration(
			AgaviConfig::get('core.config_dir') . '/tests/config_handlers.xml',
			AgaviConfig::get('core.agavi_dir') . '/config/xsl/config_handlers.xsl'
		);

		$file = $this->getIncludeFile($CHCH->execute($document));
		$handlers = include($file);
		unlink($file);

		$this->assertCount(1, $handlers);
		$this->assertTrue(isset($handlers[$hf]));
		$this->assertSame('CHCHTestHandler', $handlers[$hf]['class']);
		$this->assertSame(AgaviConfig::get('core.agavi_dir') . '/config/xsd/routing.xsd', $handlers[$hf]['validations']['single']['transformations_after']['xml_schema'][0]);
		$this->assertSame(array('foo' => 'bar', 'dir' => AgaviConfig::get('core.agavi_dir')) , $handlers[$hf]['parameters']);
	}

}
?>
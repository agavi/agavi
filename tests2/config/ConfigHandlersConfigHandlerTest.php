<?php
require_once(dirname(__FILE__) . '/ConfigHandlerTestBase.php');

class CHCHTestHandler extends AgaviConfigHandler
{
	public	$validationFile,
					$parser,
					$parameters;

	public function initialize($vf=null, $parser = null, $params=array())
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
	protected static $handlers = array();

	public function testConfigHandlersConfigHandler()
	{
		$hf = AgaviToolkit::normalizePath(AgaviConfig::get('core.config_dir') . '/routing.xml');
		$CHCH = new AgaviConfigHandlersConfigHandler();

		$file = $this->getIncludeFile($CHCH->execute(AgaviConfig::get('core.config_dir') . '/tests/config_handlers.xml'));
		include($file);
		unlink($file);

		$this->assertSame(1, count(self::$handlers));
		$this->assertTrue(isset(self::$handlers[$hf]));
		$this->assertSame('CHCHTestHandler', self::$handlers[$hf]['class']);
		$this->assertSame(AgaviConfig::get('core.agavi_dir') . '/config/xsd/routing.xsd', self::$handlers[$hf]['validation']['xml_schema'][0]);
		$this->assertSame(array('foo' => 'bar', 'dir' => AgaviConfig::get('core.agavi_dir')) , self::$handlers[$hf]['parameters']);
	}

}
?>
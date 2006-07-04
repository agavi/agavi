<?php
require_once(dirname(__FILE__) . '/ConfigHandlerTestBase.php');

class CHCHTestHandler extends AgaviConfigHandler
{
	public	$validationFile,
					$parameters;

	public function initialize($vf, $params)
	{
		$this->validationFile = $vf;
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
		$hf = AgaviConfig::get('core.config_dir') . '/routing.xml';
		$CHCH = new AgaviConfigHandlersConfigHandler();

		$file = $this->getIncludeFile($CHCH->execute(AgaviConfig::get('core.config_dir') . '/tests/config_handlers.xml'));
		include($file);
		unlink($file);

		$this->assertSame(1, count(self::$handlers));
		$this->assertTrue(isset(self::$handlers[$hf]));
		$this->assertType('CHCHTestHandler', self::$handlers[$hf]);
		$this->assertSame(AgaviConfig::get('core.agavi_dir') . '/config/xsd/routing.xsd', self::$handlers[$hf]->validationFile);
		$this->assertSame(array('foo' => 'bar', 'dir' => AgaviConfig::get('core.agavi_dir')) , self::$handlers[$hf]->parameters);
	}

}
?>
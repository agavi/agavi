<?php
require_once(dirname(__FILE__) . '/ConfigHandlerTestBase.php');

class TestLogger extends AgaviLogger
{
	const ERROR = 1;
	const INFO = 2;
	public $appenders;
	public $level;

	public function setAppender($name, AgaviLoggerAppender $appender)
	{
		$this->appenders[$name] = $appender;
	}

	public function setLevel($level)
	{
		$this->level = $level;
	}
}

class TestLogger1 extends TestLogger { }
class TestLogger2 extends TestLogger { }
class TestLogger3 extends TestLogger { }

class TestAppender extends AgaviLoggerAppender
{
	public $params = null;
	public $layout = null;

	public function initialize(AgaviContext $context, array $params = array())
	{
		$this->params = $params;
	}

	public function setLayout(AgaviLoggerLayout $layout)
	{
		$this->layout = $layout;
	}

	public function shutdown() {}
	public function write(AgaviLoggerMessage $message) {}
}

class TestAppender1 extends TestAppender { }
class TestAppender2 extends TestAppender { }
class TestAppender3 extends TestAppender { }

class TestLayout extends AgaviLoggerLayout
{
	public $params = null;

	public function initialize(AgaviContext $context, array $params = array())
	{
		$this->params = $params;
	}
	public function format(AgaviLoggerMessage $message) {}
}

class TestLayout1 extends TestLayout { }
class TestLayout2 extends TestLayout { }


class LoggingConfigHandlerTest extends ConfigHandlerTestBase
{
	protected $context;

	public function setUp()
	{
		$this->context = AgaviContext::getInstance('test');
	}

	/**
	 * Proxied because we include a compiled config that assumes it runs in the LM
	 *
	 * @see      AgaviLoggerManager::setLogger()
	 */
	protected function setLogger($name, AgaviILogger $logger)
	{
		return $this->context->getLoggerManager()->setLogger($name, $logger);
	}
	
	/**
	 * Proxied because we include a compiled config that assumes it runs in the LM
	 *
	 * @see      AgaviLoggerManager::setDefaultLoggerName()
	 */
	public function setDefaultLoggerName($name)
	{
		return $this->context->getLoggerManager()->setDefaultLoggerName($name);
	}
	
	public function testLoggingConfigHandler()
	{
		// taken from a compiled config_handlers.xml
		$handlerInfo = array (
			'class' => 'AgaviLoggingConfigHandler',
			'parameters' => 
			array (
			),
			'transformations' => 
			array (
				'single' => 
				array (
					0 => AgaviConfig::get('core.agavi_dir') . '/config/xsl/logging.xsl',
				),
				'compilation' => 
				array (
				),
			),
			'validations' => 
			array (
				'single' => 
				array (
					'transformations_before' => 
					array (
						'relax_ng' => 
						array (
						),
						'schematron' => 
						array (
						),
						'xml_schema' => 
						array (
						),
					),
					'transformations_after' => 
					array (
						'relax_ng' => 
						array (
						),
						'schematron' => 
						array (
						),
						'xml_schema' => 
						array (
							0 => AgaviConfig::get('core.agavi_dir') . '/config/xsd/logging.xsd',
						),
					),
				),
				'compilation' => 
				array (
					'transformations_before' => 
					array (
						'relax_ng' => 
						array (
						),
						'schematron' => 
						array (
						),
						'xml_schema' => 
						array (
						),
					),
					'transformations_after' => 
					array (
						'relax_ng' => 
						array (
						),
						'schematron' => 
						array (
						),
						'xml_schema' => 
						array (
						),
					),
				),
			),
		);
		
		$LCH = new AgaviLoggingConfigHandler();
		// a new-style config handler
		// it does not parse the config itself; instead, it is given a complete and merged DOM document
		$doc = AgaviXmlConfigParser::run(AgaviConfig::get('core.config_dir') . '/tests/logging.xml', AgaviConfig::get('core.environment'), null, $handlerInfo['transformations'], $handlerInfo['validations']);
		$LCH->initialize(null, $handlerInfo['parameters']);
		$cfg = $this->includeCode($LCH->execute($doc));

		$test1 = $this->context->getLoggerManager()->getLogger('test1');
		$test2 = $this->context->getLoggerManager()->getLogger('test2');
		$test3 = $this->context->getLoggerManager()->getLogger('test3');

		$this->assertInstanceOf('TestLogger1', $test1);
		$this->assertSame(TestLogger::INFO, $test1->level);
		$this->assertInstanceOf('TestAppender1', $test1->appenders['appender1']);
		$this->assertInstanceOf('TestAppender2', $test1->appenders['appender2']);
		$this->assertReference($test1->appenders['appender1'], $test2->appenders['appender1']);
		$this->assertReference($test1->appenders['appender2'], $test2->appenders['appender2']);


		$this->assertInstanceOf('TestLogger2', $test2);
		$this->assertSame(TestLogger::ERROR, $test2->level);
		$this->assertInstanceOf('TestAppender1', $test2->appenders['appender1']);
		$this->assertInstanceOf('TestAppender2', $test2->appenders['appender2']);
		$this->assertInstanceOf('TestAppender3', $test2->appenders['appender3']);

		$this->assertInstanceOf('TestLogger3', $test3);
		$this->assertSame(TestLogger::INFO | TestLogger::ERROR, $test3->level);

		$a1 = $test2->appenders['appender1'];
		$a2 = $test2->appenders['appender2'];
		$a3 = $test2->appenders['appender3'];

		$this->assertInstanceOf('TestLayout1', $a1->layout);
		$this->assertSame(array(
			'param1' => 'value1',
			'param2' => 'value2',
			),
			$a1->params
		);


		$this->assertInstanceOf('TestLayout1', $a2->layout);
		$this->assertEquals(array(), $a2->params);


		$this->assertInstanceOf('TestLayout2', $a3->layout);
		$this->assertSame(array(
			'file' => AgaviConfig::get('core.app_dir') . '/log/myapp.log',
			),
			$a3->params
		);


		$this->assertReference($a1->layout, $a2->layout);

		$l1 = $a1->layout;
		$l2 = $a3->layout;

		$this->assertSame(array(
			'param1' => 'value1',
			'param2' => 'value2',
			),
			$l1->params
		);

		$this->assertSame(array(), $l2->params);

	}
}
?>
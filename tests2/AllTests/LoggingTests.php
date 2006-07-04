<?
require_once(dirname(__FILE__) . '/../logging/AppenderTest.php');
require_once(dirname(__FILE__) . '/../logging/FileAppenderTest.php');
require_once(dirname(__FILE__) . '/../logging/LayoutTest.php');
require_once(dirname(__FILE__) . '/../logging/LoggerManagerTest.php');
require_once(dirname(__FILE__) . '/../logging/MessageTest.php');
require_once(dirname(__FILE__) . '/../logging/PassthruLayoutTest.php');

class LoggingTests
{
	public static function suite()
	{
		$suite = new PHPUnit2_Framework_TestSuite('logging');

		$suite->addTestSuite('AppenderTest');
		$suite->addTestSuite('FileAppenderTest');
		$suite->addTestSuite('LayoutTest');
		$suite->addTestSuite('LoggerManagerTest');
		$suite->addTestSuite('MessageTest');
		$suite->addTestSuite('PassthruLayoutTest');

		return $suite;
	}
}

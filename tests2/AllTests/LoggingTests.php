<?php
require_once(__DIR__ . '/../logging/AppenderTest.php');
require_once(__DIR__ . '/../logging/FileAppenderTest.php');
require_once(__DIR__ . '/../logging/LayoutTest.php');
require_once(__DIR__ . '/../logging/LoggerManagerTest.php');
require_once(__DIR__ . '/../logging/MessageTest.php');
require_once(__DIR__ . '/../logging/PassthruLayoutTest.php');

class LoggingTests
{
	public static function suite()
	{
		$suite = new PHPUnit_Framework_TestSuite('logging');

		$suite->addTestSuite('AppenderTest');
		$suite->addTestSuite('FileAppenderTest');
		$suite->addTestSuite('LayoutTest');
		$suite->addTestSuite('LoggerManagerTest');
		$suite->addTestSuite('MessageTest');
		$suite->addTestSuite('PassthruLayoutTest');

		return $suite;
	}
}

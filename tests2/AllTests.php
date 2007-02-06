<?php

date_default_timezone_set('Europe/Berlin');
error_reporting(E_ALL | E_STRICT);

if(!defined('PHPUnit_MAIN_METHOD')) {
	define('PHPUnit_MAIN_METHOD', 'AllTests::main');
}

require_once 'PHPUnit/Framework/TestSuite.php';
require_once 'PHPUnit/TextUI/TestRunner.php';


require_once('AgaviTestCase.class.php');

$testDir = dirname(__FILE__);
include($testDir . '/../src/agavi.php');
AgaviConfig::set("tests.dir", $testDir); // where the main tests dir resides
AgaviConfig::set('core.app_dir', AgaviConfig::get('tests.dir') . "/sandbox2");

AgaviConfig::set('core.default_context', 'test');
Agavi::bootstrap('testing');
AgaviContext::getInstance('test');
set_time_limit(0);

class AllTests
{
	public static function main()
	{
		$reportDir = dirname(__FILE__) . '/test_report/';
		if(version_compare(PHPUnit_Runner_Version::id(), '3.0.0', '<')) {
			PHPUnit_TextUI_TestRunner::run(self::suite(), $reportDir . 'coverage.xml', $reportDir . 'coverage.html', $reportDir . 'coverage.txt', $reportDir . 'report.html', $reportDir . 'report.txt', $reportDir . 'report.xml');
		} else {
			PHPUnit_TextUI_TestRunner::run(self::suite(), null, $reportDir);
		}
	}

	public static function suite()
	{
		$testDir = dirname(__FILE__) . '/AllTests';

		$suite = new PHPUnit_Framework_TestSuite('Agavi Framework');

		require_once($testDir . '/ActionTests.php');
		$suite->addTest(ActionTests::suite());

		require_once($testDir . '/ConfigTests.php');
		$suite->addTest(ConfigTests::suite());

		require_once($testDir . '/ControllerTests.php');
		$suite->addTest(ControllerTests::suite());

		require_once($testDir . '/CoreTests.php');
		$suite->addTest(CoreTests::suite());

		require_once($testDir . '/DatabaseTests.php');
		$suite->addTest(DatabaseTests::suite());

		require_once($testDir . '/DateTests.php');
		$suite->addTest(DateTests::suite());

		require_once($testDir . '/LoggingTests.php');
		$suite->addTest(LoggingTests::suite());

		require_once($testDir . '/ModelTests.php');
		$suite->addTest(ModelTests::suite());

		require_once($testDir . '/RendererTests.php');
		$suite->addTest(RendererTests::suite());

		require_once($testDir . '/RequestTests.php');
		$suite->addTest(RequestTests::suite());

		require_once($testDir . '/ResponseTests.php');
		$suite->addTest(ResponseTests::suite());

		require_once($testDir . '/RoutingTests.php');
		$suite->addTest(RoutingTests::suite());

		require_once($testDir . '/UserTests.php');
		$suite->addTest(UserTests::suite());

		require_once($testDir . '/UtilTests.php');
		$suite->addTest(UtilTests::suite());

//		require_once($testDir . '/ValidatorTests.php');
//		$suite->addTest(ValidatorTests::suite());

		require_once($testDir . '/ViewTests.php');
		$suite->addTest(ViewTests::suite());

		return $suite;
	}
}


if(PHPUnit_MAIN_METHOD == 'AllTests::main') {
	AllTests::main();
}
?>

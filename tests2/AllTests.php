<?php

date_default_timezone_set('Europe/Berlin');
error_reporting(E_ALL | E_STRICT);

if(!defined('PHPUnit_MAIN_METHOD')) {
	define('PHPUnit_MAIN_METHOD', 'AllTests::main');
}


$here = realpath(dirname(dirname(__FILE__)));

// add our bundled PHPUnit to include path (until a new release is out :D)
set_include_path($here . '/src/vendor' . PATH_SEPARATOR . get_include_path());

require_once 'PHPUnit/TextUI/TestRunner.php';

require_once('AgaviTestCase.class.php');

$testDir = dirname(__FILE__);
include($testDir . '/../src/agavi.php');
AgaviConfig::set('tests.dir', $testDir); // where the main tests dir resides
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
		PHPUnit_TextUI_TestRunner::run(self::suite(), array(
			'backupGlobals' => false,
			'backupStaticAttributes' => false,
		), $reportDir);
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
	// AllTests::main();
}
?>

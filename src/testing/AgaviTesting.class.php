<?php

class AgaviTesting
{
	/**
	 * @var        array An assoc array of classes and files used for autoloading.
	 */
	public static $autoloads = array(
		'AgaviActionTestCase'    => 'testing/AgaviActionTestCase.class.php',
		'AgaviFlowTestCase'      => 'testing/AgaviFlowTestCase.class.php',
		'AgaviFlowTestSuite'     => 'testing/AgaviFlowTestSuite.class.php',
		'AgaviFragmentTestCase'  => 'testing/AgaviFragmentTestCase.class.php',
		'AgaviFragmentTestSuite' => 'testing/AgaviFragmentTestSuite.class.php',
		'AgaviIFlowTestCase'     => 'testing/AgaviIFlowTestCase.interface.php',
		'AgaviIFragmentTestCase' => 'testing/AgaviIFragmentTestCase.interface.php',
		'AgaviIRemoteTestCase'   => 'testing/AgaviIRemoteTestCase.interface.php',
		'AgaviITestCase'         => 'testing/AgaviITestCase.interface.php',
		'AgaviIUnitTestCase'     => 'testing/AgaviIUnitTestCase.interface.php',
		'AgaviModelTestCase'     => 'testing/AgaviModelTestCase.class.php',
		'AgaviSeleniumTestCase'  => 'testing/AgaviSeleniumTestCase.class.php',
		'AgaviTestSuite'         => 'testing/AgaviTestSuite.class.php',
		'AgaviUnitTestCase'      => 'testing/AgaviUnitTestCase.class.php',
		'AgaviUnitTestSuite'     => 'testing/AgaviUnitTestSuite.class.php',
		'AgaviViewTestCase'      => 'testing/AgaviViewTestCase.class.php',
	);

	/**
	 * Handles autoloading of classes
	 *
	 * @param      string A class name.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public static function __autoload($class)
	{
		if(isset(self::$autoloads[$class])) {
			// class exists, let's include it
			require(AgaviConfig::get('core.agavi_dir') . '/' . self::$autoloads[$class]);
		}
	}

	/**
	 * Startup the Agavi core
	 *
	 * @param      string environment the environment to use for this session.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public static function bootstrap($environment = null)
	{
		// set up our __autoload
		spl_autoload_register(array('AgaviTesting', '__autoload'));

		if($environment === null) {
			// no env given? let's read one from testing.environment
			$environment = AgaviConfig::get('testing.environment');
		} elseif(AgaviConfig::has('testing.environment') && AgaviConfig::isReadonly('testing.environment')) {
			// env given, but testing.environment is read-only? then we must use that instead and ignore the given setting
			$environment = AgaviConfig::get('testing.environment');
		}
		
		if($environment === null) {
			// still no env? oh man...
			throw new Exception('You must supply an environment name to AgaviTesting::bootstrap() or set the name of the default environment to be used for testing in the configuration directive "testing.environment".');
		}
		
		// finally set the env to what we're really using now.
		AgaviConfig::set('testing.environment', $environment, true, true);
		
		ini_set('include_path', get_include_path().PATH_SEPARATOR.dirname(dirname(__FILE__)));

		$_ENV['AGAVI'] = AgaviConfig::toArray();
	}

	public function dispatch()
	{
		$suite = new AgaviUnitTestSuite('Foo');
		//require_once 'tests/unit/PriceFinderModelTest.php';
		//$test = new PriceFinderModelTest();
		//$test->setName('PriceFinder Model');
		//$suite->addTest($test);
		$suite->addTestFile('tests/unit/PriceFinderModelTest.php');


		// TODO: read test suites from xml or so

		$runner = PHPUnit_TextUI_TestRunner::run($suite);
	}
}

?>
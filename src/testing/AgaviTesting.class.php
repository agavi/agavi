<?php

class AgaviTesting
{
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
		
		// bootstrap the framework for autoload, config handlers etc.
		Agavi::bootstrap($environment);
		
		ini_set('include_path', get_include_path().PATH_SEPARATOR.dirname(dirname(__FILE__)));

		$GLOBALS['AGAVI_CONFIG'] = AgaviConfig::toArray();
	}

	public function dispatch()
	{
		$suite = new AgaviTestSuite('Foo');
		$suite->addTestFile('tests/unit/PriceFinderModelTest.php');
		
		$GLOBALS['__PHPUNIT_BOOTSTRAP'] = dirname(__FILE__).'/templates/AgaviBootstrap.tpl.php';

		// TODO: read test suites from xml or so

		$runner = PHPUnit_TextUI_TestRunner::run($suite);
	}
}

?>
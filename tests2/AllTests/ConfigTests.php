<?php
require_once(__DIR__ . '/../config/AgaviConfigTest.php');
require_once(__DIR__ . '/../config/AutoloadConfigHandlerTest.php');
require_once(__DIR__ . '/../config/CompileConfigHandlerTest.php');
require_once(__DIR__ . '/../config/ConfigCacheTest.php');
require_once(__DIR__ . '/../config/ConfigHandlersConfigHandlerTest.php');
require_once(__DIR__ . '/../config/ConfigHandlerTest.php');
require_once(__DIR__ . '/../config/ConfigValueHolderTest.php');
require_once(__DIR__ . '/../config/DatabaseConfigHandlerTest.php');
//require_once(__DIR__ . '/../config/FactoryConfigHandlerTest.php');
require_once(__DIR__ . '/../config/FilterConfigHandlerTest.php');
require_once(__DIR__ . '/../config/LoggingConfigHandlerTest.php');
require_once(__DIR__ . '/../config/ReturnArrayConfigHandlerTest.php');
require_once(__DIR__ . '/../config/RbacdefinitionConfigHandlerTest.php');

class ConfigTests
{
	public static function suite()
	{
		$suite = new PHPUnit_Framework_TestSuite('config');

		$suite->addTestSuite('AgaviConfigTest');
		$suite->addTestSuite('AutoloadConfigHandlerTest');
		$suite->addTestSuite('CompileConfigHandlerTest');
		$suite->addTestSuite('ConfigCacheTest');
		$suite->addTestSuite('ConfigHandlersConfigHandlerTest');
		$suite->addTestSuite('ConfigHandlerTest');
		$suite->addTestSuite('ConfigValueHolderTest');
		$suite->addTestSuite('DatabaseConfigHandlerTest');
		// $suite->addTestSuite('FactoryConfigHandlerTest');
		$suite->addTestSuite('FilterConfigHandlerTest');
		$suite->addTestSuite('LoggingConfigHandlerTest');
		$suite->addTestSuite('ReturnArrayConfigHandlerTest');
		$suite->addTestSuite('RbacDefinitionConfigHandlerTest');
		return $suite;
	}
}

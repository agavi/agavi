<?
require_once(dirname(__FILE__) . '/../validator/ErrorManagerTest.php');
require_once(dirname(__FILE__) . '/../validator/DependencyManagerTest.php');
require_once(dirname(__FILE__) . '/../validator/ValidatorTest.php');
require_once(dirname(__FILE__) . '/../validator/ValidatorManagerTest.php');
require_once(dirname(__FILE__) . '/../validator/OperatorValidatorTest.php');
require_once(dirname(__FILE__) . '/../validator/AndoperatorValidatorTest.php');
require_once(dirname(__FILE__) . '/../validator/OroperatorValidatorTest.php');
require_once(dirname(__FILE__) . '/../validator/XoroperatorValidatorTest.php');
require_once(dirname(__FILE__) . '/../validator/NotoperatorValidatorTest.php');

class ValidatorTests
{
	public static function suite()
	{
		$suite = new PHPUnit2_Framework_TestSuite('validator');

		$suite->addTestSuite('ErrorManagerTest');
		$suite->addTestSuite('DependencyManagerTest');
		$suite->addTestSuite('ValidatorTest');
		$suite->addTestSuite('ValidatorManagerTest');
		$suite->addTestSuite('OperatorValidatorTest');
		$suite->addTestSuite('AndoperatorValidatorTest');
		$suite->addTestSuite('OroperatorValidatorTest');
		$suite->addTestSuite('XoroperatorValidatorTest');
		$suite->addTestSuite('NotoperatorValidatorTest');

		return $suite;
	}
}

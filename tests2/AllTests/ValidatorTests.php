<?
require_once(dirname(__FILE__) . '/../validator/DateValidatorTest.php');
require_once(dirname(__FILE__) . '/../validator/EmailValidatorTest.php');
require_once(dirname(__FILE__) . '/../validator/NumberValidatorTest.php');
require_once(dirname(__FILE__) . '/../validator/RegexValidatorTest.php');
require_once(dirname(__FILE__) . '/../validator/StringValidatorTest.php');
require_once(dirname(__FILE__) . '/../validator/ValidatorManagerTest.php');
require_once(dirname(__FILE__) . '/../validator/ValidatorTest.php');
require_once(dirname(__FILE__) . '/../validator/ErrorManagerTest.php');
require_once(dirname(__FILE__) . '/../validator/DependencyManagerTest.php');

class ValidatorTests
{
	public static function suite()
	{
		$suite = new PHPUnit2_Framework_TestSuite('validator');

		$suite->addTestSuite('ErrorManagerTest');
		$suite->addTestSuite('DependencyManagerTest');
		$suite->addTestSuite('ValidatorTest');
		$suite->addTestSuite('ValidatorManagerTest');
		//$suite->addTestSuite('DateValidatorTest');
		//$suite->addTestSuite('EmailValidatorTest');
		//$suite->addTestSuite('NumberValidatorTest');
		//$suite->addTestSuite('RegexValidatorTest');
		//$suite->addTestSuite('StringValidatorTest');

		return $suite;
	}
}

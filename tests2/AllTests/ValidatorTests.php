<?php
//require_once(__DIR__ . '/../validator/ErrorManagerTest.php');
//require_once(__DIR__ . '/../validator/DependencyManagerTest.php');
//require_once(__DIR__ . '/../validator/ValidatorTest.php');
//require_once(__DIR__ . '/../validator/ValidatorManagerTest.php');
//require_once(__DIR__ . '/../validator/OperatorValidatorTest.php');
//require_once(__DIR__ . '/../validator/AndoperatorValidatorTest.php');
//require_once(__DIR__ . '/../validator/OroperatorValidatorTest.php');
//require_once(__DIR__ . '/../validator/XoroperatorValidatorTest.php');
//require_once(__DIR__ . '/../validator/NotoperatorValidatorTest.php');

require_once(__DIR__ . '/../validator/EmailValidatorTest.php');
require_once(__DIR__ . '/../validator/NumberValidatorTest.php');
//require_once(__DIR__ . '/../validator/DateValidatorTest.php');

class ValidatorTests
{
	public static function suite()
	{
		$suite = new PHPUnit_Framework_TestSuite('validator');

		//$suite->addTestSuite('ErrorManagerTest');
		//$suite->addTestSuite('DependencyManagerTest');
		//$suite->addTestSuite('ValidatorTest');
		//$suite->addTestSuite('ValidatorManagerTest');
		//$suite->addTestSuite('OperatorValidatorTest');
		//$suite->addTestSuite('AndoperatorValidatorTest');
		//$suite->addTestSuite('OroperatorValidatorTest');
		//$suite->addTestSuite('XoroperatorValidatorTest');
		//$suite->addTestSuite('NotoperatorValidatorTest');
		$suite->addTestSuite('EmailValidatorTest');
		$suite->addTestSuite('NumberValidatorTest');
		//$suite->addTestSuite('StringValidatorTest');
		//$suite->addTestSuite('DateValidatorTest');

		return $suite;
	}
}

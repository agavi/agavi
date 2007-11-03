<?php
//require_once(dirname(__FILE__) . '/../validator/ErrorManagerTest.php');
//require_once(dirname(__FILE__) . '/../validator/DependencyManagerTest.php');
//require_once(dirname(__FILE__) . '/../validator/ValidatorTest.php');
//require_once(dirname(__FILE__) . '/../validator/ValidatorManagerTest.php');
//require_once(dirname(__FILE__) . '/../validator/OperatorValidatorTest.php');
//require_once(dirname(__FILE__) . '/../validator/AndoperatorValidatorTest.php');
//require_once(dirname(__FILE__) . '/../validator/OroperatorValidatorTest.php');
//require_once(dirname(__FILE__) . '/../validator/XoroperatorValidatorTest.php');
//require_once(dirname(__FILE__) . '/../validator/NotoperatorValidatorTest.php');

require_once(dirname(__FILE__) . '/../validator/EmailValidatorTest.php');
require_once(dirname(__FILE__) . '/../validator/NumberValidatorTest.php');
//require_once(dirname(__FILE__) . '/../validator/DateValidatorTest.php');

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

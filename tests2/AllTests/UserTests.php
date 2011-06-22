<?php
require_once(__DIR__ . '/../user/UserTest.php');
require_once(__DIR__ . '/../user/SecurityUserTest.php');
require_once(__DIR__ . '/../user/RbacSecurityUserTest.php');

class UserTests
{
	public static function suite()
	{
		$suite = new PHPUnit_Framework_TestSuite('user');

		$suite->addTestSuite('UserTest');
		$suite->addTestSuite('SecurityUserTest');
		$suite->addTestSuite('RbacSecurityUserTest');

		return $suite;
	}
}

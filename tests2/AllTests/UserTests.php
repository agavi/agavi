<?php
require_once(dirname(__FILE__) . '/../user/UserTest.php');
require_once(dirname(__FILE__) . '/../user/SecurityUserTest.php');
require_once(dirname(__FILE__) . '/../user/RbacSecurityUserTest.php');

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

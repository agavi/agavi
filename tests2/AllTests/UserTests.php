<?
require_once(dirname(__FILE__) . '/../user/SecurityUserTest.php');
require_once(dirname(__FILE__) . '/../user/UserTest.php');

class UserTests
{
	public static function suite()
	{
		$suite = new PHPUnit2_Framework_TestSuite('user');

		$suite->addTestSuite('SecurityUserTest');
		$suite->addTestSuite('UserTest');

		return $suite;
	}
}

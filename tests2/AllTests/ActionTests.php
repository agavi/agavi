<?
require_once(dirname(__FILE__) . '/../action/ActionStackEntryTest.php');
require_once(dirname(__FILE__) . '/../action/ActionStackTest.php');
require_once(dirname(__FILE__) . '/../action/ActionTest.php');

class ActionTests
{
	public static function suite()
	{
		$suite = new PHPUnit2_Framework_TestSuite('action');

		$suite->addTestSuite('ActionTest');
		$suite->addTestSuite('ActionStackTest');
		$suite->addTestSuite('ActionStackEntryTest');
		return $suite;
	}
}

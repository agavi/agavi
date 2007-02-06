<?
require_once(dirname(__FILE__) . '/../action/ActionTest.php');

class ActionTests
{
	public static function suite()
	{
		$suite = new PHPUnit_Framework_TestSuite('action');

		$suite->addTestSuite('ActionTest');
		return $suite;
	}
}

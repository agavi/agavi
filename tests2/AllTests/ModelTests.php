<?
require_once(dirname(__FILE__) . '/../model/ModelTest.php');

class ModelTests
{
	public static function suite()
	{
		$suite = new PHPUnit_Framework_TestSuite('model');

		$suite->addTestSuite('ModelTest');

		return $suite;
	}
}

<?
require_once(dirname(__FILE__) . '/../controller/ControllerTest.php');

class ControllerTests
{
	public static function suite()
	{
		$suite = new PHPUnit_Framework_TestSuite('controller');

		$suite->addTestSuite('ControllerTest');
		return $suite;
	}
}

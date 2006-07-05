<?
require_once(dirname(__FILE__) . '/../routing/RoutingTest.php');

class RoutingTests
{
	public static function suite()
	{
		$suite = new PHPUnit2_Framework_TestSuite('routing');

		$suite->addTestSuite('RoutingTest');

		return $suite;
	}
}

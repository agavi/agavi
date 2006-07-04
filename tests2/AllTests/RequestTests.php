<?
require_once(dirname(__FILE__) . '/../request/RequestTest.php');

class RequestTests
{
	public static function suite()
	{
		$suite = new PHPUnit2_Framework_TestSuite('request');

		$suite->addTestSuite('RequestTest');

		return $suite;
	}
}

<?php
require_once(__DIR__ . '/../response/ResponseTest.php');
require_once(__DIR__ . '/../response/WebResponseTest.php');

class ResponseTests
{
	public static function suite()
	{
		$suite = new PHPUnit_Framework_TestSuite('response');

		$suite->addTestSuite('ResponseTest');
		$suite->addTestSuite('WebResponseTest');

		return $suite;
	}
}

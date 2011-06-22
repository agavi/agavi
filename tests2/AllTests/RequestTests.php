<?php
require_once(__DIR__ . '/../request/RequestTest.php');
require_once(__DIR__ . '/../request/WebRequestTest.php');

class RequestTests
{
	public static function suite()
	{
		$suite = new PHPUnit_Framework_TestSuite('request');

		$suite->addTestSuite('RequestTest');
		$suite->addTestSuite('WebRequestTest');

		return $suite;
	}
}

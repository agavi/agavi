<?php
require_once(dirname(__FILE__) . '/../request/RequestTest.php');
require_once(dirname(__FILE__) . '/../request/WebRequestTest.php');

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

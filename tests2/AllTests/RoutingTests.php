<?php
require_once(__DIR__ . '/../routing/RoutingTest.php');

require_once(__DIR__ . '/../routing/WebRoutingTest.php');

class RoutingTests
{
	public static function suite()
	{
		$suite = new PHPUnit_Framework_TestSuite('routing');

		$suite->addTestSuite('RoutingTest');
		$suite->addTestSuite('WebRoutingTest');

		return $suite;
	}
}

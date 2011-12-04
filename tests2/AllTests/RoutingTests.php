<?php
require_once(dirname(__FILE__) . '/../routing/RoutingTest.php');

require_once(dirname(__FILE__) . '/../routing/WebRoutingTest.php');

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

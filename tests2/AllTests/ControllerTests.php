<?php
require_once(__DIR__ . '/../controller/ControllerTest.php');

class ControllerTests
{
	public static function suite()
	{
		$suite = new PHPUnit_Framework_TestSuite('controller');

		$suite->addTestSuite('ControllerTest');
		return $suite;
	}
}

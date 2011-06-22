<?php
require_once(__DIR__ . '/../core/ContextTest.php');

class CoreTests
{
	public static function suite()
	{
		$suite = new PHPUnit_Framework_TestSuite('core');

		$suite->addTestSuite('ContextTest');
		return $suite;
	}
}

<?php
require_once(__DIR__ . '/../database/DatabaseManagerTest.php');

class DatabaseTests
{
	public static function suite()
	{
		$suite = new PHPUnit_Framework_TestSuite('database');

		$suite->addTestSuite('DatabaseManagerTest');

		return $suite;
	}
}

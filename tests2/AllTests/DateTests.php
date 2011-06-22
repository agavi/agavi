<?php
require_once(__DIR__ . '/../date/CalendarTest.php');
require_once(__DIR__ . '/../date/TimeZoneBoundaryTest.php');
require_once(__DIR__ . '/../date/TimeZoneTest.php');

class DateTests
{
	public static function suite()
	{
		$suite = new PHPUnit_Framework_TestSuite('date');

//		$suite->addTestSuite('CalendarTest');
		$suite->addTestSuite('TimeZoneBoundaryTest');
		$suite->addTestSuite('TimeZoneTest');
		return $suite;
	}
}

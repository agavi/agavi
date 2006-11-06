<?
require_once(dirname(__FILE__) . '/../date/CalendarTest.php');
require_once(dirname(__FILE__) . '/../date/TimeZoneBoundaryTest.php');
require_once(dirname(__FILE__) . '/../date/TimeZoneTest.php');

class DateTests
{
	public static function suite()
	{
		$suite = new PHPUnit2_Framework_TestSuite('date');

//		$suite->addTestSuite('CalendarTest');
		$suite->addTestSuite('TimeZoneBoundaryTest');
		$suite->addTestSuite('TimeZoneTest');
		return $suite;
	}
}

<?php

require_once(dirname(__FILE__) . '/BaseCalendarTest.php');

/**
 * Ported from ICU:
 *  icu/trunk/source/test/intltest/tzbdtest.cpp r19558
 */
class TimeZoneBoundaryTest extends BaseCalendarTest
{
	protected $ONE_SECOND;
	protected $ONE_MINUTE;
	protected $ONE_HOUR;
	protected $ONE_DAY;
	protected $ONE_YEAR;
	protected $SIX_MONTHS;

	protected $MONTH_LENGTH;
	protected $PST_1997_BEG;
	protected $PST_1997_END;
	protected $INTERVAL;

	public function setUp()
	{
		parent::setUp();
		$this->ONE_SECOND = 1000;
		$this->ONE_MINUTE = 60 * $this->ONE_SECOND;
		$this->ONE_HOUR   = 60 * $this->ONE_MINUTE;
		$this->ONE_DAY    = 24 * $this->ONE_HOUR;
		$this->ONE_YEAR   = floor(365.25 * $this->ONE_DAY);
		$this->SIX_MONTHS = $this->ONE_YEAR / 2;

		$this->MONTH_LENGTH = array(31, 29, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
		$this->PST_1997_BEG = 860320800000.0;
		$this->PST_1997_END = 877856400000.0;
		$this->INTERVAL = 10;


	}

	protected function findDaylightBoundaryUsingDate($d, $startMode, $expectedBoundary)
	{
		/* TODO: reenable
		if(dateToString(d, str).indexOf(startMode) == - 1) {
				logln(UnicodeString("Error: ") + startMode + " not present in " + str);
		}
		*/
		$min = $d;
		$max = $min + $this->SIX_MONTHS;
		while(($max - $min) > $this->INTERVAL) {
			$mid = ($min + $max) / 2;
			$s = $this->dateToString($mid);
			if(strpos($s, $startMode) !== false) {
				$min = $mid;
			} else {
				$max = $mid;
			}
		}
		$mindelta = $expectedBoundary - $min;
		$maxdelta = $max - $expectedBoundary;
		$this->assertTrue($mindelta >= 0 && $mindelta <= $this->INTERVAL && $maxdelta >= 0 && $maxdelta <= $this->INTERVAL, 'Expected boundary at ' . $this->dateToString($expectedBoundary));
	}

// -------------------------------------

	protected function findDaylightBoundaryUsingTimeZone($d, $startsInDST, $expectedBoundary, $tz = null)
	{
		if(!$tz) {
			$tz = $this->tm->getDefaultTimeZone();
		}
		$min = $d;
		$max = $min + $this->SIX_MONTHS;
		if($tz->inDaylightTime($d) != $startsInDST) {
			// this is not really a failure i think
			//$this->fail("FAIL: " . $tz->getId() . " inDaylightTime(" . $this->dateToString($d) . "/". $tz->inDaylightTime($d) .") != " . ($startsInDST ? "true" : "false"));
			$startsInDST = !$startsInDST;
		}
		if($tz->inDaylightTime($max) == $startsInDST) {
			$this->fail("FAIL: " . $tz->getId() . " inDaylightTime(" . $this->dateToString($max) . ") != " . ($startsInDST ? "true" : "false"));
			return;
		}

		while(($max - $min) > $this->INTERVAL) {
			$mid = ($min + $max) / 2;
			$isIn = $tz->inDaylightTime($mid);
			if($isIn == $startsInDST) {
				$min = $mid;
			} else {
				$max = $mid;
			}
		}
		$mindelta = $expectedBoundary - $min;
		$maxdelta = $max - $expectedBoundary;
		$this->assertTrue($mindelta >= 0 && $mindelta <= $this->INTERVAL && $maxdelta >= 0 && $maxdelta <= $this->INTERVAL);
}
 
// -------------------------------------
/*
UnicodeString*
TimeZoneBoundaryTest::showDate(int32_t l)
{
		return showDate(new Date(l));
}
// -------------------------------------
 
UnicodeString
TimeZoneBoundaryTest::showDate(UDate d)
{
		int32_t y, m, day, h, min, sec;
		dateToFields(d, y, m, day, h, min, sec);
		return UnicodeString("") + y + "/" + showNN(m + 1) + "/" +
				showNN(day) + " " + showNN(h) + ":" + showNN(min) +
				" \"" + dateToString(d) + "\" = " + uprv_floor(d+0.5);
}
 
// -------------------------------------
 
UnicodeString
TimeZoneBoundaryTest::showNN(int32_t n)
{
		UnicodeString nStr;
		if (n < 10) {
				nStr += UnicodeString("0", "");
		}
		return nStr + n;
}
*/
 
// -------------------------------------
 
	protected function verifyDST($d, $time_zone, $expUseDaylightTime, $expInDaylightTime, $expZoneOffset, $expDSTOffset)
	{
		$this->assertEquals($expInDaylightTime, $time_zone->inDaylightTime($d), 'expected ' . $expInDaylightTime. ' was ' . (int)$time_zone->inDaylightTime($d) . ' at ' . $this->dateToString($d) . ' in zone ' . $time_zone->getId() . '(' . get_class($time_zone) . ')');
		$this->assertEquals($expUseDaylightTime, $time_zone->useDaylightTime());
		$this->assertEquals($expZoneOffset, (int) $time_zone->getRawOffset());

		$gc = $this->tm->createCalendar(clone $time_zone);
		$gc->setTime($d);
		$offset = $time_zone->getOffset($gc->get(AgaviDateDefinitions::ERA),
				$gc->get(AgaviDateDefinitions::YEAR), $gc->get(AgaviDateDefinitions::MONTH),
				$gc->get(AgaviDateDefinitions::DATE), $gc->get(AgaviDateDefinitions::DAY_OF_WEEK),
				(($gc->get(AgaviDateDefinitions::HOUR_OF_DAY) * 60 + $gc->get(AgaviDateDefinitions::MINUTE)) * 60 + $gc->get(AgaviDateDefinitions::SECOND)) * 1000 + $gc->get(AgaviDateDefinitions::MILLISECOND)
		);

		$this->assertEquals($expDSTOffset, (int) $offset, 'expected ' . $expDSTOffset. ' was ' . $offset . ' at ' . $this->dateToString($d) . ' in zone ' . $time_zone->getId() . '(' . get_class($time_zone) . ')');
}

// -------------------------------------
	/**
	 * Check that the given year/month/dom/hour maps to and from the
	 * given epochHours.  This verifies the functioning of the
	 * calendar and time zone in conjunction with one another,
	 * including the calendar time->fields and fields->time and
	 * the time zone getOffset method.
	 *
	 * @param epochHours hours after Jan 1 1970 0:00 GMT.
	 */
	protected function verifyMapping(&$cal, $year, $month, $dom, $hour, $epochHours)
	{
		$H = 3600000.0;
		$cal->clear();
		$cal->set($year, $month, $dom, $hour, 0, 0);
		$e = $cal->getTime() / $H;
		$ed = ($epochHours * $H);
		$this->assertEquals($epochHours, $e);

		$cal->setTime($ed);
		$this->assertEquals($year, $cal->get(AgaviDateDefinitions::YEAR));
		$this->assertEquals($month, $cal->get(AgaviDateDefinitions::MONTH));
		$this->assertEquals($dom, $cal->get(AgaviDateDefinitions::DATE));
		$this->assertEquals(floatval($hour * 3600000), $cal->get(AgaviDateDefinitions::MILLISECONDS_IN_DAY));
}

	/**
	 * Test the behavior of SimpleTimeZone at the transition into and out of DST.
	 * Use a binary search to find boundaries.
	 */
	public function testBoundaries()
	{
		$pst = $this->tm->createTimeZone("America/Los_Angeles");
		$tempcal = $this->tm->createCalendar($pst);
		$this->verifyMapping($tempcal, 1997, AgaviDateDefinitions::APRIL, 3,  0, 238904.0);
		$this->verifyMapping($tempcal, 1997, AgaviDateDefinitions::APRIL, 4,  0, 238928.0);
		$this->verifyMapping($tempcal, 1997, AgaviDateDefinitions::APRIL, 5,  0, 238952.0);
		$this->verifyMapping($tempcal, 1997, AgaviDateDefinitions::APRIL, 5, 23, 238975.0);
		$this->verifyMapping($tempcal, 1997, AgaviDateDefinitions::APRIL, 6,  0, 238976.0);
		$this->verifyMapping($tempcal, 1997, AgaviDateDefinitions::APRIL, 6,  1, 238977.0);
		$this->verifyMapping($tempcal, 1997, AgaviDateDefinitions::APRIL, 6,  3, 238978.0);

		$utc = $this->tm->createTimeZone("UTC");
		$utccal =  $this->tm->createCalendar($utc);
		$this->verifyMapping($utccal, 1997, AgaviDateDefinitions::APRIL, 6, 0, 238968.0);

		$save = $this->tm->getDefaultTimeZone();
		//AgaviTimeZone::setDefault($pst);
		$this->tm->setDefaultTimeZone($pst->getId());
		
		// DST changeover for PST is 4/6/1997 at 2 hours past midnight
		// at 238978.0 epoch hours.
		$tempcal->clear();
		$tempcal->set(1997, AgaviDateDefinitions::APRIL, 6);
		$d = $tempcal->getTime();


		// i is minutes past midnight standard time
		for($i = -120; $i <= 180; $i += 60) {
			$inDST = ($i >= 120);
			$tempcal->setTime($d + $i*60*1000);
			$t = $tempcal->getTime();
			$this->verifyDST($t, $pst, true, $inDST, -8 * $this->ONE_HOUR, $inDST ? -7 * $this->ONE_HOUR : -8 * $this->ONE_HOUR);
		}
		//AgaviTimeZone::setDefault($save);
		$this->tm->setDefaultTimeZone($save->getId());


			$d = $this->date(97, AgaviDateDefinitions::APRIL, 6);
			$z = $this->tm->createTimeZone("America/Los_Angeles");
			for($i = 60; $i <= 180; $i += 15) {
				$inDST = ($i >= 120);
				$e = $d + $i * 60 * 1000;
				$this->verifyDST($e, $z, true, $inDST, - 8 * $this->ONE_HOUR, $inDST ? - 7 * $this->ONE_HOUR : - 8 * $this->ONE_HOUR);
			}

//			AgaviTimeZone::setDefault($tz = $this->tm->createTimeZone("America/Los_Angeles"));
			$this->tm->setDefaultTimeZone("America/Los_Angeles");
			$this->findDaylightBoundaryUsingDate($this->date(97, 0, 1), "PST", $this->PST_1997_BEG);
			$this->findDaylightBoundaryUsingDate($this->date(97, 6, 1), "PDT", $this->PST_1997_END);


			$z = $this->tm->createTimeZone("Australia/Adelaide");
			$this->findDaylightBoundaryUsingTimeZone($this->date(97, 0, 1), false, 859653000000.0, $z);
			$this->findDaylightBoundaryUsingTimeZone($this->date(97, 6, 1), false, 877797000000.0, $z);

			$this->findDaylightBoundaryUsingTimeZone($this->date(97, 0, 1), false, $this->PST_1997_BEG);
			$this->findDaylightBoundaryUsingTimeZone($this->date(97, 6, 1), true, $this->PST_1997_END);
	}

	protected function myTestUsingBinarySearch($tz, $d, $expectedBoundary)
	{
		$min = $d;
		$max = $min + $this->SIX_MONTHS;
		$startsInDST = $tz->inDaylightTime($d);
		$this->assertEquals($startsInDST, $tz->inDaylightTime($max));

		while(($max - $min) > $this->INTERVAL) {
			$mid = ($min + $max) / 2;
			if($tz->inDaylightTime($mid) == $startsInDST) {
				$min = $mid;
			} else {
				$max = $mid;
			}
		}

		$mindelta = $expectedBoundary - $min;
		$maxdelta = $max - $expectedBoundary;
		$this->assertTrue($mindelta >= 0 && $mindelta <= $this->INTERVAL && $maxdelta >= 0 && $maxdelta <= $this->INTERVAL);
	}

// -------------------------------------

	/**
	 * Test the handling of the "new" rules; that is, rules other than nth Day of week.
	 */
	public function TestNewRules()
	{
		$tz = new AgaviSimpleTimeZone($this->tm, -8 * $this->ONE_HOUR, "Test_1", AgaviDateDefinitions::AUGUST, 2, AgaviDateDefinitions::TUESDAY, 2 * $this->ONE_HOUR, AgaviDateDefinitions::MARCH, 15, 0, 2 * $this->ONE_HOUR);
		$this->myTestUsingBinarySearch($tz, $this->date(97, 0, 1), 858416400000.0);
		$this->myTestUsingBinarySearch($tz, $this->date(97, 6, 1), 871380000000.0);

		$tz = new AgaviSimpleTimeZone($this->tm -8 * $this->ONE_HOUR, "Test_2", AgaviDateDefinitions::APRIL, 14, - AgaviDateDefinitions::WEDNESDAY, 2 * $this->ONE_HOUR, AgaviDateDefinitions::SEPTEMBER, -20, - AgaviDateDefinitions::SUNDAY, 2 * $this->ONE_HOUR);
		$this->myTestUsingBinarySearch($tz, $this->date(97, 0, 1), 861184800000.0);
		$this->myTestUsingBinarySearch($tz, $this->date(97, 6, 1), 874227600000.0);
	}

// -------------------------------------

	protected function findBoundariesStepwise($year, $interval, $z, $expectedChanges)
	{
		$d = $this->date($year - 1900, AgaviDateDefinitions::JANUARY, 1);
		$time = $d;
		$limit = $time + $this->ONE_YEAR + $this->ONE_DAY;
		$cal = $this->tm->createCalendar($z);
		$cal->setTime($d);
		$lastState = $cal->inDaylightTime();

		$changes = 0;
		while($time < $limit) {
			$d = $time;
			$cal->setTime($d);
			$state = $cal->inDaylightTime();
			if($state != $lastState) {
//					logln(UnicodeString(state ? "Entry ": "Exit ") + "at " + d);
				$lastState = $state;
				++$changes;
			}
			$time += $interval;
		}
		if($changes == 0) {
			$this->assertTrue(!$lastState && !$z->useDaylightTime());
		} elseif($changes != 2) {
			$this->fail($changes . ' changes seen; should see 0 or 2');
		} elseif(!$z->useDaylightTime()) {
			$this->fail('useDaylightTime false but 2 changes seen');
		}

		$this->assertEquals($expectedChanges, $changes);
}

// -------------------------------------

/**
 * This test is problematic. It makes assumptions about the behavior
 * of specific zones. Since ICU's zone table is based on the Olson
 * zones (the UNIX zones), and those change from time to time, this
 * test can fail after a zone table update. If that happens, the
 * selected zones need to be updated to have the behavior
 * expected. That is, they should have DST, not have DST, and have DST
 * -- other than that this test isn't picky. 12/3/99 aliu
 *
 * Test the behavior of SimpleTimeZone at the transition into and out of DST.
 * Use a stepwise march to find boundaries.
 */
	public function testStepwise()
	{
		$zone = $this->tm->createTimeZone("America/New_York");
		$this->findBoundariesStepwise(1997, $this->ONE_DAY, $zone, 2);
		
		$zone = $this->tm->createTimeZone("UTC"); // updated 12/3/99 aliu
		$this->findBoundariesStepwise(1997, $this->ONE_DAY, $zone, 0);

		$zone = $this->tm->createTimeZone("Australia/Adelaide");
		$this->findBoundariesStepwise(1997, $this->ONE_DAY, $zone, 2);
	}
}
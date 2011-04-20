<?php

require_once(__DIR__ . '/BaseCalendarTest.php');

/**
 * Ported from ICU:
 *  icu/trunk/source/test/intltest/tztest.cpp   r22978
 */
class TimeZoneTest extends BaseCalendarTest
{
	const millisPerHour = 3600000;

// ---------------------------------------------------------------------------------

	/**
	 * Generic API testing for API coverage.
	 */
	public function disabledTestGenericAPI()
	{
		// TODO: this method needs a general overhaul to fit agavi

		$id = "NewGMT";
		$offset = 12345;

		$zone = new AgaviSimpleTimeZone($this->tm, $offset, $id);
		$this->assertFalse($zone->useDaylightTime());

		$zoneclone = clone $zone;
		$this->assertTrue($zoneclone->__is_equal($zone));
		$zoneclone->setId("abc");
		$this->assertTrue($zoneclone->__is_not_equal($zone));

/*
TODO: is_equal doesn't work yet

		$zoneclone = clone $zone;
		$this->assertTrue($zoneclone->__is_equal($zone));
		$zoneclone->setRawOffset(45678);
		$this->assertTrue($zoneclone->__is_not_equal($zone));
*/

//		logln("call uprv_timezone() which uses the host");
//		logln("to get the difference in seconds between coordinated universal");
//		logln("time and local time. E.g., -28,800 for PST (GMT-8hrs)");

//		$tzoffset = uprv_timezone();
//		logln(UnicodeString("Value returned from uprv_timezone = ") + tzoffset);
		// Invert sign because UNIX semantics are backwards
		if($tzoffset < 0)
			$tzoffset = -$tzoffset;
		// --- The following test would fail outside PST now that
		// --- PST is generally set to be default timezone in format tests
		//if ((*saveDefault == *pstZone) && (tzoffset != 28800)) {
		//  errln("FAIL: t_timezone may be incorrect.  It is not 28800");
		//}

		if($tzoffset != 28800) {
//			logln("***** WARNING: If testing in the PST timezone, uprv_timezone should return 28800! *****");
		}
		$this->assertEquals(0, $tzoffset % 1800, 't_timezone may be incorrect. It is not a multiple of 30min. It is ' . $tzoffset);

/*
		AgaviTimeZone::adoptDefault($zone);
		$defaultzone = AgaviTimeZone::createDefault();
		$this->assertFalse($defaultzone == $zone || !($defaultzone->__is_equal($zone)));
		AgaviTimeZone::adoptDefault($saveDefault);
*/
	}

// ---------------------------------------------------------------------------------

/**
 * Test the setStartRule/setEndRule API calls.
 */
	public function TestRuleAPI()
	{
		$offset = 60*60*1000*1.75; // Pick a weird offset
		$zone = new AgaviSimpleTimeZone($this->tm, $offset, "TestZone");
		$this->assertFalse($zone->useDaylightTime());

		// Establish our expected transition times.  Do this with a non-DST
		// calendar with the (above) declared local offset.
		$gc = $this->tm->createCalendar($zone);
		$gc->clear();
		$gc->set(1990, AgaviDateDefinitions::MARCH, 1);
		$marchOneStd = $gc->getTime(); // Local Std time midnight
		$gc->clear();
		$gc->set(1990, AgaviDateDefinitions::JULY, 1);
		$julyOneStd = $gc->getTime(); // Local Std time midnight

		// Starting and ending hours, WALL TIME
		$startHour = (int)(2.25 * 3600000);
		$endHour   = (int)(3.5  * 3600000);

		$zone->setStartRule(AgaviDateDefinitions::MARCH, 1, 0, $startHour);
		$zone->setEndRule  (AgaviDateDefinitions::JULY,  1, 0, $endHour);

		$gc = $this->tm->createCalendar($zone);

		$marchOne = $marchOneStd + $startHour;
		$julyOne = $julyOneStd + $endHour - 3600000; // Adjust from wall to Std time

		$expMarchOne = 636251400000.0;
		$this->assertEquals($expMarchOne, $marchOne);

		$expJulyOne = 646793100000.0;
		$this->assertEquals($expJulyOne, $julyOne);

		$this->myTestUsingBinarySearch($zone, $this->date(90, AgaviDateDefinitions::JANUARY, 1), $this->date(90, AgaviDateDefinitions::JUNE, 15), $marchOne);
		$this->myTestUsingBinarySearch($zone, $this->date(90, AgaviDateDefinitions::JUNE, 1), $this->date(90, AgaviDateDefinitions::DECEMBER, 31), $julyOne);

		$this->assertFalse($zone->inDaylightTime($marchOne - 1000) || !$zone->inDaylightTime($marchOne), 'Start rule broken');
		$this->assertFalse(!$zone->inDaylightTime($julyOne - 1000) || $zone->inDaylightTime($julyOne), 'End rule broken');

		$zone->setStartYear(1991);
		$this->assertFalse($zone->inDaylightTime($marchOne) || $zone->inDaylightTime($julyOne - 1000), 'Start year broken');
	}


	protected function findTransition($tz, $min, $max)
	{
		$cal = $this->tm->createCalendar($tz);
		$cal->setTime($min);
		$startsInDST = $cal->inDaylightTime();
		$cal->setTime($max);
		$this->assertNotEquals($startsInDST, $cal->inDaylightTime());

/*
		while(($max - $min) > $this->INTERVAL) {
			$mid = ($min + $max) / 2;
			$cal->setTime($mid);
			if($cal->inDaylightTime() == $startsInDST) {
				$min = $mid;
			} else {
				$max = $mid;
			}
		}
		$min = 1000.0 * floor($min / 1000.0);
		$max = 1000.0 * floor($max / 1000.0);
		logln(tz.getID(id) + " Before: " + min/1000 + " = " +
					dateToString(min,s,tz));
		logln(tz.getID(id) + " After:  " + max/1000 + " = " +
					dateToString(max,s,tz));
*/
}

	protected function myTestUsingBinarySearch($tz, $min, $max, $expectedBoundary)
	{
		$INTERVAL = 100;

		$cal = $this->tm->createCalendar($tz);
		$cal->setTime($min);
		$startsInDST = $cal->inDaylightTime();
		$cal->setTime($max);
		$this->assertNotEquals($startsInDST, $cal->inDaylightTime());

		while(($max - $min) > $INTERVAL) {
			$mid = ($min + $max) / 2;
			$cal->setTime($mid);
			if($cal->inDaylightTime() == $startsInDST) {
				$min = $mid;
			} else {
				$max = $mid;
			}
		}

		$mindelta = $expectedBoundary - $min;
		$maxdelta = $max - $expectedBoundary;
		$this->assertTrue($mindelta >= 0 && $mindelta <= $INTERVAL && $maxdelta >= 0 && $maxdelta <= $INTERVAL);
	}

// -------------------------------------

	/**
	 * Test the offset of the PRT timezone.
	 */
	public function testPRTOffset()
	{
		$tz = $this->tm->createTimeZone("America/Puerto_Rico");
		$expectedHour = -4;
		$expectedOffset = (((float)$expectedHour) * self::millisPerHour);
		$foundOffset = $tz->getRawOffset();
		$foundHour = (int)$foundOffset / self::millisPerHour;
		$this->assertEquals($expectedOffset, $foundOffset); 
	}

// -------------------------------------

	/**
	 * Regress a specific bug with a sequence of API calls.
	 */
	public function testVariousAPI518()
	{
		$time_zone = $this->tm->createTimeZone("America/Los_Angeles");
		$d = $this->date(97, AgaviDateDefinitions::APRIL, 30);
		$this->assertTrue($time_zone->inDaylightTime($d));
		$this->assertTrue($time_zone->useDaylightTime());
		$this->assertEquals(floatval(-8 * self::millisPerHour), $time_zone->getRawOffset());

		$gc = $this->tm->createCalendar();
		$gc->setTime($d);
		$this->assertEquals(floatval(-7 * self::millisPerHour), $time_zone->getOffset(AgaviGregorianCalendar::AD, $gc->get(AgaviDateDefinitions::YEAR), $gc->get(AgaviDateDefinitions::MONTH), $gc->get(AgaviDateDefinitions::DATE), $gc->get(AgaviDateDefinitions::DAY_OF_WEEK), 0));
	}

// -------------------------------------

	/**
	 * Test the call which retrieves the available IDs.
	 * /
	public function testGetAvailableIDs913()
	{
		UnicodeString str;
		UnicodeString *buf = new UnicodeString("TimeZone::createEnumeration() = { ");
		int32_t s_length;
		StringEnumeration* s = TimeZone::createEnumeration();
		s_length = s->count(ec);
		for (i = 0; i < s_length;++i) {
				if (i > 0) *buf += ", ";
				if ((i & 1) == 0) {
						*buf += *s->snext(ec);
				} else {
						*buf += UnicodeString(s->next(NULL, ec), "");
				}

				if((i % 5) == 4) {
						// replace s with a clone of itself
						StringEnumeration *s2 = s->clone();
						if(s2 == NULL || s_length != s2->count(ec)) {
								errln("TimezoneEnumeration.clone() failed");
						} else {
								delete s;
								s = s2;
						}
				}
		}
		*buf += " };";
		logln(*buf);

		/* Confirm that the following zones can be retrieved: The first
		 * zone, the last zone, and one in-between.  This tests the binary
		 * search through the system zone data.
		 * /
		s->reset(ec);
		int32_t middle = s_length/2;
		for (i=0; i<s_length; ++i) {
				const UnicodeString* id = s->snext(ec);
				if (i==0 || i==middle || i==(s_length-1)) {
				TimeZone *z = TimeZone::createTimeZone(*id);
				if (z == 0) {
						errln(UnicodeString("FAIL: createTimeZone(") +
									*id + ") -> 0");
				} else if (z->getID(str) != *id) {
						errln(UnicodeString("FAIL: createTimeZone(") +
									*id + ") -> zone " + str);
				} else {
						logln(UnicodeString("OK: createTimeZone(") +
									*id + ") succeeded");
				}
				delete z;
				}
		}
		delete s;

		buf->truncate(0);
		*buf += "TimeZone::createEnumeration(GMT+01:00) = { ";

		s = TimeZone::createEnumeration(1 * AgaviDateDefinitions::MILLIS_PER_HOUR);
		s_length = s->count(ec);
		for (i = 0; i < s_length;++i) {
				if (i > 0) *buf += ", ";
				*buf += *s->snext(ec);
		}
		delete s;
		*buf += " };";
		logln(*buf);


		buf->truncate(0);
		*buf += "TimeZone::createEnumeration(US) = { ";

		s = TimeZone::createEnumeration("US");
		s_length = s->count(ec);
		for (i = 0; i < s_length;++i) {
				if (i > 0) *buf += ", ";
				*buf += *s->snext(ec);
		}
		*buf += " };";
		logln(*buf);

		TimeZone *tz = TimeZone::createTimeZone("PST");
		if (tz != 0) logln("getTimeZone(PST) = " + tz->getID(str));
		else errln("FAIL: getTimeZone(PST) = null");
		delete tz;
		tz = TimeZone::createTimeZone("America/Los_Angeles");
		if (tz != 0) logln("getTimeZone(America/Los_Angeles) = " + tz->getID(str));
		else errln("FAIL: getTimeZone(PST) = null");
		delete tz;

		// @bug 4096694
		tz = TimeZone::createTimeZone("NON_EXISTENT");
		UnicodeString temp;
		if (tz == 0)
				errln("FAIL: getTimeZone(NON_EXISTENT) = null");
		else if (tz->getID(temp) != "GMT")
				errln("FAIL: getTimeZone(NON_EXISTENT) = " + temp);
		delete tz;

		delete buf;
		delete s;
}
*/


/**
 * NOTE: As of ICU 2.8, this test confirms that the "tz.alias"
 * file, used to build ICU alias zones, is working.  It also
 * looks at some genuine Olson compatibility IDs. [aliu]
 *
 * This test is problematic. It should really just confirm that
 * the list of compatibility zone IDs exist and are somewhat
 * meaningful (that is, they aren't all aliases of GMT). It goes a
 * bit further -- it hard-codes expectations about zone behavior,
 * when in fact zones are redefined quite frequently. ICU's build
 * process means that it is easy to update ICU to contain the
 * latest Olson zone data, but if a zone tested here changes, then
 * this test will fail.  I have updated the test for 1999j data,
 * but further updates will probably be required. Note that some
 * of the concerts listed below no longer apply -- in particular,
 * we do NOT overwrite real UNIX zones with 3-letter IDs. There
 * are two points of overlap as of 1999j: MET and EET. These are
 * both real UNIX zones, so we just use the official
 * definition. This test has been updated to reflect this.
 * 12/3/99 aliu
 *
 * Added tests for additional zones and aliases from the icuzones file. 
 * Markus Scherer 2006-nov-06
 * 
 * [srl - from java - 7/5/1998]
 * @bug 4130885
 * Certain short zone IDs, used since 1.1.x, are incorrect.
 *
 * The worst of these is:
 *
 * "CAT" (Central African Time) should be GMT+2:00, but instead returns a
 * zone at GMT-1:00. The zone at GMT-1:00 should be called EGT, CVT, EGST,
 * or AZOST, depending on which zone is meant, but in no case is it CAT.
 *
 * Other wrong zone IDs:
 *
 * ECT (European Central Time) GMT+1:00: ECT is Ecuador Time,
 * GMT-5:00. European Central time is abbreviated CEST.
 *
 * SST (Solomon Island Time) GMT+11:00. SST is actually Samoa Standard Time,
 * GMT-11:00. Solomon Island time is SBT.
 *
 * NST (New Zealand Time) GMT+12:00. NST is the abbreviation for
 * Newfoundland Standard Time, GMT-3:30. New Zealanders use NZST.
 *
 * AST (Alaska Standard Time) GMT-9:00. [This has already been noted in
 * another bug.] It should be "AKST". AST is Atlantic Standard Time,
 * GMT-4:00.
 *
 * PNT (Phoenix Time) GMT-7:00. PNT usually means Pitcairn Time,
 * GMT-8:30. There is no standard abbreviation for Phoenix time, as distinct
 * from MST with daylight savings.
 *
 * In addition to these problems, a number of zones are FAKE. That is, they
 * don't match what people use in the real world.
 *
 * FAKE zones:
 *
 * EET (should be EEST)
 * ART (should be EEST)
 * MET (should be IRST)
 * NET (should be AMST)
 * PLT (should be PKT)
 * BST (should be BDT)
 * VST (should be ICT)
 * CTT (should be CST) +
 * ACT (should be CST) +
 * AET (should be EST) +
 * MIT (should be WST) +
 * IET (should be EST) +
 * PRT (should be AST) +
 * CNT (should be NST)
 * AGT (should be ARST)
 * BET (should be EST) +
 *
 * + A zone with the correct name already exists and means something
 * else. E.g., EST usually indicates the US Eastern zone, so it cannot be
 * used for Brazil (BET).
 */
	public function testShortZoneIDs()
	{
/*
		int32_t i;
		// Create a small struct to hold the array
		struct
		{
				const char *id;
				int32_t    offset;
				UBool      daylight;
		}
		kReferenceList [] =
*/
		$kReferenceList = array(
															array('id' => "MIT", 'offset' => -660, 'daylight' => true),
															array('id' =>"HST", 'offset' =>  -600, 'daylight' => false),
															array('id' =>"AST", 'offset' =>  -540, 'daylight' => true),
															array('id' =>"PST", 'offset' =>  -480, 'daylight' => true),
															array('id' =>"PNT", 'offset' =>  -420, 'daylight' => false),
															array('id' =>"MST", 'offset' =>  -420, 'daylight' => false), // updated Aug 2003 aliu
															array('id' =>"CST", 'offset' =>  -360, 'daylight' => true),
															array('id' =>"IET", 'offset' =>  -300, 'daylight' => true),  // updated Jan 2006 srl
															array('id' =>"EST", 'offset' =>  -300, 'daylight' => false), // updated Aug 2003 aliu
															array('id' =>"PRT", 'offset' =>  -240, 'daylight' => false),
															array('id' =>"CNT", 'offset' =>  -210, 'daylight' => true),
															array('id' =>"AGT", 'offset' =>  -180, 'daylight' => false), // updated 30 Dec 2007
															array('id' =>"BET", 'offset' =>  -180, 'daylight' => true),
															// "CAT", -60, false, // Wrong:
															// As of bug 4130885, fix CAT (Central Africa)
															array('id' =>"CAT", 'offset' =>  120, 'daylight' => false), // Africa/Harare
															array('id' =>"GMT", 'offset' =>  0, 'daylight' => false),
															array('id' =>"UTC", 'offset' =>  0, 'daylight' => false), // ** srl: seems broken in C++
															array('id' =>"ECT", 'offset' =>  60, 'daylight' => true),
															array('id' =>"ART", 'offset' =>  120, 'daylight' => true),
															array('id' =>"EET", 'offset' =>  120, 'daylight' => true),
															array('id' =>"EAT", 'offset' =>  180, 'daylight' => false),
															array('id' =>"MET", 'offset' =>  60, 'daylight' => true), // updated 12/3/99 aliu
															array('id' =>"NET", 'offset' =>  240, 'daylight' => true), // updated 12/3/99 aliu
															array('id' =>"PLT", 'offset' =>  300, 'daylight' => false), // updated Aug 2003 aliu
															array('id' =>"IST", 'offset' =>  330, 'daylight' => false),
															array('id' =>"BST", 'offset' =>  360, 'daylight' => false),
															array('id' =>"VST", 'offset' =>  420, 'daylight' => false),
															array('id' =>"CTT", 'offset' =>  480, 'daylight' => false), // updated Aug 2003 aliu
															array('id' =>"JST", 'offset' =>  540, 'daylight' => false),
															array('id' =>"ACT", 'offset' =>  570, 'daylight' => false), // updated Aug 2003 aliu
															array('id' =>"AET", 'offset' =>  600, 'daylight' => true),
															array('id' =>"SST", 'offset' =>  660, 'daylight' => false),
															// "NST", 720, false,
															// As of bug 4130885, fix NST (New Zealand)
															array('id' =>"NST", 'offset' =>  720, 'daylight' => true), // Pacific/Auckland
															
															
/* disabled until we support the old aliases at all
															// From icuzones: 
															array('id' =>"Etc/Unknown", 'offset' => 0, false),

															array('id' =>"SystemV/AST4ADT", 'offset' => -240, 'daylight' => true),
															array('id' =>"SystemV/EST5EDT", 'offset' => -300, 'daylight' => true),
															array('id' =>"SystemV/CST6CDT", 'offset' => -360, 'daylight' => true),
															array('id' =>"SystemV/MST7MDT", 'offset' => -420, 'daylight' => true),
															array('id' =>"SystemV/PST8PDT", 'offset' => -480, 'daylight' => true),
															array('id' =>"SystemV/YST9YDT", 'offset' => -540, 'daylight' => true),
															array('id' =>"SystemV/AST4", 'offset' => -240, 'daylight' => false),
#if U_ICU_VERSION_MAJOR_NUM>3 || U_ICU_VERSION_MINOR_NUM>=8
															// CLDR 1.4.1 has an alias from SystemV/EST5 to America/Indianapolis
															// which is incorrect because Indiana has started to observe DST.
															// Re-enable this test once CLDR has fixed the alias.
															// (For example, it could alias SystemV/EST5 to Etc/GMT+5.)
															array('id' =>"SystemV/EST5", 'offset' => -300, 'daylight' => false),
#endif
															array('id' =>"SystemV/CST6", 'offset' => -360, 'daylight' => false),
															array('id' =>"SystemV/MST7", 'offset' => -420, 'daylight' => false),
															array('id' =>"SystemV/PST8", 'offset' => -480, 'daylight' => false),
															array('id' =>"SystemV/YST9", 'offset' => -540, 'daylight' => false),
															array('id' =>"SystemV/HST10", 'offset' => -600, 'daylight' => false),
*/
		);


		$compatibilityMap = array(
				// This list is copied from tz.alias.  If tz.alias
				// changes, this list must be updated.  Current as of Mar 2007
				"ACT" => "Australia/Darwin",
				"AET" => "Australia/Sydney",
				"AGT" => "America/Buenos_Aires",
				"ART" => "Africa/Cairo",
				"AST" => "America/Anchorage",
				"BET" => "America/Sao_Paulo",
				"BST" => "Asia/Dhaka", // # spelling changed in 2000h; was Asia/Dacca 
				"CAT" => "Africa/Harare",
				"CNT" => "America/St_Johns",
				"CST" => "America/Chicago",
				"CTT" => "Asia/Shanghai",
				"EAT" => "Africa/Addis_Ababa",
				"ECT" => "Europe/Paris",
				'EET' => 'Europe/Istanbul', # EET is a standard UNIX zone
				// "EST" => "America/New_York", # Defined as -05:00 
				'EST' => 'EST',
				// "HST" => "Pacific/Honolulu", # Defined as -10:00 
				'HST' => 'HST',
				'GMT' => 'Etc/GMT',
				"IET" => "America/Indianapolis",
				"IST" => "Asia/Calcutta",
				"JST" => "Asia/Tokyo",
				'MET' => 'MET', # MET is a standard UNIX zone
				"MIT" => "Pacific/Apia",
				// "MST", "America/Denver", # Defined as -07:00
				'MST' => 'MST',
				"NET" => "Asia/Yerevan",
				"NST" => "Pacific/Auckland",
				"PLT" => "Asia/Karachi",
				"PNT" => "America/Phoenix",
				"PRT" => "America/Puerto_Rico",
				"PST" => "America/Los_Angeles",
				"SST" => "Pacific/Guadalcanal",
				"UTC" => "Etc/GMT",
				"VST" => "Asia/Saigon",
		);

		foreach($kReferenceList as $entry) {
			$itsID = $compatibilityMap[$entry['id']];
			$ok = true;
			// Check existence.
			$tz = $this->tm->createTimeZone($itsID);
			if(!$tz) {
				$this->fail('Time Zone ' . $itsID . '('.$entry['id'].') does not exist!');
			}

			// Check daylight usage.
			$usesDaylight = $tz->useDaylightTime();
			$this->assertEquals($entry['daylight'], $usesDaylight, 'Zone ' . $itsID);

			// Check offset
			$offsetInMinutes = $tz->getRawOffset() / 60000;
			$this->assertEquals((float) $entry['offset'], $offsetInMinutes);
		}


/*
We don't support the old aliases (yet)


		foreach($compatibilityMap as $zone1 => $zone2) {
			$tz1 = $this->tm->createTimeZone($zone1);
			$tz2 = $this->tm->createTimeZone($zone2);

			$this->assertTrue($tz1, 'Could not find short ID zone ' . $zone1);
			$this->assertTrue($tz2, 'Could not find long ID zone ' . $zone2);

			// make NAME same so comparison will only look at the rest
			$tz2->setId($tz1->getId());

			$this->assertTrue($tz1->__is_equal($tz2));
		}
*/

	}

/**
 * Utility function for TestCustomParse
 */
	protected function formatOffset($offset, $insertSep = true)
	{
		$rv = '';
		$sign = chr(0x002B);
		if($offset < 0) {
			$sign = chr(0x002D);
			$offset = -$offset;
		}
		
		$s = $offset % 60;
		$offset /= 60;
		$m = $offset % 60;
		$h = $offset / 60;
		
		$rv .= ($sign);
		if($h >= 10) {
			$rv .= chr(0x0030 + ($h/10));
		} else {
			$rv .= chr(0x0030);
		}
		$rv .= chr(0x0030 + ($h%10));
		
		if($insertSep) {
			$rv .= chr(0x003A); /* ':' */
		}
		
		if($m >= 10) {
			$rv .= chr(0x0030 + ($m/10));
		} else {
			$rv .= chr(0x0030);
		}
		$rv .= chr(0x0030 + ($m%10));
		
		if($s) {
			if($insertSep) {
				$rv .= chr(0x003A); /* ':' */
			}
			if($s >= 10) {
				$rv .= chr(0x0030 + ($s/10));
			} else {
				$rv .= chr(0x0030);
			}
			$rv .= chr(0x0030 + ($s%10));
		}
		return $rv;
	}
	
	/** 
	 * Utility function for TestCustomParse, generating time zone ID 
	 * string for the give offset. 
	 */
	protected function formatTZID($offset)
	{
		$offsetStr = $this->formatOffset($offset, false);
		$rv = 'GMT';
		$rv .= $offsetStr;
		return $rv;
	}


	/**
	 * As part of the VM fix (see CCC approved RFE 4028006, bug
	 * 4044013), TimeZone.getTimeZone() has been modified to recognize
	 * generic IDs of the form GMT[+-]hh:mm, GMT[+-]hhmm, and
	 * GMT[+-]hh.  Test this behavior here.
	 *
	 * @bug 4044013
	 */
	public function testCustomParse()
	{
		$kUnparseable = 604800; // the number of seconds in a week. More than any offset should be.

		$kData = array(
				// ID        Expected offset in seconds
				array('customId' => "GMT",       'expectedOffset' => $kUnparseable), // Isn't custom. [returns normal GMT] 
				array('customId' => "GMT-YOUR.AD.HERE", 'expectedOffset' => $kUnparseable),
#				array('customId' => "GMT0",      'expectedOffset' => $kUnparseable),
#				array('customId' => "GMT+0",     'expectedOffset' => (0)),
				// {"GMT0",      kUnparseable), // ICU 2.8: An Olson zone ID
				// {"GMT+0",     (0)), // ICU 2.8: An Olson zone ID
				array('customId' => "GMT+1",     'expectedOffset' => (60*60)),
				array('customId' => "GMT-0030",  'expectedOffset' => (-30*60)),
				array('customId' => "GMT+15:99", 'expectedOffset' => $kUnparseable),
				array('customId' => "GMT+",      'expectedOffset' => $kUnparseable),
				array('customId' => "GMT-",      'expectedOffset' => $kUnparseable),
				array('customId' => "GMT+0:",    'expectedOffset' => $kUnparseable),
				array('customId' => "GMT-:",     'expectedOffset' => $kUnparseable),
				array('customId' => "GMT-YOUR.AD.HERE",     'expectedOffset' => $kUnparseable),
				array('customId' => "GMT+0010",  'expectedOffset' => (10*60)), // Interpret this as 00:10
				array('customId' => "GMT-10",    'expectedOffset' => (-10*60*60)),
				array('customId' => "GMT+30",    'expectedOffset' => $kUnparseable),
				array('customId' => "GMT-3:30",  'expectedOffset' => (-(3*60+30)*60)),
				array('customId' => "GMT-230",   'expectedOffset' => (-(2*60+30)*60)),
				array('customId' => "GMT+05:13:05",'expectedOffset' => ((5*60+13)*60+5)),
				array('customId' => "GMT-71023", 'expectedOffset' => (-((7*60+10)*60+23))),
				array('customId' => "GMT+01:23:45:67", 'expectedOffset' => $kUnparseable),
				array('customId' => "GMT+01:234",      'expectedOffset' => $kUnparseable),
				array('customId' => "GMT-2:31:123",    'expectedOffset' => $kUnparseable),
				array('customId' => "GMT+3:75",        'expectedOffset' => $kUnparseable),
				array('customId' => "GMT-01010101",    'expectedOffset' => $kUnparseable),
		);

		foreach($kData as $entry) {
			$id = $entry['customId'];
			$exp = $entry['expectedOffset'];
			$zone = $this->tm->createTimeZone($id);

			if(!$zone && $exp != $kUnparseable) {
				$this->fail('Time Zone ' . $id . ' does not exist!');
			} elseif($zone && $exp == $kUnparseable && $zone->getId() != 'GMT') {
				$this->fail('Time Zone ' . $id . ' exists as "' . $zone->getResolvedId() . '" but we expected it to be unparseable');
			} elseif($zone && $exp != $kUnparseable) {
				$itsID = $zone->getId();
				$ioffset = $zone->getRawOffset()/1000;
				$offset = $this->formatOffset($ioffset);
				$expectedID = $this->formatTZID($ioffset);
				 // JDK 1.3 creates custom zones with the ID "Custom"
				// JDK 1.4 creates custom zones with IDs of the form "GMT+02:00"
				// ICU creates custom zones with IDs of the form "GMT+0200"
				$this->assertNotEquals($exp, $kUnparseable);
				$this->assertEquals((float)$exp, $ioffset, 'Custom zone string ' . $id);
				$this->assertEquals($expectedID, $itsID);
			}
		}
	}

	public function testAliasedNames()
	{
		$kData[] = array(
				/* Generated by org.unicode.cldr.tool.CountItems */

				/* zoneID, canonical zoneID */
				array('from' => "Africa/Timbuktu", 'to' => "Africa/Bamako"),
				array('from' => "America/Argentina/Buenos_Aires", 'to' => "America/Buenos_Aires"),
				array('from' => "America/Argentina/Catamarca", 'to' => "America/Catamarca"),
				array('from' => "America/Argentina/ComodRivadavia", 'to' => "America/Catamarca"),
				array('from' => "America/Argentina/Cordoba", 'to' => "America/Cordoba"),
				array('from' => "America/Argentina/Jujuy", 'to' => "America/Jujuy"),
				array('from' => "America/Argentina/Mendoza", 'to' => "America/Mendoza"),
				array('from' => "America/Atka", 'to' => "America/Adak"),
				array('from' => "America/Ensenada", 'to' => "America/Tijuana"),
				array('from' => "America/Fort_Wayne", 'to' => "America/Indiana/Indianapolis"),
				array('from' => "America/Indianapolis", 'to' => "America/Indiana/Indianapolis"),
				array('from' => "America/Knox_IN", 'to' => "America/Indiana/Knox"),
				array('from' => "America/Louisville", 'to' => "America/Kentucky/Louisville"),
				array('from' => "America/Porto_Acre", 'to' => "America/Rio_Branco"),
				array('from' => "America/Rosario", 'to' => "America/Cordoba"),
				array('from' => "America/Virgin", 'to' => "America/St_Thomas"),
				array('from' => "Asia/Ashkhabad", 'to' => "Asia/Ashgabat"),
				array('from' => "Asia/Chungking", 'to' => "Asia/Chongqing"),
				array('from' => "Asia/Dacca", 'to' => "Asia/Dhaka"),
				array('from' => "Asia/Istanbul", 'to' => "Europe/Istanbul"),
				array('from' => "Asia/Macao", 'to' => "Asia/Macau"),
				array('from' => "Asia/Tel_Aviv", 'to' => "Asia/Jerusalem"),
				array('from' => "Asia/Thimbu", 'to' => "Asia/Thimphu"),
				array('from' => "Asia/Ujung_Pandang", 'to' => "Asia/Makassar"),
				array('from' => "Asia/Ulan_Bator", 'to' => "Asia/Ulaanbaatar"),
				array('from' => "Australia/ACT", 'to' => "Australia/Sydney"),
				array('from' => "Australia/Canberra", 'to' => "Australia/Sydney"),
				array('from' => "Australia/LHI", 'to' => "Australia/Lord_Howe"),
				array('from' => "Australia/NSW", 'to' => "Australia/Sydney"),
				array('from' => "Australia/North", 'to' => "Australia/Darwin"),
				array('from' => "Australia/Queensland", 'to' => "Australia/Brisbane"),
				array('from' => "Australia/South", 'to' => "Australia/Adelaide"),
				array('from' => "Australia/Tasmania", 'to' => "Australia/Hobart"),
				array('from' => "Australia/Victoria", 'to' => "Australia/Melbourne"),
				array('from' => "Australia/West", 'to' => "Australia/Perth"),
				array('from' => "Australia/Yancowinna", 'to' => "Australia/Broken_Hill"),
				array('from' => "Brazil/Acre", 'to' => "America/Rio_Branco"),
				array('from' => "Brazil/DeNoronha", 'to' => "America/Noronha"),
				array('from' => "Brazil/East", 'to' => "America/Sao_Paulo"),
				array('from' => "Brazil/West", 'to' => "America/Manaus"),
				array('from' => "Canada/Atlantic", 'to' => "America/Halifax"),
				array('from' => "Canada/Central", 'to' => "America/Winnipeg"),
				array('from' => "Canada/East-Saskatchewan", 'to' => "America/Regina"),
				array('from' => "Canada/Eastern", 'to' => "America/Toronto"),
				array('from' => "Canada/Mountain", 'to' => "America/Edmonton"),
				array('from' => "Canada/Newfoundland", 'to' => "America/St_Johns"),
				array('from' => "Canada/Pacific", 'to' => "America/Vancouver"),
				array('from' => "Canada/Saskatchewan", 'to' => "America/Regina"),
				array('from' => "Canada/Yukon", 'to' => "America/Whitehorse"),
				array('from' => "Chile/Continental", 'to' => "America/Santiago"),
				array('from' => "Chile/EasterIsland", 'to' => "Pacific/Easter"),
				array('from' => "Cuba", 'to' => "America/Havana"),
				array('from' => "Egypt", 'to' => "Africa/Cairo"),
				array('from' => "Eire", 'to' => "Europe/Dublin"),
				array('from' => "Etc/GMT+0", 'to' => "Etc/GMT"),
				array('from' => "Etc/GMT-0", 'to' => "Etc/GMT"),
				array('from' => "Etc/GMT0", 'to' => "Etc/GMT"),
				array('from' => "Etc/Greenwich", 'to' => "Etc/GMT"),
				array('from' => "Etc/UCT", 'to' => "Etc/GMT"),
				array('from' => "Etc/UTC", 'to' => "Etc/GMT"),
				array('from' => "Etc/Universal", 'to' => "Etc/GMT"),
				array('from' => "Etc/Zulu", 'to' => "Etc/GMT"),
				array('from' => "Europe/Belfast", 'to' => "Europe/London"),
				array('from' => "Europe/Nicosia", 'to' => "Asia/Nicosia"),
				array('from' => "Europe/Tiraspol", 'to' => "Europe/Chisinau"),
				array('from' => "GB", 'to' => "Europe/London"),
				array('from' => "GB-Eire", 'to' => "Europe/London"),
				array('from' => "GMT", 'to' => "Etc/GMT"),
				array('from' => "GMT+0", 'to' => "Etc/GMT"),
				array('from' => "GMT-0", 'to' => "Etc/GMT"),
				array('from' => "GMT0", 'to' => "Etc/GMT"),
				array('from' => "Greenwich", 'to' => "Etc/GMT"),
				array('from' => "Hongkong", 'to' => "Asia/Hong_Kong"),
				array('from' => "Iceland", 'to' => "Atlantic/Reykjavik"),
				array('from' => "Iran", 'to' => "Asia/Tehran"),
				array('from' => "Israel", 'to' => "Asia/Jerusalem"),
				array('from' => "Jamaica", 'to' => "America/Jamaica"),
				array('from' => "Japan", 'to' => "Asia/Tokyo"),
				array('from' => "Kwajalein", 'to' => "Pacific/Kwajalein"),
				array('from' => "Libya", 'to' => "Africa/Tripoli"),
				array('from' => "Mexico/BajaNorte", 'to' => "America/Tijuana"),
				array('from' => "Mexico/BajaSur", 'to' => "America/Mazatlan"),
				array('from' => "Mexico/General", 'to' => "America/Mexico_City"),
				array('from' => "NZ", 'to' => "Pacific/Auckland"),
				array('from' => "NZ-CHAT", 'to' => "Pacific/Chatham"),
				array('from' => "Navajo", 'to' => "America/Shiprock"),
				array('from' => "PRC", 'to' => "Asia/Shanghai"),
				array('from' => "Pacific/Samoa", 'to' => "Pacific/Pago_Pago"),
				array('from' => "Pacific/Yap", 'to' => "Pacific/Truk"),
				array('from' => "Poland", 'to' => "Europe/Warsaw"),
				array('from' => "Portugal", 'to' => "Europe/Lisbon"),
				array('from' => "ROC", 'to' => "Asia/Taipei"),
				array('from' => "ROK", 'to' => "Asia/Seoul"),
				array('from' => "Singapore", 'to' => "Asia/Singapore"),
				array('from' => "Turkey", 'to' => "Europe/Istanbul"),
				array('from' => "UCT", 'to' => "Etc/GMT"),
				array('from' => "US/Alaska", 'to' => "America/Anchorage"),
				array('from' => "US/Aleutian", 'to' => "America/Adak"),
				array('from' => "US/Arizona", 'to' => "America/Phoenix"),
				array('from' => "US/Central", 'to' => "America/Chicago"),
				array('from' => "US/East-Indiana", 'to' => "America/Indiana/Indianapolis"),
				array('from' => "US/Eastern", 'to' => "America/New_York"),
				array('from' => "US/Hawaii", 'to' => "Pacific/Honolulu"),
				array('from' => "US/Indiana-Starke", 'to' => "America/Indiana/Knox"),
				array('from' => "US/Michigan", 'to' => "America/Detroit"),
				array('from' => "US/Mountain", 'to' => "America/Denver"),
				array('from' => "US/Pacific", 'to' => "America/Los_Angeles"),
				array('from' => "US/Pacific-New", 'to' => "America/Los_Angeles"),
				array('from' => "US/Samoa", 'to' => "Pacific/Pago_Pago"),
				array('from' => "UTC", 'to' => "Etc/GMT"),
				array('from' => "Universal", 'to' => "Etc/GMT"),
				array('from' => "W-SU", 'to' => "Europe/Moscow"),
				array('from' => "Zulu", 'to' => "Etc/GMT"),
				/* Total: 113 */

		);
/* TODO: implement
		TimeZone::EDisplayType styles[] = { TimeZone::SHORT, TimeZone::LONG };
		UBool useDst[] = { false, true };
		int32_t noLoc = uloc_countAvailable();

		if(isICUVersionAtLeast(ICU_37)) {
				errln("This test needs to be fixed. This test fails in exhaustive mode because we need to implement generic timezones.\n");
		}

		int32_t i, j, k, loc;
		UnicodeString fromName, toName;
		TimeZone *from = NULL, *to = NULL;
		for(i = 0; i < (int32_t)(sizeof(kData)/sizeof(kData[0])); i++) {
				from = TimeZone::createTimeZone(kData[i].from);
				to = TimeZone::createTimeZone(kData[i].to);
				if(!from->hasSameRules(*to)) {
						errln("different at %i\n", i);
				}
				if(!quick && isICUVersionAtLeast(ICU_37)) {
						errln("This test needs to be fixed. This test fails in exhaustive mode because we need to implement generic timezones.\n");
						for(loc = 0; loc < noLoc; loc++) {
								const char* locale = uloc_getAvailable(loc); 
								for(j = 0; j < (int32_t)(sizeof(styles)/sizeof(styles[0])); j++) {
										for(k = 0; k < (int32_t)(sizeof(useDst)/sizeof(useDst[0])); k++) {
												fromName.remove();
												toName.remove();
												from->getDisplayName(useDst[k], styles[j],locale, fromName);
												to->getDisplayName(useDst[k], styles[j], locale, toName);
												if(fromName.compare(toName) != 0) {
														errln("Fail: Expected "+toName+" but got " + prettify(fromName) 
																+ " for locale: " + locale + " index: "+ loc 
																+ " to id "+ kData[i].to
																+ " from id " + kData[i].from);
												}
										}
								}
						}
				} else {
						fromName.remove();
						toName.remove();
						from->getDisplayName(fromName);
						to->getDisplayName(toName);
						if(fromName.compare(toName) != 0) {
								errln("Fail: Expected "+toName+" but got " + fromName);
						}
				}
				delete from;
				delete to;
		}
*/
	}

/**
 * Test the basic functionality of the getDisplayName() API.
 *
 * @bug 4112869
 * @bug 4028006
 *
 * See also API change request A41.
 *
 * 4/21/98 - make smarter, so the test works if the ext resources
 * are present or not.
 */
	public function testDisplayName()
	{
		$enLocale = $this->tm->getLocale('en_US');

		$i = 0;
		$zone = $this->tm->createTimeZone('America/Los_Angeles');
		$name = $zone->getDisplayName($enLocale);
		$this->assertEquals('Pacific Standard Time', $name);

		//*****************************************************************
		// THE FOLLOWING LINES MUST BE UPDATED IF THE LOCALE DATA CHANGES
		// THE FOLLOWING LINES MUST BE UPDATED IF THE LOCALE DATA CHANGES
		// THE FOLLOWING LINES MUST BE UPDATED IF THE LOCALE DATA CHANGES
		//*****************************************************************

		$data = array(
			array(false, AgaviTimeZone::SHORT, 'PST'),
			array(true,  AgaviTimeZone::SHORT, 'PDT'),
			array(false, AgaviTimeZone::LONG, 'Pacific Standard Time'),
			array(true,  AgaviTimeZone::LONG, 'Pacific Daylight Time'),
		);

		foreach($data as $item) {
			$name = $zone->getDisplayName($item[0], $item[1], $enLocale);
			$this->assertEquals($item[2], $name);

			$name = $zone->getDisplayName($item[0], $item[1]);
			$this->assertEquals($item[2], $name);
		}

		// Make sure that we don't display the DST name by constructing a fake
		// PST zone that has DST all year long.
		$zone2 = new AgaviSimpleTimeZone($this->tm, 0, "America/Los_Angeles");

		$zone2->setStartRule(AgaviDateDefinitions::JANUARY, 1, 0, 0);
		$zone2->setEndRule(AgaviDateDefinitions::DECEMBER, 31, 0, 0);

		$inDaylight = '';
		if($zone2->inDaylightTime(0)) {
			$inDaylight = 'true';
		} else {
			$inDaylight = 'false';
		}
//		logln(UnicodeString("Modified PST inDaylightTime->") + inDaylight );

		$name = $zone2->getDisplayName($enLocale);
		$this->assertEquals('Pacific Standard Time', $name);

		// Make sure we get the default display format for Locales
		// with no display name data.
		$mt_MT = $this->tm->getLocale("mt_MT");

		$name = $zone->getDisplayName($mt_MT);
		//*****************************************************************
		// THE FOLLOWING LINE MUST BE UPDATED IF THE LOCALE DATA CHANGES
		// THE FOLLOWING LINE MUST BE UPDATED IF THE LOCALE DATA CHANGES
		// THE FOLLOWING LINE MUST BE UPDATED IF THE LOCALE DATA CHANGES
		//*****************************************************************

		$this->assertTrue($name == 'GMT-08:00' || 
											$name == 'GMT-8:00'  || 
											$name == 'GMT-0800'  || 
											$name == 'GMT-800',    'Expected GMT-08:00 or something similar for PST in mt_MT but got ' . $name . ' in ' . $zone->getId());
//	************************************************************
//	THE ABOVE FAILURE MAY JUST MEAN THE LOCALE DATA HAS CHANGED
//	************************************************************

		// Now try a non-existent zone
		$zone2 = new AgaviSimpleTimeZone($this->tm, 90*60*1000, "xyzzy");
		$name = $zone2->getDisplayName($enLocale);

		$this->assertTrue($name == 'GMT+01:30' || 
											$name == 'GMT+1:30'  || 
											$name == 'GMT+0130'  || 
											$name == 'GMT+130',    'Expected GMT+01:30 or something similar but got ' . $name);
	}

/**
 * @bug 4107276
 */
	public function testDSTSavings()
	{
		// It might be better to find a way to integrate this test into the main TimeZone
		// tests above, but I don't have time to figure out how to do this (or if it's
		// even really a good idea).  Let's consider that a future.  --rtg 1/27/98
		$tz = new AgaviSimpleTimeZone($this->tm, -5 * AgaviDateDefinitions::MILLIS_PER_HOUR, "dstSavingsTest",
																		AgaviDateDefinitions::MARCH, 1, 0, 0, AgaviDateDefinitions::SEPTEMBER, 1, 0, 0,
																		(0.5 * AgaviDateDefinitions::MILLIS_PER_HOUR));

		$this->assertEquals(-5 * AgaviDateDefinitions::MILLIS_PER_HOUR, $tz->getRawOffset());

		$this->assertTrue($tz->useDaylightTime(), 'Test time zone should use DST but claims it doesn\'t.');
		
		$this->assertEquals(0.5 * AgaviDateDefinitions::MILLIS_PER_HOUR, $tz->getDSTSavings());

		$offset = $tz->getOffset(AgaviGregorianCalendar::AD, 1998, AgaviDateDefinitions::JANUARY, 1, 
															AgaviDateDefinitions::THURSDAY, 10 * AgaviDateDefinitions::MILLIS_PER_HOUR);
		$this->assertEquals(-5 * AgaviDateDefinitions::MILLIS_PER_HOUR, $offset);

		$offset = $tz->getOffset(AgaviGregorianCalendar::AD, 1998, AgaviDateDefinitions::JUNE, 1, AgaviDateDefinitions::MONDAY,
															10 * AgaviDateDefinitions::MILLIS_PER_HOUR);
		$this->assertEquals(-4.5 * AgaviDateDefinitions::MILLIS_PER_HOUR, $offset);

		$tz->setDSTSavings(AgaviDateDefinitions::MILLIS_PER_HOUR);
		$offset = $tz->getOffset(AgaviGregorianCalendar::AD, 1998, AgaviDateDefinitions::JANUARY, 1,
															AgaviDateDefinitions::THURSDAY, 10 * AgaviDateDefinitions::MILLIS_PER_HOUR);
		$this->assertEquals(-5 * AgaviDateDefinitions::MILLIS_PER_HOUR, $offset);

		$offset = $tz->getOffset(AgaviGregorianCalendar::AD, 1998, AgaviDateDefinitions::JUNE, 1, AgaviDateDefinitions::MONDAY,
															10 * AgaviDateDefinitions::MILLIS_PER_HOUR);
		$this->assertEquals(-4 * AgaviDateDefinitions::MILLIS_PER_HOUR, $offset);
	}

/**
 * @bug 4107570
 */
	public function testAlternateRules()
	{
		// Like TestDSTSavings, this test should probably be integrated somehow with the main
		// test at the top of this class, but I didn't have time to figure out how to do that.
		//                      --rtg 1/28/98

		$tz = new AgaviSimpleTimeZone($this->tm, -5 * AgaviDateDefinitions::MILLIS_PER_HOUR, "alternateRuleTest");

		// test the day-of-month API
		$tz->setStartRule(AgaviDateDefinitions::MARCH, 10, 12 * AgaviDateDefinitions::MILLIS_PER_HOUR);
		$tz->setEndRule(AgaviDateDefinitions::OCTOBER, 20, 12 * AgaviDateDefinitions::MILLIS_PER_HOUR);

		$offset = $tz->getOffset(AgaviGregorianCalendar::AD, 1998, AgaviDateDefinitions::MARCH, 5,
															AgaviDateDefinitions::THURSDAY, 10 * AgaviDateDefinitions::MILLIS_PER_HOUR);
		$this->assertEquals(-5 * AgaviDateDefinitions::MILLIS_PER_HOUR, $offset);

		$offset = $tz->getOffset(AgaviGregorianCalendar::AD, 1998, AgaviDateDefinitions::MARCH, 15,
															AgaviDateDefinitions::SUNDAY, 10 * AgaviDateDefinitions::MILLIS_PER_HOUR);
		$this->assertEquals(-4 * AgaviDateDefinitions::MILLIS_PER_HOUR, $offset);

		$offset = $tz->getOffset(AgaviGregorianCalendar::AD, 1998, AgaviDateDefinitions::OCTOBER, 15,
															AgaviDateDefinitions::THURSDAY, 10 * AgaviDateDefinitions::MILLIS_PER_HOUR);
		$this->assertEquals(-4 * AgaviDateDefinitions::MILLIS_PER_HOUR, $offset);

		$offset = $tz->getOffset(AgaviGregorianCalendar::AD, 1998, AgaviDateDefinitions::OCTOBER, 25,
															AgaviDateDefinitions::SUNDAY, 10 * AgaviDateDefinitions::MILLIS_PER_HOUR);
		$this->assertEquals(-5 * AgaviDateDefinitions::MILLIS_PER_HOUR, $offset);

		// test the day-of-week-after-day-in-month API
		$tz->setStartRule(AgaviDateDefinitions::MARCH, 10, AgaviDateDefinitions::FRIDAY, intval(12 * AgaviDateDefinitions::MILLIS_PER_HOUR), true);
		$tz->setEndRule(AgaviDateDefinitions::OCTOBER, 20, AgaviDateDefinitions::FRIDAY, intval(12 * AgaviDateDefinitions::MILLIS_PER_HOUR), false);

		$offset = $tz->getOffset(AgaviGregorianCalendar::AD, 1998, AgaviDateDefinitions::MARCH, 11,
															AgaviDateDefinitions::WEDNESDAY, 10 * AgaviDateDefinitions::MILLIS_PER_HOUR);
		$this->assertEquals(-5 * AgaviDateDefinitions::MILLIS_PER_HOUR, $offset);

		$offset = $tz->getOffset(AgaviGregorianCalendar::AD, 1998, AgaviDateDefinitions::MARCH, 14,
															AgaviDateDefinitions::SATURDAY, 10 * AgaviDateDefinitions::MILLIS_PER_HOUR);
		$this->assertEquals(-4 * AgaviDateDefinitions::MILLIS_PER_HOUR, $offset);

		$offset = $tz->getOffset(AgaviGregorianCalendar::AD, 1998, AgaviDateDefinitions::OCTOBER, 15,
															AgaviDateDefinitions::THURSDAY, 10 * AgaviDateDefinitions::MILLIS_PER_HOUR);
		$this->assertEquals(-4 * AgaviDateDefinitions::MILLIS_PER_HOUR, $offset);

		$offset = $tz->getOffset(AgaviGregorianCalendar::AD, 1998, AgaviDateDefinitions::OCTOBER, 17,
															AgaviDateDefinitions::SATURDAY, 10 * AgaviDateDefinitions::MILLIS_PER_HOUR);
		$this->assertEquals(-5 * AgaviDateDefinitions::MILLIS_PER_HOUR, $offset);
	}

	public function testFractionalDST()
	{
		$tz_icu = $this->tm->createTimeZone('Australia/Lord_Howe'); // 30 min offset
		$dst_icu = $tz_icu->getDSTSavings();

		$expected = 1800000;
		$this->assertEquals((float) $expected, $dst_icu);
	}

	public function testHistorical()
	{
		$H = AgaviDateDefinitions::MILLIS_PER_HOUR;

		$data = array(
				// Add transition points (before/after) as desired to test historical
				// behavior.
				array('id' => "America/Los_Angeles", 'time' => 638963999,  'offset' => -8*$H), // Sun Apr 01 01:59:59 GMT-08:00 1990
				array('id' => "America/Los_Angeles", 'time' => 638964000,  'offset' => -7*$H), // Sun Apr 01 03:00:00 GMT-07:00 1990
				array('id' => "America/Los_Angeles", 'time' => 657104399,  'offset' => -7*$H), // Sun Oct 28 01:59:59 GMT-07:00 1990
				array('id' => "America/Los_Angeles", 'time' => 657104400,  'offset' => -8*$H), // Sun Oct 28 01:00:00 GMT-08:00 1990
				array('id' => "America/Goose_Bay",   'time' => -116445601, 'offset' => -4*$H), // Sun Apr 24 01:59:59 GMT-04:00 1966
				array('id' => "America/Goose_Bay",   'time' => -116445600, 'offset' => -3*$H), // Sun Apr 24 03:00:00 GMT-03:00 1966
				array('id' => "America/Goose_Bay",   'time' => -100119601, 'offset' => -3*$H), // Sun Oct 30 01:59:59 GMT-03:00 1966
				array('id' => "America/Goose_Bay",   'time' => -100119600, 'offset' => -4*$H), // Sun Oct 30 01:00:00 GMT-04:00 1966
				array('id' => "America/Goose_Bay",   'time' => -84391201,  'offset' => -4*$H), // Sun Apr 30 01:59:59 GMT-04:00 1967
				array('id' => "America/Goose_Bay",   'time' => -84391200,  'offset' => -3*$H), // Sun Apr 30 03:00:00 GMT-03:00 1967
				array('id' => "America/Goose_Bay",   'time' => -68670001,  'offset' => -3*$H), // Sun Oct 29 01:59:59 GMT-03:00 1967
				array('id' => "America/Goose_Bay",   'time' => -68670000,  'offset' => -4*$H), // Sun Oct 29 01:00:00 GMT-04:00 1967
		);

		foreach($data as $item) {
			$id = $item['id'];
			$tz = $this->tm->createTimeZone($id);
			$this->assertEquals($id, $tz->getId());

			$raw = 0;
			$dst = 0;
			$when = $item['time'] * AgaviDateDefinitions::MILLIS_PER_SECOND;
			$tz->getOffsetRef($when, false, $raw, $dst);
			$this->assertEquals($item['offset'], $raw + $dst, 'Zone ' . $id);
		}
	}
	
	// Test that a transition at the end of February is handled correctly.
	public function testFebruary() {
		// Time zone with daylight savings time from the first Sunday in November
		// to the last Sunday in February.
		// Similar to the new rule for Brazil (Sao Paulo) in tzdata2006n.
		// 
		// Note: In tzdata2007h, the rule had changed, so no actual zones uses 
		// lastSun in Feb anymore.
		$tz1 = new AgaviSimpleTimeZone($this->tm, -3 * AgaviDateDefinitions::MILLIS_PER_HOUR,          // raw offset: 3h before (west of) GMT
		                               "nov-feb",
		                               AgaviDateDefinitions::NOVEMBER, 1, AgaviDateDefinitions::SUNDAY,   // start: November, first, Sunday
		                               0,                               //        midnight wall time
		                               AgaviDateDefinitions::FEBRUARY, -1, AgaviDateDefinitions::SUNDAY,  // end:   February, last, Sunday
		                               0                                //        midnight wall time
		);

		// Now hardcode the same rules as for Brazil, so that we cover the intended code 
		// even when in the future zoneinfo hardcodes these transition dates. 
		$tz2 = new AgaviSimpleTimeZone($this->tm, -3 * AgaviDateDefinitions::MILLIS_PER_HOUR,          // raw offset: 3h before (west of) GMT 
		                               "nov-feb2", 
		                               AgaviDateDefinitions::NOVEMBER, 1, -AgaviDateDefinitions::SUNDAY,  // start: November, 1 or after, Sunday 
		                               0,                               //        midnight wall time 
		                               AgaviDateDefinitions::FEBRUARY, -29, -AgaviDateDefinitions::SUNDAY,// end:   February, 29 or before, Sunday 
		                               0                                //        midnight wall time 
		); 

		// Gregorian calendar with the UTC time zone for getting sample test date/times.
		$gc = new AgaviGregorianCalendar(AgaviTimeZone::getGMT($this->tm));

		$data = array(
			array( 'year' => 2006, 'month' => AgaviDateDefinitions::NOVEMBER, 'day' =>  5, 'hour' => 02, 'minute' => 59, 'second' => 59, 'offsetHours' => -3 ),
			array( 'year' => 2006, 'month' => AgaviDateDefinitions::NOVEMBER, 'day' =>  5, 'hour' => 03, 'minute' => 00, 'second' => 00, 'offsetHours' => -2 ),
			array( 'year' => 2007, 'month' => AgaviDateDefinitions::FEBRUARY, 'day' => 25, 'hour' => 01, 'minute' => 59, 'second' => 59, 'offsetHours' => -2 ),
			array( 'year' => 2007, 'month' => AgaviDateDefinitions::FEBRUARY, 'day' => 25, 'hour' => 02, 'minute' => 00, 'second' => 00, 'offsetHours' => -3 ),

			array( 'year' => 2007, 'month' => AgaviDateDefinitions::NOVEMBER, 'day' =>  4, 'hour' => 02, 'minute' => 59, 'second' => 59, 'offsetHours' => -3 ),
			array( 'year' => 2007, 'month' => AgaviDateDefinitions::NOVEMBER, 'day' =>  4, 'hour' => 03, 'minute' => 00, 'second' => 00, 'offsetHours' => -2 ),
			array( 'year' => 2008, 'month' => AgaviDateDefinitions::FEBRUARY, 'day' => 24, 'hour' => 01, 'minute' => 59, 'second' => 59, 'offsetHours' => -2 ),
			array( 'year' => 2008, 'month' => AgaviDateDefinitions::FEBRUARY, 'day' => 24, 'hour' => 02, 'minute' => 00, 'second' => 00, 'offsetHours' => -3 ),

			array( 'year' => 2008, 'month' => AgaviDateDefinitions::NOVEMBER, 'day' =>  2, 'hour' => 02, 'minute' => 59, 'second' => 59, 'offsetHours' => -3 ),
			array( 'year' => 2008, 'month' => AgaviDateDefinitions::NOVEMBER, 'day' =>  2, 'hour' => 03, 'minute' => 00, 'second' => 00, 'offsetHours' => -2 ),
			array( 'year' => 2009, 'month' => AgaviDateDefinitions::FEBRUARY, 'day' => 22, 'hour' => 01, 'minute' => 59, 'second' => 59, 'offsetHours' => -2 ),
			array( 'year' => 2009, 'month' => AgaviDateDefinitions::FEBRUARY, 'day' => 22, 'hour' => 02, 'minute' => 00, 'second' => 00, 'offsetHours' => -3 ),

			array( 'year' => 2009, 'month' => AgaviDateDefinitions::NOVEMBER, 'day' =>  1, 'hour' => 02, 'minute' => 59, 'second' => 59, 'offsetHours' => -3 ),
			array( 'year' => 2009, 'month' => AgaviDateDefinitions::NOVEMBER, 'day' =>  1, 'hour' => 03, 'minute' => 00, 'second' => 00, 'offsetHours' => -2 ),
			array( 'year' => 2010, 'month' => AgaviDateDefinitions::FEBRUARY, 'day' => 28, 'hour' => 01, 'minute' => 59, 'second' => 59, 'offsetHours' => -2 ),
			array( 'year' => 2010, 'month' => AgaviDateDefinitions::FEBRUARY, 'day' => 28, 'hour' => 02, 'minute' => 00, 'second' => 00, 'offsetHours' => -3 ),
		);

		$timezones = array($tz1, $tz2);

#	    TimeZone *tz;
#	    UDate dt;
#	    int32_t t, i, raw, dst;
		$t = $i = $raw = $dst = null;
		for($t = 0; $t < count($timezones); ++$t) {
			$tz = clone $timezones[$t];
			for($i = 0; $i < count($data); ++$i) {
				$gc->set($data[$i]['year'], $data[$i]['month'], $data[$i]['day'],
				          $data[$i]['hour'], $data[$i]['minute'], $data[$i]['second']);
				$dt = $gc->getTime();
				$tz->getOffsetRef($dt, false, $raw, $dst);
				if(($raw + $dst) != $data[$i]['offsetHours'] * AgaviDateDefinitions::MILLIS_PER_HOUR) {
					$this->fail(sprintf("test case %d.%d: tz.getOffset(%04d-%02d-%02d %02d:%02d:%02d) returns %d+%d != %d",
					                    $t, $i,
					                    $data[$i]['year'], $data[$i]['month'] + 1, $data[$i]['day'],
					                    $data[$i]['hour'], $data[$i]['minute'], $data[$i]['second'],
					                    $raw, $dst, $data[$i]['offsetHours'] * AgaviDateDefinitions::MILLIS_PER_HOUR)
					);
				}
			}
		}
	}
}
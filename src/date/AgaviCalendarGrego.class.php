<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2005-2009 the Agavi Project.                                |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE   |
// | file that was distributed with this source code. You can also view the    |
// | LICENSE file online at http://www.agavi.org/LICENSE.txt                   |
// |   vi: set noexpandtab:                                                    |
// |   Local Variables:                                                        |
// |   indent-tabs-mode: t                                                     |
// |   End:                                                                    |
// +---------------------------------------------------------------------------+

/**
 * A utility class providing proleptic Gregorian calendar functions used by 
 * time zone and calendar code.
 *
 * Note: Unlike AgaviGregorianCalendar, all computations performed by this
 * class occur in the pure proleptic GregorianCalendar.
 *
 * @package    agavi
 * @subpackage date
 *
 * @author     Dominik del Bondio <ddb@bitxtender.com>
 * @author     The ICU Project
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.11.0
 *
 * @version    $Id$
 */
final class AgaviCalendarGrego
{
	/**
	 * Private constructor to prevent instantiation of this class
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	private function __construct()
	{
	}

	const JULIAN_1_CE    = 1721426; // January 1, 1 CE Gregorian
	const JULIAN_1970_CE = 2440588; // January 1, 1970 CE Gregorian

	private static $DAYS_BEFORE = array(
		0,31,59,90,120,151,181,212,243,273,304,334,
		0,31,60,91,121,152,182,213,244,274,305,335
	);

	private static $MONTH_LENGTH = array(
		31,28,31,30,31,30,31,31,30,31,30,31,
		31,29,31,30,31,30,31,31,30,31,30,31
	);

	/**
	 * Return TRUE if the given year is a leap year.
	 * 
	 * @param      int  Gregorian year, with 0 == 1 BCE, -1 == 2 BCE, etc.
	 * 
	 * @return     bool If the year is a leap year.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	public static function isLeapYear($year)
	{
		// year&0x3 == year%4
		return (($year & 0x3) == 0) && (($year % 100 != 0) || ($year % 400 == 0));
	}

	/**
	 * Return the number of days in the given month.
	 *
	 * @param      int Gregorian year, with 0 == 1 BCE, -1 == 2 BCE, etc.
	 * @param      int Month 0-based month, with 0==Jan
	 * 
	 * @return     int The number of days in the given month
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	public static function monthLength($year, $month)
	{
		return self::$MONTH_LENGTH[$month + (self::isLeapYear($year) ? 12 : 0)];
	}

	/**
	 * Return the length of a previous month of the Gregorian calendar.
	 * 
	 * @param      int The extended year
	 * @param      int The 0-based month number
	 * @return     int The number of days in the month previous to the given month
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	public static function previousMonthLength($y, $m)
	{
		return ($m > 0) ? self::monthLength($y, $m - 1) : 31;
	}

	/**
	 * Convert a year, month, and day-of-month, given in the proleptic
	 * Gregorian calendar, to 1970 epoch days.
	 * 
	 * @param      int   Gregorian year, with 0 == 1 BCE, -1 == 2 BCE, etc.
	 * @param      int   0-based month, with 0==Jan
	 * @param      int   1-based day of month
	 * 
	 * @return     float The day number, with day 0 == Jan 1 1970
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	public static function fieldsToDay($year, $month, $dom)
	{
		$y = $year - 1;

		$julian = 365 * $y + floor($y / 4) + (self::JULIAN_1_CE - 3) + // Julian cal
				floor($y / 400) - floor($y / 100) + 2 + // => Gregorian cal
				self::$DAYS_BEFORE[$month + (self::isLeapYear($year) ? 12 : 0)] + $dom; // => month/dom

		return $julian - self::JULIAN_1970_CE; // JD => epoch day
	}
	
	/**
	 * Convert a 1970-epoch day number to proleptic Gregorian year,
	 * month, day-of-month, and day-of-week.
	 * 
	 * @param      float Day 1970-epoch day (integral value)
	 * @param      int   Output parameter to receive year
	 * @param      int   Output parameter to receive month (0-based, 0==Jan)
	 * @param      int   Output parameter to receive day-of-month (1-based)
	 * @param      int   Output parameter to receive day-of-week (1-based, 1==Sun)
	 * @param      int   Output parameter to receive day-of-year (1-based)
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	public static function dayToFields($day, &$year, &$month, &$dom, &$dow, &$doy = 0)
	{

		// Convert from 1970 CE epoch to 1 CE epoch (Gregorian calendar)
		$day += self::JULIAN_1970_CE - self::JULIAN_1_CE;

		// Convert from the day number to the multiple radix
		// representation.  We use 400-year, 100-year, and 4-year cycles.
		// For example, the 4-year cycle has 4 years + 1 leap day; giving
		// 1461 == 365*4 + 1 days.
		$n400 = (int) AgaviToolkit::floorDivide($day, 146097, $doy); // 400-year cycle length
		$n100 = (int) AgaviToolkit::floorDivide($doy, 36524, $doy); // 100-year cycle length
		$n4   = (int) AgaviToolkit::floorDivide($doy, 1461, $doy); // 4-year cycle length
		$n1   = (int) AgaviToolkit::floorDivide($doy, 365, $doy);
		$year = 400 * $n400 + 100 * $n100 + 4 * $n4 + $n1;
		if($n100 == 4 || $n1 == 4) {
			$doy = 365; // Dec 31 at end of 4- or 400-year cycle
		} else {
			++$year;
		}
		
		$isLeap = self::isLeapYear($year);
		
		// Gregorian day zero is a Monday.
		$dow = (int) fmod($day + 1, 7);
		$dow += ($dow < 0) ? (AgaviDateDefinitions::SUNDAY + 7) : AgaviDateDefinitions::SUNDAY;

		// Common Julian/Gregorian calculation
		$correction = 0;
		$march1 = $isLeap ? 60 : 59; // zero-based DOY for March 1
		if($doy >= $march1) {
			$correction = $isLeap ? 1 : 2;
		}
		$month = (int) ((12 * ($doy + $correction) + 6) / 367); // zero-based month
		$dom = $doy - self::$DAYS_BEFORE[$month + ($isLeap ? 12 : 0)] + 1; // one-based DOM
		$doy++; // one-based doy
	}

	/**
	 * Converts Julian day to time as milliseconds.
	 * 
	 * @param      int   The given Julian day number.
	 * 
	 * @return     float Time as milliseconds.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	public static function julianDayToMillis($julian)
	{
		return ($julian - AgaviDateDefinitions::EPOCH_START_AS_JULIAN_DAY) * AgaviDateDefinitions::MILLIS_PER_DAY;
	}

	/**
	 * Converts time as milliseconds to Julian day.
	 * 
	 * @param      float The given milliseconds.
	 * 
	 * @return     int   The Julian day number.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	public static function millisToJulianDay($millis)
	{
	 return (AgaviDateDefinitions::EPOCH_START_AS_JULIAN_DAY + floor($millis / AgaviDateDefinitions::MILLIS_PER_DAY));
	}

	/** 
	 * Calculates the Gregorian day shift value for an extended year.
	 *
	 * @param      int Extended year 
	 *
	 * @return     int Number of days to ADD to Julian in order to convert 
	 *                 from J->G
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	public static function gregorianShift($eyear)
	{
		$y = $eyear - 1;
		$gregShift = floor($y / 400) - floor($y / 100) + 2;
		return $gregShift;
	}
}

?>
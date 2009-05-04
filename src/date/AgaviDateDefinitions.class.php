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
 * Field definitions for the AgaviCalendar and day of week and months constants
 *
 * Based on ICU:
 *  icu/trunk/source/i18n/ucal.h              r19775
 *  icu/trunk/source/i18n/unicode/calendar.h  r20375
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
final class AgaviDateDefinitions
{
	/** 
	 * Field number indicating the era, e.g., {@link AgaviGregorianCalendar::AD} 
	 * or {@link AgaviGregorianCalendar::BC} in the Gregorian (Julian) calendar.
	 * 
	 * This is a calendar-specific value.
	 * 
	 * @since      0.11.0
	 */
	const ERA                     = 0;

	/**
	 * Field number indicating the year. This is a calendar-specific value.
	 * 
	 * @since      0.11.0
	 */
	const YEAR                    = 1;

	/**
	 * Field number indicating the month. 
	 * 
	 * This is a calendar-specific value. The first month of the year is 
	 * {@link JANUARY}; the last depends on the number of months in a year.
	 * 
	 * @see        JANUARY
	 * @see        FEBRUARY
	 * @see        MARCH
	 * @see        APRIL
	 * @see        MAY
	 * @see        JUNE
	 * @see        JULY
	 * @see        AUGUST
	 * @see        SEPTEMBER
	 * @see        OCTOBER
	 * @see        NOVEMBER
	 * @see        DECEMBER
	 * @see        UNDECIMBER
	 * 
	 * @since      0.11.0
	 */
	const MONTH                   = 2;

	/**
	 * Field number indicating the week number within the current year.
	 * 
	 * The first week of the year, as defined by <kbd>FIRST_DAY_OF_WEEK</kbd> and 
	 * <kbd>MINIMAL_DAYS_IN_FIRST_WEEK</kbd> attributes, has value 1. Subclasses 
	 * define the value of <kbd>WEEK_OF_YEAR</kbd> for days  before the first week
	 * of the year.
	 * 
	 * @since      0.11.0
	 */
	const WEEK_OF_YEAR            = 3;

	/**
	 * Field number indicating the week number within the current month.
	 * 
	 * The first week of the month, as defined by <kbd>FIRST_DAY_OF_WEEK</kbd> and
	 * <kbd>MINIMAL_DAYS_IN_FIRST_WEEK</kbd> attributes, has value 1.
	 * Subclasses define the value of <kbd>WEEK_OF_MONTH</kbd> for days before 
	 * the first week of the month.
	 * 
	 * @see        getFirstDayOfWeek
	 * @see        getMinimalDaysInFirstWeek
	 * 
	 * @since      0.11.0
	 */
	const WEEK_OF_MONTH           = 4;

	/**
	 * Field number indicating the day of the month.
	 * 
	 * This is a synonym for {@link DAY_OF_MONTH}. The first day of the month 
	 * has value 1.
	 * 
	 * @see        DAY_OF_MONTH
	 * 
	 * @since      0.11.0
	 */
	const DATE                    = 5;

	/**
	 * Field number indicating the day number within the current year.
	 * 
	 * The first day of the year has value 1.
	 * 
	 * @since      0.11.0
	 */
	const DAY_OF_YEAR             = 6;

	/**
	 * Field number indicating the day of the week.
	 * 
	 * This field takes values {@link SUNDAY}, {@link MONDAY}, {@link TUESDAY}, 
	 * {@link WEDNESDAY}, {@link THURSDAY}, {@link FRIDAY}, and {@link SATURDAY}.
	 * 
	 * @see        SUNDAY
	 * @see        MONDAY
	 * @see        TUESDAY
	 * @see        WEDNESDAY
	 * @see        THURSDAY
	 * @see        FRIDAY
	 * @see        SATURDAY
	 * 
	 * @since      0.11.0
	 */
	const DAY_OF_WEEK             = 7;

	/**
	 * Field number indicating the ordinal number of the day of the week within 
	 * the current month.
	 * 
	 * Together with the {@link DAY_OF_WEEK} field, this uniquely specifies a day
	 * within a month.  Unlike {@link WEEK_OF_MONTH} and {@link WEEK_OF_YEAR},
	 * this field's value does <i>not</i> depend on
	 * {@link AgaviCalendar::getFirstDayOfWeek()} or
	 * {@link AgaviCalendar::getMinimalDaysInFirstWeek()}. 
	 * <kbd>DAY_OF_MONTH 1</kbd> through <kbd>7</kbd> always correspond to 
	 * <kbd>{@link DAY_OF_WEEK_IN_MONTH} 1</kbd>; <kbd>8</kbd> through 
	 * <kbd>15</kbd> correspond to <kbd>{@link DAY_OF_WEEK_IN_MONTH} 2</kbd>, 
	 * and so on. <kbd>{@link DAY_OF_WEEK_IN_MONTH} 0</kbd> indicates the week 
	 * before <kbd>{@link DAY_OF_WEEK_IN_MONTH} 1</kbd>. Negative values count 
	 * back from the end of the month, so the last Sunday of a month is specified
	 * as <kbd>{@link DAY_OF_WEEK} = {@link SUNDAY}, 
	 * {@link DAY_OF_WEEK_IN_MONTH} = -1</kbd>. Because negative values count 
	 * backward they will usually be aligned differently within the month than 
	 * positive values.  For example, if a month has 31 days, 
	 * <kbd>{@link DAY_OF_WEEK_IN_MONTH} -1</kbd> will overlap
	 * <kbd>{@link DAY_OF_WEEK_IN_MONTH} 5</kbd> and the end of <kbd>4</kbd>.
	 * 
	 * @see        DAY_OF_WEEK
	 * @see        WEEK_OF_MONTH
	 * 
	 * @since      0.11.0
	 */
	const DAY_OF_WEEK_IN_MONTH    = 8;

	/**
	 * Field number indicating  whether the {@link HOUR} is before or after noon.
	 * 
	 * E.g., at 10:04:15.250 PM the <kbd>AM_PM</kbd> is {@link PM}.
	 * 
	 * @see        AM
	 * @see        PM
	 * @see        HOUR
	 * 
	 * @since      0.11.0
	 */
	const AM_PM                   = 9;

	/**
	 * Field number indicating the hour of the morning or afternoon.
	 * 
	 * <kbd>HOUR</kbd> is used for the 12-hour clock.
	 * E.g., at 10:04:15.250 PM the <kbd>HOUR</kbd> is 10.
	 * 
	 * @see        AM_PM
	 * @see        HOUR_OF_DAY
	 * 
	 * @since      0.11.0
	 */
	const HOUR                    = 10;

	/**
	 * Field number indicating the hour of the day.
	 * 
	 * <kbd>HOUR_OF_DAY</kbd> is used for the 24-hour clock.
	 * E.g., at 10:04:15.250 PM the <kbd>HOUR_OF_DAY</kbd> is 22.
	 * 
	 * @see #HOUR
	 * 
	 * @since      0.11.0
	 */
	const HOUR_OF_DAY             = 11;

	/**
	 * Field number indicating the  minute within the hour.
	 * 
	 * E.g., at 10:04:15.250 PM the <kbd>MINUTE</kbd> is 4.
	 * 
	 * @since      0.11.0
	 */
	const MINUTE                  = 12;

	/**
	 * Field number indicating the second within the minute.
	 * 
	 * E.g., at 10:04:15.250 PM the <kbd>SECOND</kbd> is 15.
	 * 
	 * @since      0.11.0
	 */
	const SECOND                  = 13;

	/**
	 * Field number indicating the millisecond within the second.
	 * 
	 * E.g., at 10:04:15.250 PM the <kbd>MILLISECOND</kbd> is 250.
	 * 
	 * @since      0.11.0
	 */
	const MILLISECOND             = 14;

	/**
	 * Field number indicating the raw offset from GMT in milliseconds.
	 * 
	 * @since      0.11.0
	 */
	const ZONE_OFFSET             = 15;

	/**
	 * Field number indicating the  daylight savings offset in milliseconds.
	 * 
	 * @since      0.11.0
	 */
	const DST_OFFSET              = 16;
	
	/**
	 * Field number indicating the extended year corresponding to the
	 * {@link WEEK_OF_YEAR} field.
	 * 
	 * This may be one greater or less than the value of {@link EXTENDED_YEAR}.
	 * 
	 * @since      0.11.0
	 */
	const YEAR_WOY                = 17;

	/**
	 * Field number indicating the localized day of week.
	 * 
	 * This will be a value from 1 to 7 inclusive, with 1 being the localized 
	 * first day of the week.
	 * 
	 * @since      0.11.0
	 */
	const DOW_LOCAL               = 18;
	
	/**
	 * Year of this calendar system, encompassing all supra-year fields.
	 *
	 * For  example, in Gregorian/Julian calendars, positive Extended Year values 
	 * indicate years AD, 1 BC = 0 extended, 2 BC = -1 extended, and so on.
	 * 
	 * @since      0.11.0
	 */
	const EXTENDED_YEAR           = 19;

	/**
	 * Field number indicating the modified Julian day number.
	 * 
	 * This is different from the conventional Julian day number in two regards.
	 * First, it demarcates days at local zone midnight, rather than noon GMT.
	 * Second, it is a local number; that is, it depends on the local time
	 * zone. It can be thought of as a single number that encompasses all
	 * the date-related fields.
	 * 
	 * @since      0.11.0
	 */
	const JULIAN_DAY              = 20;

	/**
	 * Ranges from 0 to 23:59:59.999 (regardless of DST).
	 * 
	 * This field behaves <i>exactly</i> like a composite of all time-related 
	 * fields, not  including the zone fields. As such, it also reflects 
	 * discontinuities of  those fields on DST transition days. On a day of DST 
	 * onset, it will jump  forward. On a day of DST cessation, it will jump
	 * backward.  This reflects the fact that it must be combined with the 
	 * {@link DST_OFFSET} field to obtain a unique local time value.
	 * 
	 * @since      0.11.0
	 */
	const MILLISECONDS_IN_DAY     = 21;
	
	/**
	 * Field count
	 * 
	 * @since      0.11.0
	 */
	const FIELD_COUNT             = 22;

	/**
	 * Field number indicating the day of the month.
	 * 
	 * This is a synonym for {@link DATE}. The first day of the month has value 1.
	 * 
	 * @see        DATE
	 * 
	 * @since      0.11.0
	 */
	const DAY_OF_MONTH            = AgaviDateDefinitions::DATE;

	/** January */
	const JANUARY                 = 0;
	/** February */
	const FEBRUARY                = 1;
	/** March */
	const MARCH                   = 2;
	/** April */
	const APRIL                   = 3;
	/** May */
	const MAY                     = 4;
	/** June */
	const JUNE                    = 5;
	/** July */
	const JULY                    = 6;
	/** August */
	const AUGUST                  = 7;
	/** September */
	const SEPTEMBER               = 8;
	/** October */
	const OCTOBER                 = 9;
	/** November */
	const NOVEMBER                = 10;
	/** December */
	const DECEMBER                = 11;
	/** Value of the <kbd>MONTH</kbd> field indicating the
		* thirteenth month of the year. Although the Gregorian calendar
		* does not use this value, lunar calendars do.
		*/
	const UNDECIMBER              = 12;

	/** Sunday */
	const SUNDAY                  = 1;
	/** Monday */
	const MONDAY                  = 2;
	/** Tuesday */
	const TUESDAY                 = 3;
	/** Wednesday */
	const WEDNESDAY               = 4;
	/** Thursday */
	const THURSDAY                = 5;
	/** Friday */
	const FRIDAY                  = 6;
	/** Saturday */
	const SATURDAY                = 7;

	/** 
	 * The number of milliseconds per second
	 */
	const MILLIS_PER_SECOND          = 1000.0;
	/** 
	 * The number of milliseconds per minute
	 */
	const MILLIS_PER_MINUTE          = 60000.0;
	/**
	 * The number of milliseconds per hour
	 */
	const MILLIS_PER_HOUR            = 3600000.0;
	/**
	 * The number of milliseconds per day
	 */
	const MILLIS_PER_DAY             = 86400000.0;
	/**
	 * The number of milliseconds per week 
	 */
	const MILLIS_PER_WEEK            = 604800000.0; // 7 * MILLIS_PER_DAY

	/**
	 * The number of seconds per day
	 */
	const SECONDS_PER_DAY            = 86400;

	/**
	 * The start year of the epoch
	 */
	const EPOCH_YEAR                 = 1970;

	/**
	 * January the 1. in year 1 (Gregorian) as julian day
	 */
	const JAN_1_1_JULIAN_DAY         = 1721426.0;

	/**
	 * January 1, 1970 (Gregorian) as julian day
	 */
	const EPOCH_START_AS_JULIAN_DAY  = 2440588.0;

	private function __construct()
	{
	}
}

?>
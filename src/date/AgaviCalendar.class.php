<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2003-2007 the Agavi Project.                                |
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
 * 
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
abstract class AgaviCalendar
{
	const GREGORIAN = 'gregorian';

	/**
	 * @var        AgaviTranslationManager The translation manager instance.
	 * @since      0.11.0
	 */

	protected $translationManager;

	/**
	 * Returns the translation manager.
	 *
	 * @return     AgaviTranslationManager The translation manager.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getTranslationManager()
	{
		return $this->translationManager;
	}

	/**
	 * Initialize the variables to default values.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	protected function initVariables()
	{
		$this->fIsTimeSet = false;
		$this->fAreFieldsInSync = false;
		$this->fAreAllFieldsSet = false;
		$this->fAreFieldsVirtuallySet = false;
		$this->fNextStamp = self::kMinimumUserStamp;
		$this->fTime = 0;
		$this->fLenient = true;
		$this->fZone = null;
	}

	/**
	 * Called by the overload handler in the constructor. 
	 *
	 * @param      AgaviTimeZone The timezone to use.
	 * @param      AgaviLocale   The locale to use.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	protected function constructorOO(AgaviTimeZone $zone, AgaviLocale $locale)
	{
		$this->translationManager = $zone->getTranslationManager();
		$this->clear();
		$this->fZone = clone $zone;
		$this->setWeekCountData($locale, null);
	}

	/**
	 * Marker for end of resolve set (row or group).
	 */
	const RESOLVE_STOP  = -1;

	/**
	 * Value to be bitwised "ORed" against resolve table field values for 
	 * remapping.  Example: (UCAL_DATE | kResolveRemap) in 1st column will cause 
	 * 'UCAL_DATE' to be returned, but will not examine the value of UCAL_DATE.
	 */
	const RESOLVE_REMAP = 32;

	/**
	 * The minimum supported Julian day.  This value is equivalent to
	 * MIN_MILLIS.
	 */
	const MIN_JULIAN                 = -19009842; //  -0x7F000000;  this was recalculated based on MIN_MILLIS

	/**
	 * The minimum supported epoch milliseconds.  This value is equivalent
	 * to MIN_JULIAN.
	 */
	const MIN_MILLIS                 = -1853317152000000.0; // ((MIN_JULIAN - kEpochStartAsJulianDay) * kOneDay)

	/**
	 * The maximum supported Julian day.  This value is equivalent to
	 * MAX_MILLIS.
	 */
	const MAX_JULIAN                 = 23939830.0; // (+0x7F000000)

	/**
	 * The maximum supported epoch milliseconds.  This value is equivalent
	 * to MAX_JULIAN.
	 */
	const MAX_MILLIS                 = 1857534508800000.0; // ((MAX_JULIAN - kEpochStartAsJulianDay) * kOneDay)

	const LIMIT_MINIMUM             = 0;
	const LIMIT_GREATEST_MINIMUM    = 1;
	const LIMIT_LEAST_MAXIMUM       = 2;
	const LIMIT_MAXIMUM             = 3;
	const LIMIT_COUNT               = 4;

	protected static $kCalendarLimits = array(
		//               Minimum        Greatest min            Least max         Greatest max
		array(         /*N/A*/-1,          /*N/A*/-1,           /*N/A*/-1,           /*N/A*/-1 ), // ERA
		array(         /*N/A*/-1,          /*N/A*/-1,           /*N/A*/-1,           /*N/A*/-1 ), // YEAR
		array(         /*N/A*/-1,          /*N/A*/-1,           /*N/A*/-1,           /*N/A*/-1 ), // MONTH
		array(         /*N/A*/-1,          /*N/A*/-1,           /*N/A*/-1,           /*N/A*/-1 ), // WEEK_OF_YEAR
		array(         /*N/A*/-1,          /*N/A*/-1,           /*N/A*/-1,           /*N/A*/-1 ), // WEEK_OF_MONTH
		array(         /*N/A*/-1,          /*N/A*/-1,           /*N/A*/-1,           /*N/A*/-1 ), // DAY_OF_MONTH
		array(         /*N/A*/-1,          /*N/A*/-1,           /*N/A*/-1,           /*N/A*/-1 ), // DAY_OF_YEAR
		array(                 1,                  1,                   7,                   7 ), // DAY_OF_WEEK
		array(         /*N/A*/-1,          /*N/A*/-1,           /*N/A*/-1,           /*N/A*/-1 ), // DAY_OF_WEEK_IN_MONTH
		array(                 0,                  0,                   1,                   1 ), // AM_PM
		array(                 0,                  0,                  11,                  11 ), // HOUR
		array(                 0,                  0,                  23,                  23 ), // HOUR_OF_DAY
		array(                 0,                  0,                  59,                  59 ), // MINUTE
		array(                 0,                  0,                  59,                  59 ), // SECOND
		array(                 0,                  0,                 999,                 999 ), // MILLISECOND
		//    -12*self::kOneHour, -12*self::kOneHour,   12*self::kOneHour,   15*self::kOneHour
		array(         -43200000,          -43200000,            43200000,            54000000 ), // ZONE_OFFSET
		array(                 0,                  0,AgaviDateDefinitions::MILLIS_PER_HOUR , AgaviDateDefinitions::MILLIS_PER_HOUR ), // DST_OFFSET
		array(         /*N/A*/-1,          /*N/A*/-1,           /*N/A*/-1,           /*N/A*/-1 ), // YEAR_WOY
		array(                 1,                  1,                   7,                   7 ), // DOW_LOCAL
		array(         /*N/A*/-1,          /*N/A*/-1,           /*N/A*/-1,           /*N/A*/-1 ), // EXTENDED_YEAR
		array(  self::MIN_JULIAN,   self::MIN_JULIAN,    self::MAX_JULIAN,    self::MAX_JULIAN ), // JULIAN_DAY
		//                     0,                  0, 24*self::kOneHour-1, 24*self::kOneHour-1
		array(                 0,                  0,           86399999,             86399999 ), // MILLISECONDS_IN_DAY
	);

	/**
	 * Returns the current UTC (GMT) time measured in milliseconds since 0:00:00 
	 * on 1/1/70 (derived from the system time).
	 *
	 * @return     float The current UTC time in milliseconds.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	public static function getNow()
	{
		return time() * AgaviDateDefinitions::MILLIS_PER_SECOND;
	}

	/**
	 * Gets this Calendar's time as milliseconds. May involve recalculation of 
	 * time due to previous calls to set time field values. The time specified is 
	 * non-local UTC (GMT) time.
	 *
	 * @return     float The current time in UTC (GMT) time, or zero if the 
	 *                   operation failed.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	public function getTime()
	{
		return $this->getTimeInMillis();
	}

	/**
	 * Sets this Calendar's current time with the given UDate. The time specified 
	 * should be in non-local UTC (GMT) time.
	 *
	 * @param      float The given UDate in UTC (GMT) time.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	public function setTime($date)
	{
		$this->setTimeInMillis($date);
	}

	/**
	 * Returns the php native DateTime object which represents the time of this 
	 * object. Also supports dates which are not in the range of a unix timestamp.
	 * It will also set the DateTime object to be in the same time zone as this
	 * Calendar object.
	 * Please note that this method will only work on PHP 5.1.x when you have 
	 * explicitly enabled the new DateTime support. This restriction does not 
	 * apply to 5.2 and upwards. 
	 * When the Calendar object is in the AD era, the result of the conversion 
	 * is undefined.
	 *
	 * @return     DateTime The native DateTime.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getNativeDateTime()
	{
		$date = new DateTime(
			sprintf(
				'%d-%d-%d %d:%d:%d', 
				$this->get(AgaviDateDefinitions::YEAR), $this->get(AgaviDateDefinitions::MONTH) + 1, $this->get(AgaviDateDefinitions::DATE),
				$this->get(AgaviDateDefinitions::HOUR_OF_DAY), $this->get(AgaviDateDefinitions::MINUTE), $this->get(AgaviDateDefinitions::SECOND)
			),
			new DateTimeZone($this->getTimeZone()->getId())
		);
		return $date;
	}

	/**
	 * Gets this Calendar's time as unix timestamp. May involve recalculation of 
	 * time due to previous calls to set time field values. The time specified is 
	 * non-local UTC (GMT) time.
	 *
	 * @return     int The current time in UTC (GMT) time as unix timestamp.
	 *
	 * @throws     <b>OverflowException</b> when the date can't be represented by
	 *                                      an unix timestamp.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getUnixTimestamp()
	{
		$unixTime = floor($this->getTimeInMillis() / AgaviDateDefinitions::MILLIS_PER_SECOND);
		$unixTimeInt = (int) $unixTime;
		// lets check if the int can't represent the time anymore
		if($unixTime != $unixTimeInt) {
			throw new OverflowException('cannot convert the date ' . $this->get(AgaviDateDefinitions::YEAR) . '/' . $this->get(AgaviDateDefinitions::MONTH) . '/' . $this->get(AgaviDateDefinitions::DATE) . ' into a unix timestamp');
		}
		return $unixTimeInt;
	}

	/**
	 * Sets this Calendar's current time with the given unix timestamp. The time 
	 * specified  should be in non-local UTC (GMT) time.
	 *
	 * @param      int The given date in UTC (GMT) time.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function setUnixTimestamp($timestamp)
	{
		$this->setTimeInMillis($timestamp * AgaviDateDefinitions::MILLIS_PER_SECOND);
	}

	public function __is_equal($that)
	{
		return $this->isEquivalentTo($that) && $this->getTimeInMillis() == $that->getTimeInMillis();
	}

	public function __is_not_equal($that)
	{
		return !$this->isEquivalentTo($that) || $this->getTimeInMillis() != $that->getTimeInMillis();
	}

	/**
	 * Returns TRUE if the given Calendar object is equivalent to this
	 * one.  An equivalent Calendar will behave exactly as this one
	 * does, but it may be set to a different time.  By contrast, for
	 * the operator==() method to return TRUE, the other Calendar must
	 * be set to the same time.
	 *
	 * @param      AgaviCalendar the Calendar to be compared with this Calendar
	 * 
	 * @return     bool 
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	public function isEquivalentTo($other)
	{
		return	get_class($this)                   == get_class($other) &&
						$this->isLenient()                 == $other->isLenient() &&
						$this->getFirstDayOfWeek()         == $other->getFirstDayOfWeek() &&
						$this->getMinimalDaysInFirstWeek() == $other->getMinimalDaysInFirstWeek() &&
						$this->getTimeZone()->__is_equal($other->getTimeZone());

	}

	/**
	 * Compares the Calendar time, whereas Calendar::operator== compares the 
	 * equality of Calendar objects.
	 *
	 * @param      AgaviCalendar The Calendar to be compared with this Calendar.
	 *                           The object may be modified physically
	 * 
	 * @return     bool True if the current time of this Calendar is equal to the
	 *                  time of Calendar when; false otherwise.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	public function equals($when)
	{
		return ($this->__is_equal($when) || $this->getTime() == $when->getTime());
	}

	/**
	 * Returns true if this Calendar's current time is before "when"'s current 
	 * time.
	 *
	 * @param      AgaviCalendar The Calendar to be compared with this Calendar.
	 *                           The object may be modified physically.
	 * 
	 * @return     bool True if the current time of this Calendar is before the
	 *                  time of Calendar when; false otherwise.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	public function before($when)
	{
		return ($this->__is_not_equal($when) && $this->getTimeInMillis() < $when->getTimeInMillis());
	}

	/**
	 * Returns true if this Calendar's current time is after "when"'s current 
	 * time.
	 *
	 * @param      AgaviCalendar The Calendar to be compared with this Calendar.
	 *                           The object may be modified physically.
	 * 
	 * @return     bool True if the current time of this Calendar is after the 
	 *                  time of Calendar when; false otherwise.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	public function after($when)
	{
		return ($this->__is_not_equal($when) && $this->getTimeInMillis() > $when->getTimeInMillis());
	}

	/**
	 * UDate Arithmetic function. Adds the specified (signed) amount of time to 
	 * the given time field, based on the calendar's rules. For example, to 
	 * subtract 5 days from the current time of the calendar, call 
	 * add(AgaviCalendarDefinitions::DATE, -5). When adding on the month or 
	 * AgaviCalendarDefinitions::DATE field, other fields like date might conflict
	 * and need to be changed. For instance, adding 1 month on the date 01/31/96 
	 * will result in 02/29/96.
	 *
	 * @param      int Specifies which date field to modify.
	 * @param      int The amount of time to be added to the field, in the natural
	 *                 unit for that field (e.g., days for the day fields, hours 
	 *                 for the hour field.)
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	public function add($field, $amount)
	{
		if($amount == 0) {
			return;   // Do nothing!
		}

		// We handle most fields in the same way.  The algorithm is to add
		// a computed amount of millis to the current millis.  The only
		// wrinkle is with DST -- for some fields, like the DAY_OF_MONTH,
		// we don't want the HOUR to shift due to changes in DST.  If the
		// result of the add operation is to move from DST to Standard, or
		// vice versa, we need to adjust by an hour forward or back,
		// respectively.  For such fields we set keepHourInvariant to TRUE.

		// We only adjust the DST for fields larger than an hour.  For
		// fields smaller than an hour, we cannot adjust for DST without
		// causing problems.  for instance, if you add one hour to April 5,
		// 1998, 1:00 AM, in PST, the time becomes "2:00 AM PDT" (an
		// illegal value), but then the adjustment sees the change and
		// compensates by subtracting an hour.  As a result the time
		// doesn't advance at all.

		// For some fields larger than a day, such as a UCAL_MONTH, we pin the
		// UCAL_DAY_OF_MONTH.  This allows <March 31>.add(UCAL_MONTH, 1) to be
		// <April 30>, rather than <April 31> => <May 1>.

		$delta = (float) $amount; // delta in ms
		$keepHourInvariant = true;

		switch($field) {
			case AgaviDateDefinitions::ERA:
				$this->set($field, $this->get($field) + $amount);
				$this->pinField(AgaviDateDefinitions::ERA);
				return;

			case AgaviDateDefinitions::YEAR:
			case AgaviDateDefinitions::EXTENDED_YEAR:
			case AgaviDateDefinitions::YEAR_WOY:
			case AgaviDateDefinitions::MONTH:
				$this->set($field, $this->get($field) + $amount);
				$this->pinField(AgaviDateDefinitions::DAY_OF_MONTH);
				return;

			case AgaviDateDefinitions::WEEK_OF_YEAR:
			case AgaviDateDefinitions::WEEK_OF_MONTH:
			case AgaviDateDefinitions::DAY_OF_WEEK_IN_MONTH:
				$delta *= AgaviDateDefinitions::MILLIS_PER_WEEK;
				break;

			case AgaviDateDefinitions::AM_PM:
				$delta *= 12 * AgaviDateDefinitions::MILLIS_PER_HOUR;
				break;

			case AgaviDateDefinitions::DAY_OF_MONTH:
			case AgaviDateDefinitions::DAY_OF_YEAR:
			case AgaviDateDefinitions::DAY_OF_WEEK:
			case AgaviDateDefinitions::DOW_LOCAL:
			case AgaviDateDefinitions::JULIAN_DAY:
				$delta *= AgaviDateDefinitions::MILLIS_PER_DAY;
				break;

			case AgaviDateDefinitions::HOUR_OF_DAY:
			case AgaviDateDefinitions::HOUR:
				$delta *= AgaviDateDefinitions::MILLIS_PER_HOUR;
				$keepHourInvariant = false;
				break;

			case AgaviDateDefinitions::MINUTE:
				$delta *= AgaviDateDefinitions::MILLIS_PER_MINUTE;
				$keepHourInvariant = false;
				break;

			case AgaviDateDefinitions::SECOND:
				$delta *= AgaviDateDefinitions::MILLIS_PER_SECOND;
				$keepHourInvariant = false;
				break;

			case AgaviDateDefinitions::MILLISECOND:
			case AgaviDateDefinitions::MILLISECONDS_IN_DAY:
				$keepHourInvariant = false;
				break;

			default:
				throw new InvalidArgumentException('AgaviCalendar::add(): field ' . $field . ' is not supported');
				return;
		}

		// In order to keep the hour invariant (for fields where this is
		// appropriate), record the DST_OFFSET before and after the add()
		// operation.  If it has changed, then adjust the millis to
		// compensate.
		$dst = 0;
		$hour = 0;
		if($keepHourInvariant) {
			$dst = $this->get(AgaviDateDefinitions::DST_OFFSET);
			$hour = $this->internalGet(AgaviDateDefinitions::HOUR_OF_DAY);
		}

		$this->setTimeInMillis($this->getTimeInMillis() + $delta);

		if($keepHourInvariant) {
			$dst -= $this->get(AgaviDateDefinitions::DST_OFFSET);
			if($dst != 0) {
				// We have done an hour-invariant adjustment but the
				// DST offset has altered.  We adjust millis to keep
				// the hour constant.  In cases such as midnight after
				// a DST change which occurs at midnight, there is the
				// danger of adjusting into a different day.  To avoid
				// this we make the adjustment only if it actually
				// maintains the hour.
				
				/* double */
				$t = $this->internalGetTime();
				$this->setTimeInMillis($t + $dst);
				if($this->get(AgaviDateDefinitions::HOUR_OF_DAY) != $hour) {
					$this->setTimeInMillis($t);
				}
			}
		}
	}

	/**
	 * Time Field Rolling function. Rolls by the given amount on the given
	 * time field. For example, to roll the current date up by one day, call
	 * roll(AgaviCalendarDefinitions::DATE, +1). When rolling on the month or
	 * AgaviCalendarDefinitions::MONTH field, other fields like date might 
	 * conflict and, need to be changed. For instance, rolling the month up on the
	 * date 01/31/96 will result in 02/29/96.  Rolling by a positive value always 
	 * means rolling forward in time; e.g., rolling the year by +1 on "100 BC" 
	 * will result in "99 BC", for Gregorian calendar. When rolling on the 
	 * hour-in-day or AgaviCalendarDefinitions::HOUR_OF_DAY field, it will
	 * roll the hour value in the range between 0 and 23, which is zero-based.
	 * <P>
	 * The only difference between roll() and add() is that roll() does not change
	 * the value of more significant fields when it reaches the minimum or maximum
	 * of its range, whereas add() does.
	 *
	 * @param      int The time field.
	 * @param      int Indicates amount to roll.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	public function roll($field, $amount)
	{
		// this is overloaded in c++ with roll($field, $up)
		if(is_bool($amount)) {
			$amount = $amount ? +1 : -1;
		}

		if($amount == 0) {
			return; // Nothing to do
		}

		$this->complete();

		switch($field) {
			case AgaviDateDefinitions::DAY_OF_MONTH:
			case AgaviDateDefinitions::AM_PM:
			case AgaviDateDefinitions::MINUTE:
			case AgaviDateDefinitions::SECOND:
			case AgaviDateDefinitions::MILLISECOND:
			case AgaviDateDefinitions::MILLISECONDS_IN_DAY:
			case AgaviDateDefinitions::ERA:
				// These are the standard roll instructions.  These work for all
				// simple cases, that is, cases in which the limits are fixed, such
				// as the hour, the day of the month, and the era.
				{
					$min = $this->getActualMinimum($field);
					$max = $this->getActualMaximum($field);
					$gap = $max - $min + 1;

					$value = $this->internalGet($field) + $amount;
					$value = ($value - $min) % $gap;
					if($value < 0) {
						$value += $gap;
					}
					$value += $min;

					$this->set($field, $value);
					return;
				}

			case AgaviDateDefinitions::HOUR:
			case AgaviDateDefinitions::HOUR_OF_DAY:
				// Rolling the hour is difficult on the ONSET and CEASE days of
				// daylight savings.  For example, if the change occurs at
				// 2 AM, we have the following progression:
				// ONSET: 12 Std -> 1 Std -> 3 Dst -> 4 Dst
				// CEASE: 12 Dst -> 1 Dst -> 1 Std -> 2 Std
				// To get around this problem we don't use fields; we manipulate
				// the time in millis directly.
				{
					// Assume min == 0 in calculations below
					/* double */ $start = $this->getTimeInMillis();
					$oldHour = $this->internalGet($field);
					$max = $this->getMaximum($field);
					$newHour = ($oldHour + $amount) % ($max + 1);
					if($newHour < 0) {
						$newHour += $max + 1;
					}
					$this->setTimeInMillis($start + AgaviDateDefinitions::MILLIS_PER_HOUR * ($newHour - $oldHour));
					return;
				}

			case AgaviDateDefinitions::MONTH:
				// Rolling the month involves both pinning the final value
				// and adjusting the DAY_OF_MONTH if necessary.  We only adjust the
				// DAY_OF_MONTH if, after updating the MONTH field, it is illegal.
				// E.g., <jan31>.roll(MONTH, 1) -> <feb28> or <feb29>.
				{
						$max = $this->getActualMaximum(AgaviDateDefinitions::MONTH);
						$mon = ($this->internalGet(AgaviDateDefinitions::MONTH) + $amount) % ($max + 1);

						if($mon < 0) {
							$mon += ($max + 1);
						}
						$this->set(AgaviDateDefinitions::MONTH, $mon);

						// Keep the day of month in range.  We don't want to spill over
						// into the next month; e.g., we don't want jan31 + 1 mo -> feb31 ->
						// mar3.
						$this->pinField(AgaviDateDefinitions::DAY_OF_MONTH);
						return;
				}

			case AgaviDateDefinitions::YEAR:
			case AgaviDateDefinitions::YEAR_WOY:
			case AgaviDateDefinitions::EXTENDED_YEAR:
				// Rolling the year can involve pinning the DAY_OF_MONTH.
				$this->set($field, $this->internalGet($field) + $amount);
				$this->pinField(AgaviDateDefinitions::MONTH);
				$this->pinField(AgaviDateDefinitions::DAY_OF_MONTH);
				return;

			case AgaviDateDefinitions::WEEK_OF_MONTH:
				{
						// This is tricky, because during the roll we may have to shift
						// to a different day of the week.  For example:

						//    s  m  t  w  r  f  s
						//          1  2  3  4  5
						//    6  7  8  9 10 11 12

						// When rolling from the 6th or 7th back one week, we go to the
						// 1st (assuming that the first partial week counts).  The same
						// thing happens at the end of the month.

						// The other tricky thing is that we have to figure out whether
						// the first partial week actually counts or not, based on the
						// minimal first days in the week.  And we have to use the
						// correct first day of the week to delineate the week
						// boundaries.

						// Here's our algorithm.  First, we find the real boundaries of
						// the month.  Then we discard the first partial week if it
						// doesn't count in this locale.  Then we fill in the ends with
						// phantom days, so that the first partial week and the last
						// partial week are full weeks.  We then have a nice square
						// block of weeks.  We do the usual rolling within this block,
						// as is done elsewhere in this method.  If we wind up on one of
						// the phantom days that we added, we recognize this and pin to
						// the first or the last day of the month.  Easy, eh?

						// Normalize the DAY_OF_WEEK so that 0 is the first day of the week
						// in this locale.  We have dow in 0..6.
						$dow = $this->internalGet(AgaviDateDefinitions::DAY_OF_WEEK) - $this->getFirstDayOfWeek();
						if($dow < 0) {
							$dow += 7;
						}

						// Find the day of the week (normalized for locale) for the first
						// of the month.
						$fdm = ($dow - $this->internalGet(AgaviDateDefinitions::DAY_OF_MONTH) + 1) % 7;
						if($fdm < 0) {
							$fdm += 7;
						}

						// Get the first day of the first full week of the month,
						// including phantom days, if any.  Figure out if the first week
						// counts or not; if it counts, then fill in phantom days.  If
						// not, advance to the first real full week (skip the partial week).
						if((7 - $fdm) < $this->getMinimalDaysInFirstWeek()) {
							$start = 8 - $fdm; // Skip the first partial week
						} else {
							$start = 1 - $fdm; // This may be zero or negative
						}

						// Get the day of the week (normalized for locale) for the last
						// day of the month.
						$monthLen = $this->getActualMaximum(AgaviDateDefinitions::DAY_OF_MONTH);
						$ldm = ($monthLen - $this->internalGet(AgaviDateDefinitions::DAY_OF_MONTH) + $dow) % 7;
						// We know monthLen >= DAY_OF_MONTH so we skip the += 7 step here.

						// Get the limit day for the blocked-off rectangular month; that
						// is, the day which is one past the last day of the month,
						// after the month has already been filled in with phantom days
						// to fill out the last week.  This day has a normalized DOW of 0.
						$limit = $monthLen + 7 - $ldm;

						// Now roll between start and (limit - 1).
						$gap = $limit - $start;
						$day_of_month = ($this->internalGet(AgaviDateDefinitions::DAY_OF_MONTH) + $amount * 7 - $start) % $gap;
						if($day_of_month < 0) {
							$day_of_month += $gap;
						}
						$day_of_month += $start;

						// Finally, pin to the real start and end of the month.
						if($day_of_month < 1) {
							$day_of_month = 1;
						}
						if($day_of_month > $monthLen) {
							$day_of_month = $monthLen;
						}

						// Set the DAY_OF_MONTH.  We rely on the fact that this field
						// takes precedence over everything else (since all other fields
						// are also set at this point).  If this fact changes (if the
						// disambiguation algorithm changes) then we will have to unset
						// the appropriate fields here so that DAY_OF_MONTH is attended
						// to.
						$this->set(AgaviDateDefinitions::DAY_OF_MONTH, $day_of_month);
						return;
				}
			case AgaviDateDefinitions::WEEK_OF_YEAR:
				{
					// This follows the outline of WEEK_OF_MONTH, except it applies
					// to the whole year.  Please see the comment for WEEK_OF_MONTH
					// for general notes.

					// Normalize the DAY_OF_WEEK so that 0 is the first day of the week
					// in this locale.  We have dow in 0..6.
					$dow = $this->internalGet(AgaviDateDefinitions::DAY_OF_WEEK) - $this->getFirstDayOfWeek();
					if($dow < 0) {
						$dow += 7;
					}

					// Find the day of the week (normalized for locale) for the first
					// of the year.
					$fdy = ($dow - $this->internalGet(AgaviDateDefinitions::DAY_OF_YEAR) + 1) % 7;
					if($fdy < 0) {
						$fdy += 7;
					}

					// Get the first day of the first full week of the year,
					// including phantom days, if any.  Figure out if the first week
					// counts or not; if it counts, then fill in phantom days.  If
					// not, advance to the first real full week (skip the partial week).
					if((7 - $fdy) < $this->getMinimalDaysInFirstWeek()) {
						$start = 8 - $fdy; // Skip the first partial week
					} else {
						$start = 1 - $fdy; // This may be zero or negative
					}

					// Get the day of the week (normalized for locale) for the last
					// day of the year.
					$yearLen = $this->getActualMaximum(AgaviDateDefinitions::DAY_OF_YEAR);
					$ldy = ($yearLen - $this->internalGet(AgaviDateDefinitions::DAY_OF_YEAR) + $dow) % 7;
					// We know yearLen >= DAY_OF_YEAR so we skip the += 7 step here.

					// Get the limit day for the blocked-off rectangular year; that
					// is, the day which is one past the last day of the year,
					// after the year has already been filled in with phantom days
					// to fill out the last week.  This day has a normalized DOW of 0.
					$limit = $yearLen + 7 - $ldy;

					// Now roll between start and (limit - 1).
					$gap = $limit - $start;
					$day_of_year = ($this->internalGet(AgaviDateDefinitions::DAY_OF_YEAR) + $amount * 7 - $start) % $gap;
					if($day_of_year < 0) {
						$day_of_year += $gap;
					}
					$day_of_year += $start;

					// Finally, pin to the real start and end of the month.
					if($day_of_year < 1) {
						$day_of_year = 1;
					}
					if($day_of_year > $yearLen) {
						$day_of_year = $yearLen;
					}

					// Make sure that the year and day of year are attended to by
					// clearing other fields which would normally take precedence.
					// If the disambiguation algorithm is changed, this section will
					// have to be updated as well.
					$this->set(AgaviDateDefinitions::DAY_OF_YEAR, $day_of_year);
					$this->clear(AgaviDateDefinitions::MONTH);
					return;
				}
			case AgaviDateDefinitions::DAY_OF_YEAR:
				{
					// Roll the day of year using millis.  Compute the millis for
					// the start of the year, and get the length of the year.
					$delta = (float) ($amount * AgaviDateDefinitions::MILLIS_PER_DAY); // Scale up from days to millis
					$min2 = (float) ($this->internalGet(AgaviDateDefinitions::DAY_OF_YEAR) - 1);
					$min2 *= AgaviDateDefinitions::MILLIS_PER_DAY;
					$min2 = $this->internalGetTime() - $min2;

					$yearLength = (float) $this->getActualMaximum(AgaviDateDefinitions::DAY_OF_YEAR);
					$oneYear = $yearLength;
					$oneYear *= AgaviDateDefinitions::MILLIS_PER_DAY;
					$newtime = fmod(($this->internalGetTime() + $delta - $min2), $oneYear);
					if($newtime < 0) {
						$newtime += $oneYear;
					}
					$this->setTimeInMillis($newtime + $min2);
					return;
				}
			case AgaviDateDefinitions::DAY_OF_WEEK:
			case AgaviDateDefinitions::DOW_LOCAL:
				{
					// Roll the day of week using millis.  Compute the millis for
					// the start of the week, using the first day of week setting.
					// Restrict the millis to [start, start+7days).
					$delta = (float) ($amount * AgaviDateDefinitions::MILLIS_PER_DAY); // Scale up from days to millis
					// Compute the number of days before the current day in this
					// week.  This will be a value 0..6.
					$leadDays = $this->internalGet($field);
					$leadDays -= ($field == AgaviDateDefinitions::DAY_OF_WEEK) ? $this->getFirstDayOfWeek() : 1;
					if($leadDays < 0) {
						$leadDays += 7;
					}
					$min2 = (float) ($this->internalGetTime() - $leadDays * AgaviDateDefinitions::MILLIS_PER_DAY);
					$newtime = fmod(($this->internalGetTime() + $delta - $min2), AgaviDateDefinitions::MILLIS_PER_WEEK);
					if($newtime < 0) {
						$newtime += AgaviDateDefinitions::MILLIS_PER_WEEK;
					}
					$this->setTimeInMillis($newtime + $min2);
					return;
				}
			case AgaviDateDefinitions::DAY_OF_WEEK_IN_MONTH:
				{
					// Roll the day of week in the month using millis.  Determine
					// the first day of the week in the month, and then the last,
					// and then roll within that range.
					$delta = (float) ($amount * AgaviDateDefinitions::MILLIS_PER_WEEK); // Scale up from weeks to millis
					// Find the number of same days of the week before this one
					// in this month.
					$preWeeks = (int) (($this->internalGet(AgaviDateDefinitions::DAY_OF_MONTH) - 1) / 7);
					// Find the number of same days of the week after this one
					// in this month.
					$postWeeks = (int) (($this->getActualMaximum(AgaviDateDefinitions::DAY_OF_MONTH) - $this->internalGet(AgaviDateDefinitions::DAY_OF_MONTH)) / 7);
					// From these compute the min and gap millis for rolling.
					$min2 = (float) ($this->internalGetTime() - $preWeeks * AgaviDateDefinitions::MILLIS_PER_WEEK);
					$gap2 = (float) (AgaviDateDefinitions::MILLIS_PER_WEEK * ($preWeeks + $postWeeks + 1)); // Must add 1!
					// Roll within this range
					$newtime = fmod(($this->internalGetTime() + $delta - $min2), $gap2);
					if($newtime < 0) {
						$newtime += $gap2;
					}
					$this->setTimeInMillis($newtime + $min2);
					return;
				}
			case AgaviDateDefinitions::JULIAN_DAY:
				$this->set($field, $this->internalGet($field) + $amount);
				return;
		default:
				// Other fields cannot be rolled by this method
				throw new InvalidArgumentException('AgaviCalendar::roll(): field ' . $field . ' cannot be rolled with this method');
		}
	}

	/**
	 * Return the difference between the given time and the time this
	 * calendar object is set to.  If this calendar is set
	 * <em>before</em> the given time, the returned value will be
	 * positive.  If this calendar is set <em>after</em> the given
	 * time, the returned value will be negative.  The
	 * <code>field</code> parameter specifies the units of the return
	 * value.  For example, if <code>fieldDifference(when,
	 * Calendar::MONTH)</code> returns 3, then this calendar is set to
	 * 3 months before <code>when</code>, and possibly some addition
	 * time less than one month.
	 *
	 * <p>As a side effect of this call, this calendar is advanced
	 * toward <code>when</code> by the given amount.  That is, calling
	 * this method has the side effect of calling <code>add(field,
	 * n)</code>, where <code>n</code> is the return value.
	 *
	 * <p>Usage: To use this method, call it first with the largest
	 * field of interest, then with progressively smaller fields.  For
	 * example:
	 *
	 * <pre>
	 * int y = cal->fieldDifference(when, Calendar::YEAR, err);
	 * int m = cal->fieldDifference(when, Calendar::MONTH, err);
	 * int d = cal->fieldDifference(when, Calendar::DATE, err);</pre>
	 *
	 * computes the difference between <code>cal</code> and
	 * <code>when</code> in years, months, and days.
	 *
	 * <p>Note: <code>fieldDifference()</code> is
	 * <em>asymmetrical</em>.  That is, in the following code:
	 *
	 * <pre>
	 * cal->setTime(date1, err);
	 * int m1 = cal->fieldDifference(date2, Calendar::MONTH, err);
	 * int d1 = cal->fieldDifference(date2, Calendar::DATE, err);
	 * cal->setTime(date2, err);
	 * int m2 = cal->fieldDifference(date1, Calendar::MONTH, err);
	 * int d2 = cal->fieldDifference(date1, Calendar::DATE, err);</pre>
	 *
	 * one might expect that <code>m1 == -m2 && d1 == -d2</code>.
	 * However, this is not generally the case, because of
	 * irregularities in the underlying calendar system (e.g., the
	 * Gregorian calendar has a varying number of days per month).
	 *
	 * @param      float     When the date to compare this calendar's time to
	 * @param      int       The field in which to compute the result
	 * 
	 * @return     int       The difference, either positive or negative, between
	 *                       this calendar's time and <code>when</code>, in terms
	 *                       of <code>field</code>.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	public function fieldDifference($targetMs /* $when */, $field)
	{
		$min = 0;
		$startMs = (float) $this->getTimeInMillis();
		if($targetMs instanceof AgaviCalendar) {
			$targetMs = (float) $targetMs->getTimeInMillis();
		}
		// Always add from the start millis.  This accomodates
		// operations like adding years from February 29, 2000 up to
		// February 29, 2004.  If 1, 1, 1, 1 is added to the year
		// field, the DOM gets pinned to 28 and stays there, giving an
		// incorrect DOM difference of 1.  We have to add 1, reset, 2,
		// reset, 3, reset, 4.
		if($startMs < $targetMs) {
			$max = 1;
			// Find a value that is too large
			while(true) {
				$this->setTimeInMillis($startMs);
				$this->add($field, $max);
				$ms = (float) $this->getTimeInMillis();
				if($ms == $targetMs) {
					return $max;
				} elseif($ms > $targetMs) {
					break;
				} else {
					$max <<= 1;
					if($max < 0) {
						// TODO: check if we can change this to float to support a larger range
						// Field difference too large to fit into int32_t
						throw new InvalidArgumentException('The difference is to large to fit into an integer');
					}
				}
			}
			// Do a binary search
			while(($max - $min) > 1) {
				$t = (int) (($min + $max) / 2);
				$this->setTimeInMillis($startMs);
				$this->add($field, $t);
				$ms = (float) $this->getTimeInMillis();
				if($ms == $targetMs) {
					return $t;
				} elseif($ms > $targetMs) {
					$max = $t;
				} else {
					$min = $t;
				}
			}
		} elseif($startMs > $targetMs) {
			$max = -1;
			// Find a value that is too small
			while(true) {
				$this->setTimeInMillis($startMs);
				$this->add($field, $max);
				$ms = (float) $this->getTimeInMillis();
				if($ms == $targetMs) {
					return $max;
				} elseif($ms < $targetMs) {
					break;
				} else {
					$max <<= 1;
					if($max == 0) {
						// TODO: see above 
						// Field difference too large to fit into int32_t
						throw new InvalidArgumentException('The difference is to large to fit into an integer');
					}
				}
			}
			// Do a binary search
			while(($min - $max) > 1) {
				$t = (int) (($min + $max) / 2);
				$this->setTimeInMillis($startMs);
				$this->add($field, $t);
				$ms = (float) $this->getTimeInMillis();
				if($ms == $targetMs) {
					return $t;
				} elseif($ms < $targetMs) {
					$max = $t;
				} else {
					$min = $t;
				}
			}
		}
		// Set calendar to end point
		$this->setTimeInMillis($startMs);
		$this->add($field, $min);

		return $min;

	}

	/**
	 * Sets the calendar's time zone to be the same as the one passed in. The 
	 * TimeZone passed in is _not_ adopted; the client is still responsible for 
	 * deleting it.
	 *
	 * @param      AgaviTimeZone The given time zone.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	public function setTimeZone($zone)
	{
		// Do nothing if passed-in zone is NULL
		if(!$zone) {
			return;
		}

		// fZone should always be non-null
		$this->fZone = $zone;

		// if the zone changes, we need to recompute the time fields
		$this->fAreFieldsInSync = false;
	}

	/**
	 * Returns a reference to the time zone owned by this calendar.
	 *
	 * @return     AgaviTimeZone The time zone object associated with this 
	 *                           calendar.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	public function getTimeZone()
	{
		return $this->fZone;
	}

	/**
	 * Queries if the current date for this Calendar is in Daylight Savings Time.
	 *
	 * @return     bool True if the current date for this Calendar is in 
	 *                  Daylight Savings Time, false, otherwise.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	public abstract function inDaylightTime();

	/**
	 * Specifies whether or not date/time interpretation is to be lenient. With 
	 * lenient interpretation, a date such as "February 942, 1996" will be treated
	 * as being equivalent to the 941st day after February 1, 1996. With strict 
	 * interpretation, such dates will cause an error when computing time from the
	 * time field values representing the dates.
	 *
	 * @param      bool True specifies date/time interpretation to be lenient.
	 *
	 * @see        DateFormat#setLenient
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	public function setLenient($lenient)
	{
		$this->fLenient = $lenient;
	}

	/**
	 * Tells whether date/time interpretation is to be lenient.
	 *
	 * @return     bool True tells that date/time interpretation is to be lenient.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	public function isLenient()
	{
		return $this->fLenient;
	}

	/**
	 * Sets what the first day of the week is; e.g., Sunday in US, Monday in
	 * France.
	 *
	 * @param      int The given first day of the week.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	public function setFirstDayOfWeek($value)
	{
		$this->fFirstDayOfWeek = $value;
	}
	
	/**
	 * Gets what the first day of the week is; e.g., Sunday in US, Monday in
	 * France.
	 *
	 * @return     int The first day of the week.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	public function getFirstDayOfWeek()
	{
		return $this->fFirstDayOfWeek;
	}

	/**
	 * Sets what the minimal days required in the first week of the year are; For
	 * example, if the first week is defined as one that contains the first day of
	 * the first month of a year, call the method with value 1. If it must be a
	 * full week, use value 7.
	 *
	 * @param      int The given minimal days required in the first week of the
	 *                 year.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	public function setMinimalDaysInFirstWeek($value)
	{
		$this->fMinimalDaysInFirstWeek = $value;
	}
	
	/**
	 * Gets what the minimal days required in the first week of the year are;
	 * e.g., if the first week is defined as one that contains the first day of
	 * the first month of a year, getMinimalDaysInFirstWeek returns 1. If the
	 * minimal days required must be a full week, getMinimalDaysInFirstWeek
	 * returns 7.
	 *
	 * @return     int The minimal days required in the first week of the year.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	public function getMinimalDaysInFirstWeek()
	{
		return $this->fMinimalDaysInFirstWeek;
	}
	
	/**
	 * Gets the minimum value for the given time field. e.g., for Gregorian
	 * DAY_OF_MONTH, 1.
	 *
	 * @param      string The given time field.
	 * 
	 * @return     int    The minimum value for the given time field.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	public function getMinimum($field)
	{
		return $this->getLimit($field, self::LIMIT_MINIMUM);
	}
	
	/**
	 * Gets the maximum value for the given time field. e.g., for Gregorian
	 * DAY_OF_MONTH, 31.
	 *
	 * @param      string The given time field.
	 * 
	 * @return     int    The maximum value for the given time field.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	public function getMaximum($field)
	{
		return $this->getLimit($field, self::LIMIT_MAXIMUM);
	}
	
	/**
	 * Gets the highest minimum value for the given field if varies. Otherwise
	 * same as getMinimum(). For Gregorian, no difference.
	 *
	 * @param      string The given time field.
	 * 
	 * @return     int    The highest minimum value for the given time field.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	public function getGreatestMinimum($field)
	{
		return $this->getLimit($field, self::LIMIT_GREATEST_MINIMUM);
	}
	
	/**
	 * Gets the lowest maximum value for the given field if varies. Otherwise same
	 * as getMaximum(). e.g., for Gregorian DAY_OF_MONTH, 28.
	 *
	 * @param      string The given time field.
	 * 
	 * @return     int    The lowest maximum value for the given time field.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	public function getLeastMaximum($field)
	{
		return $this->getLimit($field, self::LIMIT_LEAST_MAXIMUM);
	}
	
	/**
	 * Return the minimum value that this field could have, given the current
	 * date. For the Gregorian calendar, this is the same as getMinimum() and
	 * getGreatestMinimum().
	 *
	 * The version of this function on Calendar uses an iterative algorithm to
	 * determine the actual minimum value for the field.  There is almost always a
	 * more efficient way to accomplish this (in most cases, you can simply return
	 * getMinimum()).  GregorianCalendar overrides this function with a more
	 * efficient implementation.
	 *
	 * @param      string the field to determine the minimum of
	 * 
	 * @return     int    the minimum of the given field for the current date of
	 *                    this Calendar
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	public function getActualMinimum($field)
	{
		$fieldValue = $this->getGreatestMinimum($field);
		$endValue = $this->getMinimum($field);

		// if we know that the minimum value is always the same, just return it
		if($fieldValue == $endValue) {
			return $fieldValue;
		}

		// clone the calendar so we don't mess with the real one, and set it to
		// accept anything for the field values
		$work = clone $this;
		$work->setLenient(true);

		// now try each value from getLeastMaximum() to getMaximum() one by one until
		// we get a value that normalizes to another value.	 The last value that
		// normalizes to itself is the actual minimum for the current date
		$result = $fieldValue;

		do {
			$work->set($field, $fieldValue);
			if($work->get($field) != $fieldValue) {
				break;
			} else {
				$result = $fieldValue;
				$fieldValue--;
			}
		} while($fieldValue >= $endValue);

		return $result;
	}

	/**
	 * Return the maximum value that this field could have, given the current
	 * date. For example, with the date "Feb 3, 1997" and the DAY_OF_MONTH field,
	 * the actual maximum would be 28; for "Feb 3, 1996" it s 29.  Similarly for a
	 * Hebrew calendar,for some years the actual maximum for MONTH is 12, and for
	 * others 13.
	 *
	 * The version of this function on Calendar uses an iterative algorithm to
	 * determine the actual maximum value for the field.  There is almost always a
	 * more efficient way to accomplish this (in most cases, you can simply return
	 * getMaximum()).  GregorianCalendar overrides this function with a more
	 * efficient implementation.
	 *
	 * @param      string the field to determine the maximum of
	 * 
	 * @return     int    the maximum of the given field for the current date of
	 *                    this Calendar
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	public function getActualMaximum($field)
	{
		$result = 0;
		switch($field) {
			case AgaviDateDefinitions::DATE:
				$cal = clone $this;
				$cal->prepareGetActual($field, false);
				$result = $this->handleGetMonthLength($cal->get(AgaviDateDefinitions::EXTENDED_YEAR), $cal->get(AgaviDateDefinitions::MONTH));
				break;

			case AgaviDateDefinitions::DAY_OF_YEAR:
				$cal = clone $this;
				$cal->prepareGetActual($field, false);
				$result = $this->handleGetYearLength($cal->get(AgaviDateDefinitions::EXTENDED_YEAR));
				break;

			case AgaviDateDefinitions::DAY_OF_WEEK:
			case AgaviDateDefinitions::AM_PM:
			case AgaviDateDefinitions::HOUR:
			case AgaviDateDefinitions::HOUR_OF_DAY:
			case AgaviDateDefinitions::MINUTE:
			case AgaviDateDefinitions::SECOND:
			case AgaviDateDefinitions::MILLISECOND:
			case AgaviDateDefinitions::ZONE_OFFSET:
			case AgaviDateDefinitions::DST_OFFSET:
			case AgaviDateDefinitions::DOW_LOCAL:
			case AgaviDateDefinitions::JULIAN_DAY:
			case AgaviDateDefinitions::MILLISECONDS_IN_DAY:
				// These fields all have fixed minima/maxima
				$result = $this->getMaximum($field);
				break;

			default:
				// For all other fields, do it the hard way....
				$result = $this->getActualHelper($field, $this->getLeastMaximum($field), $this->getMaximum($field));
				break;
		}
		return $result;
	}

	/**
	 * Gets all time field values. Recalculate the current time field
	 * values if the time value has been changed by a call to setTime(). Return
	 * zero for unset fields if any fields have been explicitly set by a call to
	 * set(). To force a recomputation of all fields regardless of the previous
	 * state, call complete().
	 *
	 * @return     array All fields of this instance.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	public function getAll()
	{
		// field values are only computed when actually requested; for more on when computation
		// of various things happens, see the "data flow in Calendar" description at the top
		// of this file
		$this->complete();
		return $this->fFields;
	}

	/**
	 * Gets the value for a given time field. Recalculate the current time field
	 * values if the time value has been changed by a call to setTime(). Return
	 * zero for unset fields if any fields have been explicitly set by a call to
	 * set(). To force a recomputation of all fields regardless of the previous
	 * state, call complete().
	 *
	 * @param      string The given time field.
	 * 
	 * @return     int    The value for the given time field, or zero if the field
	 *                    is unset, and set() has been called for any other field.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	public function get($field)
	{
		// field values are only computed when actually requested; for more on when computation
		// of various things happens, see the "data flow in Calendar" description at the top
		// of this file
		$this->complete();
		return $this->fFields[$field];
	}

	/**
	 * Determines if the given time field has a value set. This can affect in the
	 * resolving of time in Calendar. Unset fields have a value of zero, by
	 * definition.
	 *
	 * @param      string The given time field.
	 * 
	 * @return     bool   True if the given time field has a value set; false
	 *                    otherwise.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	public function _isSet($field) // isset is a keyword in php
	{
		return $this->fAreFieldsVirtuallySet || ($this->fStamp[$field] != self::kUnset);
	}

	/**
	 * TODO: describe overload bla
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	public function set()
	{
		$arguments = func_get_args();

		$fName = AgaviToolkit::overloadHelper(array(
			array('name' => 'set1',
						'parameters' => array('int', 'int')),
			array('name' => 'set2',
						'parameters' => array('int', 'int', 'int')),
			array('name' => 'set3',
						'parameters' => array('int', 'int', 'int', 'int', 'int')),
			array('name' => 'set4',
						'parameters' => array('int', 'int', 'int', 'int', 'int', 'int')),
			),
			$arguments
		);
		call_user_func_array(array($this, $fName), $arguments);
	}

	/**
	 * Sets the given time field with the given value.
	 *
	 * @param      int    The given time field.
	 * @param      int    The value to be set for the given time field.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	public function set1($field, $value)
	{
		if($this->fAreFieldsVirtuallySet) {
			$this->computeFields();
		}
		
		$this->fFields[$field]    = $value;
		$this->fStamp[$field]     = $this->fNextStamp++;
		$this->fIsSet[$field]     = true; // Remove later
		$this->fIsTimeSet = $this->fAreFieldsInSync = $this->fAreFieldsVirtuallySet = false;

	}

	/**
	 * Sets the values for the fields YEAR, MONTH, and DATE. Other field values
	 * are retained; call clear() first if this is not desired.
	 *
	 * @param      int The value used to set the YEAR time field.
	 * @param      int The value used to set the MONTH time field. Month value is
	 *                 0-based. e.g., 0 for January.
	 * @param      int The value used to set the DATE time field.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	public function set2($year, $month, $date)
	{
		$this->set1(AgaviDateDefinitions::YEAR, $year);
		$this->set1(AgaviDateDefinitions::MONTH, $month);
		$this->set1(AgaviDateDefinitions::DATE, $date);
	}

	/**
	 * Sets the values for the fields YEAR, MONTH, DATE, HOUR_OF_DAY, and MINUTE.
	 * Other field values are retained; call 
	 * ) first if this is not desired.
	 *
	 * @param      int The value used to set the YEAR time field.
	 * @param      int The value used to set the MONTH time field. Month value is
	 *                 0-based. E.g., 0 for January.
	 * @param      int The value used to set the DATE time field.
	 * @param      int The value used to set the HOUR_OF_DAY time field.
	 * @param      int The value used to set the MINUTE time field.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	public function set3($year, $month, $date, $hour, $minute)
	{
		$this->set1(AgaviDateDefinitions::YEAR, $year);
		$this->set1(AgaviDateDefinitions::MONTH, $month);
		$this->set1(AgaviDateDefinitions::DATE, $date);
		$this->set1(AgaviDateDefinitions::HOUR_OF_DAY, $hour);
		$this->set1(AgaviDateDefinitions::MINUTE, $minute);
	}

	/**
	 * Sets the values for the fields YEAR, MONTH, DATE, HOUR_OF_DAY, MINUTE, and
	 * SECOND. Other field values are retained; call clear() first if this is not
	 * desired.
	 *
	 * @param      int The value used to set the YEAR time field.
	 * @param      int The value used to set the MONTH time field. Month value is
	 *                 0-based. E.g., 0 for January.
	 * @param      int The value used to set the DATE time field.
	 * @param      int The value used to set the HOUR_OF_DAY time field.
	 * @param      int The value used to set the MINUTE time field.
	 * @param      int The value used to set the SECOND time field.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	public function set4($year, $month, $date, $hour, $minute, $second)
	{
		$this->set1(AgaviDateDefinitions::YEAR, $year);
		$this->set1(AgaviDateDefinitions::MONTH, $month);
		$this->set1(AgaviDateDefinitions::DATE, $date);
		$this->set1(AgaviDateDefinitions::HOUR_OF_DAY, $hour);
		$this->set1(AgaviDateDefinitions::MINUTE, $minute);
		$this->set1(AgaviDateDefinitions::SECOND, $second);
	}

	/**
	 * TODO: describe overload bla
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	public function clear()
	{
		$arguments = func_get_args();

		$fName = AgaviToolkit::overloadHelper(array(
			array('name' => 'clear1',
						'parameters' => array()),
			array('name' => 'clear2',
						'parameters' => array('int')),
			),
			$arguments
		);
		call_user_func_array(array($this, $fName), $arguments);
	}

	/**
	 * Clears the values of all the time fields, making them both unset and
	 * assigning them a value of zero. The field values will be determined during
	 * the next resolving of time into time fields.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	public function clear1()
	{
		for($i = 0; $i < AgaviDateDefinitions::FIELD_COUNT; ++$i) {
			$this->fFields[$i]     = 0; // Must do this; other code depends on it
			$this->fStamp[$i]      = self::kUnset;
			$this->fIsSet[$i]      = false; // Remove later
		}
		$this->fIsTimeSet = $this->fAreFieldsInSync = $this->fAreAllFieldsSet = $this->fAreFieldsVirtuallySet = false;
		// fTime is not 'cleared' - may be used if no fields are set.
	}

	/**
	 * Clears the value in the given time field, both making it unset and
	 * assigning it a value of zero. This field value will be determined during
	 * the next resolving of time into time fields.
	 *
	 * @param      string The time field to be cleared.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	public function clear2($field)
	{
		if($this->fAreFieldsVirtuallySet) {
			$this->computeFields();
		}
		$this->fFields[$field]        = 0;
		$this->fStamp[$field]         = self::kUnset;
		$this->fIsSet[$field]         = false; // Remove later
		$this->fIsTimeSet = $this->fAreFieldsInSync = $this->fAreAllFieldsSet = $this->fAreFieldsVirtuallySet = false;
	}

	/**
	 * Converts Calendar's time field values to GMT as milliseconds.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	protected function computeTime()
	{
		if(!$this->isLenient()) {
			$this->validateFields();
		}

		// Compute the Julian day
		$julianDay = $this->computeJulianDay();

		$millis = AgaviCalendarGrego::julianDayToMillis($julianDay);

		$millisInDay = 0;

		// We only use MILLISECONDS_IN_DAY if it has been set by the user.
		// This makes it possible for the caller to set the calendar to a
		// time and call clear(MONTH) to reset the MONTH to January.  This
		// is legacy behavior.  Without this, clear(MONTH) has no effect,
		// since the internally set JULIAN_DAY is used.
		if($this->fStamp[AgaviDateDefinitions::MILLISECONDS_IN_DAY] >= (self::kMinimumUserStamp) &&
				$this->newestStamp(AgaviDateDefinitions::AM_PM, AgaviDateDefinitions::MILLISECOND, self::kUnset) <= $this->fStamp[AgaviDateDefinitions::MILLISECONDS_IN_DAY]) {
			$millisInDay = $this->internalGet(AgaviDateDefinitions::MILLISECONDS_IN_DAY);
		} else {
			$millisInDay = $this->computeMillisInDay();
		}

		// Compute the time zone offset and DST offset.  There are two potential
		// ambiguities here.  We'll assume a 2:00 am (wall time) switchover time
		// for discussion purposes here.
		// 1. The transition into DST.  Here, a designated time of 2:00 am - 2:59 am
		//    can be in standard or in DST depending.  However, 2:00 am is an invalid
		//    representation (the representation jumps from 1:59:59 am Std to 3:00:00 am DST).
		//    We assume standard time.
		// 2. The transition out of DST.  Here, a designated time of 1:00 am - 1:59 am
		//    can be in standard or DST.  Both are valid representations (the rep
		//    jumps from 1:59:59 DST to 1:00:00 Std).
		//    Again, we assume standard time.
		// We use the TimeZone object, unless the user has explicitly set the ZONE_OFFSET
		// or DST_OFFSET fields; then we use those fields.
		if($this->fStamp[AgaviDateDefinitions::ZONE_OFFSET] >= (self::kMinimumUserStamp) ||
				$this->fStamp[AgaviDateDefinitions::DST_OFFSET] >= (self::kMinimumUserStamp)) {
			$millisInDay -= $this->internalGet(AgaviDateDefinitions::ZONE_OFFSET) + $this->internalGet(AgaviDateDefinitions::DST_OFFSET);
		} else {
			$millisInDay -= $this->computeZoneOffset($millis, $millisInDay);
		}
	
		$this->internalSetTime($millis + $millisInDay);
	}

	/**
	 * Converts GMT as milliseconds to time field values. This allows you to sync
	 * up the time field values with a new time that is set for the calendar.
	 * This method does NOT recompute the time first; to recompute the time, then
	 * the fields, use the method complete().
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	protected function computeFields()
	{
		// Compute local wall millis
		$localMillis = $this->internalGetTime();

		$rawOffset = $dstOffset = 0;
		$this->getTimeZone()->getOffsetRef($localMillis, false, $rawOffset, $dstOffset);
		$localMillis += $rawOffset; 

		// Mark fields as set.  Do this before calling handleComputeFields().
		$mask =   //fInternalSetMask;
			(1 << AgaviDateDefinitions::ERA) |
			(1 << AgaviDateDefinitions::YEAR) |
			(1 << AgaviDateDefinitions::MONTH) |
			(1 << AgaviDateDefinitions::DAY_OF_MONTH) | // = UCAL_DATE
			(1 << AgaviDateDefinitions::DAY_OF_YEAR) |
			(1 << AgaviDateDefinitions::EXTENDED_YEAR);

		for($i = 0; $i < AgaviDateDefinitions::FIELD_COUNT; ++$i) {
			if(($mask & 1) == 0) {
				$this->fStamp[$i] = self::kInternallySet;
				$this->fIsSet[$i] = true; // Remove later
			} else {
				$this->fStamp[$i] = self::kUnset;
				$this->fIsSet[$i] = false; // Remove later
			}
			$mask >>= 1;
		}

		// We used to check for and correct extreme millis values (near
		// Long.MIN_VALUE or Long.MAX_VALUE) here.  Such values would cause
		// overflows from positive to negative (or vice versa) and had to
		// be manually tweaked.  We no longer need to do this because we
		// have limited the range of supported dates to those that have a
		// Julian day that fits into an int.  This allows us to implement a
		// JULIAN_DAY field and also removes some inelegant code. - Liu
		// 11/6/00

		$days = floor($localMillis / AgaviDateDefinitions::MILLIS_PER_DAY);

		$this->internalSet(AgaviDateDefinitions::JULIAN_DAY, $days + AgaviDateDefinitions::EPOCH_START_AS_JULIAN_DAY);

		// In some cases we will have to call this method again below to
		// adjust for DST pushing us into the next Julian day.
		$this->computeGregorianAndDOWFields($this->fFields[AgaviDateDefinitions::JULIAN_DAY]);

		$millisInDay =  (int) ($localMillis - ($days * AgaviDateDefinitions::MILLIS_PER_DAY));
		if($millisInDay < 0) {
			$millisInDay += (int) AgaviDateDefinitions::MILLIS_PER_DAY;
		}

		// Adjust our millisInDay for DST.  dstOffset will be zero if DST
		// is not in effect at this time of year, or if our zone does not
		// use DST.
		$millisInDay += $dstOffset;

		// If DST has pushed us into the next day, we must call
		// computeGregorianAndDOWFields() again.  This happens in DST between
		// 12:00 am and 1:00 am every day.  The first call to
		// computeGregorianAndDOWFields() will give the wrong day, since the
		// Standard time is in the previous day.
		if($millisInDay >= (int) AgaviDateDefinitions::MILLIS_PER_DAY) {
			$millisInDay -= (int) AgaviDateDefinitions::MILLIS_PER_DAY; // ASSUME dstOffset < 24:00

			// We don't worry about overflow of JULIAN_DAY because the
			// allowable range of JULIAN_DAY has slop at the ends (that is,
			// the max is less that 0x7FFFFFFF and the min is greater than
			// -0x80000000).
			$this->computeGregorianAndDOWFields(++$this->fFields[AgaviDateDefinitions::JULIAN_DAY]);
		}

		// Call framework method to have subclass compute its fields.
		// These must include, at a minimum, MONTH, DAY_OF_MONTH,
		// EXTENDED_YEAR, YEAR, DAY_OF_YEAR.  This method will call internalSet(),
		// which will update stamp[].
		$this->handleComputeFields($this->fFields[AgaviDateDefinitions::JULIAN_DAY]);

		// Compute week-related fields, based on the subclass-computed
		// fields computed by handleComputeFields().
		$this->computeWeekFields();

		// Compute time-related fields.  These are indepent of the date and
		// of the subclass algorithm.  They depend only on the local zone
		// wall milliseconds in day.
		$this->fFields[AgaviDateDefinitions::MILLISECONDS_IN_DAY] = $millisInDay;
		$this->fFields[AgaviDateDefinitions::MILLISECOND] = $millisInDay % 1000;
		$millisInDay /= 1000;
		$this->fFields[AgaviDateDefinitions::SECOND] = $millisInDay % 60;
		$millisInDay /= 60;
		$this->fFields[AgaviDateDefinitions::MINUTE] = $millisInDay % 60;
		$millisInDay /= 60;
		$this->fFields[AgaviDateDefinitions::HOUR_OF_DAY] = (int) $millisInDay;
		$this->fFields[AgaviDateDefinitions::AM_PM] = (int) ($millisInDay / 12); // Assume AM == 0
		$this->fFields[AgaviDateDefinitions::HOUR] = $millisInDay % 12;
		$this->fFields[AgaviDateDefinitions::ZONE_OFFSET] = $rawOffset;
		$this->fFields[AgaviDateDefinitions::DST_OFFSET] = $dstOffset;
	}

	/**
	 * Gets this Calendar's current time as a long.
	 *
	 * @return     double the current time as UTC milliseconds from the epoch.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	protected function getTimeInMillis()
	{
		if(!$this->fIsTimeSet) {
			$this->updateTime();
		}
		
		return $this->fTime;
	}

	/**
	 * Sets this Calendar's current time from the given long value.
	 * 
	 * @param      double the new time in UTC milliseconds from the epoch.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	protected function setTimeInMillis($millis)
	{
		if($millis > self::MAX_MILLIS) {
			$millis = self::MAX_MILLIS;
		} elseif($millis < self::MIN_MILLIS) {
			$millis = self::MIN_MILLIS;
		}
		
		$this->fTime = $millis;
		$this->fAreFieldsInSync = $this->fAreAllFieldsSet = false;
		$this->fIsTimeSet = $this->fAreFieldsVirtuallySet = true;
	}

	/**
	 * Recomputes the current time from currently set fields, and then fills in
	 * any unset fields in the time field list.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	protected function complete()
	{
		if(!$this->fIsTimeSet) {
			$this->updateTime();
		}

		if(!$this->fAreFieldsInSync) {
			$this->computeFields(); // fills in unset fields

			$this->fAreFieldsInSync        = true;
			$this->fAreAllFieldsSet     = true;
		}
	}

	/**
	 * Gets the value for a given time field. Subclasses can use this function to
	 * get field values without forcing recomputation of time. If the field's
	 * stamp is UNSET, the defaultValue is used.
	 *
	 * @param      string The given time field.
	 * @param      int    a default value used if the field is unset.
	 * @return     int    The value for the given time field.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	protected function internalGet($field, $defaultValue = null)
	{
		return $this->fStamp[$field] > self::kUnset ? $this->fFields[$field] : $defaultValue;
	}

	/**
	 * Sets the value for a given time field.  This is a fast internal method for
	 * subclasses.  It does not affect the fAreFieldsInSync, isTimeSet, or 
	 * areAllFieldsSet flags.
	 *
	 * @param      string The given time field.
	 * @param      int    The value for the given time field.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	protected function internalSet($field, $value)
	{
		$this->fFields[$field]  = $value;
		$this->fStamp[$field]   = self::kInternallySet;
		$this->fIsSet[$field]   = true; // Remove later
	}

	/**
	 * Prepare this calendar for computing the actual minimum or maximum.
	 * This method modifies this calendar's fields; it is called on a
	 * temporary calendar.
	 * 
	 * @param      string The given time field
	 * @param      bool   
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	protected function prepareGetActual($field, $isMinimum)
	{
		$this->set(AgaviDateDefinitions::MILLISECONDS_IN_DAY, 0);

		switch($field) {
			case AgaviDateDefinitions::YEAR:
			case AgaviDateDefinitions::YEAR_WOY:
			case AgaviDateDefinitions::EXTENDED_YEAR:
				$this->set(AgaviDateDefinitions::DAY_OF_YEAR, $this->getGreatestMinimum(AgaviDateDefinitions::DAY_OF_YEAR));
				break;

			case AgaviDateDefinitions::MONTH:
				$this->set(AgaviDateDefinitions::DATE, $this->getGreatestMinimum(AgaviDateDefinitions::DATE));
				break;

			case AgaviDateDefinitions::DAY_OF_WEEK_IN_MONTH:
				// For dowim, the maximum occurs for the DOW of the first of the
				// month.
				$this->set(AgaviDateDefinitions::DATE, 1);
				$this->set(AgaviDateDefinitions::DAY_OF_WEEK, $this->get(AgaviDateDefinitions::DAY_OF_WEEK)); // Make this user set
				break;

			case AgaviDateDefinitions::WEEK_OF_MONTH:
			case AgaviDateDefinitions::WEEK_OF_YEAR:
				// If we're counting weeks, set the day of the week to either the
				// first or last localized DOW.  We know the last week of a month
				// or year will contain the first day of the week, and that the
				// first week will contain the last DOW.
				$dow = $this->fFirstDayOfWeek;
				if($isMinimum) {
					$dow = ($dow + 6) % 7; // set to last DOW
					if($dow < AgaviDateDefinitions::SUNDAY) {
						$dow += 7;
					}
				}

				$this->set(AgaviDateDefinitions::DAY_OF_WEEK, $dow);
				break;
		default:
				;
		}

		// Do this last to give it the newest time stamp
		$this->set($field, $this->getGreatestMinimum($field));
	}

	/**
	 * Subclass API for defining limits of different types.
	 * Subclasses must implement this method to return limits for the
	 * following fields:
	 *
	 * <pre>UCAL_ERA
	 * UCAL_YEAR
	 * UCAL_MONTH
	 * UCAL_WEEK_OF_YEAR
	 * UCAL_WEEK_OF_MONTH
	 * UCAL_DATE (DAY_OF_MONTH on Java)
	 * UCAL_DAY_OF_YEAR
	 * UCAL_DAY_OF_WEEK_IN_MONTH
	 * UCAL_YEAR_WOY
	 * UCAL_EXTENDED_YEAR</pre>
	 *
	 * @param      string one of the above field numbers
	 * @param      int one of <code>MINIMUM</code>, <code>GREATEST_MINIMUM</code>,
	 *                 <code>LEAST_MAXIMUM</code>, or <code>MAXIMUM</code>
	 * 
	 * @return     int 
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	protected abstract function handleGetLimit($field, $limitType);

	/**
	 * Return a limit for a field.
	 * 
	 * @param      string the field, from <code>0..UCAL_MAX_FIELD</code>
	 * @param      int the type specifier for the limit
	 * 
	 * @see        #ELimitType
	 * 
	 * @return     int  
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	protected function getLimit($field, $limitType)
	{
		switch($field) {
			case AgaviDateDefinitions::DAY_OF_WEEK:
			case AgaviDateDefinitions::AM_PM:
			case AgaviDateDefinitions::HOUR:
			case AgaviDateDefinitions::HOUR_OF_DAY:
			case AgaviDateDefinitions::MINUTE:
			case AgaviDateDefinitions::SECOND:
			case AgaviDateDefinitions::MILLISECOND:
			case AgaviDateDefinitions::ZONE_OFFSET:
			case AgaviDateDefinitions::DST_OFFSET:
			case AgaviDateDefinitions::DOW_LOCAL:
			case AgaviDateDefinitions::JULIAN_DAY:
			case AgaviDateDefinitions::MILLISECONDS_IN_DAY:
				return self::$kCalendarLimits[$field][$limitType];
			default:
				return $this->handleGetLimit($field, $limitType);
		}
	}

	/**
	 * Return the Julian day number of day before the first day of the
	 * given month in the given extended year.  Subclasses should override
	 * this method to implement their calendar system.
	 * 
	 * @param      int  the extended year
	 * @param      int  the zero-based month, or 0 if useMonth is false
	 * @param      bool useMonth if false, compute the day before the first day of
	 *                  the given year, otherwise, compute the day before the 
	 *                  first day of the given month
	 * 
	 * @return     int  the Julian day number of the day before the first
	 *                  day of the given month and year
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	protected abstract function handleComputeMonthStart($eyear, $month, $useMonth);

	/**
	 * Return the number of days in the given month of the given extended
	 * year of this calendar system.  Subclasses should override this
	 * method if they can provide a more correct or more efficient
	 * implementation than the default implementation in Calendar.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	protected function handleGetMonthLength($extendedYear, $month)
	{
		return $this->handleComputeMonthStart($extendedYear, $month + 1, true) -
						$this->handleComputeMonthStart($extendedYear, $month, true);
	}

	/**
	 * Return the number of days in the given extended year of this
	 * calendar system.  Subclasses should override this method if they can
	 * provide a more correct or more efficient implementation than the
	 * default implementation in Calendar.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	protected function handleGetYearLength($eyear)
	{
		return $this->handleComputeMonthStart($eyear + 1, 0, false) -
						$this->handleComputeMonthStart($eyear, 0, false);
	}

	/**
	 * Return the extended year defined by the current fields.  This will
	 * use the UCAL_EXTENDED_YEAR field or the UCAL_YEAR and supra-year fields 
	 * (such as UCAL_ERA) specific to the calendar system, depending on which set 
	 * of fields is newer.
	 * 
	 * @return     int the extended year
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	protected abstract function handleGetExtendedYear();

	/**
	 * Subclasses may override this.  This method calls
	 * handleGetMonthLength() to obtain the calendar-specific month
	 * length.
	 * 
	 * @param      string which field to use to calculate the date
	 * 
	 * @return     int    julian day specified by calendar fields.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	protected function handleComputeJulianDay($bestField)
	{
		$useMonth = ($bestField == AgaviDateDefinitions::DAY_OF_MONTH ||
									$bestField == AgaviDateDefinitions::WEEK_OF_MONTH ||
									$bestField == AgaviDateDefinitions::DAY_OF_WEEK_IN_MONTH);
		$year = 0;

		if($bestField == AgaviDateDefinitions::WEEK_OF_YEAR) {
			$year = $this->internalGet(AgaviDateDefinitions::YEAR_WOY, $this->handleGetExtendedYear());
			$this->internalSet(AgaviDateDefinitions::EXTENDED_YEAR, $year);
		} else {
			$year = $this->handleGetExtendedYear();
			$this->internalSet(AgaviDateDefinitions::EXTENDED_YEAR, $year);
		}

		// Get the Julian day of the day BEFORE the start of this year.
		// If useMonth is true, get the day before the start of the month.

		// give calendar subclass a chance to have a default 'first' month
		$month = 0;

		if($this->_isSet(AgaviDateDefinitions::MONTH)) {
			$month = $this->internalGet(AgaviDateDefinitions::MONTH);
		} else {
			$month = $this->getDefaultMonthInYear();
		}

		$julianDay = $this->handleComputeMonthStart($year, $useMonth ? $month : 0, $useMonth);

		if($bestField == AgaviDateDefinitions::DAY_OF_MONTH) {

			// give calendar subclass a chance to have a default 'first' dom
			$dayOfMonth = 0;
			if($this->_isSet(AgaviDateDefinitions::DAY_OF_MONTH)) {
				$dayOfMonth = $this->internalGet(AgaviDateDefinitions::DAY_OF_MONTH, 1);
			} else {
				$dayOfMonth = $this->getDefaultDayInMonth($month);
			}

			return $julianDay + $dayOfMonth;
		}

		if($bestField == AgaviDateDefinitions::DAY_OF_YEAR) {
			return $julianDay + $this->internalGet(AgaviDateDefinitions::DAY_OF_YEAR);
		}

		$firstDayOfWeek = $this->getFirstDayOfWeek(); // Localized fdw

		// At this point julianDay is the 0-based day BEFORE the first day of
		// January 1, year 1 of the given calendar.  If julianDay == 0, it
		// specifies (Jan. 1, 1) - 1, in whatever calendar we are using (Julian
		// or Gregorian). (or it is before the month we are in, if useMonth is True)

		// At this point we need to process the WEEK_OF_MONTH or
		// WEEK_OF_YEAR, which are similar, or the DAY_OF_WEEK_IN_MONTH.
		// First, perform initial shared computations.  These locate the
		// first week of the period.

		// Get the 0-based localized DOW of day one of the month or year.
		// Valid range 0..6.
		$first = $this->julianDayToDayOfWeek($julianDay + 1) - $firstDayOfWeek;
		if($first < 0) {
			$first += 7;
		}

		$dowLocal = $this->getLocalDOW();

		// Find the first target DOW (dowLocal) in the month or year.
		// Actually, it may be just before the first of the month or year.
		// It will be an integer from -5..7.
		$date = 1 - $first + $dowLocal;

		if($bestField == AgaviDateDefinitions::DAY_OF_WEEK_IN_MONTH) {
			// Adjust the target DOW to be in the month or year.
			if($date < 1) {
				$date += 7;
			}

			// The only trickiness occurs if the day-of-week-in-month is
			// negative.
			$dim = $this->internalGet(AgaviDateDefinitions::DAY_OF_WEEK_IN_MONTH, 1);
			if($dim >= 0) {
				$date += 7 * ($dim - 1);
			} else {
				// Move date to the last of this day-of-week in this month,
				// then back up as needed.  If dim==-1, we don't back up at
				// all.  If dim==-2, we back up once, etc.  Don't back up
				// past the first of the given day-of-week in this month.
				// Note that we handle -2, -3, etc. correctly, even though
				// values < -1 are technically disallowed.
				$m = $this->internalGet(AgaviDateDefinitions::MONTH, AgaviDateDefinitions::JANUARY);
				$monthLength = $this->handleGetMonthLength($year, $m);
				$date += (intval(($monthLength - $date) / 7) + $dim + 1) * 7;
			}
		} else {
			if($bestField == AgaviDateDefinitions::WEEK_OF_YEAR) {  // ------------------------------------- WOY -------------
				if(!$this->_isSet(AgaviDateDefinitions::YEAR_WOY) ||  // YWOY not set at all or
						( ($this->resolveFields(self::$kYearPrecedence) != AgaviDateDefinitions::YEAR_WOY) // YWOY doesn't have precedence
						&& ($this->fStamp[AgaviDateDefinitions::YEAR_WOY] != self::kInternallySet) ) ) // (excluding where all fields are internally set - then YWOY is used)
				{
					// need to be sure to stay in 'real' year.
					$woy = $this->internalGet($bestField);

					$nextJulianDay = $this->handleComputeMonthStart($year + 1, 0, false); // jd of day before jan 1
					$nextFirst = $this->julianDayToDayOfWeek($nextJulianDay + 1) - $firstDayOfWeek; 

					if($nextFirst < 0) { // 0..6 ldow of Jan 1
						$nextFirst += 7;
					}

					if($woy==1) {  // FIRST WEEK ---------------------------------

						// nextFirst is now the localized DOW of Jan 1  of y-woy+1
						if(($nextFirst > 0) &&   // Jan 1 starts on FDOW
							(7 - $nextFirst) >= $this->getMinimalDaysInFirstWeek()) // or enough days in the week
						{
							// Jan 1 of (yearWoy+1) is in yearWoy+1 - recalculate JD to next year
							$julianDay = $nextJulianDay;

							// recalculate 'first' [0-based local dow of jan 1]
							$first = $this->julianDayToDayOfWeek($julianDay + 1) - $firstDayOfWeek;
							if($first < 0) {
								$first += 7;
							}
							// recalculate date.
							$date = 1 - $first + $dowLocal;
						}
					} elseif($woy >= $this->getLeastMaximum($bestField)) {
						// could be in the last week- find out if this JD would overstep
						$testDate = $date;
						if((7 - $first) < $this->getMinimalDaysInFirstWeek()) {
							$testDate += 7;
						}

						// Now adjust for the week number.
						$testDate += 7 * ($woy - 1);

						if($julianDay + $testDate > $nextJulianDay) { // is it past Dec 31?  (nextJulianDay is day BEFORE year+1's  Jan 1)
							// Fire up the calculating engines.. retry YWOY = (year-1)
							$julianDay = $this->handleComputeMonthStart($year - 1, 0, false); // jd before Jan 1 of previous year
							$first = $this->julianDayToDayOfWeek($julianDay + 1) - $firstDayOfWeek; // 0 based local dow   of first week

							if($first < 0) { // 0..6
								$first += 7;
							}
							$date = 1 - $first + $dowLocal;

						} /* correction needed */
					} /* leastmaximum */
				} /* resolvefields(year) != year_woy */
			} /* bestfield != week_of_year */

			// assert(bestField == WEEK_OF_MONTH || bestField == WEEK_OF_YEAR)
			// Adjust for minimal days in first week
			if((7 - $first) < $this->getMinimalDaysInFirstWeek()) {
				$date += 7;
			}

			// Now adjust for the week number.
			$date += 7 * ($this->internalGet($bestField) - 1);
		}

		return $julianDay + $date;

	}

	/**
	 * Subclasses must override this to convert from week fields
	 * (YEAR_WOY and WEEK_OF_YEAR) to an extended year in the case
	 * where YEAR, EXTENDED_YEAR are not set.
	 * The Calendar implementation assumes yearWoy is in extended gregorian form
	 * 
	 * @internal
	 * 
	 * @param      int 
	 * @param      int 
	 * 
	 * @return     int the extended year, UCAL_EXTENDED_YEAR
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	protected function handleGetExtendedYearFromWeekFields($yearWoy, $woy)
	{
		// We have UCAL_YEAR_WOY and UCAL_WEEK_OF_YEAR - from those, determine 
		// what year we fall in, so that other code can set it properly.
		// (code borrowed from computeWeekFields and handleComputeJulianDay)
		//return yearWoy;

		// First, we need a reliable DOW.
		$bestField = $this->resolveFields(self::$kDatePrecedence); // !! Note: if subclasses have a different table, they should override handleGetExtendedYearFromWeekFields 

		// Now, a local DOW
		$dowLocal = $this->getLocalDOW(); // 0..6
		$firstDayOfWeek = $this->getFirstDayOfWeek(); // Localized fdw
		$jan1Start = $this->handleComputeMonthStart($yearWoy, 0, false);
		$nextJan1Start = $this->handleComputeMonthStart($yearWoy + 1, 0, false); // next year's Jan1 start

		// At this point julianDay is the 0-based day BEFORE the first day of
		// January 1, year 1 of the given calendar.  If julianDay == 0, it
		// specifies (Jan. 1, 1) - 1, in whatever calendar we are using (Julian
		// or Gregorian). (or it is before the month we are in, if useMonth is True)

		// At this point we need to process the WEEK_OF_MONTH or
		// WEEK_OF_YEAR, which are similar, or the DAY_OF_WEEK_IN_MONTH.
		// First, perform initial shared computations.  These locate the
		// first week of the period.

		// Get the 0-based localized DOW of day one of the month or year.
		// Valid range 0..6.
		$first = $this->julianDayToDayOfWeek($jan1Start + 1) - $firstDayOfWeek;
		if($first < 0) {
			$first += 7;
		}
		$nextFirst = $this->julianDayToDayOfWeek($nextJan1Start + 1) - $firstDayOfWeek;
		if($nextFirst < 0) {
			$nextFirst += 7;
		}

		$minDays = $this->getMinimalDaysInFirstWeek();
		$jan1InPrevYear = false;  // January 1st in the year of WOY is the 1st week?  (i.e. first week is < minimal )
		//UBool nextJan1InPrevYear = false; // January 1st of Year of WOY + 1 is in the first week? 

		if((7 - $first) < $minDays) { 
			$jan1InPrevYear = true;
		}

		//   if((7 - nextFirst) < minDays) {
		//     nextJan1InPrevYear = true;
		//   }

		switch($bestField) {
			case AgaviDateDefinitions::WEEK_OF_YEAR:
				if($woy == 1) {
					if($jan1InPrevYear == true) {
						// the first week of January is in the previous year
						// therefore WOY1 is always solidly within yearWoy
						return $yearWoy;
					} else {
						// First WOY is split between two years
						if($dowLocal < $first) { // we are prior to Jan 1
							return $yearWoy - 1; // previous year
						} else {
							return $yearWoy; // in this year
						}
					}
				} elseif($woy >= $this->getLeastMaximum($bestField)) {
					// we _might_ be in the last week.. 
					$jd =  // Calculate JD of our target day:
								$jan1Start +  // JD of Jan 1
								(7 - $first) + //  days in the first week (Jan 1.. )
								($woy - 1) * 7 + // add the weeks of the year
								$dowLocal;   // the local dow (0..6) of last week
					if($jan1InPrevYear == false) {
						$jd -= 7; // woy already includes Jan 1's week.
					}

					if(($jd + 1) >= $nextJan1Start) {
						// we are in week 52 or 53 etc. - actual year is yearWoy+1
						return $yearWoy + 1;
					} else {
						// still in yearWoy;
						return $yearWoy;
					}
				} else {
					// we're not possibly in the last week -must be ywoy
					return $yearWoy;
				}
				break;

			case AgaviDateDefinitions::DATE:
				if(($this->internalGet(AgaviDateDefinitions::MONTH)==0) &&
						($woy >= $this->getLeastMaximum(AgaviDateDefinitions::WEEK_OF_YEAR))) {
					return $yearWoy + 1; // month 0, late woy = in the next year
				} elseif($woy == 1) {
					//if(nextJan1InPrevYear) {
						if($this->internalGet(AgaviDateDefinitions::MONTH)==0) {
							return $yearWoy;
						} else {
							return $yearWoy - 1;
						}
					//}
				}

				//(internalGet(UCAL_DATE) <= (7-first)) /* && in minDow  */ ) {
				//within 1st week and in this month.. 
				//return yearWoy+1;
				return $yearWoy;
				break;

			default: // assume the year is appropriate
				return $yearWoy;
				break;
		}

		return $yearWoy;

	}

	/**
	 * Compute the Julian day from fields.  Will determine whether to use
	 * the JULIAN_DAY field directly, or other fields.
	 * 
	 * @return     int the julian day
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	protected function computeJulianDay()
	{
		// We want to see if any of the date fields is newer than the
		// JULIAN_DAY.  If not, then we use JULIAN_DAY.  If so, then we do
		// the normal resolution.  We only use JULIAN_DAY if it has been
		// set by the user.  This makes it possible for the caller to set
		// the calendar to a time and call clear(MONTH) to reset the MONTH
		// to January.  This is legacy behavior.  Without this,
		// clear(MONTH) has no effect, since the internally set JULIAN_DAY
		// is used.
		if($this->fStamp[AgaviDateDefinitions::JULIAN_DAY] >= (int)self::kMinimumUserStamp) {
			$bestStamp = $this->newestStamp(AgaviDateDefinitions::ERA, AgaviDateDefinitions::DAY_OF_WEEK_IN_MONTH, self::kUnset);
			$bestStamp = $this->newestStamp(AgaviDateDefinitions::YEAR_WOY, AgaviDateDefinitions::EXTENDED_YEAR, $bestStamp);
			if($bestStamp <= $this->fStamp[AgaviDateDefinitions::JULIAN_DAY]) {
				return $this->internalGet(AgaviDateDefinitions::JULIAN_DAY);
			}
		}

		$bestField = $this->resolveFields($this->getFieldResolutionTable());
		if($bestField == AgaviDateDefinitions::FIELD_COUNT) {
			$bestField = AgaviDateDefinitions::DAY_OF_MONTH;
		}

		return $this->handleComputeJulianDay($bestField);
	}

	/**
	 * Compute the milliseconds in the day from the fields.  This is a
	 * value from 0 to 23:59:59.999 inclusive, unless fields are out of
	 * range, in which case it can be an arbitrary value.  This value
	 * reflects local zone wall time.
	 * 
	 * @return     int The milliseconds in the day
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	protected function computeMillisInDay()
	{
		// Do the time portion of the conversion.

		$millisInDay = 0;

		// Find the best set of fields specifying the time of day.  There
		// are only two possibilities here; the HOUR_OF_DAY or the
		// AM_PM and the HOUR.
		$hourOfDayStamp = $this->fStamp[AgaviDateDefinitions::HOUR_OF_DAY];
		$hourStamp = ($this->fStamp[AgaviDateDefinitions::HOUR] > $this->fStamp[AgaviDateDefinitions::AM_PM]) ? $this->fStamp[AgaviDateDefinitions::HOUR] : $this->fStamp[AgaviDateDefinitions::AM_PM];
		$bestStamp = ($hourStamp > $hourOfDayStamp) ? $hourStamp : $hourOfDayStamp;

		// Hours
		if($bestStamp != self::kUnset) {
			if($bestStamp == $hourOfDayStamp) {
				// Don't normalize here; let overflow bump into the next period.
				// This is consistent with how we handle other fields.
				$millisInDay += $this->internalGet(AgaviDateDefinitions::HOUR_OF_DAY);
			} else {
				// Don't normalize here; let overflow bump into the next period.
				// This is consistent with how we handle other fields.
				$millisInDay += $this->internalGet(AgaviDateDefinitions::HOUR);
				$millisInDay += 12 * $this->internalGet(AgaviDateDefinitions::AM_PM); // Default works for unset AM_PM
			}
		}

		// We use the fact that unset == 0; we start with millisInDay
		// == HOUR_OF_DAY.
		$millisInDay *= 60;
		$millisInDay += $this->internalGet(AgaviDateDefinitions::MINUTE); // now have minutes
		$millisInDay *= 60;
		$millisInDay += $this->internalGet(AgaviDateDefinitions::SECOND); // now have seconds
		$millisInDay *= 1000;
		$millisInDay += $this->internalGet(AgaviDateDefinitions::MILLISECOND); // now have millis

		return $millisInDay;

	}

	/**
	 * This method can assume EXTENDED_YEAR has been set.
	 * 
	 * @param      double milliseconds of the date fields
	 * @param      int    milliseconds of the time fields; may be out or range.
	 * 
	 * @return     int    
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	protected function computeZoneOffset($millis, $millisInDay)
	{
		$rawOffset = $dstOffset = 0;
		$this->getTimeZone()->getOffsetRef($millis + $millisInDay, true, $rawOffset, $dstOffset);
		return $rawOffset + $dstOffset;
		// Note: Because we pass in wall millisInDay, rather than
		// standard millisInDay, we interpret "1:00 am" on the day
		// of cessation of DST as "1:00 am Std" (assuming the time
		// of cessation is 2:00 am).

	}

	/**
	 * Determine the best stamp in a range.
	 * 
	 * @param      string first enum to look at
	 * @param      string last enum to look at
	 * @param      int    stamp prior to function call
	 * 
	 * @return     int    the stamp value of the best stamp
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	protected function newestStamp($first, $last, $bestStampSoFar)
	{
		$bestStamp = $bestStampSoFar;
		for($i = (int) $first; $i <= (int) $last; ++$i) {
			if($this->fStamp[$i] > $bestStamp) {
				$bestStamp = $this->fStamp[$i];
			}
		}
		return $bestStamp;
	}

	/**
	 * @var        array Precedence table for Dates
	 * @see #resolveFields
	 * @internal
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	static $kDatePrecedence = array(
		array(
			array(AgaviDateDefinitions::DAY_OF_MONTH, self::RESOLVE_STOP),
			array(AgaviDateDefinitions::WEEK_OF_YEAR, AgaviDateDefinitions::DAY_OF_WEEK, self::RESOLVE_STOP),
			array(AgaviDateDefinitions::WEEK_OF_MONTH, AgaviDateDefinitions::DAY_OF_WEEK, self::RESOLVE_STOP),
			array(AgaviDateDefinitions::DAY_OF_WEEK_IN_MONTH, AgaviDateDefinitions::DAY_OF_WEEK, self::RESOLVE_STOP),
			array(AgaviDateDefinitions::WEEK_OF_YEAR, AgaviDateDefinitions::DOW_LOCAL, self::RESOLVE_STOP),
			array(AgaviDateDefinitions::WEEK_OF_MONTH, AgaviDateDefinitions::DOW_LOCAL, self::RESOLVE_STOP),
			array(AgaviDateDefinitions::DAY_OF_WEEK_IN_MONTH, AgaviDateDefinitions::DOW_LOCAL, self::RESOLVE_STOP),
			array(AgaviDateDefinitions::DAY_OF_YEAR, self::RESOLVE_STOP),
			//    kResolveRemap | UCAL_DAY_OF_MONTH
			array(37, AgaviDateDefinitions::YEAR, self::RESOLVE_STOP),  // if YEAR is set over YEAR_WOY use DAY_OF_MONTH
			//    kResolveRemap | UCAL_WEEK_OF_YEAR
			array(35, AgaviDateDefinitions::YEAR_WOY, self::RESOLVE_STOP),  // if YEAR_WOY is set,  calc based on WEEK_OF_YEAR
			array(self::RESOLVE_STOP),
		),
		array(
			array(AgaviDateDefinitions::WEEK_OF_YEAR, self::RESOLVE_STOP),
			array(AgaviDateDefinitions::WEEK_OF_MONTH, self::RESOLVE_STOP),
			array(AgaviDateDefinitions::DAY_OF_WEEK_IN_MONTH, self::RESOLVE_STOP),
			//    kResolveRemap | UCAL_DAY_OF_WEEK_IN_MONTH
			array(40, AgaviDateDefinitions::DAY_OF_WEEK, self::RESOLVE_STOP),
			//    self::kResolveRemap | UCAL_DAY_OF_WEEK_IN_MONTH
			array(40, AgaviDateDefinitions::DOW_LOCAL, self::RESOLVE_STOP),
			array(self::RESOLVE_STOP),
		),
		array(
			array(self::RESOLVE_STOP),
		),
	);

	/**
	 * @var        array Precedence table for Year
	 * @see #resolveFields
	 * @internal
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	protected static $kYearPrecedence = array(
		array(
			array(AgaviDateDefinitions::YEAR, self::RESOLVE_STOP),
			array(AgaviDateDefinitions::EXTENDED_YEAR, self::RESOLVE_STOP),
			array(AgaviDateDefinitions::YEAR_WOY, AgaviDateDefinitions::WEEK_OF_YEAR, self::RESOLVE_STOP),  // YEAR_WOY is useless without WEEK_OF_YEAR
			array(self::RESOLVE_STOP),
		),
		array(
			array(self::RESOLVE_STOP),
		),
	);

	/**
	 * @var        array Precedence table for Day of Week
	 * @see #resolveFields
	 * @internal
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	protected static $kDOWPrecedence = array(
		array(
			array(AgaviDateDefinitions::DAY_OF_WEEK, self::RESOLVE_STOP, self::RESOLVE_STOP),
			array(AgaviDateDefinitions::DOW_LOCAL, self::RESOLVE_STOP, self::RESOLVE_STOP),
			array(self::RESOLVE_STOP),
		),
		array(
			array(self::RESOLVE_STOP),
		),
	);

	/**
	 * Given a precedence table, return the newest field combination in
	 * the table, or UCAL_FIELD_COUNT if none is found.
	 *
	 * <p>The precedence table is a 3-dimensional array of integers.  It
	 * may be thought of as an array of groups.  Each group is an array of
	 * lines.  Each line is an array of field numbers.  Within a line, if
	 * all fields are set, then the time stamp of the line is taken to be
	 * the stamp of the most recently set field.  If any field of a line is
	 * unset, then the line fails to match.  Within a group, the line with
	 * the newest time stamp is selected.  The first field of the line is
	 * returned to indicate which line matched.
	 *
	 * <p>In some cases, it may be desirable to map a line to field that
	 * whose stamp is NOT examined.  For example, if the best field is
	 * DAY_OF_WEEK then the DAY_OF_WEEK_IN_MONTH algorithm may be used.  In
	 * order to do this, insert the value <code>kResolveRemap | F</code> at
	 * the start of the line, where <code>F</code> is the desired return
	 * field value.  This field will NOT be examined; it only determines
	 * the return value if the other fields in the line are the newest.
	 *
	 * <p>If all lines of a group contain at least one unset field, then no
	 * line will match, and the group as a whole will fail to match.  In
	 * that case, the next group will be processed.  If all groups fail to
	 * match, then UCAL_FIELD_COUNT is returned.
	 * @internal
	 * 
	 * @param      array the precedence table
	 * 
	 * @return     int   the best field
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	protected function resolveFields($precedenceTable)
	{
		$bestField = AgaviDateDefinitions::FIELD_COUNT;
		for($g = 0; $precedenceTable[$g][0][0] != -1 && ($bestField == AgaviDateDefinitions::FIELD_COUNT); ++$g) {
			$bestStamp = self::kUnset;
			for($l = 0; $precedenceTable[$g][$l][0] != -1; ++$l) {
				$lineStamp = self::kUnset;
				// Skip over first entry if it is negative
				for($i = (($precedenceTable[$g][$l][0] >= self::RESOLVE_REMAP) ? 1 : 0); $precedenceTable[$g][$l][$i] != -1; ++$i) {
					$s = $this->fStamp[$precedenceTable[$g][$l][$i]];

					// If any field is unset then don't use this line
					if($s == self::kUnset) {
//					goto linesInGroup;
						continue 2;
					} elseif($s > $lineStamp) {
						$lineStamp = $s;
					}
				}
				// Record new maximum stamp & field no.
				if($lineStamp > $bestStamp) {
					$bestStamp = $lineStamp;
					$bestField = $precedenceTable[$g][$l][0]; // First field refers to entire line
				}
//linesInGroup:
			}
		}
		return ($bestField >= self::RESOLVE_REMAP) ? ($bestField & (self::RESOLVE_REMAP - 1)) : $bestField ;
	}

	/**
	 * @return     array
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	protected function getFieldResolutionTable()
	{
		return self::$kDatePrecedence;
	}

	/**
	 * Return the field that is newer, either defaultField, or alternateField.
	 * If neither is newer or neither is set, return defaultField.
	 * @internal
	 * 
	 * @param      int
	 * @param      int
	 * 
	 * @return     int
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	protected function newerField($defaultField, $alternateField)
	{
		if($this->fStamp[$alternateField] > $this->fStamp[$defaultField]) {
			return $alternateField;
		}
		return $defaultField;
	}

	/**
	 * Helper function for calculating limits by trial and error
	 * 
	 * @param      string The field being investigated
	 * @param      int    starting (least max) value of field
	 * @param      int    ending (greatest max) value of field
	 * 
	 * @return     int    
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	protected function getActualHelper($field, $startValue, $endValue)
	{
		if($startValue == $endValue) {
			// if we know that the maximum value is always the same, just return it
			return $startValue;
		}

		$delta = ($endValue > $startValue) ? 1 : -1;

		// clone the calendar so we don't mess with the real one, and set it to
		// accept anything for the field values
		$work = clone $this;
		$work->setLenient(true);
		$work->prepareGetActual($field, $delta < 0);

		// now try each value from the start to the end one by one until
		// we get a value that normalizes to another value.  The last value that
		// normalizes to itself is the actual maximum for the current date
		$result = $startValue;
		do {
			$work->set($field, $startValue);
			if($work->get($field) != $startValue) {
				break;
			} else {
				$result = $startValue;
				$startValue += $delta;
			}
		} while($result != $endValue);

		return $result;
	}

	/**
	 * @var        bool The flag which indicates if the current time is set in the
	 *                  calendar.
	 */
	protected $fIsTimeSet = false;

	/**
	 * @var        bool True if the fields are in sync with the currently set time
	 *                  of this Calendar. If false, then the next attempt to get 
	 *                  the value of a field will force a recomputation of all 
	 *                  fields from the current value of the time field.
	 */
	protected $fAreFieldsInSync = false;

	/**
	 * @var        bool True if all of the fields have been set.  This is 
	 *                  initially false, and set to true by computeFields().
	 */
	protected $fAreAllFieldsSet = false;

	/**
	 * @var        bool True if all fields have been virtually set, but have not 
	 *                  yet been computed.  This occurs only in setTimeInMillis().
	 *                  A calendar set to this state will compute all fields from 
	 *                  the time if it becomes necessary, but otherwise will delay
	 *                  such computation.
	 */
	protected $fAreFieldsVirtuallySet = false;

	/**
	 * Get the current time without recomputing.
	 *
	 * @return     float the current time without recomputing.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	protected function internalGetTime()
	{
		return $this->fTime;
	}

	/**
	 * Set the current time without affecting flags or fields.
	 *
	 * @param      float The time to be set
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	protected function internalSetTime($time)
	{
		$this->fTime = $time;
	}

	/**
	 * @var        array The time fields containing values into which the millis
	 *                   is computed.
	 */
	protected $fFields;

	/**
	 * @var        array The flags which tell if a specified time field for the
	 *                   calendar is set.
	 * 
	 * @deprecated ICU 2.8 use (fStamp[n]!=kUnset)
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	protected $fIsSet;

	/** Special values of stamp[]
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	const kUnset                 = 0;
	const kInternallySet         = 1;
	const kMinimumUserStamp      = 2;

	/**
	 * @var        array Pseudo-time-stamps which specify when each field was set.
	 *                   There are two special values, UNSET and INTERNALLY_SET. 
	 *                   Values from MINIMUM_USER_SET to Integer.MAX_VALUE are 
	 *                   legal user set values.
	 */
	protected $fStamp;

	/**
	 * Subclasses may override this method to compute several fields
	 * specific to each calendar system.  These are:
	 *
	 * <ul><li>ERA
	 * <li>YEAR
	 * <li>MONTH
	 * <li>DAY_OF_MONTH
	 * <li>DAY_OF_YEAR
	 * <li>EXTENDED_YEAR</ul>
	 *
	 * Subclasses can refer to the DAY_OF_WEEK and DOW_LOCAL fields, which
	 * will be set when this method is called.  Subclasses can also call
	 * the getGregorianXxx() methods to obtain Gregorian calendar
	 * equivalents for the given Julian day.
	 *
	 * <p>In addition, subclasses should compute any subclass-specific
	 * fields, that is, fields from BASE_FIELD_COUNT to
	 * getFieldCount() - 1.
	 *
	 * <p>The default implementation in <code>Calendar</code> implements
	 * a pure proleptic Gregorian calendar.
	 * @internal
	 * 
	 * @param      int The julian day
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	protected function handleComputeFields($julianDay)
	{
		$this->internalSet(AgaviDateDefinitions::MONTH, $this->getGregorianMonth());
		$this->internalSet(AgaviDateDefinitions::DAY_OF_MONTH, $this->getGregorianDayOfMonth());
		$this->internalSet(AgaviDateDefinitions::DAY_OF_YEAR, $this->getGregorianDayOfYear());
		$eyear = $this->getGregorianYear();
		$this->internalSet(AgaviDateDefinitions::EXTENDED_YEAR, $eyear);
		$era = GregorianCalendar::AD;
		if($eyear < 1) {
			$era = GregorianCalendar::BC;
			$eyear = 1 - $eyear;
		}
		$this->internalSet(AgaviDateDefinitions::ERA, $era);
		$this->internalSet(AgaviDateDefinitions::YEAR, $eyear);
	}

	/**
	 * Return the extended year on the Gregorian calendar as computed by
	 * <code>computeGregorianFields()</code>.
	 * @see #computeGregorianFields
	 * @internal
	 * 
	 * @return     int The gregorian year
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	protected function getGregorianYear()
	{
		return $this->fGregorianYear;
	}

	/**
	 * Return the month (0-based) on the Gregorian calendar as computed by
	 * <code>computeGregorianFields()</code>.
	 * @see #computeGregorianFields
	 * @internal
	 * 
	 * @return     int The gregorian month
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	protected function getGregorianMonth()
	{
		return $this->fGregorianMonth;
	}

	/**
	 * Return the day of year (1-based) on the Gregorian calendar as
	 * computed by <code>computeGregorianFields()</code>.
	 * @see #computeGregorianFields
	 * @internal
	 * 
	 * @return     int The gregorian day of year
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	protected function getGregorianDayOfYear()
	{
		return $this->fGregorianDayOfYear;
	}

	/**
	 * Return the day of month (1-based) on the Gregorian calendar as
	 * computed by <code>computeGregorianFields()</code>.
	 * @see #computeGregorianFields
	 * @internal
	 * 
	 * @return     int The gregorian day of month
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	protected function getGregorianDayOfMonth()
	{
		return $this->fGregorianDayOfMonth;
	}

	/**
	 * Called by computeJulianDay.  Returns the default month (0-based) for the 
	 * year, taking year and era into account.  Defaults to 0 for Gregorian, which
	 * doesn't care.
	 * 
	 * @return     int The default month for the year.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	protected function getDefaultMonthInYear()
	{
		return 0;
	}

	/**
	 * Called by computeJulianDay.  Returns the default day (1-based) for the 
	 * month, taking currently-set year and era into account.  Defaults to 1 for
	 * Gregorian.
	 * 
	 * @param      int The months
	 * 
	 * @return     int The default day for the month
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	protected function getDefaultDayInMonth($month)
	{
		return 1;
	}

	//-------------------------------------------------------------------------
	// Protected utility methods for use by subclasses.  These are very handy
	// for implementing add, roll, and computeFields.
	//-------------------------------------------------------------------------

	/**
	 * Adjust the specified field so that it is within
	 * the allowable range for the date to which this calendar is set.
	 * For example, in a Gregorian calendar pinning the 
	 * AgaviCalendarDefinitions::DAY_OF_MONTH field for a calendar set to April 31
	 * would cause it to be set to April 30.
	 * <p>
	 * <b>Subclassing:</b>
	 * <br>
	 * This utility method is intended for use by subclasses that need to 
	 * implement their own overrides of {@link #roll roll} and {@link #add add}.
	 * <p>
	 * <b>Note:</b>
	 * <code>pinField</code> is implemented in terms of
	 * {@link #getActualMinimum getActualMinimum}
	 * and {@link #getActualMaximum getActualMaximum}.  If either of those methods
	 * uses a slow, iterative algorithm for a particular field, it would be
	 * unwise to attempt to call <code>pinField</code> for that field.  If you
	 * really do need to do so, you should override this method to do
	 * something more efficient for that field.
	 * <p>
	 * 
	 * @param      string The calendar field whose value should be pinned.
	 *
	 * @see        getActualMinimum
	 * @see        getActualMaximum
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	protected function pinField($field)
	{
		$max = $this->getActualMaximum($field);
		$min = $this->getActualMinimum($field);

		if($this->fFields[$field] > $max) {
			$this->set($field, $max);
		} elseif($this->fFields[$field] < $min) {
			$this->set($field, $min);
		}
	}

	/**
	 * Return the week number of a day, within a period. This may be the week 
	 * number in a year or the week number in a month. Usually this will be a 
	 * value >= 1, but if some initial days of the period are excluded from 
	 * week 1, because getMinimalDaysInFirstWeek is > 1, then the week number will
	 * be zero for those initial days. This method requires the day number and day
	 * of week for some known date in the period in order to determine the day of 
	 * week on the desired day.
	 * <p>
	 * <b>Subclassing:</b>
	 * <br>
	 * This method is intended for use by subclasses in implementing their
	 * {@link #computeTime computeTime} and/or {@link #computeFields computeFields} methods.
	 * It is often useful in {@link #getActualMinimum getActualMinimum} and
	 * {@link #getActualMaximum getActualMaximum} as well.
	 * <p>
	 * This variant is handy for computing the week number of some other
	 * day of a period (often the first or last day of the period) when its day
	 * of the week is not known but the day number and day of week for some other
	 * day in the period (e.g. the current date) <em>is</em> known.
	 * <p>
	 * 
	 * @param      int The {@link #UCalendarDateFields DAY_OF_YEAR} or
	 *                 {@link #UCalendarDateFields DAY_OF_MONTH} whose week 
	 *                 number is desired.
	 *                 Should be 1 for the first day of the period.
	 *
	 * @param      int The {@link #UCalendarDateFields DAY_OF_YEAR}
	 *                 or {@link #UCalendarDateFields DAY_OF_MONTH} for a day in 
	 *                 the period whose {@link #UCalendarDateFields DAY_OF_WEEK} 
	 *                 is specified by the <code>knownDayOfWeek</code> parameter.
	 *                 Should be 1 for first day of period.
	 *
	 * @param      int The {@link #UCalendarDateFields DAY_OF_WEEK} for the day
	 *                 corresponding to the <code>knownDayOfPeriod</code> 
	 *                 parameter.
	 *                 1-based with 1=Sunday.
	 *
	 * @return     int The week number (one-based), or zero if the day falls 
	 *                 before the first week because
	 *                 {@link #getMinimalDaysInFirstWeek getMinimalDaysInFirstWeek}
	 *                 is more than one.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	protected function weekNumber1($desiredDay, $dayOfPeriod, $dayOfWeek)
	{
		// Determine the day of the week of the first day of the period
		// in question (either a year or a month).  Zero represents the
		// first day of the week on this calendar.
		$periodStartDayOfWeek = ($dayOfWeek - $this->getFirstDayOfWeek() - $dayOfPeriod + 1) % 7;
		if($periodStartDayOfWeek < 0) {
			$periodStartDayOfWeek += 7;
		}

		// Compute the week number.  Initially, ignore the first week, which
		// may be fractional (or may not be).  We add periodStartDayOfWeek in
		// order to fill out the first week, if it is fractional.
		$weekNo = (int) (($desiredDay + $periodStartDayOfWeek - 1) / 7);

		// If the first week is long enough, then count it.  If
		// the minimal days in the first week is one, or if the period start
		// is zero, we always increment weekNo.
		if((7 - $periodStartDayOfWeek) >= $this->getMinimalDaysInFirstWeek()) {
			++$weekNo;
		}

		return $weekNo;
	}

	/**
	 * Return the week number of a day, within a period. This may be the week number in
	 * a year, or the week number in a month. Usually this will be a value >= 1, but if
	 * some initial days of the period are excluded from week 1, because
	 * {@link #getMinimalDaysInFirstWeek getMinimalDaysInFirstWeek} is > 1,
	 * then the week number will be zero for those
	 * initial days. This method requires the day of week for the given date in order to
	 * determine the result.
	 * <p>
	 * <b>Subclassing:</b>
	 * <br>
	 * This method is intended for use by subclasses in implementing their
	 * {@link #computeTime computeTime} and/or {@link #computeFields computeFields} methods.
	 * It is often useful in {@link #getActualMinimum getActualMinimum} and
	 * {@link #getActualMaximum getActualMaximum} as well.
	 * <p>
	 * @param      int The {@link #UCalendarDateFields DAY_OF_YEAR} or
	 *                 {@link #UCalendarDateFields DAY_OF_MONTH} whose week 
	 *                 number is desired. Should be 1 for the first day of the 
	 *                 period.
	 *
	 * @param      int The {@link #UCalendarDateFields DAY_OF_WEEK} for the day
	 *                 corresponding to the <code>dayOfPeriod</code> parameter.
	 *                 1-based with 1=Sunday.
	 *
	 * @return     int The week number (one-based), or zero if the day falls 
	 *                 before the first week because
	 *                 {@link #getMinimalDaysInFirstWeek getMinimalDaysInFirstWeek}
	 *                 is more than one.
	 * @internal
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	protected function weekNumber($dayOfPeriod, $dayOfWeek)
	{
		return $this->weekNumber1($dayOfPeriod, $dayOfPeriod, $dayOfWeek);
	}

	/**
	 * returns the local DOW, valid range 0..6
	 * @internal
	 * 
	 * @return     int
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	protected function getLocalDOW()
	{
		// Get zero-based localized DOW, valid range 0..6.  This is the DOW
		// we are looking for.
		$dowLocal = 0;
		switch($this->resolveFields(self::$kDOWPrecedence)) {
			case AgaviDateDefinitions::DAY_OF_WEEK:
				$dowLocal = $this->internalGet(AgaviDateDefinitions::DAY_OF_WEEK) - $this->fFirstDayOfWeek;
				break;
			case AgaviDateDefinitions::DOW_LOCAL:
				$dowLocal = $this->internalGet(AgaviDateDefinitions::DOW_LOCAL) - 1;
				break;
			default:
				break;
		}
		$dowLocal = $dowLocal % 7;
		if($dowLocal < 0) {
			$dowLocal += 7;
		}
		return $dowLocal;
	}

	/**
	 * @var        int The next available value for fStamp[]
	 */
	private $fNextStamp = 1;// = MINIMUM_USER_STAMP;

	/**
	 * @var        float The current time set for the calendar.
	 */
	private $fTime;

	/**
	 * @see   #setLenient
	 * @var        bool 
	 */
	private $fLenient;

	/**
	 * @var        AgaviTimeZone Time zone affects the time calculation done by 
	 *                           Calendar. Calendar subclasses use the time zone 
	 *                           data to produce the local time.
	 */
	private $fZone;

	/**
	 * Both firstDayOfWeek and minimalDaysInFirstWeek are locale-dependent. They are
	 * used to figure out the week count for a specific date for a given locale. These
	 * must be set when a Calendar is constructed. For example, in US locale,
	 * firstDayOfWeek is SUNDAY; minimalDaysInFirstWeek is 1. They are used to figure
	 * out the week count for a specific date for a given locale. These must be set when
	 * a Calendar is constructed.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	private $fFirstDayOfWeek;
	private $fMinimalDaysInFirstWeek;

	/**
	 * Sets firstDayOfWeek and minimalDaysInFirstWeek. Called at Calendar construction
	 * time.
	 *
	 * @param      AgaviLocale The given locale.
	 * @param      string      The calendar type identifier, e.g: gregorian, 
	 *                         buddhist, etc.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	private function setWeekCountData($desiredLocale, $type)
	{
		// Read the week count data from the resource bundle.  This should
		// have the form:
		//
		//   DateTimeElements:intvector {
		//      1,    // first day of week
		//      1     // min days in week
		//   }
		//   Both have a range of 1..7

		$this->fFirstDayOfWeek = AgaviDateDefinitions::SUNDAY;
		$this->fMinimalDaysInFirstWeek = 1;

		$tm = $desiredLocale->getContext()->getTranslationManager();
		$cdata = $tm->getTerritoryData($desiredLocale->getLocaleTerritory());
		if(isset($cdata['week']['firstDay'])) {
			$this->fFirstDayOfWeek = (int) $cdata['week']['firstDay'];
		}
		if(isset($cdata['week']['minDays'])) {
			$this->fMinimalDaysInFirstWeek = (int) $cdata['week']['minDays'];
		}
	}

	/**
	 * Recompute the time and update the status fields isTimeSet
	 * and areFieldsSet.  Callers should check isTimeSet and only
	 * call this method if isTimeSet is false.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	private function updateTime()
	{
		$this->computeTime();

		// If we are lenient, we need to recompute the fields to normalize
		// the values.  Also, if we haven't set all the fields yet (i.e.,
		// in a newly-created object), we need to fill in the fields. [LIU]
		if($this->isLenient() || ! $this->fAreAllFieldsSet) 
			$this->fAreFieldsInSync = false;

		$this->fIsTimeSet = true;
		$this->fAreFieldsVirtuallySet = false;
	}

	/**
	 * @var        int The Gregorian year, as computed by computeGregorianFields()
	 *                 and returned by getGregorianYear().
	 */
	private $fGregorianYear;

	/**
	 * @var        int The Gregorian month, as computed by 
	 *                 computeGregorianFields() and returned by 
	 *                 getGregorianMonth().
	 */
	private $fGregorianMonth;

	/**
	 * @var        int The Gregorian day of the year, as computed by
	 *                 computeGregorianFields() and returned by 
	 *                 getGregorianDayOfYear().
	 */
	private $fGregorianDayOfYear;

	/**
	 * @var        int The Gregorian day of the month, as computed by
	 *                 computeGregorianFields() and returned by 
	 *                 getGregorianDayOfMonth().
	 */
	private $fGregorianDayOfMonth;

	/* calculations */

	/**
	 * Compute the Gregorian calendar year, month, and day of month from
	 * the given Julian day.  These values are not stored in fields, but in
	 * member variables gregorianXxx.  Also compute the DAY_OF_WEEK and
	 * DOW_LOCAL fields.
	 * 
	 * @param      int The julian day
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	private function computeGregorianAndDOWFields($julianDay)
	{
		$this->computeGregorianFields($julianDay);

		// Compute day of week: JD 0 = Monday
		$dow = $this->julianDayToDayOfWeek($julianDay);
		$this->internalSet(AgaviDateDefinitions::DAY_OF_WEEK, $dow);

		// Calculate 1-based localized day of week
		$dowLocal = $dow - $this->getFirstDayOfWeek() + 1;
		if($dowLocal < 1) {
			$dowLocal += 7;
		}
		$this->internalSet(AgaviDateDefinitions::DOW_LOCAL, $dowLocal);
		$this->fFields[AgaviDateDefinitions::DOW_LOCAL] = $dowLocal;
	}

	/**
	 * Compute the Gregorian calendar year, month, and day of month from the
	 * Julian day.  These values are not stored in fields, but in member
	 * variables gregorianXxx.  They are used for time zone computations and by
	 * subclasses that are Gregorian derivatives.  Subclasses may call this
	 * method to perform a Gregorian calendar millis->fields computation.
	 * To perform a Gregorian calendar fields->millis computation, call
	 * computeGregorianMonthStart().
	 * @see #computeGregorianMonthStart
	 * 
	 * @param      int The julian day
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	private function computeGregorianFields($julianDay)
	{
		$gregorianDayOfWeekUnused = 0;
		AgaviCalendarGrego::dayToFields($julianDay - AgaviDateDefinitions::EPOCH_START_AS_JULIAN_DAY, $this->fGregorianYear, $this->fGregorianMonth, $this->fGregorianDayOfMonth, $gregorianDayOfWeekUnused, $this->fGregorianDayOfYear);
	}

	/**
	 * Compute the fields WEEK_OF_YEAR, YEAR_WOY, WEEK_OF_MONTH,
	 * DAY_OF_WEEK_IN_MONTH, and DOW_LOCAL from EXTENDED_YEAR, YEAR,
	 * DAY_OF_WEEK, and DAY_OF_YEAR.  The latter fields are computed by the
	 * subclass based on the calendar system.
	 *
	 * <p>The YEAR_WOY field is computed simplistically.  It is equal to YEAR
	 * most of the time, but at the year boundary it may be adjusted to YEAR-1
	 * or YEAR+1 to reflect the overlap of a week into an adjacent year.  In
	 * this case, a simple increment or decrement is performed on YEAR, even
	 * though this may yield an invalid YEAR value.  For instance, if the YEAR
	 * is part of a calendar system with an N-year cycle field CYCLE, then
	 * incrementing the YEAR may involve incrementing CYCLE and setting YEAR
	 * back to 0 or 1.  This is not handled by this code, and in fact cannot be
	 * simply handled without having subclasses define an entire parallel set of
	 * fields for fields larger than or equal to a year.  This additional
	 * complexity is not warranted, since the intention of the YEAR_WOY field is
	 * to support ISO 8601 notation, so it will typically be used with a
	 * proleptic Gregorian calendar, which has no field larger than a year.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	private function computeWeekFields()
	{
		$eyear = $this->fFields[AgaviDateDefinitions::EXTENDED_YEAR];
		$year = $this->fFields[AgaviDateDefinitions::YEAR];
		$dayOfWeek = $this->fFields[AgaviDateDefinitions::DAY_OF_WEEK];
		$dayOfYear = $this->fFields[AgaviDateDefinitions::DAY_OF_YEAR];

		// WEEK_OF_YEAR start
		// Compute the week of the year.  For the Gregorian calendar, valid week
		// numbers run from 1 to 52 or 53, depending on the year, the first day
		// of the week, and the minimal days in the first week.  For other
		// calendars, the valid range may be different -- it depends on the year
		// length.  Days at the start of the year may fall into the last week of
		// the previous year; days at the end of the year may fall into the
		// first week of the next year.  ASSUME that the year length is less than
		// 7000 days.
		$yearOfWeekOfYear = $year;
		$relDow = ($dayOfWeek + 7 - $this->getFirstDayOfWeek()) % 7; // 0..6
		$relDowJan1 = ($dayOfWeek - $dayOfYear + 7001 - $this->getFirstDayOfWeek()) % 7; // 0..6
		$woy = (int) (($dayOfYear - 1 + $relDowJan1) / 7); // 0..53
		if((7 - $relDowJan1) >= $this->getMinimalDaysInFirstWeek()) {
			++$woy;
		}

		// Adjust for weeks at the year end that overlap into the previous or
		// next calendar year.
		if($woy == 0) {
			// We are the last week of the previous year.
			// Check to see if we are in the last week; if so, we need
			// to handle the case in which we are the first week of the
			// next year.

			$prevDoy = $dayOfYear + $this->handleGetYearLength($eyear - 1);
			$woy = $this->weekNumber($prevDoy, $dayOfWeek);
			$yearOfWeekOfYear--;
		} else {
			$lastDoy = $this->handleGetYearLength($eyear);
			// Fast check: For it to be week 1 of the next year, the DOY
			// must be on or after L-5, where L is yearLength(), then it
			// cannot possibly be week 1 of the next year:
			//          L-5                  L
			// doy: 359 360 361 362 363 364 365 001
			// dow:      1   2   3   4   5   6   7
			if($dayOfYear >= ($lastDoy - 5)) {
				$lastRelDow = ($relDow + $lastDoy - $dayOfYear) % 7;
				if($lastRelDow < 0) {
					$lastRelDow += 7;
				}
				if(((6 - $lastRelDow) >= $this->getMinimalDaysInFirstWeek()) &&
						(($dayOfYear + 7 - $relDow) > $lastDoy)) {
					$woy = 1;
					$yearOfWeekOfYear++;
				}
			}
		}
		$this->fFields[AgaviDateDefinitions::WEEK_OF_YEAR] = $woy;
		$this->fFields[AgaviDateDefinitions::YEAR_WOY] = $yearOfWeekOfYear;
		// WEEK_OF_YEAR end

		$dayOfMonth = $this->fFields[AgaviDateDefinitions::DAY_OF_MONTH];
		$this->fFields[AgaviDateDefinitions::WEEK_OF_MONTH] = $this->weekNumber($dayOfMonth, $dayOfWeek);
		$this->fFields[AgaviDateDefinitions::DAY_OF_WEEK_IN_MONTH] = intval(($dayOfMonth-1) / 7) + 1;

	}

	/**
	 * Ensure that each field is within its valid range by calling {@link
	 * #validateField(int, int&)} on each field that has been set.  This method
	 * should only be called if this calendar is not lenient.
	 * @see #isLenient
	 * @see #validateField(int, int&)
	 * @internal
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	private function validateFields()
	{
		for($field = 0; $field < AgaviDateDefinitions::FIELD_COUNT; ++$field) {
			if($this->_isSet($field)) {
				$this->validateField($field);
			}
		}
	}

	/**
	 * Validate a single field of this calendar.  Subclasses should
	 * override this method to validate any calendar-specific fields.
	 * Generic fields can be handled by
	 * <code>Calendar.validateField()</code>.
	 * @see #validateField(int, int, int, int&)
	 * @internal
	 * 
	 * @param      string The field
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	private function validateField($field)
	{
		switch($field) {
			case AgaviDateDefinitions::DAY_OF_MONTH:
				$y = $this->handleGetExtendedYear();
				$this->validateField1($field, 1, $this->handleGetMonthLength($y, $this->internalGet(AgaviDateDefinitions::MONTH)));
				break;
			case AgaviDateDefinitions::DAY_OF_YEAR:
				$y = $this->handleGetExtendedYear();
				$this->validateField1($field, 1, $this->handleGetYearLength($y));
				break;
			case AgaviDateDefinitions::DAY_OF_WEEK_IN_MONTH:
				if($this->internalGet($field) == 0) {
					throw new InvalidArgumentException('DAY_OF_WEEK_IN_MONTH cannot be zero');
					return;
				}
				$this->validateField1($field, $this->getMinimum($field), $this->getMaximum($field));
				break;
			default:
				$this->validateField1($field, $this->getMinimum($field), $this->getMaximum($field));
				break;
		}
	}

	/**
	 * Validate a single field of this calendar given its minimum and
	 * maximum allowed value.  If the field is out of range,
	 * <code>U_ILLEGAL_ARGUMENT_ERROR</code> will be set.  Subclasses may
	 * use this method in their implementation of {@link
	 * #validateField(int, int&)}.
	 * @internal
	 * 
	 * @param      string 
	 * @param      int    
	 * @param      int    
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	private function validateField1($field, $min, $max)
	{
		$value = $this->fFields[$field];
		if($value < $min || $value > $max) {
			throw new InvalidArgumentException('Illegal argument error. Field: ' . $field . '. Value ' . $value . ' is not within ' . $min . ' and ' . $max);
		}
	}

	/**
	 * Convert a quasi Julian date to the day of the week. The Julian date used here is
	 * not a true Julian date, since it is measured from midnight, not noon. Return
	 * value is one-based.
	 *
	 * @param      double The given Julian date number.
	 * 
	 * @return     int    Day number from 1..7 (SUN..SAT).
	 * 
	 * @internal
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	protected function julianDayToDayOfWeek($julian)
	{
		// If julian is negative, then julian%7 will be negative, so we adjust
		// accordingly.  We add 1 because Julian day 0 is Monday.

		$dayOfWeek = fmod($julian + 1, 7);

		$result = ($dayOfWeek + (($dayOfWeek < 0) ? (7 + AgaviDateDefinitions::SUNDAY) : AgaviDateDefinitions::SUNDAY));
		return $result;
	}

	/**
	 * @var        string
	 */
	private $validLocale;

	/**
	 * @var        string
	 */
	private $actualLocale;

	/**
	 * @internal
	 * @return     bool if this calendar has a default century (i.e. 03 -> 2003)
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	public abstract function haveDefaultCentury();

	/**
	 * @internal
	 * @return     float the start of the default century, as a UDate
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	public abstract function defaultCenturyStart();

	/**
	 * @internal
	 * @return     int the beginning year of the default century, as a year
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	public abstract function defaultCenturyStartYear();

	/**
	 * Returns the type of the implementing calendar.
	 *
	 * @return     string The type of this calendar (gegorian, ...)
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public abstract function getType();
}

?>
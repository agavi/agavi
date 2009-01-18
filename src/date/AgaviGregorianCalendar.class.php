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
class AgaviGregorianCalendar extends AgaviCalendar
{
	const CUTOVER_JULIAN_DAY = 2299161;
	//const kPapalCutover = (2299161.0 - kEpochStartAsJulianDay) * AgaviDateDefinitions::MILLIS_PER_DAY;
	const PAPAL_CUTOVER      = -12219292800000.0;

	/**
	 * Constructor.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	public function __construct()
	{
		$this->initVariables();

		$arguments = func_get_args();

		$fName = AgaviToolkit::overloadHelper(
			array(
				array(
					'name' => 'constructorO',
					'parameters' => array('object')
				),
				array(
					'name' => 'constructorOO',
					'parameters' => array('object', 'object')
					),
				array(
					'name' => 'constructorOIII',
					'parameters' => array('object', 'int', 'int', 'int')
				),
				array(
					'name' => 'constructorOIIIII',
					'parameters' => array('object', 'int', 'int', 'int', 'int', 'int')
				),
				array(
					'name' => 'constructorOIIIIII',
					'parameters' => array('object', 'int', 'int', 'int', 'int', 'int', 'int')
				),
			),
			$arguments
		);
		call_user_func_array(array($this, $fName), $arguments);
	}

	/**
	 * Initialize all variables to default values.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	protected function initVariables()
	{
		parent::initVariables();

		$this->fGregorianCutover = self::PAPAL_CUTOVER;
		$this->fCutoverJulianDay = self::CUTOVER_JULIAN_DAY;
		$this->fNormalizedGregorianCutover = $this->fGregorianCutover;
		$this->fGregorianCutoverYear = 1582;
		$this->fIsGregorian = true;
		$this->fInvertGregorian = false;
	}

	protected function constructorO($zoneOrLocale)
	{
		$zone = null;
		$locale = null;

		if($zoneOrLocale instanceof AgaviTimeZone) {
			$this->translationManager = $zoneOrLocale->getTranslationManager();
			$zone = $zoneOrLocale;
			$locale = $this->translationManager->getCurrentLocale();
		} elseif($zoneOrLocale instanceof AgaviLocale) {
			$this->translationManager = $zoneOrLocale->getTranslationManager();
			$zone = $this->translationManager->getCurrentTimeZone();
			$locale = $zoneOrLocale;
		} elseif($zoneOrLocale instanceof AgaviTranslationManager) {
			$this->translationManager = $zoneOrLocale;
			$zone = $this->translationManager->getCurrentTimeZone();
			$locale = $this->translationManager->getCurrentLocale();
		} else {
			throw new InvalidArgumentException('Object of type ' . get_class($zoneOrLocale) . ' was not expected');
		}
		parent::constructorOO($zone, $locale);

		$this->setTimeInMillis(self::getNow());
		$this->set(AgaviDateDefinitions::ERA, self::AD);
	}

	protected function constructorOO(AgaviTimeZone $zone, AgaviLocale $locale)
	{
		parent::constructorOO($zone, $locale);
		$this->setTimeInMillis(self::getNow());
		$this->set(AgaviDateDefinitions::ERA, self::AD);
	}

	protected function constructorOIII($tm, $year, $month, $date)
	{
		parent::constructorOO($tm->getCurrentTimeZone(), $tm->getCurrentLocale());
		$this->set(AgaviDateDefinitions::ERA, self::AD);
		$this->set(AgaviDateDefinitions::YEAR, $year);
		$this->set(AgaviDateDefinitions::MONTH, $month);
		$this->set(AgaviDateDefinitions::DATE, $date);
	}

	protected function constructorOIIIII($tm, $year, $month, $date, $hour, $minute)
	{
		parent::constructorOO($tm->getCurrentTimeZone(), $tm->getCurrentLocale());
		$this->set(AgaviDateDefinitions::ERA, self::AD);
		$this->set(AgaviDateDefinitions::YEAR, $year);
		$this->set(AgaviDateDefinitions::MONTH, $month);
		$this->set(AgaviDateDefinitions::DATE, $date);
		$this->set(AgaviDateDefinitions::HOUR_OF_DAY, $hour);
		$this->set(AgaviDateDefinitions::MINUTE, $minute);
	}

	protected function constructorOIIIIII($tm, $year, $month, $date, $hour, $minute, $second)
	{
		parent::constructorOO($tm->getCurrentTimeZone(), $tm->getCurrentLocale());
		$this->set(AgaviDateDefinitions::ERA, self::AD);
		$this->set(AgaviDateDefinitions::YEAR, $year);
		$this->set(AgaviDateDefinitions::MONTH, $month);
		$this->set(AgaviDateDefinitions::DATE, $date);
		$this->set(AgaviDateDefinitions::HOUR_OF_DAY, $hour);
		$this->set(AgaviDateDefinitions::MINUTE, $minute);
		$this->set(AgaviDateDefinitions::SECOND, $second);
	}

	/**
	 * Useful constants for GregorianCalendar and TimeZone.
	 */
	 const BC = 0;
	 const AD = 1;

	/**
	 * Sets the GregorianCalendar change date. This is the point when the switch 
	 * from Julian dates to Gregorian dates occurred. Default is 00:00:00 local 
	 * time, October 15, 1582. Previous to this time and date will be Julian 
	 * dates.
	 *
	 * @param      float The given Gregorian cutover date.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	public function setGregorianChange($date)
	{
		$this->fGregorianCutover = $date;

		// Precompute two internal variables which we use to do the actual
		// cutover computations.  These are the normalized cutover, which is the
		// midnight at or before the cutover, and the cutover year.  The
		// normalized cutover is in pure date milliseconds; it contains no time
		// of day or timezone component, and it used to compare against other
		// pure date values.
		$cutoverDay = (int) floor($this->fGregorianCutover / AgaviDateDefinitions::MILLIS_PER_DAY);
		$this->fNormalizedGregorianCutover = $cutoverDay * AgaviDateDefinitions::MILLIS_PER_DAY;

		// Handle the rare case of numeric overflow.  If the user specifies a
		// change of UDate(Long.MIN_VALUE), in order to get a pure Gregorian
		// calendar, then the epoch day is -106751991168, which when multiplied
		// by ONE_DAY gives 9223372036794351616 -- the negative value is too
		// large for 64 bits, and overflows into a positive value.  We correct
		// this by using the next day, which for all intents is semantically
		// equivalent.
		if($cutoverDay < 0 && $this->fNormalizedGregorianCutover > 0) {
			$this->fNormalizedGregorianCutover = ($cutoverDay + 1) * AgaviDateDefinitions::MILLIS_PER_DAY;
		}

		// Normalize the year so BC values are represented as 0 and negative
		// values.
		$cal = new AgaviGregorianCalendar($this->getTimeZone());

		$cal->setTime($date);
		$this->fGregorianCutoverYear = $cal->get(AgaviDateDefinitions::YEAR);
		if($cal->get(AgaviDateDefinitions::ERA) == self::BC) 
			$this->fGregorianCutoverYear = 1 - $this->fGregorianCutoverYear;
		$this->fCutoverJulianDay = $cutoverDay;
	}

	/**
	 * Gets the Gregorian Calendar change date. This is the point when the switch
	 * from Julian dates to Gregorian dates occurred. Default is 00:00:00 local 
	 * time, October 15, 1582. Previous to this time and date will be Julian 
	 * dates.
	 *
	 * @return     float The Gregorian cutover time for this calendar.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	public function getGregorianChange()
	{
		return $this->fGregorianCutover;
	}

	/**
	 * Return true if the given year is a leap year. Determination of whether a 
	 * year is a leap year is actually very complicated. We do something crude 
	 * and mostly correct here, but for a real determination you need a lot of 
	 * contextual information. For example, in Sweden, the change from Julian to 
	 * Gregorian happened in a complex way resulting in missed leap years and 
	 * double leap years between 1700 and 1753. Another example is that after the 
	 * start of the Julian calendar in 45 B.C., the leap years did not regularize
	 * until 8 A.D. This method ignores these quirks, and pays attention only to 
	 * the Julian onset date and the Gregorian cutover (which can be changed).
	 *
	 * @param      int  The given year.
	 *
	 * @return     bool if the given year is a leap year; false otherwise.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	public function isLeapYear($year)
	{
		// MSVC complains bitterly if we try to use Grego::isLeapYear here
		// NOTE: year&0x3 == year%4
		return ($year >= $this->fGregorianCutoverYear
						? AgaviCalendarGrego::isLeapYear($year) // Gregorian
						: (($year & 0x3) == 0)); // Julian
	}

	/**
	 * Returns true if the given Calendar object is equivalent to this
	 * one.  Calendar override.
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
		// Calendar override.
		return AgaviCalendar::isEquivalentTo($other) && $this->getGregorianChange() == $other->getGregorianChange();
	}

	/**
	 * @see        AgaviCalendar::getActualMinimum
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	public function getActualMinimum($field)
	{
		return $this->getMinimum($field);
	}

	/**
	 * @see        AgaviCalendar::getActualMaximum
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	public function getActualMaximum($field)
	{
		/* It is a known limitation that the code here (and in getActualMinimum)
		* won't behave properly at the extreme limits of GregorianCalendar's
		* representable range (except for the code that handles the YEAR
		* field).  That's because the ends of the representable range are at
		* odd spots in the year.  For calendars with the default Gregorian
		* cutover, these limits are Sun Dec 02 16:47:04 GMT 292269055 BC to Sun
		* Aug 17 07:12:55 GMT 292278994 AD, somewhat different for non-GMT
		* zones.  As a result, if the calendar is set to Aug 1 292278994 AD,
		* the actual maximum of DAY_OF_MONTH is 17, not 30.  If the date is Mar
		* 31 in that year, the actual maximum month might be Jul, whereas is
		* the date is Mar 15, the actual maximum might be Aug -- depending on
		* the precise semantics that are desired.  Similar considerations
		* affect all fields.  Nonetheless, this effect is sufficiently arcane
		* that we permit it, rather than complicating the code to handle such
		* intricacies. - liu 8/20/98

		* UPDATE: No longer true, since we have pulled in the limit values on
		* the year. - Liu 11/6/00 */

		switch($field) {

			case AgaviDateDefinitions::YEAR:
				/* The year computation is no different, in principle, from the
				* others, however, the range of possible maxima is large.  In
				* addition, the way we know we've exceeded the range is different.
				* For these reasons, we use the special case code below to handle
				* this field.
				*
				* The actual maxima for YEAR depend on the type of calendar:
				*
				*     Gregorian = May 17, 292275056 BC - Aug 17, 292278994 AD
				*     Julian    = Dec  2, 292269055 BC - Jan  3, 292272993 AD
				*     Hybrid    = Dec  2, 292269055 BC - Aug 17, 292278994 AD
				*
				* We know we've exceeded the maximum when either the month, date,
				* time, or era changes in response to setting the year.  We don't
				* check for month, date, and time here because the year and era are
				* sufficient to detect an invalid year setting.  NOTE: If code is
				* added to check the month and date in the future for some reason,
				* Feb 29 must be allowed to shift to Mar 1 when setting the year.
				*/
				{
					$cal = clone $this;

					$cal->setLenient(true);

					$era = $cal->get(AgaviDateDefinitions::ERA);
					$d = $cal->getTime();

					/* Perform a binary search, with the invariant that lowGood is a
					* valid year, and highBad is an out of range year.
					*/
					$lowGood = self::$kGregorianCalendarLimits[AgaviDateDefinitions::YEAR][1];
					$highBad = self::$kGregorianCalendarLimits[AgaviDateDefinitions::YEAR][2] + 1;
					while((lowGood + 1) < highBad) {
						$y = (int)(($lowGood + $highBad) / 2);
						$cal->set(AgaviDateDefinitions::YEAR, $y);
						if($cal->get(AgaviDateDefinitions::YEAR) == $y && $cal->get(AgaviDateDefinitions::ERA) == $era) {
							$lowGood = $y;
						} else {
							$highBad = $y;
							$cal->setTime($d); // Restore original fields
						}
					}

					return $lowGood;
				}

			default:
				return parent::getActualMaximum($field);
		}
	}

	/**
	 * @see        AgaviCalendar::inDaylightTime
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	public function inDaylightTime()
	{
		if(!$this->getTimeZone()->useDaylightTime()) 
			return false;

		// Force an update of the state of the Calendar.
		$this->complete(); // cast away const

		return ($this->internalGet(AgaviDateDefinitions::DST_OFFSET) != 0);
	}

	/**
	 * Return the ERA.  We need a special method for this because the
	 * default ERA is AD, but a zero (unset) ERA is BC.
	 * 
	 * @return     int The ERA.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	protected function internalGetEra()
	{
		return $this->_isSet(AgaviDateDefinitions::ERA) ? $this->internalGet(AgaviDateDefinitions::ERA) : self::AD;
	}

	/**
	 * @see        AgaviCalendar::handleComputeMonthStart
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	protected function handleComputeMonthStart($eyear, $month,$useMonth)
	{
		// If the month is out of range, adjust it into range, and
		// modify the extended year value accordingly.
		if($month < 0 || $month > 11) {
			$eyear += AgaviToolkit::floorDivide($month, 12, $month);
		}

		$isLeap = ($eyear % 4 == 0);
		$y = $eyear - 1;
		$julianDay = 365 * $y + floor($y / 4) + (AgaviDateDefinitions::JAN_1_1_JULIAN_DAY - 3);

		$this->fIsGregorian = ($eyear >= $this->fGregorianCutoverYear);

		if($this->fInvertGregorian) {
			$this->fIsGregorian = !$this->fIsGregorian;
		}
		if($this->fIsGregorian) {
			$isLeap = $isLeap && (($eyear % 100 != 0) || ($eyear % 400 == 0));
			// Add 2 because Gregorian calendar starts 2 days after
			// Julian calendar
			$gregShift = AgaviCalendarGrego::gregorianShift($eyear);

			$julianDay += $gregShift;
		}

		// At this point julianDay indicates the day BEFORE the first
		// day of January 1, <eyear> of either the Julian or Gregorian
		// calendar.

		if($month != 0) {
			$julianDay += $isLeap ? self::$kLeapNumDays[$month] : self::$kNumDays[$month];
		}

		return $julianDay;
	}

	/**
	 * @see        AgaviCalendar::handleComputeJulianDay
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	protected function handleComputeJulianDay($bestField)
	{
		$this->fInvertGregorian = false;

		$jd = parent::handleComputeJulianDay($bestField);

		if(($bestField == AgaviDateDefinitions::WEEK_OF_YEAR) &&  // if we are doing WOY calculations, we are counting relative to Jan 1 *julian*
				($this->internalGet(AgaviDateDefinitions::EXTENDED_YEAR) == $this->fGregorianCutoverYear) && $jd >= $this->fCutoverJulianDay) { 
			$this->fInvertGregorian = true;  // So that the Julian Jan 1 will be used in handleComputeMonthStart
			return parent::handleComputeJulianDay($bestField);
		}

		// The following check handles portions of the cutover year BEFORE the
		// cutover itself happens.
		//if((fIsGregorian==true) != (jd >= fCutoverJulianDay)) {  /*  cutoverJulianDay)) { */
		if(($this->fIsGregorian == true) != ($jd >= $this->fCutoverJulianDay)) {  /*  cutoverJulianDay)) { */
			$this->fInvertGregorian = true;
			$jd = parent::handleComputeJulianDay($bestField);
		} else {
		}

		if($this->fIsGregorian && ($this->internalGet(AgaviDateDefinitions::EXTENDED_YEAR) == $this->fGregorianCutoverYear)) {
			$gregShift = AgaviCalendarGrego::gregorianShift($this->internalGet(AgaviDateDefinitions::EXTENDED_YEAR));
			if($bestField == AgaviDateDefinitions::DAY_OF_YEAR) {
				$jd -= $gregShift;
			} elseif($bestField == AgaviDateDefinitions::WEEK_OF_MONTH) {
				$weekShift = 14;
				$jd += $weekShift; // shift by weeks for week based fields.
			}
		}

		return $jd;
	}

	/**
	 * @see        AgaviCalendar::handleGetMonthLength
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	protected function handleGetMonthLength($extendedYear, $month)
	{
		if(!isset(self::$kLeapMonthLength[$month])) {
			return null;
		}
		return $this->isLeapYear($extendedYear) ? self::$kLeapMonthLength[$month] : self::$kMonthLength[$month];
	}

	/**
	 * @see        AgaviCalendar::handleGetYearLength
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	protected function handleGetYearLength($eyear)
	{
		return $this->isLeapYear($eyear) ? 366 : 365;
	}

	/**
	 * Return the length of the given month.
	 * 
	 * @param      int The given month.
	 * 
	 * @return     int The length of the given month.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	protected function monthLength($month)
	{
		$year = $this->internalGet(AgaviDateDefinitions::EXTENDED_YEAR);
		return $this->handleGetMonthLength($year, $month);
	}

	/**
	 * Return the length of the month according to the given year.
	 * 
	 * @param      int The given month.
	 * @param      int The given year.
	 * 
	 * @return     int The length of the month.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	protected function monthLength1($month, $year)
	{
		return $this->isLeapYear($year) ? self::$kLeapMonthLength[$month] : self::$kMonthLength[$month];
	}
	
	/**
	 * Return the length of the given year.
	 * 
	 * @param      int The given year.
	 * @return     int The length of the given year.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	protected function yearLength1($year)
	{
		return $this->isLeapYear($year) ? 366 : 365;
	}
	
	/**
	 * Return the length of the year field.
	 * 
	 * @return     int The length of the year field
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	protected function yearLength()
	{
		return $this->isLeapYear($this->internalGet(AgaviDateDefinitions::YEAR)) ? 366 : 365;
	}

	/**
	 * After adjustments such as add(MONTH), add(YEAR), we don't want the
	 * month to jump around.  E.g., we don't want Jan 31 + 1 month to go to Mar
	 * 3, we want it to go to Feb 28.  Adjustments which might run into this
	 * problem call this method to retain the proper month.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	protected function pinDayOfMonth()
	{
		$monthLen = $this->monthLength(internalGet(AgaviDateDefinitions::MONTH));
		$dom = $this->internalGet(AgaviDateDefinitions::DATE);
		if($dom > $monthLen) 
			$this->set(AgaviDateDefinitions::DATE, $monthLen);
	}

	/**
	 * Return the day number with respect to the epoch. 
	 * January 1, 1970 (Gregorian) is day zero.
	 * 
	 * @return     float the day number with respect to the epoch.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	protected function getEpochDay()
	{
		$this->complete();
		// Divide by 1000 (convert to seconds) in order to prevent overflow when
		// dealing with UDate(Long.MIN_VALUE) and UDate(Long.MAX_VALUE).
		$wallSec = $this->internalGetTime() / 1000.0 + ($this->internalGet(AgaviDateDefinitions::ZONE_OFFSET) + $this->internalGet(AgaviDateDefinitions::DST_OFFSET)) / 1000.0;

		return floor($wallSec / (AgaviDateDefinitions::MILLIS_PER_DAY / 1000.0));
	}

	/**
	 * @see        AgaviCalendar::handleGetLimit
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	protected function handleGetLimit($field, $limitType)
	{
		return self::$kGregorianCalendarLimits[$field][$limitType];
	}

	/**
	 * @see        AgaviCalendar::handleGetExtendedYear
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	protected function handleGetExtendedYear()
	{
		$year = AgaviDateDefinitions::EPOCH_YEAR;

		switch($this->resolveFields(self::$kYearPrecedence)) {
			case AgaviDateDefinitions::EXTENDED_YEAR:
				$year = $this->internalGet(AgaviDateDefinitions::EXTENDED_YEAR, AgaviDateDefinitions::EPOCH_YEAR);
				break;

			case AgaviDateDefinitions::YEAR:
				{
					// The year defaults to the epoch start, the era to AD
					$era = $this->internalGet(AgaviDateDefinitions::ERA, self::AD);
					if($era == self::BC) {
						$year = 1 - $this->internalGet(AgaviDateDefinitions::YEAR, 1); // Convert to extended year
					} else {
						$year = $this->internalGet(AgaviDateDefinitions::YEAR, AgaviDateDefinitions::EPOCH_YEAR);
					}
				}
				break;

			case AgaviDateDefinitions::YEAR_WOY:
				$year = $this->handleGetExtendedYearFromWeekFields($this->internalGet(AgaviDateDefinitions::YEAR_WOY), $this->internalGet(AgaviDateDefinitions::WEEK_OF_YEAR));
				break;

			default:
				$year = AgaviDateDefinitions::EPOCH_YEAR;
		}
		return $year;
	}

	/**
	 * @see        AgaviCalendar::handleGetExtendedYearFromWeekFields
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	protected function handleGetExtendedYearFromWeekFields($yearWoy, $woy)
	{
		// convert year to extended form
		$era = $this->internalGet(AgaviDateDefinitions::ERA, self::AD);
		if($era == self::BC) {
			$yearWoy = 1 - $yearWoy;
		}
		return parent::handleGetExtendedYearFromWeekFields($yearWoy, $woy);
	}

	/**
	 * @see        AgaviCalendar::handleComputeFields
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	protected function handleComputeFields($julianDay)
	{
		if($julianDay >= $this->fCutoverJulianDay) {
			$month = $this->getGregorianMonth();
			$dayOfMonth = $this->getGregorianDayOfMonth();
			$dayOfYear = $this->getGregorianDayOfYear();
			$eyear = $this->getGregorianYear();
		} else {
			// The Julian epoch day (not the same as Julian Day)
			// is zero on Saturday December 30, 0 (Gregorian).
			$julianEpochDay = $julianDay - (AgaviDateDefinitions::JAN_1_1_JULIAN_DAY - 2);
			$eyear = (int) floor((4 * $julianEpochDay + 1464) / 1461);

			// Compute the Julian calendar day number for January 1, eyear
			$january1 = 365 * ($eyear - 1) + floor(($eyear -1 ) / 4);
			$dayOfYear = ($julianEpochDay - $january1); // 0-based

			// Julian leap years occurred historically every 4 years starting
			// with 8 AD.  Before 8 AD the spacing is irregular; every 3 years
			// from 45 BC to 9 BC, and then none until 8 AD.  However, we don't
			// implement this historical detail; instead, we implement the
			// computatinally cleaner proleptic calendar, which assumes
			// consistent 4-year cycles throughout time.
			$isLeap = (($eyear & 0x3) == 0); // equiv. to (eyear%4 == 0)

			// Common Julian/Gregorian calculation
			$correction = 0;
			$march1 = $isLeap ? 60 : 59; // zero-based DOY for March 1
			if($dayOfYear >= $march1) {
				$correction = $isLeap ? 1 : 2;
			}
			$month = (int) ((12 * ($dayOfYear + $correction) + 6) / 367); // zero-based month
			$dayOfMonth = $dayOfYear - ($isLeap ? self::$kLeapNumDays[$month] : self::$kNumDays[$month]) + 1; // one-based DOM
			++$dayOfYear;
		}

		// [j81] if we are after the cutover in its year, shift the day of the year
		if(($eyear == $this->fGregorianCutoverYear) && ($julianDay >= $this->fCutoverJulianDay)) {
			//from handleComputeMonthStart
			$gregShift = AgaviCalendarGrego::gregorianShift($eyear);
			$dayOfYear += $gregShift;
		}

		$this->internalSet(AgaviDateDefinitions::MONTH, $month);
		$this->internalSet(AgaviDateDefinitions::DAY_OF_MONTH, $dayOfMonth);
		$this->internalSet(AgaviDateDefinitions::DAY_OF_YEAR, $dayOfYear);
		$this->internalSet(AgaviDateDefinitions::EXTENDED_YEAR, $eyear);
		$era = self::AD;
		if($eyear < 1) {
			$era = self::BC;
			$eyear = 1 - $eyear;
		}
		$this->internalSet(AgaviDateDefinitions::ERA, $era);
		$this->internalSet(AgaviDateDefinitions::YEAR, $eyear);
	}

	/**
	 * Compute the julian day number of the given year.
	 * 
	 * @param      bool   If true, using Gregorian calendar, otherwise using 
	 *                    Julian calendar.
	 * @param      int    The given year.
	 * @param      bool   True if the year is a leap year.
	 * 
	 * @return     double 
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	private static function computeJulianDayOfYear($isGregorian, $year, &$isLeap)
	{
		$isLeap = ($year % 4 == 0);
		$y = $year - 1;
		$julianDay = 365.0 * $y + floor($y / 4) + (AgaviDateDefinitions::JAN_1_1_JULIAN_DAY - 3);

		if($isGregorian) {
			$isLeap = $isLeap && (($year % 100 != 0) || ($year % 400 == 0));
			// Add 2 because Gregorian calendar starts 2 days after Julian calendar
			$julianDay += AgaviCalendarGrego::gregorianShift($year);
		}

		return $julianDay;
	}
	
	/**
	 * Validates the values of the set time fields.  True if they're all valid.
	 * 
	 * @return     bool True if the set time fields are all valid.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	private function validateFields()
	{
		for($field = 0; $field < AgaviDateDefinitions::FIELD_COUNT; ++$field) {
			// Ignore DATE and DAY_OF_YEAR which are handled below
			if($field != AgaviDateDefinitions::DATE &&
					$field != AgaviDateDefinitions::DAY_OF_YEAR &&
					$this->_isSet($field) &&
					! $this->boundsCheck($this->internalGet($field), $field)) {
				return false;
			}
		}

		// Values differ in Least-Maximum and Maximum should be handled
		// specially.
		if($this->_isSet(AgaviDateDefinitions::DATE)) {
			$date = $this->internalGet(AgaviDateDefinitions::DATE);
			if($date < $this->getMinimum(AgaviDateDefinitions::DATE) ||
					$date > $this->monthLength($this->internalGet(AgaviDateDefinitions::MONTH))) {
				return false;
			}
		}

		if($this->_isSet(AgaviDateDefinitions::DAY_OF_YEAR)) {
			$days = $this->internalGet(AgaviDateDefinitions::DAY_OF_YEAR);
			if($days < 1 || $days > $this->yearLength()) {
				return false;
			}
		}

		// Handle DAY_OF_WEEK_IN_MONTH, which must not have the value zero.
		// We've checked against minimum and maximum above already.
		if($this->_isSet(AgaviDateDefinitions::DAY_OF_WEEK_IN_MONTH) &&
			0 == $this->internalGet(AgaviDateDefinitions::DAY_OF_WEEK_IN_MONTH)) {
			return false;
		}

		return true;
	}

	/**
	 * Validates the value of the given time field.  True if it's valid.
	 * 
	 * @param      int  
	 * @param      int  
	 * 
	 * @return     bool 
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	private function boundsCheck($value, $field)
	{
		return $value >= $this->getMinimum($field) && $value <= $this->getMaximum(field);
	}

	/**
	 * Return the pseudo-time-stamp for two fields, given their
	 * individual pseudo-time-stamps.  If either of the fields
	 * is unset, then the aggregate is unset.  Otherwise, the
	 * aggregate is the later of the two stamps.
	 * 
	 * @param      int One given field.
	 * @param      int Another given field.
	 * @return     int The pseudo-time-stamp for two fields.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	private function aggregateStamp($stamp_a, $stamp_b)
	{
		return ((($stamp_a != AgaviCalendar::kUnset && $stamp_b != AgaviCalendar::kUnset)
			? max($stamp_a, $stamp_b)
			: AgaviCalendar::kUnset
		));
	}

	/**
	 * @var        float The point at which the Gregorian calendar rules are used,
	 *                   measured in milliseconds from the standard epoch. Default
	 *                   is October 15, 1582 (Gregorian) 00:00:00 UTC, that is, 
	 *                   October 4, 1582 (Julian) is followed by October 15, 1582 
	 *                   (Gregorian).  This corresponds to Julian day number 
	 *                   2299161. This is measured from the standard epoch, not in
	 *                   Julian Days.
	 */
	private $fGregorianCutover;

	/**
	 * @var        int Julian day number of the Gregorian cutover
	 */
	private $fCutoverJulianDay;

	/**
	 * @var        float Midnight, local time (using this Calendar's TimeZone)
	 *                   at or before the gregorianCutover. This is a pure 
	 *                   date value with no time of day or timezone component.
	 */
	private $fNormalizedGregorianCutover;// = gregorianCutover;

	/**
	 * @var        int The year of the gregorianCutover, with 0 representing
	 *                 1 BC, -1 representing 2 BC, etc.
	 */
	private $fGregorianCutoverYear;// = 1582;

	/**
	 * @var        int The year of the gregorianCutover, with 0 representing
	 *                 1 BC, -1 representing 2 BC, etc.
	 */
	private $fGregorianCutoverJulianDay;// = 2299161;

	/**
	 * Converts time as milliseconds to Julian date. The Julian date used here is
	 * not a true Julian date, since it is measured from midnight, not noon.
	 *
	 * @param      float The given milliseconds.
	 * 
	 * @return     float The Julian date number.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	private static function millisToJulianDay($millis)
	{
		return AgaviDateDefinitions::EPOCH_START_AS_JULIAN_DAY + floor($millis / AgaviDateDefinitions::MILLIS_PER_DAY);
	}

	/**
	 * Converts Julian date to time as milliseconds. The Julian date used here is
	 * not a true Julian date, since it is measured from midnight, not noon.
	 *
	 * @param      float The given Julian date number.
	 * 
	 * @return     float Time as milliseconds.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	private static function julianDayToMillis($julian)
	{
		return (($julian - AgaviDateDefinitions::EPOCH_START_AS_JULIAN_DAY) * AgaviDateDefinitions::MILLIS_PER_DAY);
	}

	/**
	 * @var        bool Used by handleComputeJulianDay() and 
	 *                  handleComputeMonthStart(). Temporary field indicating 
	 *                  whether the calendar is currently Gregorian as opposed to
	 *                  Julian.
	 */
	private $fIsGregorian;

	/**
	 * @var        bool Used by handleComputeJulianDay() and 
	 *                  handleComputeMonthStart(). Temporary field indicating that
	 *                  the sense of the gregorian cutover should be inverted to 
	 *                  handle certain calculations on and around the cutover 
	 *                  date.
	 */
	private $fInvertGregorian;

	/**
	 * @see        AgaviCalendar::haveDefaultCentury
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	public function haveDefaultCentury()
	{
		return true;
	}

	/**
	 * @see        AgaviCalendar::defaultCenturyStart
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	public function defaultCenturyStart()
	{
		return $this->internalGetDefaultCenturyStart();
	}

	/**
	 * @see        AgaviCalendar::defaultCenturyStartYear
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	public function defaultCenturyStartYear()
	{
		return $this->internalGetDefaultCenturyStartYear();
	}

	/**
	 * @see        AgaviCalendar::getType
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getType()
	{
		return AgaviCalendar::GREGORIAN;
	}

	/**
	 * @var        float The system maintains a static default century start date.
	 *                   This is initialized the first time it is used.
	 *                   Before then, it is set to SYSTEM_DEFAULT_CENTURY to
	 *                   indicate an uninitialized state.  Once the system default
	 *                   century date and year are set, they do not change.
	 */
	private static $fgSystemDefaultCenturyStart;

	/**
	 * @var        int See documentation for systemDefaultCenturyStart.
	 */
	private static $fgSystemDefaultCenturyStartYear = -1;

	/**
	 * @var        int Default value that indicates the defaultCenturyStartYear is
	 *                 unitialized
	 */
	private static $fgSystemDefaultCenturyYear = -1;

	/**
	 * @var        float Default value that indicates the UDate of the beginning 
	 *                   of the system default century
	 */
	private static $fgSystemDefaultCentury;

	/**
	 * Returns the beginning date of the 100-year window that dates with 2-digit
	 * years are considered to fall within.
	 * 
	 * @return     float The beginning date of the 100-year window that dates
	 *                   with 2-digit years are considered to fall within.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	private function internalGetDefaultCenturyStart()
	{
		// lazy-evaluate systemDefaultCenturyStart
		$needsUpdate = false;;
		$needsUpdate = (self::$fgSystemDefaultCenturyStart == self::$fgSystemDefaultCentury);

		if($needsUpdate) {
			$this->initializeSystemDefaultCentury();
		}

		// use defaultCenturyStart unless it's the flag value;
		// then use systemDefaultCenturyStart

		return self::$fgSystemDefaultCenturyStart;
	}

	/**
	 * Returns the first year of the 100-year window that dates with 2-digit years
	 * are considered to fall within.
	 * 
	 * @return     int The first year of the 100-year window that dates with 
	 *                 2-digit years are considered to fall within.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	private function internalGetDefaultCenturyStartYear()
	{
		// lazy-evaluate systemDefaultCenturyStartYear
		$needsUpdate = (self::$fgSystemDefaultCenturyStart == self::$fgSystemDefaultCentury);

		if($needsUpdate) {
			$this->initializeSystemDefaultCentury();
		}

		// use defaultCenturyStart unless it's the flag value;
		// then use systemDefaultCenturyStartYear

		return self::$fgSystemDefaultCenturyStartYear;
	}

	/**
	 * Initializes the 100-year window that dates with 2-digit years are 
	 * considered to fall within so that its start date is 80 years before the 
	 * current time.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project
	 * @since      0.11.0
	 */
	private static function initializeSystemDefaultCentury()
	{
		// initialize systemDefaultCentury and systemDefaultCenturyYear based
		// on the current time.  They'll be set to 80 years before
		// the current time.
		// No point in locking as it should be idempotent.
		if(self::$fgSystemDefaultCenturyStart == self::$fgSystemDefaultCentury) {
			$calendar = new GregorianCalendar();
			$calendar->setTime(AgaviCalendar::getNow());
			$calendar->add(AgaviDateDefinitions::YEAR, -80);

			$newStart = $calendar->getTime();
			$newYear  = $calendar->get(AgaviDateDefinitions::YEAR);
			self::$fgSystemDefaultCenturyStart = $newStart;
			self::$fgSystemDefaultCenturyStartYear = $newYear;
		}
	}

	protected static $kNumDays         = array(0,  31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334); // 0-based, for day-in-year
	protected static $kLeapNumDays     = array(0,  31, 60, 91, 121, 152, 182, 213, 244, 274, 305, 335); // 0-based, for day-in-year
	protected static $kMonthLength     = array(31, 28, 31, 30,  31,  30,  31,  31,  30,  31,  30,  31); // 0-based
	protected static $kLeapMonthLength = array(31, 29, 31, 30,  31,  30,  31,  31,  30,  31,  30,  31); // 0-based
	protected static $kGregorianCalendarLimits = array(
		//     Minimum  Greatest   Least      Maximum
		//                Minimum   Maximum
		array(        0,        0,        1,        1 ), // ERA
		array(        1,        1,   140742,   144683 ), // YEAR
		array(        0,        0,       11,       11 ), // MONTH
		array(        1,        1,       52,       53 ), // WEEK_OF_YEAR
		array(        0,        0,        4,        6 ), // WEEK_OF_MONTH
		array(        1,        1,       28,       31 ), // DAY_OF_MONTH
		array(        1,        1,      365,      366 ), // DAY_OF_YEAR
		array(/*N/A*/-1,/*N/A*/-1,/*N/A*/-1,/*N/A*/-1 ), // DAY_OF_WEEK
		array(       -1,       -1,        4,        6 ), // DAY_OF_WEEK_IN_MONTH
		array(/*N/A*/-1,/*N/A*/-1,/*N/A*/-1,/*N/A*/-1 ), // AM_PM
		array(/*N/A*/-1,/*N/A*/-1,/*N/A*/-1,/*N/A*/-1 ), // HOUR
		array(/*N/A*/-1,/*N/A*/-1,/*N/A*/-1,/*N/A*/-1 ), // HOUR_OF_DAY
		array(/*N/A*/-1,/*N/A*/-1,/*N/A*/-1,/*N/A*/-1 ), // MINUTE
		array(/*N/A*/-1,/*N/A*/-1,/*N/A*/-1,/*N/A*/-1 ), // SECOND
		array(/*N/A*/-1,/*N/A*/-1,/*N/A*/-1,/*N/A*/-1 ), // MILLISECOND
		array(/*N/A*/-1,/*N/A*/-1,/*N/A*/-1,/*N/A*/-1 ), // ZONE_OFFSET
		array(/*N/A*/-1,/*N/A*/-1,/*N/A*/-1,/*N/A*/-1 ), // DST_OFFSET
		array(  -140742,  -140742,   140742,   144683 ), // YEAR_WOY
		array(/*N/A*/-1,/*N/A*/-1,/*N/A*/-1,/*N/A*/-1 ), // DOW_LOCAL
		array(  -140742,  -140742,   140742,   144683 ), // EXTENDED_YEAR
		array(/*N/A*/-1,/*N/A*/-1,/*N/A*/-1,/*N/A*/-1 ), // JULIAN_DAY
		array(/*N/A*/-1,/*N/A*/-1,/*N/A*/-1,/*N/A*/-1 ), // MILLISECONDS_IN_DAY
	);
}

?>
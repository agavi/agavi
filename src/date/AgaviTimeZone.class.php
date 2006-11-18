<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2003-2006 the Agavi Project.                                |
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
 * @author     The ICU Project ({@link http://icu.sourceforge.net})
 * @copyright  (c) Authors
 * @since      0.11.0
 *
 * @version    $Id$
 */
abstract class AgaviTimeZone
{
	/**
	 * The translation manager instance.
	 *
	 * @var        AgaviTranslationManager
	 */
	protected $translationManager = null;

	/**
	 * The id of this time zone.
	 *
	 * @var        string
	 */
	protected $id;

	/**
	 * @var        string The "resolved" id. This means if the original id pointed
	 *                    to a link timezone this will contain the id of the 
	 *                    timezone the link resolved to.
	 */
	protected $resolvedId = null;


	/**
	 * Returns the translation manager for this TimeZone.
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
	 * The GMT time zone has a raw offset of zero and does not use daylight
	 * savings time. This is a commonly used time zone.
	 * 
	 * @param      AgaviTranslationManager The translation manager
	 * 
	 * @return     AgaviTimeZone The GMT time zone.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project ({@link http://icu.sourceforge.net})
	 * @since      0.11.0
	 */
	public static function getGMT(AgaviTranslationManager $tm)
	{
		return new AgaviSimpleTimeZone($tm, 0, 'GMT');
	}



	/**
	 * TODO: document the overloads
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getOffset()
	{
		$arguments = func_get_args();
		$fName = AgaviToolkit::overloadHelper(array(
			array('name' => 'getOffsetIIIIII',
						'parameters' => array('int', 'int', 'int', 'int', 'int', 'int')),
			array('name' => 'getOffsetIIIIIII',
						'parameters' => array('int', 'int', 'int', 'int', 'int', 'int', 'int')),
			),
			$arguments
		);

		return call_user_func_array(array($this, $fName), $arguments);
	}

	/**
	 * Returns the time zone raw and GMT offset for the given moment
	 * in time.  Upon return, local-millis = GMT-millis + rawOffset +
	 * dstOffset.  All computations are performed in the proleptic
	 * Gregorian calendar.  The default implementation in the TimeZone
	 * class delegates to the 8-argument getOffset().
	 *
	 * @param      float Moment in time for which to return offsets, in units of 
	 *                   milliseconds from January 1, 1970 0:00 GMT, either GMT
	 *                   time or local wall time, depending on `local'.
	 * @param      bool  If true, `date' is local wall time; otherwise it
	 *                   is in GMT time.
	 * @param      int   Output parameter to receive the raw offset, that is, the
	 *                   offset not including DST adjustments
	 * @param      int   Output parameter to receive the DST offset, that is, the 
	 *                   offset to be added to `rawOffset' to obtain the total 
	 *                   offset between local and GMT time. If DST is not in 
	 *                   effect, this value is zero; otherwise it is a positive 
	 *                   value, typically one hour.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project ({@link http://icu.sourceforge.net})
	 * @since      0.11.0
	 */
	public function getOffsetRef($date, $local, &$rawOffset, &$dstOffset)
	{
		$rawOffset = $this->getRawOffset();

		// Convert to local wall millis if necessary
		if(!$local) {
			$date += $rawOffset; // now in local standard millis
		}

		// When local==FALSE, we might have to recompute. This loop is
		// executed once, unless a recomputation is required; then it is
		// executed twice.
		for($pass = 0; true; ++$pass) {
			$year = $month = $dom = $dow = 0;
			$day = floor($date / AgaviDateDefinitions::MILLIS_PER_DAY);
			$millis = (int) ($date - $day * AgaviDateDefinitions::MILLIS_PER_DAY);
			
			AgaviCalendarGrego::dayToFields($day, $year, $month, $dom, $dow);
			
			$dstOffset = $this->getOffsetIIIIIII(AgaviGregorianCalendar::AD, $year, $month, $dom, $dow, $millis, AgaviCalendarGrego::monthLength($year, $month)) - $rawOffset;

			// Recompute if local==FALSE, dstOffset!=0, and addition of
			// the dstOffset puts us in a different day.
			if($pass != 0 || $local || $dstOffset == 0) {
				break;
			}
			$date += $dstOffset;
			if(floor($date / AgaviDateDefinitions::MILLIS_PER_DAY) == $day) {
				break;
			}
		}
	}


	/**
	 * Sets the TimeZone's raw GMT offset (i.e., the number of milliseconds to 
	 * add to GMT to get local time, before taking daylight savings time into 
	 * account).
	 *
	 * @param      int The new raw GMT offset for this time zone.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project ({@link http://icu.sourceforge.net})
	 * @since      0.11.0
	 */
	public abstract function setRawOffset($offsetMillis);

	/**
	 * Returns the TimeZone's raw GMT offset (i.e., the number of milliseconds to 
	 * add to GMT to get local time, before taking daylight savings time into 
	 * account).
	 *
	 * @return     int The TimeZone's raw GMT offset.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project ({@link http://icu.sourceforge.net})
	 * @since      0.11.0
	 */
	public abstract function getRawOffset();

	/**
	 * Returns the TimeZone's ID.
	 *
	 * @return     string This TimeZone's ID.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project ({@link http://icu.sourceforge.net})
	 * @since      0.11.0
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Sets the TimeZone's ID to the specified value.  This doesn't affect any 
	 * other fields (for example, if you say
	 * <code>
	 *   $foo = $tm->createTimeZone('America/New_York');
	 *   $foo->setId('America/Los_Angeles');
	 * </code>
	 * the time zone's GMT offset and daylight-savings rules don't change to those
	 * for Los Angeles. They're still those for New York. Only the ID has 
	 * changed.)
	 *
	 * @param      string The new timezone ID.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project ({@link http://icu.sourceforge.net})
	 * @since      0.11.0
	 */
	public function setId($id)
	{
		$this->id = $id;
	}


	/**
	 * Returns the resolved TimeZone's ID.
	 *
	 * @return     string This TimeZone's ID.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project ({@link http://icu.sourceforge.net})
	 * @since      0.11.0
	 */
	public function getResolvedId()
	{
		if($this->resolvedId === null) {
			return $this->id;
		}

		return $this->resolvedId;
	}

	/**
	 * Sets the resolved TimeZone's ID.
	 *
	 * @param      string The resolved timezone ID.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project ({@link http://icu.sourceforge.net})
	 * @since      0.11.0
	 */
	public function setResolvedId($id)
	{
		$this->resolvedId = $id;
	}

	/**
	 * Enum for use with getDisplayName
	 * @stable ICU 2.4
	 */
	/**
	 * Selector for short display name
	 * @stable ICU 2.4
	 */
	const SHORT = 1;
	/**
	 * Selector for long display name
	 * @stable ICU 2.4
	 */
	const LONG = 2;

	/**
	 * Returns a name of this time zone suitable for presentation to the user
	 * in the specified locale.
	 * If the display name is not available for the locale,
	 * then this method returns a string in the format
	 * <code>GMT[+-]hh:mm</code>.
	 * 
	 * @param      bool If true, return the daylight savings name.
	 * @param      int  Either <code>self::LONG</code> or <code>self::SHORT</code>
	 * @param      AgaviLocale The locale in which to supply the display name.
	 *
	 * @return     string the human-readable name of this time zone in the given 
	 *                    locale or in the default locale if the given locale is 
	 *                    not recognized.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project ({@link http://icu.sourceforge.net})
	 * @since      0.11.0
	 */
	public function getDisplayName($daylight = null, $style = null, $locale = null)
	{
		if($daylight === null) {
			$daylight = false;
			$style = self::LONG;
			$locale = $this->translationManager->getCurrentLocale();
		} elseif($daylight instanceof AgaviLocale) {
			$locale = $daylight;
			$daylight = false;
			$style = self::LONG;
		} elseif(is_bool($daylight) && $style !== null) {
			if($locale === null) {
				$locale = $this->translationManager->getCurrentLocale();
			}
		} else {
			throw new InvalidArgumentException('Illegal arguments for AgaviTimeZone::getDisplayName');
		}

		$displayString = null;

		if($daylight && $this->useDaylightTime()) {
			if($style == self::LONG) { 
				$displayString = $locale->getTimeZoneLongDaylightName($this->getId());
			} else {
				$displayString = $locale->getTimeZoneShortDaylightName($this->getId());
			}
		} else {
			if($style == self::LONG) { 
				$displayString = $locale->getTimeZoneLongStandardName($this->getId());
			} else {
				$displayString = $locale->getTimeZoneShortStandardName($this->getId());
			}
		}

		if(!$displayString) {
			$displayString = $this->getGmtString($daylight);
		}

		return $displayString;
	}

	/**
	 * Returns the GMT+-hh:mm representation of this timezone.
	 * 
	 * @param      bool Whether dst is active.
	 *
	 * @return     string The formatted representation.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	protected function getGmtString($daylight)
	{
		$value = $this->getRawOffset() + ($daylight ? $this->getDSTSavings() : 0);

		if($value < 0) {
			$str = 'GMT-';
			$value = -$value; // suppress the '-' sign for text display.
		} else {
			$str = 'GMT+';
		}

		$str .=		str_pad((int) ($value / AgaviDateDefinitions::MILLIS_PER_HOUR), 2, '0', STR_PAD_LEFT)
						. ':'
						. str_pad((int) (($value % AgaviDateDefinitions::MILLIS_PER_HOUR) / AgaviDateDefinitions::MILLIS_PER_MINUTE),  2, '0', STR_PAD_LEFT);
		return $str;
	}

	/**
	 * Queries if this time zone uses daylight savings time.
	 * 
	 * @return     bool If this time zone uses daylight savings time,
	 *                  false, otherwise.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project ({@link http://icu.sourceforge.net})
	 * @since      0.11.0
	 */
	public abstract function useDaylightTime();

	/**
	 * Returns true if this zone has the same rule and offset as another zone.
	 * That is, if this zone differs only in ID, if at all.
	 * 
	 * @param      AgaviTimeZone The object to be compared with
	 * 
	 * @return     bool True if the given zone is the same as this one,
	 *                  with the possible exception of the ID
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project ({@link http://icu.sourceforge.net})
	 * @since      0.11.0
	 */
	public function hasSameRules($other)
	{
		return ($this->getRawOffset() == $other->getRawOffset() && 
						$this->useDaylightTime() == $other->useDaylightTime());
	}

	/**
	 * Returns the amount of time to be added to local standard time
	 * to get local wall clock time.
	 * <p>
	 * The default implementation always returns 3600000 milliseconds
	 * (i.e., one hour) if this time zone observes Daylight Saving
	 * Time. Otherwise, 0 (zero) is returned.
	 * <p>
	 * If an underlying TimeZone implementation subclass supports
	 * historical Daylight Saving Time changes, this method returns
	 * the known latest daylight saving value.
	 *
	 * @return     int The amount of saving time in milliseconds
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project ({@link http://icu.sourceforge.net})
	 * @since      0.11.0
	 */
	public function getDSTSavings()
	{
		if($this->useDaylightTime()) {
			return 3600000;
		}
		return 0;
	}

	/**
	 * Construct a timezone with a given ID.
	 * 
	 * @param      AgaviTranslationManager The translation Manager
	 * @param      string A system time zone ID
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project ({@link http://icu.sourceforge.net})
	 * @since      0.11.0
	 */
	protected function __construct(AgaviTranslationManager $tm, $id = '')
	{
		$this->translationManager = $tm;
		$this->id = $id;
	}

	/**
	 * Returns the TimeZone's adjusted GMT offset (i.e., the number of 
	 * milliseconds to add to GMT to get local time in this time zone, taking 
	 * daylight savings time into account) as of a particular reference date.
	 * The reference date is used to determine whether daylight savings time is 
	 * in effect and needs to be figured into the offset that is returned (in 
	 * other words, what is the adjusted GMT offset in this time zone at this 
	 * particular date and time?).  For the time zones produced by 
	 * createTimeZone(), the reference data is specified according to the 
	 * Gregorian calendar, and the date and time fields are local standard time.
	 *
	 * <p>Note: Don't call this method. Instead, call the getOffsetRef() which 
	 * returns both the raw and the DST offset for a given time. This method
	 * is retained only for backward compatibility.
	 *
	 * @param      int The reference date's era
	 * @param      int The reference date's year
	 * @param      int The reference date's month (0-based; 0 is January)
	 * @param      int The reference date's day-in-month (1-based)
	 * @param      int The reference date's day-of-week (1-based; 1 is Sunday)
	 * @param      int The reference date's milliseconds in day, local standard 
	 *                 time
	 * 
	 * @return     int The offset in milliseconds to add to GMT to get local time.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project ({@link http://icu.sourceforge.net})
	 * @since      0.11.0
	 */
	protected abstract function getOffsetIIIIII($era, $year, $month, $day, $dayOfWeek, $millis);

	/**
	 * Gets the time zone offset, for current date, modified in case of
	 * daylight savings. This is the offset to add *to* UTC to get local time.
	 *
	 * <p>Note: Don't call this method. Instead, call the getOffsetRef(), which 
	 * returns both the raw and the DST offset for a given time. This method
	 * is retained only for backward compatibility.
	 *
	 * @param      int The era of the given date.
	 * @param      int The year in the given date.
	 * @param      int The month in the given date.
	 *                 Month is 0-based. e.g., 0 for January.
	 * @param      int The day-in-month of the given date.
	 * @param      int The day-of-week of the given date.
	 * @param      int The millis in day in <em>standard</em> local time.
	 * @param      int The length of the given month in days.
	 * 
	 * @return     int The offset to add *to* GMT to get local time.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project ({@link http://icu.sourceforge.net})
	 * @since      0.11.0
	 */
	protected abstract function getOffsetIIIIIII($era, $year, $month, $day, $dayOfWeek, $milliseconds, $monthLength);


	/**
	 * Parse a custom time zone identifier and return a corresponding zone.
	 * 
	 * @param      AgaviTranslationManager The translation manager
	 * @param      string A string of the form GMT[+-]hh:mm, GMT[+-]hhmm, or
	 *                    GMT[+-]hh.
	 * 
	 * @return     AgaviTimeZone A newly created AgaviSimpleTimeZone with the 
	 *                           given offset and no Daylight Savings Time, or 
	 *                           null if the id cannot be parsed.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project ({@link http://icu.sourceforge.net})
	 * @since      0.11.0
	 */
	public static function createCustomTimeZone(AgaviTranslationManager $tm, $id)
	{
		$hours = 0;
		$minutes = 0;
		$negative = false;
		if(preg_match('#^GMT([+-]?)(\d{1,2}):(\d{1,2})$#', $id, $match)) {
			$negative = $match[1] == '-';
			$hours = $match[2];
			$minutes = $match[3];
		} elseif(preg_match('#^GMT([+-]?)(\d{1,2})(\d{2})$#', $id, $match)) {
			$negative = $match[1] == '-';
			$hours = $match[2];
			$minutes = $match[3];
		} elseif(preg_match('#^GMT([+-]?)(\d{1,2})$#', $id, $match)) {
			$negative = $match[1] == '-';
			// Be strict about interpreting something as hh; it must be
			// an offset < 30, and it must be one or two digits. Thus
			// 0010 is interpreted as 00:10, but 10 is interpreted as 10:00.
			if($match[2] < 30) {
				$hours = $match[2];
			} else {
				$minutes = $match[2];
			}
		} else {
			return null;
		}

		$offset = $hours * 60 + $minutes;
		
		if($negative)
			$offset = -$offset;

		return new AgaviSimpleTimeZone($tm, $offset * 60000.0, 'Custom');
	}

	/**
	 * Responsible for setting up DEFAULT_ZONE.  Uses routines in TPlatformUtilities
	 * (i.e., platform-specific calls) to get the current system time zone.  Failing
	 * that, uses the platform-specific default time zone.  Failing that, uses GMT.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project ({@link http://icu.sourceforge.net})
	 * @since      0.11.0
	 */
	private static function initDefault()
	{
		// We access system timezone data through TPlatformUtilities,
		// including tzset(), timezone, and tzname[].
		$rawOffset = 0;
		$hostID = '';

// TODO: look up how to integrate this
/*
		// First, try to create a system timezone, based
		// on the string ID in tzname[0].
		// Get the timezone ID from the host.  This function should do
		// any required host-specific remapping; e.g., on Windows this
		// function maps the Date and Time control panel setting to an
		// ICU timezone ID.
		hostID = uprv_tzname(0);
		
		// Invert sign because UNIX semantics are backwards
		rawOffset = uprv_timezone() * -AgaviDateDefinitions::MILLIS_PER_SECOND;
*/

		$default_zone = null;

		/* Make sure that the string is NULL terminated to prevent BoundsChecker/Purify warnings. */
		$default_zone = self::createSystemTimeZone($hostID);

		$hostIDLen = strlen($hostID);
		if($default_zone != null && $rawOffset != $default_zone->getRawOffset() && (3 <= $hostIDLen && $hostIDLen <= 4)) {
				// Uh oh. This probably wasn't a good id.
				// It was probably an ambiguous abbreviation
				$default_zone = null;
		}

		// Construct a fixed standard zone with the host's ID
		// and raw offset.
		if($default_zone == null) {
			$default_zone = new AgaviSimpleTimeZone($rawOffset, $hostID);
		}

		// If we _still_ don't have a time zone, use GMT.
		if($default_zone == null) {
			$default_zone = clone self::getGMT();
		}

		// If DEFAULT_ZONE is still NULL, set it up.
		if(self::$DEFAULT_ZONE === NULL) {
			self::$DEFAULT_ZONE = $default_zone;
		}
	}

	/**
	 * Lookup the given name in our system zone table. If found, instantiate a 
	 * new zone of that name and return it. If not found, return null.
	 * 
	 * @param      AgaviTranslationManager The translation manager
	 * @param      string The given name of a system time zone.
	 * 
	 * @return     AgaviTimeZone The timezone indicated by the 'name'.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project ({@link http://icu.sourceforge.net})
	 * @since      0.11.0
	 */
	public static function createSystemTimeZone(AgaviTranslationManager $tm, $name)
	{
		$z = null;
/*
		UResourceBundle *top = openOlsonResource(id, res, ec);
		U_DEBUG_TZ_MSG(("post-err=%s\n", u_errorName(ec)));
		if (U_SUCCESS(ec)) {
*/
// TODO: create ...
//			$z = new OlsonTimeZone($top, $res);
		$z = new AgaviSimpleTimeZone($tm, 0, $name);
		$z->setId($name);

		return $z;
	}



	/**
	 * Returns true if the two TimeZones are equal. (The AgaviTimeZone version 
	 * only compares IDs, but subclasses are expected to also compare the fields 
	 * they add.)
	 *
	 * @param      AgaviTimeZone The object to be compared with.
	 * 
	 * @return     bool          True if the given TimeZone is equal to this 
	 *                           TimeZone; false otherwise.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project ({@link http://icu.sourceforge.net})
	 * @since      0.11.0
	 */
	public function __is_equal($that) {
		return get_class($this) == get_class($that) && $this->getId() == $that->getId();
	}

	/**
	 * Returns true if the two TimeZones are NOT equal; that is, if operator==() 
	 * returns false.
	 *
	 * @param      AgaviTimeZone The object to be compared with.
	 *
	 * @return     bool          True if the given TimeZone is not equal to this 
	 *                           TimeZone; false otherwise.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project ({@link http://icu.sourceforge.net})
	 * @since      0.11.0
	 */
	public function __is_not_equal($that) {
		return get_class($this) != get_class($that) || $this->getId() != $that->getId();
	}

	/**
	 * Queries if the given date is in daylight savings time in
	 * this time zone.
	 * This method is wasteful since it creates a new AgaviGregorianCalendar and
	 * deletes it each time it is called. 
	 *
	 * @param      float The given time
	 * 
	 * @return     bool  True if the given date is in daylight savings time,
	 *                   false, otherwise.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project ({@link http://icu.sourceforge.net})
	 * @since      0.11.0
	 */
	public function inDaylightTime($date)
	{
		$cal = new AgaviGregorianCalendar($this);
		$cal->setTime($date);
		return $cal->inDaylightTime();
	}
}

?>
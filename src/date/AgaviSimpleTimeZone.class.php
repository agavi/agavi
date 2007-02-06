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
 * @author     The ICU Project ({@link http://icu.sourceforge.net})
 * @copyright  Authors
 * @copyright  The Agavi Project
 * @since      0.11.0
 *
 * @version    $Id$
 */
class AgaviSimpleTimeZone extends AgaviTimeZone
{

	/**
	 * TimeMode is used, together with a millisecond offset after
	 * midnight, to specify a rule transition time.  Most rules
	 * transition at a local wall time, that is, according to the
	 * current time in effect, either standard, or DST.  However, some
	 * rules transition at local standard time, and some at a specific
	 * UTC time.  Although it might seem that all times could be
	 * converted to wall time, thus eliminating the need for this
	 * parameter, this is not the case.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project ({@link http://icu.sourceforge.net})
	 * @since      0.11.0
	 */
	const WALL_TIME = 0.0;
	const STANDARD_TIME = 1.0;
	const UTC_TIME = 2.0;

	/**
	 * Returns true if the two TimeZone objects are equal; that is, they have
	 * the same ID, raw GMT offset, and DST rules.
	 *
	 * @param      AgaviTimeZone The SimpleTimeZone object to be compared with.
	 * 
	 * @return     bool True if the given time zone is equal to this time zone; 
	 *                  false otherwise.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project ({@link http://icu.sourceforge.net})
	 * @since      0.11.0
	 */
	function __is_equal($that)
	{
		return ($this === $that ||
						(get_class($this) == get_class($that) &&
							AgaviTimeZone::__is_equal($that) &&
							$this->hasSameRules($that)
						));
	}

	public function __construct()
	{
		$arguments = func_get_args();
		if(count($arguments) == 1) {
			parent::__construct($arguments[0]);
			return;
		}
		$fName = AgaviToolkit::overloadHelper(array(
			array('name' => 'constructorOIS',
						'parameters' => array('object', 'int', 'string')),
			array('name' => 'constructorOISIIIIIIII',
						'parameters' => array('object', 'int', 'string', 'int', 'int', 'int', 'int', 'int', 'int', 'int', 'int')),
			array('name' => 'constructorOISIIIIIIIII',
						'parameters' => array('object', 'int', 'string', 'int', 'int', 'int', 'int', 'int', 'int', 'int', 'int', 'int')),
			array('name' => 'constructorOISIIIIIIIIIII',
						'parameters' => array('object', 'int', 'string', 'int', 'int', 'int', 'int', 'int', 'int', 'int', 'int', 'int', 'int', 'int')),
			),
			$arguments
		);
		call_user_func_array(array($this, $fName), $arguments);
	}

	/**
	 * Constructs a SimpleTimeZone with the given raw GMT offset and time zone ID,
	 * and which doesn't observe daylight savings time.  Normally you should use
	 * TimeZone::createInstance() to create a TimeZone instead of creating a
	 * SimpleTimeZone directly with this constructor.
	 *
	 * @param      AgaviTranslationManager The translation Manager
	 * @param      int    The given base time zone offset to GMT.
	 * @param      string The timezone ID which is obtained from
	 *                    TimeZone.getAvailableIDs.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project ({@link http://icu.sourceforge.net})
	 * @since      0.11.0
	 */
	protected function constructorOIS(AgaviTranslationManager $tm, $rawOffsetGMT, $id)
	{
		parent::__construct($tm, $id);
		$this->startMonth = 0;
		$this->startDay = 0;
		$this->startDayOfWeek = 0;
		$this->startTime = 0;
		$this->startTimeMode = self::WALL_TIME;
		$this->endTimeMode = self::WALL_TIME;
		$this->endMonth = 0;
		$this->endDay = 0;
		$this->endDayOfWeek = 0;
		$this->endTime = 0;
		$this->startYear = 0;
		$this->rawOffset = $rawOffsetGMT;
		$this->useDaylight = false;
		$this->startMode = self::DOM_MODE;
		$this->endMode = self::DOM_MODE;
		$this->dstSavings = AgaviDateDefinitions::MILLIS_PER_HOUR;
	}

	/**
	 * Construct a SimpleTimeZone with the given raw GMT offset, time zone ID,
	 * and times to start and end daylight savings time. To create a TimeZone that
	 * doesn't observe daylight savings time, don't use this constructor; use
	 * SimpleTimeZone(rawOffset, ID) instead. Normally, you should use
	 * TranslationManager->createTimeZone() to create a TimeZone instead of 
	 * creating a SimpleTimeZone directly with this constructor.
	 * <P>
	 * Various types of daylight-savings time rules can be specfied by using 
	 * different values for startDay and startDayOfWeek and endDay and 
	 * endDayOfWeek.  For a complete explanation of how these parameters work, 
	 * see the documentation for setStartRule().
	 *
	 * @param      AgaviTranslationManager The translation Manager
	 * @param      int    The new SimpleTimeZone's raw GMT offset
	 * @param      string The new SimpleTimeZone's time zone ID.
	 * @param      int    The daylight savings starting month. Month is
	 *                          0-based. eg, 0 for January.
	 * @param      int    The daylight savings starting
	 *                          day-of-week-in-month. See setStartRule() for a
	 *                          complete explanation.
	 * @param      int    The daylight savings starting day-of-week.
	 *                          See setStartRule() for a complete explanation.
	 * @param      int    The daylight savings starting time, expressed as the
	 *                          number of milliseconds after midnight.
	 * @param      int    The daylight savings ending month. Month is
	 *                          0-based. eg, 0 for January.
	 * @param      int    The daylight savings ending day-of-week-in-month.
	 *                          See setStartRule() for a complete explanation.
	 * @param      int    The daylight savings ending day-of-week.
	 *                          See setStartRule() for a complete explanation.
	 * @param      int    The daylight savings ending time, expressed as the
	 *                          number of milliseconds after midnight.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project ({@link http://icu.sourceforge.net})
	 * @since      0.11.0
	 */
	protected function constructorOISIIIIIIII(AgaviTranslationManager $tm, $rawOffsetGMT, $id, $savingsStartMonth, $savingsStartDay, $savingsStartDayOfWeek, $savingsStartTime, $savingsEndMonth,  $savingsEndDay, $savingsEndDayOfWeek, $savingsEndTime)
	{
		parent::__construct($tm, $id);
		$this->construct($rawOffsetGMT, $savingsStartMonth, $savingsStartDay, $savingsStartDayOfWeek, $savingsStartTime, self::WALL_TIME, $savingsEndMonth, $savingsEndDay, $savingsEndDayOfWeek, $savingsEndTime, self::WALL_TIME, AgaviDateDefinitions::MILLIS_PER_HOUR);
	}

	/**
	 * Construct a SimpleTimeZone with the given raw GMT offset, time zone ID,
	 * and times to start and end daylight savings time. To create a TimeZone that
	 * doesn't observe daylight savings time, don't use this constructor; use
	 * SimpleTimeZone(rawOffset, ID) instead. Normally, you should use
	 * TimeZone.createInstance() to create a TimeZone instead of creating a
	 * SimpleTimeZone directly with this constructor.
	 * <P>
	 * Various types of daylight-savings time rules can be specfied by using 
	 * different values for startDay and startDayOfWeek and endDay and 
	 * endDayOfWeek. For a complete explanation of how these parameters work, see
	 * the documentation for setStartRule().
	 *
	 * @param      AgaviTranslationManager The translation Manager
	 * @param      int    The new SimpleTimeZone's raw GMT offset
	 * @param      string The new SimpleTimeZone's time zone ID.
	 * @param      int    The daylight savings starting month. Month is
	 *                    0-based. eg, 0 for January.
	 * @param      int    The daylight savings starting
	 *                    day-of-week-in-month. See setStartRule() for a
	 *                    complete explanation.
	 * @param      int    The daylight savings starting day-of-week.
	 *                    See setStartRule() for a complete explanation.
	 * @param      int    The daylight savings starting time, expressed as the
	 *                    number of milliseconds after midnight.
	 * @param      int    The daylight savings ending month. Month is
	 *                    0-based. eg, 0 for January.
	 * @param      int    The daylight savings ending day-of-week-in-month.
	 *                    See setStartRule() for a complete explanation.
	 * @param      int    The daylight savings ending day-of-week.
	 *                    See setStartRule() for a complete explanation.
	 * @param      int    The daylight savings ending time, expressed as the
	 *                    number of milliseconds after midnight.
	 * @param      int    The number of milliseconds added to standard time
	 *                    to get DST time. Default is one hour.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project ({@link http://icu.sourceforge.net})
	 * @since      0.11.0
	 */
	protected function constructorOISIIIIIIIII(AgaviTranslationManager $tm, $rawOffsetGMT, $id, $savingsStartMonth, $savingsStartDay, $savingsStartDayOfWeek, $savingsStartTime, $savingsEndMonth, $savingsEndDay, $savingsEndDayOfWeek, $savingsEndTime, $savingsDST)
	{
		parent::__construct($tm, $id);
		$this->construct($rawOffsetGMT, $savingsStartMonth, $savingsStartDay, $savingsStartDayOfWeek, $savingsStartTime, self::WALL_TIME, $savingsEndMonth, $savingsEndDay, $savingsEndDayOfWeek, $savingsEndTime, self::WALL_TIME, $savingsDST);
	}

	/**
	 * Construct a SimpleTimeZone with the given raw GMT offset, time zone ID,
	 * and times to start and end daylight savings time. To create a TimeZone that
	 * doesn't observe daylight savings time, don't use this constructor; use
	 * SimpleTimeZone(rawOffset, ID) instead. Normally, you should use
	 * TimeZone.createInstance() to create a TimeZone instead of creating a
	 * SimpleTimeZone directly with this constructor.
	 * <P>
	 * Various types of daylight-savings time rules can be specfied by using 
	 * different values for startDay and startDayOfWeek and endDay and 
	 * endDayOfWeek. For a complete explanation of how these parameters work, see 
	 * the documentation for setStartRule().
	 *
	 * @param      AgaviTranslationManager The translation Manager
	 * @param      int    The new SimpleTimeZone's raw GMT offset
	 * @param      string The new SimpleTimeZone's time zone ID.
	 * @param      int    The daylight savings starting month. Month is
	 *                    0-based. eg, 0 for January.
	 * @param      int    The daylight savings starting
	 *                    day-of-week-in-month. See setStartRule() for a
	 *                    complete explanation.
	 * @param      int    The daylight savings starting day-of-week.
	 *                    See setStartRule() for a complete explanation.
	 * @param      int    The daylight savings starting time, expressed as the
	 *                    number of milliseconds after midnight.
	 * @param      int    Whether the start time is local wall time, local
	 *                    standard time, or UTC time. Default is local wall time.
	 * @param      int    The daylight savings ending month. Month is
	 *                    0-based. eg, 0 for January.
	 * @param      int    The daylight savings ending day-of-week-in-month.
	 *                    See setStartRule() for a complete explanation.
	 * @param      int    The daylight savings ending day-of-week.
	 *                    See setStartRule() for a complete explanation.
	 * @param      int    The daylight savings ending time, expressed as the
	 *                    number of milliseconds after midnight.
	 * @param      int    Whether the end time is local wall time, local
	 *                    standard time, or UTC time. Default is local wall time.
	 * @param      int    The number of milliseconds added to standard time
	 *                    to get DST time. Default is one hour.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project ({@link http://icu.sourceforge.net})
	 * @since      0.11.0
	 */
	protected function constructorOISIIIIIIIIIII(AgaviTranslationManager $tm, $rawOffsetGMT, $id, $savingsStartMonth, $savingsStartDay, $savingsStartDayOfWeek, $savingsStartTime, $savingsStartTimeMode, $savingsEndMonth, $savingsEndDay, $savingsEndDayOfWeek, $savingsEndTime, $savingsEndTimeMode, $savingsDST)
	{
		parent::__construct($tm, $id);
		$this->construct($rawOffsetGMT, $savingsStartMonth, $savingsStartDay, $savingsStartDayOfWeek, $savingsStartTime, $savingsStartTimeMode, $savingsEndMonth, $savingsEndDay, $savingsEndDayOfWeek, $savingsEndTime, $savingsEndTimeMode, $savingsDST);
	}

	/**
	 * Sets the daylight savings starting year, that is, the year this time zone 
	 * began observing its specified daylight savings time rules.  The time zone 
	 * is considered not to observe daylight savings time prior to that year; 
	 * SimpleTimeZone doesn't support historical daylight-savings-time rules.
	 * 
	 * @param      int The daylight savings starting year.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project ({@link http://icu.sourceforge.net})
	 * @since      0.11.0
	 */
	public function setStartYear($year)
	{
		$this->startYear = $year;
	}

	public function setStartRule()
	{
		$arguments = func_get_args();
		$fName = AgaviToolkit::overloadHelper(array(
			array('name' => 'setStartRuleIIII',
						'parameters' => array('int', 'int', 'int', 'int')),
			array('name' => 'setStartRuleIIIIF',
						'parameters' => array('int', 'int', 'int', 'int', 'double')),
			array('name' => 'setStartRuleIII',
						'parameters' => array('int', 'int', 'int')),
			array('name' => 'setStartRuleIIIF',
						'parameters' => array('int', 'int', 'int', 'double')),
			array('name' => 'setStartRuleIIIIB',
						'parameters' => array('int', 'int', 'int', 'int', 'bool')),
			array('name' => 'setStartRuleIIIIFB',
						'parameters' => array('int', 'int', 'int', 'int', 'float', 'bool')),
			),
			$arguments
		);
		call_user_func_array(array($this, $fName), $arguments);
	}

	/**
	 * Sets the daylight savings starting rule. For example, in the U.S., Daylight
	 * Savings Time starts at the first Sunday in April, at 2 AM in standard time.
	 * Therefore, you can set the start rule by calling:
	 * <code>
	 *  setStartRule(AgaviDateDefinitions::APRIL, 1, AgaviDateDefinitions::SUNDAY,
	 *  2*60*60*1000);
	 * </code>
	 * The dayOfWeekInMonth and dayOfWeek parameters together specify how to 
	 * calculate the exact starting date.  Their exact meaning depend on their 
	 * respective signs, allowing various types of rules to be constructed, as 
	 * follows:
	 * <ul>
	 *   <li>If both dayOfWeekInMonth and dayOfWeek are positive, they specify the
	 *       day of week in the month (e.g., (2, WEDNESDAY) is the second 
	 *       Wednesday of the month).
	 *   </li>
	 *   <li>If dayOfWeek is positive and dayOfWeekInMonth is negative, they 
	 *       specify the day of week in the month counting backward from the end 
	 *       of the month. (e.g., (-1, MONDAY) is the last Monday in the month)
	 *   </li>
	 *   <li>If dayOfWeek is zero and dayOfWeekInMonth is positive, 
	 *       dayOfWeekInMonth specifies the day of the month, regardless of what 
	 *       day of the week it is. (e.g., (10, 0) is the tenth day of the month)
	 *   </li>
	 *   <li>If dayOfWeek is zero and dayOfWeekInMonth is negative, 
	 *       dayOfWeekInMonth specifies the day of the month counting backward 
	 *       from the end of the month, regardless of what day of the week it is 
	 *       (e.g., (-2, 0) is the next-to-last day of the month).
	 *   </li>
	 *   <li>If dayOfWeek is negative and dayOfWeekInMonth is positive, they 
	 *       specify the first specified day of the week on or after the specfied 
	 *       day of the month. (e.g., (15, -SUNDAY) is the first Sunday after the 
	 *       15th of the month [or the 15th itself if the 15th is a Sunday].)
	 *   </li>
	 *   <li>If dayOfWeek and DayOfWeekInMonth are both negative, they specify the
	 *       last specified day of the week on or before the specified day of the 
	 *       month. (e.g., (-20, -TUESDAY) is the last Tuesday before the 20th of 
	 *       the month [or the 20th itself if the 20th is a Tuesday].)
	 *   </li>
	 * </ul>
	 * 
	 * @param      int The daylight savings starting month. Month is 0-based.
	 *                 eg, 0 for January.
	 * @param      int The daylight savings starting day-of-week-in-month. Please 
	 *                 see the member description for an example.
	 * @param      int The daylight savings starting day-of-week. Please see
	 *                 the member description for an example.
	 * @param      int The daylight savings starting time. Please see the member
	 *                 description for an example.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project ({@link http://icu.sourceforge.net})
	 * @since      0.11.0
	 */
	public function setStartRuleIIII($month, $dayOfWeekInMonth, $dayOfWeek, $time)
	{
		$this->setStartRuleIIIIF($month, $dayOfWeekInMonth, $dayOfWeek, $time, self::WALL_TIME);
	}

	/**
	 * Sets the daylight savings starting rule. For example, in the U.S., Daylight
	 * Savings Time starts at the first Sunday in April, at 2 AM in standard time.
	 * Therefore, you can set the start rule by calling:
	 * setStartRule(TimeFields.APRIL, 1, TimeFields.SUNDAY, 2*60*60*1000);
	 * The dayOfWeekInMonth and dayOfWeek parameters together specify how to 
	 * calculate the exact starting date.  Their exact meaning depend on their 
	 * respective signs, allowing various types of rules to be constructed, as 
	 * follows:
	 * <ul>
	 *   <li>If both dayOfWeekInMonth and dayOfWeek are positive, they specify the
	 *       day of week in the month (e.g., (2, WEDNESDAY) is the second 
	 *       Wednesday of the month).
	 *   </li>
	 *   <li>If dayOfWeek is positive and dayOfWeekInMonth is negative, they 
	 *       specify the day of week in the month counting backward from the end 
	 *       of the month. (e.g., (-1, MONDAY) is the last Monday in the month)
	 *   </li>
	 *   <li>If dayOfWeek is zero and dayOfWeekInMonth is positive, 
	 *       dayOfWeekInMonth specifies the day of the month, regardless of what 
	 *       day of the week it is. (e.g., (10, 0) is the tenth day of the month)
	 *   </li>
	 *   <li>If dayOfWeek is zero and dayOfWeekInMonth is negative, 
	 *       dayOfWeekInMonth specifies the day of the month counting backward 
	 *       from the end of the month, regardless of what day of the week it is 
	 *       (e.g., (-2, 0) is the next-to-last day of the month).
	 *   </li>
	 *   <li>If dayOfWeek is negative and dayOfWeekInMonth is positive, they 
	 *       specify the first specified day of the week on or after the specfied 
	 *       day of the month. (e.g., (15, -SUNDAY) is the first Sunday after the 
	 *       15th of the month [or the 15th itself if the 15th is a Sunday].)
	 *   </li>
	 *   <li>If dayOfWeek and DayOfWeekInMonth are both negative, they specify the
	 *       last specified day of the week on or before the specified day of the 
	 *       month. (e.g., (-20, -TUESDAY) is the last Tuesday before the 20th of 
	 *       the month [or the 20th itself if the 20th is a Tuesday].)
	 *   </li>
	 * </ul>
	 * 
	 * @param      int The daylight savings starting month. Month is 0-based.
	 *                 eg, 0 for January.
	 * @param      int The daylight savings starting day-of-week-in-month.
	 *                 Please see the member description for an example.
	 * @param      int The daylight savings starting day-of-week. Please see
	 *                 the member description for an example.
	 * @param      int The daylight savings starting time. Please see the member
	 *                 description for an example.
	 * @param      float Whether the time is local wall time, local standard time,
	 *                   or UTC time. Default is local wall time.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project ({@link http://icu.sourceforge.net})
	 * @since      0.11.0
	 */
	public function setStartRuleIIIIF($month, $dayOfWeekInMonth, $dayOfWeek, $time, $mode)
	{
		$this->startMonth     = $month;
		$this->startDay       = $dayOfWeekInMonth;
		$this->startDayOfWeek = $dayOfWeek;
		$this->startTime      = $time;
		$this->startTimeMode  = $mode;
		$this->decodeStartRule();
	}

	/**
	 * Sets the DST start rule to a fixed date within a month.
	 *
	 * @param      int The month in which this rule occurs (0-based).
	 * @param      int The date in that month (1-based).
	 * @param      int The time of that day (number of millis after midnight)
	 *                 when DST takes effect in local wall time, which is
	 *                 standard time in this case.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project ({@link http://icu.sourceforge.net})
	 * @since      0.11.0
	 */
	public function setStartRuleIII($month, $dayOfMonth, $time)
	{
		$this->setStartRuleIIIF($month, $dayOfMonth, $time, self::WALL_TIME);
	}



	/**
	 * Sets the DST start rule to a fixed date within a month.
	 *
	 * @param      int The month in which this rule occurs (0-based).
	 * @param      int The date in that month (1-based).
	 * @param      int The time of that day (number of millis after midnight)
	 *                      when DST takes effect in local wall time, which is
	 *                      standard time in this case.
	 * @param      float Whether the time is local wall time, local standard time,
	 *                   or UTC time. Default is local wall time.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project ({@link http://icu.sourceforge.net})
	 * @since      0.11.0
	 */
	public function setStartRuleIIIF($month, $dayOfMonth, $time, $mode)
	{
		$this->setStartRuleIIIIF($month, $dayOfMonth, 0, $time, $mode);
	}

	/**
	 * Sets the DST start rule to a weekday before or after a give date within
	 * a month, e.g., the first Monday on or after the 8th.
	 *
	 * @param      int  The month in which this rule occurs (0-based).
	 * @param      int  A date within that month (1-based).
	 * @param      int  The day of the week on which this rule occurs.
	 * @param      int  The time of that day (number of millis after midnight)
	 *                  when DST takes effect in local wall time, which is
	 *                  standard time in this case.
	 * @param      bool If true, this rule selects the first dayOfWeek on
	 *                  or after dayOfMonth.  If false, this rule selects
	 *                  the last dayOfWeek on or before dayOfMonth.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project ({@link http://icu.sourceforge.net})
	 * @since      0.11.0
	 */
	public function setStartRuleIIIIB($month, $dayOfMonth, $dayOfWeek, $time, $after)
	{
		$this->setStartRuleIIIIFB($month, $dayOfMonth, $dayOfWeek, $time, self::WALL_TIME, $after);
	}

	/**
	 * Sets the DST start rule to a weekday before or after a give date within
	 * a month, e.g., the first Monday on or after the 8th.
	 *
	 * @param      int The month in which this rule occurs (0-based).
	 * @param      int A date within that month (1-based).
	 * @param      int The day of the week on which this rule occurs.
	 * @param      int The time of that day (number of millis after midnight)
	 *                 when DST takes effect in local wall time, which is
	 *                 standard time in this case.
	 * @param      float Whether the time is local wall time, local standard time,
	 *                   or UTC time. Default is local wall time.
	 * @param      bool If true, this rule selects the first dayOfWeek on
	 *                  or after dayOfMonth.  If false, this rule selects
	 *                  the last dayOfWeek on or before dayOfMonth.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project ({@link http://icu.sourceforge.net})
	 * @since      0.11.0
	 */
	public function setStartRuleIIIIFB($month, $dayOfMonth, $dayOfWeek, $time, $mode, $after)
	{
		$this->setStartRuleIIIIF($month, $after ? $dayOfMonth : -$dayOfMonth, -$dayOfWeek, $time, $mode);
	}


	public function setEndRule()
	{
		$arguments = func_get_args();
		$fName = AgaviToolkit::overloadHelper(array(
			array('name' => 'setEndRuleIIII',
						'parameters' => array('int', 'int', 'int', 'int')),
			array('name' => 'setEndRuleIIIIF',
						'parameters' => array('int', 'int', 'int', 'int', 'double')),
			array('name' => 'setEndRuleIII',
						'parameters' => array('int', 'int', 'int')),
			array('name' => 'setEndRuleIIIF',
						'parameters' => array('int', 'int', 'int', 'double')),
			array('name' => 'setEndRuleIIIIB',
						'parameters' => array('int', 'int', 'int', 'int', 'bool')),
			array('name' => 'setEndRuleIIIIFB',
						'parameters' => array('int', 'int', 'int', 'int', 'float', 'bool')),
			),
			$arguments
		);
		call_user_func_array(array($this, $fName), $arguments);
	}


	/**
	 * Sets the daylight savings ending rule. For example, in the U.S., Daylight
	 * Savings Time ends at the last (-1) Sunday in October, at 2 AM in standard 
	 * time.
	 * Therefore, you can set the end rule by calling:
	 * <pre>
	 * .   setEndRule(TimeFields.OCTOBER, -1, TimeFields.SUNDAY, 2*60*60*1000);
	 * </pre>
	 * Various other types of rules can be specified by manipulating the dayOfWeek
	 * and dayOfWeekInMonth parameters.  For complete details, see the 
	 * documentation for setStartRule().
	 *
	 * @param      int The daylight savings ending month. Month is 0-based.
	 *                 eg, 0 for January.
	 * @param      int The daylight savings ending day-of-week-in-month.
	 *                 See setStartRule() for a complete explanation.
	 * @param      int The daylight savings ending day-of-week. See setStartRule()
	 *                 for a complete explanation.
	 * @param      int The daylight savings ending time. Please see the member
	 *                 description for an example.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project ({@link http://icu.sourceforge.net})
	 * @since      0.11.0
	 */
	public function setEndRuleIIII($month, $dayOfWeekInMonth, $dayOfWeek, $time)
	{
		$this->setEndRuleIIIIF($month, $dayOfWeekInMonth, $dayOfWeek, $time, self::WALL_TIME);
	}

	/**
	 * Sets the daylight savings ending rule. For example, in the U.S., Daylight
	 * Savings Time ends at the last (-1) Sunday in October, at 2 AM in standard 
	 * time.
	 * Therefore, you can set the end rule by calling:
	 * <pre>
	 * .   setEndRule(TimeFields.OCTOBER, -1, TimeFields.SUNDAY, 2*60*60*1000);
	 * </pre>
	 * Various other types of rules can be specified by manipulating the dayOfWeek
	 * and dayOfWeekInMonth parameters.  For complete details, see the 
	 * documentation for setStartRule().
	 *
	 * @param      int The daylight savings ending month. Month is 0-based.
	 *                 eg, 0 for January.
	 * @param      int The daylight savings ending day-of-week-in-month.
	 *                 See setStartRule() for a complete explanation.
	 * @param      int The daylight savings ending day-of-week. See setStartRule()
	 *                 for a complete explanation.
	 * @param      int The daylight savings ending time. Please see the member
	 *                 description for an example.
	 * @param      float Whether the time is local wall time, local standard time,
	 *                   or UTC time. Default is local wall time.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project ({@link http://icu.sourceforge.net})
	 * @since      0.11.0
	 */
	public function setEndRuleIIIIF($month, $dayOfWeekInMonth, $dayOfWeek, $time, $mode)
	{
		$this->endMonth     = $month;
		$this->endDay       = $dayOfWeekInMonth;
		$this->endDayOfWeek = $dayOfWeek;
		$this->endTime      = $time;
		$this->endTimeMode  = $mode;
		$this->decodeEndRule();
	}

	/**
	 * Sets the DST end rule to a fixed date within a month.
	 *
	 * @param      int The month in which this rule occurs (0-based).
	 * @param      int The date in that month (1-based).
	 * @param      int The time of that day (number of millis after midnight)
	 *                 when DST ends in local wall time, which is daylight
	 *                 time in this case.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project ({@link http://icu.sourceforge.net})
	 * @since      0.11.0
	 */
	public function setEndRuleIII($month, $dayOfMonth, $time)
	{
		$this->setEndRuleIIIF($month, $dayOfMonth, $time, self::WALL_TIME);
	}

	/**
	 * Sets the DST end rule to a fixed date within a month.
	 *
	 * @param      int The month in which this rule occurs (0-based).
	 * @param      int The date in that month (1-based).
	 * @param      int The time of that day (number of millis after midnight)
	 *                 when DST ends in local wall time, which is daylight
	 *                 time in this case.
	 * @param      float Whether the time is local wall time, local standard time,
	 *                   or UTC time. Default is local wall time.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project ({@link http://icu.sourceforge.net})
	 * @since      0.11.0
	 */
	public function setEndRuleIIIF($month, $dayOfMonth, $time, $mode)
	{
		$this->setEndRuleIIIIF($month, $dayOfMonth, 0, $time, $mode);
	}

	/**
	 * Sets the DST end rule to a weekday before or after a give date within
	 * a month, e.g., the first Monday on or after the 8th.
	 *
	 * @param      int  The month in which this rule occurs (0-based).
	 * @param      int  A date within that month (1-based).
	 * @param      int  The day of the week on which this rule occurs.
	 * @param      int  The time of that day (number of millis after midnight)
	 *                  when DST ends in local wall time, which is daylight
	 *                  time in this case.
	 * @param      bool If true, this rule selects the first dayOfWeek on
	 *                  or after dayOfMonth.  If false, this rule selects
	 *                  the last dayOfWeek on or before dayOfMonth.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project ({@link http://icu.sourceforge.net})
	 * @since      0.11.0
	 */
	public function setEndRuleIIIIB($month, $dayOfMonth, $dayOfWeek, $time, $after)
	{
		$this->setEndRuleIIIIFB($month, $dayOfMonth, $dayOfWeek, $time, self::WALL_TIME, $after);
	}

	/**
	 * Sets the DST end rule to a weekday before or after a give date within
	 * a month, e.g., the first Monday on or after the 8th.
	 *
	 * @param      int  The month in which this rule occurs (0-based).
	 * @param      int  A date within that month (1-based).
	 * @param      int  The day of the week on which this rule occurs.
	 * @param      int  The time of that day (number of millis after midnight)
	 *                  when DST ends in local wall time, which is daylight
	 *                  time in this case.
	 * @param      float Whether the time is local wall time, local standard
	 *                   time, or UTC time. Default is local wall time.
	 * @param      bool If true, this rule selects the first dayOfWeek on
	 *                  or after dayOfMonth.  If false, this rule selects
	 *                  the last dayOfWeek on or before dayOfMonth.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project ({@link http://icu.sourceforge.net})
	 * @since      0.11.0
	 */
	public function setEndRuleIIIIFB($month, $dayOfMonth, $dayOfWeek, $time, $mode, $after)
	{
		$this->setEndRuleIIIIF($month, $after ? $dayOfMonth : -$dayOfMonth, -$dayOfWeek, $time, $mode);
	}


	public function getOffset()
	{
		$arguments = func_get_args();
		$fName = AgaviToolkit::overloadHelper(array(
			array('name' => 'getOffsetIIIIII',
						'parameters' => array('int', 'int', 'int', 'int', 'int', 'int')),
			array('name' => 'getOffsetIIIIIII',
						'parameters' => array('int', 'int', 'int', 'int', 'int', 'int', 'int')),
			array('name' => 'getOffsetIIIIIIII',
						'parameters' => array('int', 'int', 'int', 'int', 'int', 'int', 'int', 'int')),
			),
			$arguments
		);

		return call_user_func_array(array($this, $fName), $arguments);
	}

	/**
	 * Returns the TimeZone's adjusted GMT offset (i.e., the number of 
	 * milliseconds to add to GMT to get local time in this time zone, taking 
	 * daylight savings time into account) as of a particular reference date.
	 * The reference date is used to determine whether daylight savings time is in
	 * effect and needs to be figured into the offset that is returned (in other 
	 * words, what is the adjusted GMT offset in this time zone at this particular
	 * date and time?).  For the time zones produced by createTimeZone(), the 
	 * reference data is specified according to the Gregorian calendar, and the 
	 * date and time fields are in GMT, NOT local time.
	 *
	 * @param      int The reference date's era
	 * @param      int The reference date's year
	 * @param      int The reference date's month (0-based; 0 is January)
	 * @param      int The reference date's day-in-month (1-based)
	 * @param      int The reference date's day-of-week (1-based; 1 is Sunday)
	 * @param      int The reference date's milliseconds in day, UTT (NOT local 
	 *                 time).
	 * 
	 * @return     int The offset in milliseconds to add to GMT to get local time.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project ({@link http://icu.sourceforge.net})
	 * @since      0.11.0
	 */
	public function getOffsetIIIIII($era, $year, $month, $day, $dayOfWeek, $millis)
	{
		// Check the month before calling Grego::monthLength(). This
		// duplicates the test that occurs in the 7-argument getOffset(),
		// however, this is unavoidable. We don't mind because this method, in
		// fact, should not be called; internal code should always call the
		// 7-argument getOffset(), and outside code should use Calendar.get(int
		// field) with fields ZONE_OFFSET and DST_OFFSET. We can't get rid of
		// this method because it's public API. - liu 8/10/98
		if($month < AgaviDateDefinitions::JANUARY || $month > AgaviDateDefinitions::DECEMBER) {
				throw new InvalidArgumentException('Month out of range');
				return 0;
		}

		return $this->getOffsetIIIIIII($era, $year, $month, $day, $dayOfWeek, $millis, AgaviCalendarGrego::monthLength($year, $month));
	}

	/**
	 * Gets the time zone offset, for current date, modified in case of
	 * daylight savings. This is the offset to add *to* UTC to get local time.
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
	public function getOffsetIIIIIII($era, $year, $month, $day, $dayOfWeek, $millis, $monthLength)
	{
		// Check the month before indexing into STATICMONTHLENGTH. This
		// duplicates a test that occurs in the 9-argument getOffset(),
		// however, this is unavoidable. We don't mind because this method, in
		// fact, should not be called; internal code should always call the
		// 9-argument getOffset(), and outside code should use Calendar.get(int
		// field) with fields ZONE_OFFSET and DST_OFFSET. We can't get rid of
		// this method because it's public API. - liu 8/10/98
		if($month < AgaviDateDefinitions::JANUARY || $month > AgaviDateDefinitions::DECEMBER) {
				throw new InvalidArgumentException('Month out of range');
				return -1;
		}

		return $this->getOffsetIIIIIIII($era, $year, $month, $day, $dayOfWeek, $millis, AgaviCalendarGrego::monthLength($year, $month), AgaviCalendarGrego::previousMonthLength($year, $month));
	}

	/**
	 * Gets the time zone offset, for current date, modified in case of
	 * daylight savings. This is the offset to add *to* UTC to get local time.
	 *
	 * @param      int The era of the given date.
	 * @param      int The year in the given date.
	 * @param      int The month in the given date.
	 *                 Month is 0-based. e.g., 0 for January.
	 * @param      int The day-in-month of the given date.
	 * @param      int The day-of-week of the given date.
	 * @param      int The millis in day in <em>standard</em> local time.
	 * @param      int The length of the given month in days.
	 * @param      int Length of the previous month in days.
	 * 
	 * @return     int The offset to add *to* GMT to get local time.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project ({@link http://icu.sourceforge.net})
	 * @since      0.11.0
	 */
	public function getOffsetIIIIIIII($era, $year, $month, $day, $dayOfWeek, $millis, $monthLength, $prevMonthLength)
	{
		if(($era != AgaviGregorianCalendar::AD && $era != AgaviGregorianCalendar::BC)
				|| $month < AgaviDateDefinitions::JANUARY
				|| $month > AgaviDateDefinitions::DECEMBER
				|| $day < 1
				|| $day > $monthLength
				|| $dayOfWeek < AgaviDateDefinitions::SUNDAY
				|| $dayOfWeek > AgaviDateDefinitions::SATURDAY
				|| $millis < 0
				|| $millis >= AgaviDateDefinitions::MILLIS_PER_DAY
				|| $monthLength < 28
				|| $monthLength > 31
				|| $prevMonthLength < 28
				|| $prevMonthLength > 31) {
				throw new InvalidArgumentException('One of the supplied parameters is out of range');
				return -1;
		}

		$result = $this->rawOffset;

		// Bail out if we are before the onset of daylight savings time
		if(!$this->useDaylight || $year < $this->startYear || $era != AgaviGregorianCalendar::AD) 
			return $result;

		// Check for southern hemisphere.  We assume that the start and end
		// month are different.
		$southern = ($this->startMonth > $this->endMonth);

		// Compare the date to the starting and ending rules.+1 = date>rule, -1
		// = date<rule, 0 = date==rule.
		$startCompare = self::compareToRule($month, $monthLength, $prevMonthLength, $day, $dayOfWeek, 
																									$millis, $this->startTimeMode == self::UTC_TIME ? -$this->rawOffset : 0,
																									$this->startMode, $this->startMonth, $this->startDayOfWeek, $this->startDay, $this->startTime);
		$endCompare = 0;

		/* We don't always have to compute endCompare.  For many instances,
		 * startCompare is enough to determine if we are in DST or not.  In the
		 * northern hemisphere, if we are before the start rule, we can't have
		 * DST.  In the southern hemisphere, if we are after the start rule, we
		 * must have DST.  This is reflected in the way the next if statement
		 * (not the one immediately following) short circuits. */
		if($southern != ($startCompare >= 0)) {
			$endCompare = self::compareToRule($month, $monthLength, $prevMonthLength, $day, $dayOfWeek, $millis,
																	$this->endTimeMode == self::WALL_TIME ? $this->dstSavings : ($this->endTimeMode == self::UTC_TIME ? -$this->rawOffset : 0),
																	$this->endMode, $this->endMonth, $this->endDayOfWeek, $this->endDay, $this->endTime);
		}

		// Check for both the northern and southern hemisphere cases.  We
		// assume that in the northern hemisphere, the start rule is before the
		// end rule within the calendar year, and vice versa for the southern
		// hemisphere.
		if((!$southern && ($startCompare >= 0 && $endCompare < 0)) || ($southern && ($startCompare >= 0 || $endCompare < 0)))
			$result += $this->dstSavings;

		return $result;
	}

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
	public function getRawOffset()
	{
		return $this->rawOffset;
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
	public function setRawOffset($offsetMillis)
	{
		$this->rawOffset = $offsetMillis;
	}

	/**
	 * Sets the amount of time in ms that the clock is advanced during DST.
	 * 
	 * @param      int The number of milliseconds the time is advanced with 
	 *                 respect to standard time when the daylight savings rules
	 *                 are in effect. A positive number, typically one hour 
	 *                 (3600000).
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project ({@link http://icu.sourceforge.net})
	 * @since      0.11.0
	 */
	public function setDSTSavings($millisSavedDuringDST)
	{
		if($millisSavedDuringDST <= 0) {
			throw new InvalidArgumentException('The amount must be a positive number');
		} else {
			$this->dstSavings = $millisSavedDuringDST;
		}
	}

	/**
	 * Returns the amount of time in ms that the clock is advanced during DST.
	 * 
	 * @return     int The number of milliseconds the time is advanced with 
	 *                 respect to standard time when the daylight savings rules
	 *                 are in effect. A positive number, typically one hour 
	 *                 (3600000).
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project ({@link http://icu.sourceforge.net})
	 * @since      0.11.0
	 */
	public function getDSTSavings()
	{
		return $this->dstSavings;
	}

	/**
	 * Queries if this TimeZone uses Daylight Savings Time.
	 *
	 * @return     bool True if this TimeZone uses Daylight Savings Time; 
	 *                  false otherwise.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project ({@link http://icu.sourceforge.net})
	 * @since      0.11.0
	 */
	public function useDaylightTime()
	{
		return $this->useDaylight;
	}

	/**
	 * Return true if this zone has the same rules and offset as another zone.
	 * 
	 * @param      AgaviTimeZone The TimeZone object to be compared with
	 * 
	 * @return     bool True if the given zone has the same rules and offset as 
	 *                  this one
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project ({@link http://icu.sourceforge.net})
	 * @since      0.11.0
	 */
	public function hasSameRules($other)
	{
		if($this === $other) {
			return true;
		}
		if(get_class($this) != get_class($other)) {
			return false;
		}

return true;
// TODO: implement properly
/*
		return $this->rawOffset     == $that->rawOffset &&
					 $this->useDaylight     == $that->useDaylight &&
					 (!$this->useDaylight
					 // Only check rules if using DST
					 || ($this->dstSavings     == $that->dstSavings &&
							 $this->startMode      == $that->startMode &&
							 $this->startMonth     == $that->startMonth &&
							 $this->startDay       == $that->startDay &&
							 $this->startDayOfWeek == $that->startDayOfWeek &&
							 $this->startTime      == $that->startTime &&
							 $this->startTimeMode  == $that->startTimeMode &&
							 $this->endMode        == $that->endMode &&
							 $this->endMonth       == $that->endMonth &&
							 $this->endDay         == $that->endDay &&
							 $this->endDayOfWeek   == $that->endDayOfWeek &&
							 $this->endTime        == $that->endTime &&
							 $this->endTimeMode    == $that->endTimeMode &&
							 $this->startYear      == $that->startYear));
*/
	}

	/**
	 * Constants specifying values of startMode and endMode.
	 */
	const DOM_MODE = 1;
	const DOW_IN_MONTH_MODE = 2;
	const DOW_GE_DOM_MODE = 3;
	const DOW_LE_DOM_MODE = 4;

	/**
	 * Internal construction method.
	 * @param      int The new SimpleTimeZone's raw GMT offset
	 * @param      int the month DST starts
	 * @param      int the day DST starts
	 * @param      int the DOW DST starts
	 * @param      int the time DST starts
	 * @param      int Whether the start time is local wall time, local
	 *                 standard time, or UTC time. Default is local wall time.
	 * @param      int the month DST ends
	 * @param      int the day DST ends
	 * @param      int the DOW DST ends
	 * @param      int the time DST ends
	 * @param      int Whether the end time is local wall time, local
	 *                 standard time, or UTC time. Default is local wall time.
	 * @param      int The number of milliseconds added to standard time
	 *                 to get DST time. Default is one hour.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project ({@link http://icu.sourceforge.net})
	 * @since      0.11.0
	 */
	private function construct($rawOffsetGMT, $startMonth, $startDay, $startDayOfWeek, $startTime, $startTimeMode, $endMonth, $endDay, $endDayOfWeek, $endTime, $endTimeMode, $dstSavings)
	{
		$this->rawOffset      = $rawOffsetGMT;
		$this->startMonth     = $startMonth;
		$this->startDay       = $startDay;
		$this->startDayOfWeek = $startDayOfWeek;
		$this->startTime      = $startTime;
		$this->startTimeMode  = $startTimeMode;
		$this->endMonth       = $endMonth;
		$this->endDay         = $endDay;
		$this->endDayOfWeek   = $endDayOfWeek;
		$this->endTime        = $endTime;
		$this->endTimeMode    = $endTimeMode;
		$this->dstSavings     = $dstSavings;
		$this->startYear      = 0;
		$this->startMode      = self::DOM_MODE;
		$this->endMode        = self::DOM_MODE;

		$this->decodeRules();

		if($dstSavings <= 0) {
			throw new InvalidArgumentException('The DST savings amount must be a positive number');
		}
	}

	/**
	 * Compare a given date in the year to a rule. Return 1, 0, or -1, depending
	 * on whether the date is after, equal to, or before the rule date. The
	 * millis are compared directly against the ruleMillis, so any
	 * standard-daylight adjustments must be handled by the caller.
	 *
	 * @return     int 1 if the date is after the rule date, -1 if the date is 
	 *                 before the rule date, or 0 if the date is equal to the rule
	 *                 date.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project ({@link http://icu.sourceforge.net})
	 * @since      0.11.0
	 */
	private static function compareToRule($month, $monthLen, $prevMonthLen, $dayOfMonth, $dayOfWeek, $millis, $millisDelta, $ruleMode, $ruleMonth,  $ruleDayOfWeek, $ruleDay, $ruleMillis)
	{
		// Make adjustments for startTimeMode and endTimeMode
		$millis += $millisDelta;
		while($millis >= AgaviDateDefinitions::MILLIS_PER_DAY) {
			$millis -= AgaviDateDefinitions::MILLIS_PER_DAY;
			++$dayOfMonth;
			$dayOfWeek = 1 + ($dayOfWeek % 7); // dayOfWeek is one-based
			if($dayOfMonth > $monthLen) {
				$dayOfMonth = 1;
				/* When incrementing the month, it is desirible to overflow
				 * from DECEMBER to DECEMBER+1, since we use the result to
				 * compare against a real month. Wraparound of the value
				 * leads to bug 4173604. */
				++$month;
			}
		}
		while($millis < 0) {
			$millis += AgaviDateDefinitions::MILLIS_PER_DAY;
			--$dayOfMonth;
			$dayOfWeek = 1 + (($dayOfWeek + 5) % 7); // dayOfWeek is one-based
			if($dayOfMonth < 1) {
				$dayOfMonth = $prevMonthLen;
				--$month;
			}
		}

		// first compare months. If they're different, we don't have to worry about 
		// days and times
		if($month < $ruleMonth) {
			return -1;
		} elseif($month > $ruleMonth) {
			return 1;
		}

		// calculate the actual day of month for the rule
		$ruleDayOfMonth = 0;
		// Adjust the ruleDay to the monthLen, for non-leap year February 29 rule days.
		if($ruleDay > $monthLen) {
			$ruleDay = $monthLen;
		}

		switch($ruleMode) {
			// if the mode is day-of-month, the day of month is given
			case self::DOM_MODE:
				$ruleDayOfMonth = $ruleDay;
				break;

			// if the mode is day-of-week-in-month, calculate the day-of-month from it
			case self::DOW_IN_MONTH_MODE:
				// In this case ruleDay is the day-of-week-in-month (this code is using
				// the dayOfWeek and dayOfMonth parameters to figure out the day-of-week
				// of the first day of the month, so it's trusting that they're really
				// consistent with each other)
				if($ruleDay > 0) {
					$ruleDayOfMonth = 1 + ($ruleDay - 1) * 7 + (7 + $ruleDayOfWeek - ($dayOfWeek - $dayOfMonth + 1)) % 7;

					// if ruleDay is negative (we assume it's not zero here), we have to 
					// do the same calculation figuring backward from the last day of the 
					// month.
				} else {
					// (again, this code is trusting that dayOfWeek and dayOfMonth are
					// consistent with each other here, since we're using them to figure
					// the day of week of the first of the month)
					$ruleDayOfMonth = $monthLen + ($ruleDay + 1) * 7 - (7 + ($dayOfWeek + $monthLen - $dayOfMonth) - $ruleDayOfWeek) % 7;
				}
				break;

			case self::DOW_GE_DOM_MODE:
				$ruleDayOfMonth = $ruleDay + (49 + $ruleDayOfWeek - $ruleDay - $dayOfWeek + $dayOfMonth) % 7;
				break;

			case self::DOW_LE_DOM_MODE:
				$ruleDayOfMonth = $ruleDay - (49 - $ruleDayOfWeek + $ruleDay + $dayOfWeek - $dayOfMonth) % 7;
				// Note at this point ruleDayOfMonth may be <1, although it will
				// be >=1 for well-formed rules.
				break;
		}

		// now that we have a real day-in-month for the rule, we can compare days...
		if($dayOfMonth < $ruleDayOfMonth) {
			return -1;
		} elseif($dayOfMonth > $ruleDayOfMonth) {
			return 1;
		}

		// ...and if they're equal, we compare times
		if($millis < $ruleMillis) {
			return -1;
		} elseif($millis > $ruleMillis) {
			return 1;
		} else {
			return 0;
		}
	}




	//----------------------------------------------------------------------
	// Rule representation
	//
	// We represent the following flavors of rules:
	//       5        the fifth of the month
	//       lastSun  the last Sunday in the month
	//       lastMon  the last Monday in the month
	//       Sun>=8   first Sunday on or after the eighth
	//       Sun<=25  last Sunday on or before the 25th
	// This is further complicated by the fact that we need to remain
	// backward compatible with the 1.1 FCS.  Finally, we need to minimize
	// API changes.  In order to satisfy these requirements, we support
	// three representation systems, and we translate between them.
	//
	// INTERNAL REPRESENTATION
	// This is the format SimpleTimeZone objects take after construction or
	// streaming in is complete.  Rules are represented directly, using an
	// unencoded format.  We will discuss the start rule only below; the end
	// rule is analogous.
	//   startMode      Takes on enumerated values DAY_OF_MONTH,
	//                  DOW_IN_MONTH, DOW_AFTER_DOM, or DOW_BEFORE_DOM.
	//   startDay       The day of the month, or for DOW_IN_MONTH mode, a
	//                  value indicating which DOW, such as +1 for first,
	//                  +2 for second, -1 for last, etc.
	//   startDayOfWeek The day of the week.  Ignored for DAY_OF_MONTH.
	//
	// ENCODED REPRESENTATION
	// This is the format accepted by the constructor and by setStartRule()
	// and setEndRule().  It uses various combinations of positive, negative,
	// and zero values to encode the different rules.  This representation
	// allows us to specify all the different rule flavors without altering
	// the API.
	//   MODE              startMonth    startDay    startDayOfWeek
	//   DOW_IN_MONTH_MODE >=0           !=0         >0
	//   DOM_MODE          >=0           >0          ==0
	//   DOW_GE_DOM_MODE   >=0           >0          <0
	//   DOW_LE_DOM_MODE   >=0           <0          <0
	//   (no DST)          don't care    ==0         don't care
	//
	// STREAMED REPRESENTATION
	// We must retain binary compatibility with the 1.1 FCS.  The 1.1 code only
	// handles DOW_IN_MONTH_MODE and non-DST mode, the latter indicated by the
	// flag useDaylight.  When we stream an object out, we translate into an
	// approximate DOW_IN_MONTH_MODE representation so the object can be parsed
	// and used by 1.1 code.  Following that, we write out the full
	// representation separately so that contemporary code can recognize and
	// parse it.  The full representation is written in a "packed" format,
	// consisting of a version number, a length, and an array of bytes.  Future
	// versions of this class may specify different versions.  If they wish to
	// include additional data, they should do so by storing them after the
	// packed representation below.
	//----------------------------------------------------------------------


	/**
	 * Given a set of encoded rules in startDay and startDayOfMonth, decode
	 * them and set the startMode appropriately.  Do the same for endDay and
	 * endDayOfMonth.
	 * <P>
	 * Upon entry, the day of week variables may be zero or
	 * negative, in order to indicate special modes.  The day of month
	 * variables may also be negative.
	 * <P>
	 * Upon exit, the mode variables will be
	 * set, and the day of week and day of month variables will be positive.
	 * <P>
	 * This method also recognizes a startDay or endDay of zero as indicating
	 * no DST.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project ({@link http://icu.sourceforge.net})
	 * @since      0.11.0
	 */
	private function decodeRules()
	{
		$this->decodeStartRule();
		$this->decodeEndRule();
	}

	/**
	 * Decode the start rule and validate the parameters.  The parameters are
	 * expected to be in encoded form, which represents the various rule modes
	 * by negating or zeroing certain values.  Representation formats are:
	 * <p>
	 * <pre>
	 *            DOW_IN_MONTH  DOM    DOW>=DOM  DOW<=DOM  no DST
	 *            ------------  -----  --------  --------  ----------
	 * month       0..11        same    same      same     don't care
	 * day        -5..5         1..31   1..31    -1..-31   0
	 * dayOfWeek   1..7         0      -1..-7    -1..-7    don't care
	 * time        0..ONEDAY    same    same      same     don't care
	 * </pre>
	 * The range for month does not include UNDECIMBER since this class is
	 * really specific to GregorianCalendar, which does not use that month.
	 * The range for time includes ONEDAY (vs. ending at ONEDAY-1) because the
	 * end rule is an exclusive limit point.  That is, the range of times that
	 * are in DST include those >= the start and < the end.  For this reason,
	 * it should be possible to specify an end of ONEDAY in order to include the
	 * entire day.  Although this is equivalent to time 0 of the following day,
	 * it's not always possible to specify that, for example, on December 31.
	 * While arguably the start range should still be 0..ONEDAY-1, we keep
	 * the start and end ranges the same for consistency.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project ({@link http://icu.sourceforge.net})
	 * @since      0.11.0
	 */
	private function decodeStartRule() 
	{
		$this->useDaylight = (($this->startDay != 0) && ($this->endDay != 0) ? true : false);
		if($this->useDaylight && $this->dstSavings == 0) {
			$this->dstSavings = AgaviDateDefinitions::MILLIS_PER_HOUR;
		}
		if($this->startDay != 0) {
			if($this->startMonth < AgaviDateDefinitions::JANUARY || $this->startMonth > AgaviDateDefinitions::DECEMBER) {
				throw new InvalidArgumentException('startMonth out of range');
			}
			if($this->startTime < 0 || $this->startTime > AgaviDateDefinitions::MILLIS_PER_DAY || $this->startTimeMode < self::WALL_TIME || $this->startTimeMode > self::UTC_TIME) {
				throw new InvalidArgumentException('startTime out of range');
			}
			if($this->startDayOfWeek == 0) {
				$this->startMode = self::DOM_MODE;
			} else {
				if($this->startDayOfWeek > 0) {
					$this->startMode = self::DOW_IN_MONTH_MODE;
				} else {
					$this->startDayOfWeek = -$this->startDayOfWeek;
					if($this->startDay > 0) {
						$this->startMode = self::DOW_GE_DOM_MODE;
					} else {
						$this->startDay = -$this->startDay;
						$this->startMode = self::DOW_LE_DOM_MODE;
					}
				}
				if($this->startDayOfWeek > AgaviDateDefinitions::SATURDAY) {
					throw new InvalidArgumentException('startDayOfWeek out of range');
				}
			}
			if($this->startMode == self::DOW_IN_MONTH_MODE) {
				if($this->startDay < -5 || $this->startDay > 5) {
					throw new AgaviException('startDay out of range');
				}
			} elseif($this->startDay < 1 || $this->startDay > self::$STATICMONTHLENGTH[$this->startMonth]) {
				throw new InvalidArgumentException('startDay out of range');
			}
		}
	}


	/**
	 * Decode the end rule and validate the parameters.  This method is exactly
	 * analogous to decodeStartRule().
	 *
	 * @see        AgaviSimpleTimeZone::decodeStartRule
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     The ICU Project ({@link http://icu.sourceforge.net})
	 * @since      0.11.0
	 */
	private function decodeEndRule() 
	{
		$this->useDaylight = (($this->startDay != 0) && ($this->endDay != 0) ? true : false);
		if($this->useDaylight && $this->dstSavings == 0) {
			$this->dstSavings = AgaviDateDefinitions::MILLIS_PER_HOUR;
		}
		if($this->endDay != 0) {
			if($this->endMonth < AgaviDateDefinitions::JANUARY || $this->endMonth > AgaviDateDefinitions::DECEMBER) {
				throw new InvalidArgumentException('endMonth out of range');
			}
			if($this->endTime < 0 || $this->endTime > AgaviDateDefinitions::MILLIS_PER_DAY || $this->endTimeMode < self::WALL_TIME || $this->endTimeMode > self::UTC_TIME) {
				throw new InvalidArgumentException('endTime out of range');
			}
			if($this->endDayOfWeek == 0) {
				$this->endMode = self::DOM_MODE;
			} else {
				if($this->endDayOfWeek > 0) {
					$this->endMode = self::DOW_IN_MONTH_MODE;
				} else {
					$this->endDayOfWeek = -$this->endDayOfWeek;
					if($this->endDay > 0) {
						$this->endMode = self::DOW_GE_DOM_MODE;
					} else {
						$this->endDay = -$this->endDay;
						$this->endMode = self::DOW_LE_DOM_MODE;
					}
				}
				if($this->endDayOfWeek > AgaviDateDefinitions::SATURDAY) {
					throw new InvalidArgumentException('endDayOfWeek out of range');
				}
			}
			if($this->endMode == self::DOW_IN_MONTH_MODE) {
				if($this->endDay < -5 || $this->endDay > 5) {
					throw new InvalidArgumentException('endDay out of range');
				}
			} elseif($this->endDay < 1 || $this->endDay > self::$STATICMONTHLENGTH[$this->endMonth]) {
				throw new InvalidArgumentException('endDay out of range');
			}
		}
	}

	private $startMonth, $startDay, $startDayOfWeek;   // the month, day, DOW, and time DST starts
	private $startTime;
	private $startTimeMode, $endTimeMode; // Mode for startTime, endTime; see TimeMode
	private $endMonth, $endDay, $endDayOfWeek; // the month, day, DOW, and time DST ends
	private $endTime;
	private $startYear;  // the year these DST rules took effect
	private $rawOffset;  // the TimeZone's raw GMT offset
	private $useDaylight; // flag indicating whether this TimeZone uses DST
	private static $STATICMONTHLENGTH = array(31, 29, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
	private $startMode, $endMode;   // flags indicating what kind of rules the DST rules are

	/**
	 * A positive value indicating the amount of time saved during DST in ms.
	 * Typically one hour; sometimes 30 minutes.
	 */
	private $dstSavings;
}

?>
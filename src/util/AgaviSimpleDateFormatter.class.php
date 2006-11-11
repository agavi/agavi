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
 * The simple date formatter will format numbers according to a given format
 *
 * @package    agavi
 * @subpackage util
 *
 * @author     Dominik del Bondio <ddb@bitxtender.com>
 * @author     Agavi Project <info@agavi.org>
 * @copyright  (c) Authors
 * @since      0.11.0
 *
 * @version    $Id$
 */
class AgaviSimpleDateFormatter
{
	/**
	 * @var        string The format string which will be given to sprintf
	 */
	protected $formatString = '';

	/**
	 * Constructs a new date formatter.
	 *
	 * @param      string Format to be used for formatting.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function __construct($format = null)
	{
		if($format) {
			$this->setFormat($format);
		}
	}

	const T_TEXT                  = -1;

	const T_ERA                   = 0;
	const T_YEAR                  = 1;
	const T_MONTH                 = 2;
	const T_DATE                  = 3;
	const T_HOUR_1_24             = 4;
	const T_HOUR_0_23             = 5;
	const T_MINUTE                = 6;
	const T_SECOND                = 7;
	const T_FRACTIONAL_SECOND     = 8;
	const T_DAY_OF_WEEK           = 9;
	const T_DAY_OF_YEAR           = 10;
	const T_DAY_OF_WEEK_IN_MONTH  = 11;
	const T_WEEK_OF_YEAR          = 12;
	const T_WEEK_OF_MONTH         = 13;
	const T_AM_PM                 = 14;
	const T_HOUR_1_12             = 15;
	const T_HOUR_0_11             = 16;
	const T_TIMEZONE              = 17;
	const T_ISO_YEAR              = 18;
	const T_LOCAL_DAY_OF_WEEK     = 19;
	const T_EXTENDED_YEAR         = 20;
	const T_MODIFIED_JD           = 21;
	const T_MS_IN_DAY             = 22;
	const T_TIMEZONE_RFC          = 23;
	const T_TIMEZONE_WALL         = 24;
	const T_SA_LOCAL_DAY_OF_WEEK  = 25;
	const T_SA_MONTH              = 26;
	const T_QUARTER               = 27;
	const T_SA_QUARTER            = 28;

	/**
	 * @var        array The default mapping of format characters to their 
	 *                   meanings.
	 */
	protected static $defaultMap = array(
		'G' => self::T_ERA,
		'y' => self::T_YEAR,
		'M' => self::T_MONTH,
		'd' => self::T_DATE,
		'k' => self::T_HOUR_1_24,
		'H' => self::T_HOUR_0_23,
		'm' => self::T_MINUTE,
		's' => self::T_SECOND,
		'S' => self::T_FRACTIONAL_SECOND,
		'E' => self::T_DAY_OF_WEEK,
		'D' => self::T_DAY_OF_YEAR,
		'F' => self::T_DAY_OF_WEEK_IN_MONTH,
		'w' => self::T_WEEK_OF_YEAR,
		'W' => self::T_WEEK_OF_MONTH,
		'a' => self::T_AM_PM,
		'h' => self::T_HOUR_1_12,
		'K' => self::T_HOUR_0_11,
		'z' => self::T_TIMEZONE,
		'Y' => self::T_ISO_YEAR,
		'e' => self::T_LOCAL_DAY_OF_WEEK,
		'u' => self::T_EXTENDED_YEAR,
		'g' => self::T_MODIFIED_JD,
		'A' => self::T_MS_IN_DAY,
		'Z' => self::T_TIMEZONE_RFC,
		'v' => self::T_TIMEZONE_WALL,
		'c' => self::T_SA_LOCAL_DAY_OF_WEEK,
		'L' => self::T_SA_MONTH,
		'Q' => self::T_QUARTER,
		'q' => self::T_SA_QUARTER,
	);

	/**
	 * @var        array The list of tokens in the format.
	 */
	protected $tokenList;

	/**
	 * Sets the format which should be used.
	 *
	 * @param      string Format to be used for formatting.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function setFormat($format)
	{
		$this->internalParseFormat($format, self::$defaultMap);
	}

	/**
	 * Sets the format which should be used. This will use the the format 
	 * characters specified in the locale instead the default ones. 
	 *
	 * NOTE: this function is not implemented yet!
	 *
	 * @param      string Format to be used for formatting.
	 * @param      AgaviLocale The locale.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function setLocalizedFormat($format, AgaviLocale $locale)
	{

	}

	/**
	 * Formats a given date.
	 *
	 * @param      mixed The date. This can either be an array containing all the
	 *                   needed info with the AgaviDateDefinitions constants as 
	 *                   keys or an unix timestamp (doesn't work yet!) or an
	 *                   AgaviCalendar instance.
	 * @param      string The calendar type this date should be formatted in 
	 *                    (this will usually be gregorian)
	 * @param      AgaviLocale The locale to format the date in.
	 *
	 * @return     string The formatted date.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function format($data, $calendarType, $locale)
	{
		$tzid = null;
		if($data instanceof AgaviCalendar) {
			$tzid = $data->getTimeZone()->getId();
			$data = $data->getAll();
		} elseif(!is_array($data)) {
			throw new AgaviException('Invalid argument ' . $data);
		}
		$out = '';

		foreach($this->tokenList as $token) {
			$count = $token[1];
			switch($token[0]) {
				case self::T_TEXT:
					$out .= $token[1];
					break;

				case self::T_ERA:
					$era = $data[AgaviDateDefinitions::ERA];
					if($count == 4) {
						$out .= $locale->getCalendarEraWide($calendarType, $era);
					} elseif($count == 5) {
						$out .= $locale->getCalendarEraNarrow($calendarType, $era);
					} else {
						$out .= $locale->getCalendarEraAbbreviated($calendarType, $era);
					}
					break;

				case self::T_YEAR:
					$year = $data[AgaviDateDefinitions::YEAR];
					if($count == 2) {
						// strip year to 2 chars
						$year = $year % 100;
					}
					$out .= str_pad($year, $count, '0', STR_PAD_LEFT);
					break;

				case self::T_MONTH:
				case self::T_SA_MONTH:
					$month = $data[AgaviDateDefinitions::MONTH] + 1;
					if($count == 3) {
						$out .= $locale->getCalendarMonthAbbreviated($calendarType, $month);
					} elseif($count == 4) {
						$out .= $locale->getCalendarMonthWide($calendarType, $month);
					} elseif($count == 5) {
						$out .= $locale->getCalendarMonthNarrow($calendarType, $month);
					} else {
						$out .= str_pad($month, $count, '0', STR_PAD_LEFT);
					}
					break;

				case self::T_DATE:
					$out .= str_pad($data[AgaviDateDefinitions::DATE], $count, '0', STR_PAD_LEFT);
					break;

				case self::T_HOUR_1_24:
					$out .= str_pad($data[AgaviDateDefinitions::HOUR_OF_DAY] + 1, $count, '0', STR_PAD_LEFT);
					break;

				case self::T_HOUR_0_23:
					$out .= str_pad($data[AgaviDateDefinitions::HOUR_OF_DAY], $count, '0', STR_PAD_LEFT);
					break;

				case self::T_MINUTE:
					$out .= str_pad($data[AgaviDateDefinitions::MINUTE], $count, '0', STR_PAD_LEFT);
					break;

				case self::T_SECOND:
					$out .= str_pad($data[AgaviDateDefinitions::SECOND], $count, '0', STR_PAD_LEFT);
					break;

				case self::T_FRACTIONAL_SECOND:
					$out .= substr($data[AgaviDateDefinitions::MILLISECOND], 0, $count);
					break;

				case self::T_DAY_OF_WEEK:
					$dow = $data[AgaviDateDefinitions::DAY_OF_WEEK];
					if($count == 4) {
						$out .= $locale->getCalendarDayWide($calendarType, $dow);
					} elseif($count == 5) {
						$out .= $locale->getCalendarDayNarrow($calendarType, $dow);
					} else {
						$out .= $locale->getCalendarDayAbbreviated($calendarType, $dow);
					}
					break;

				case self::T_DAY_OF_YEAR:
					$out .= str_pad($data[AgaviDateDefinitions::DAY_OF_YEAR], $count, '0', STR_PAD_LEFT);
					break;

				case self::T_DAY_OF_WEEK_IN_MONTH:
					$out .= str_pad($data[AgaviDateDefinitions::DAY_OF_WEEK_IN_MONTH], $count, '0', STR_PAD_LEFT);
					break;

				case self::T_WEEK_OF_YEAR:
					$out .= str_pad($data[AgaviDateDefinitions::WEEK_OF_YEAR], $count, '0', STR_PAD_LEFT);
					break;

				case self::T_WEEK_OF_MONTH:
					$out .= str_pad($data[AgaviDateDefinitions::WEEK_OF_MONTH], $count, '0', STR_PAD_LEFT);
					break;

				case self::T_AM_PM:
					$isPm = $data[AgaviDateDefinitions::AM_PM];
					if($isPm) {
						$out .= $locale->getCalendarPm($calendarType);
					} else {
						$out .= $locale->getCalendarAm($calendarType);
					}
					break;

				case self::T_HOUR_1_12:
					$out .= str_pad($data[AgaviDateDefinitions::HOUR] + 1, $count, '0', STR_PAD_LEFT);
					break;

				case self::T_HOUR_0_11:
					$out .= str_pad($data[AgaviDateDefinitions::HOUR], $count, '0', STR_PAD_LEFT);
					break;

				case self::T_TIMEZONE:
				case self::T_TIMEZONE_WALL:
					if(!$tzid) {
						$out .= $this->getGmtZoneString($data);
					} else {

						$displayString = '';

						if($token[0] == self::T_TIMEZONE_WALL) {
							if($count < 4) {
								$displayString = $locale->getTimeZoneShortGenericName($tzid);
							} else {
								$displayString = $locale->getTimeZoneLongGenericName($tzid);
							}
						} else {
							if($data[AgaviDateDefinitions::DST_OFFSET] != 0) {
								if($count < 4) {
									$displayString = $locale->getTimeZoneShortDaylightName($tzid);
								} else {
									$displayString = $locale->getTimeZoneLongDaylightName($tzid);
								}
							} else {
								if($count < 4) {
									$displayString = $locale->getTimeZoneShortStandardName($tzid);
								} else {
									$displayString = $locale->getTimeZoneLongStandardName($tzid);
								}
							}
						}

						if(!$displayString) {
							$out .= $this->getGmtZoneString($data);
						} else {
							$out .= $displayString;
						}

					}
					break;

				case self::T_ISO_YEAR:
					$out .= str_pad($data[AgaviDateDefinitions::YEAR_WOY], $count, '0', STR_PAD_LEFT);
					break;

				case self::T_LOCAL_DAY_OF_WEEK:
				case self::T_SA_LOCAL_DAY_OF_WEEK:
					$dow = $data[AgaviDateDefinitions::DOW_LOCAL];
					if($count == 4) {
						$out .= $locale->getCalendarDayWide($calendarType, $dow);
					} elseif($count == 5) {
						$out .= $locale->getCalendarDayNarrow($calendarType, $dow);
					} elseif($count == 3) {
						$out .= $locale->getCalendarDayAbbreviated($calendarType, $dow);
					} else {
						$out .= str_pad($dow, $count, '0', STR_PAD_LEFT);
					}
					break;

				case self::T_EXTENDED_YEAR:
					$out .= str_pad($data[AgaviDateDefinitions::EXTENDED_YEAR], $count, '0', STR_PAD_LEFT);
					break;

				case self::T_MODIFIED_JD:
					$out .= str_pad($data[AgaviDateDefinitions::JULIAN_DAY], $count, '0', STR_PAD_LEFT);
					break;

				case self::T_MS_IN_DAY:
					$out .= str_pad($data[AgaviDateDefinitions::MILLISECONDS_IN_DAY], $count, '0', STR_PAD_LEFT);
					break;

				case self::T_TIMEZONE_RFC:
					$sign = '+';

					$value = ($data[AgaviDateDefinitions::ZONE_OFFSET] + $data[AgaviDateDefinitions::DST_OFFSET]) / AgaviDateDefinitions::MILLIS_PER_MINUTE;
					if($value < 0) {
						$value = -$value;
						$sign = '-';
					}

					$value = ($value / 3) * 5 + ($value % 60); // minutes => KKmm
					$out .= $sign . str_pad($value, 4, '0', STR_PAD_LEFT);
					break;

				case self::T_QUARTER:
				case self::T_SA_QUARTER:
					$quarter = intval($data[AgaviDateDefinitions::MONTH] / 3);
					if($count == 3) {
						$out .= $locale->getCalendarQuarterAbbreviated($calendarType, $quarter);
					} elseif($count == 4) {
						$out .= $locale->getCalendarQuarterWide($calendarType, $quarter);
					} else {
						$out .= str_pad($quarter, $count, '0', STR_PAD_LEFT);
					}
					break;
			}
		}

		return $out;
	}

	/**
	 * Returns the GMT-+hhmm string for a the offset given in data.
	 *
	 * @param      array Date information.
	 *
	 * @return     string The timezone string.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	protected function getGmtZoneString($data)
	{
		$value = $data[AgaviDateDefinitions::ZONE_OFFSET] + $data[AgaviDateDefinitions::DST_OFFSET];

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
	 * Parses the format with the given character to token map.
	 *
	 * @param      string The format to parse.
	 * @param      array  The character to token map.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	protected function internalParseFormat($format, $charToTokenMap)
	{
		$this->tokenList = array();
		$tokenIdx = 0;

		$escapeStr = '';

		$inEscape = false;
		$fLen = strlen($format);
		for($i = 0; $i < $fLen; ++$i) {
			$c = $format[$i];
			$cNext = ($i + 1 < $fLen) ? $format[$i+1] : 0;

			if($inEscape) {
				if($c == '\'') {
					if($cNext == '\'') {
						$escapeStr .= '\'';
						++$i;
					} else {
						$inEscape = false;
						$this->tokenList[$tokenIdx] = array(self::T_TEXT, $escapeStr);
						++$tokenIdx;
						$escapeStr = '';
					}
				} else {
					$escapeStr .= $c;
				}
			} else {
				if($c == '\'') {
					if($cNext == '\'') {
						$this->tokenList[$tokenIdx] = array(self::T_TEXT, $c);
						++$tokenIdx;
						++$i;
					} else {
						$escapeStr = '';
						$inEscape = true;
					}
				} else {
					if(preg_match('#[a-z]#i', $c)) {
						if(isset($charToTokenMap[$c])) {
							$tok = $charToTokenMap[$c];
							if($tokenIdx > 0 && $this->tokenList[$tokenIdx - 1][0] == $tok) {
								// increase the tokencount if the last token was the same as this 
								++$this->tokenList[$tokenIdx - 1][1];
							} else {
								$this->tokenList[$tokenIdx] = array($tok, 1);
								++$tokenIdx;
							}
						} else {
							throw new AgaviException('Unknown pattern char ' . ord($c));
						}
					} else {
						$this->tokenList[$tokenIdx] = array(self::T_TEXT, $c);
						++$tokenIdx;
					}
				}
			}
		}
		if($escapeStr) {
			$this->tokenList[$tokenIdx] = array(self::T_TEXT, $escapeStr);
			++$tokenIdx;
		}
	}

}

?>
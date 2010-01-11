<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2005-2010 the Agavi Project.                                |
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
 * This class lets your format date and time according to a given format 
 * definition.
 *
 * @package    agavi
 * @subpackage util
 *
 * @author     Dominik del Bondio <ddb@bitxtender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.11.0
 *
 * @version    $Id$
 */
class AgaviDateFormat
{
	/**
	 * @var        string The format string given by the user
	 */
	protected $originalFormatString = null;

	/**
	 * @var        string The format string which will be given to sprintf
	 */
	protected $formatString = '';

	/**
	 * @var        AgaviContext An AgaviContext instance.
	 */
	protected $context = null;

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
	
	/**
	 * Initialize this Format.
	 *
	 * @param      AgaviContext The current application context.
	 * @param      array        An associative array of initialization parameters
	 *
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      1.0.0
	 */
	public function initialize(AgaviContext $context, array $parameters = array())
	{
		$this->context = $context;
	}
	
	/**
	 * Retrieve the current application context.
	 *
	 * @return     AgaviContext The current Context instance.
	 *
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      1.0.0
	 */
	public final function getContext()
	{
		return $this->context;
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
	const T_TIMEZONE_GENERIC      = 24;
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
		'v' => self::T_TIMEZONE_GENERIC,
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
	 * Returns the format which is currently used to format dates.
	 *
	 * @return     string The current format.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getFormat()
	{
		return $this->originalFormatString;
	}

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
	 * @param      AgaviLocale|string The locale to format the date in.
	 *
	 * @return     string The formatted date.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function format($data, $calendarType = AgaviCalendar::GREGORIAN, $locale = null)
	{
		if($locale instanceof AgaviLocale) {
			$tm = $locale->getContext()->getTranslationManager();
		} elseif($this->context) {
			$tm = $this->context->getTranslationManager();
			if($locale) {
				$locale = $tm->getLocale($locale);
			} else {
				$locale = $tm->getCurrentLocale();
			}
		} else {
			throw new InvalidArgumentException('This AgaviDateFormat has not been initialize()d. To be able to pass a string as locale you need to call initialize() or create this AgaviDateFormat using AgaviTranslationManager::createDateFormat()');
		}
		
		$tzid = null;
		if($locale->getLocaleTimeZone()) {
			// use the timezone from the current/provided locale if available
			$tzid = $tm->resolveTimeZoneId($locale->getLocaleTimeZone());
		} else {
			// otherwise use the default timezone
			$tzid = $tm->getDefaultTimeZone()->getResolvedId();
		}
		
		$cal = null;
		// remember if cloning the calendar is required on changes
		// (this is only true if the calendar was passed to this method and not
		// implicitly created)
		$calNeedsClone = false;
		if($data instanceof AgaviCalendar) {
			$cal = $data;
			$calNeedsClone = true;
		} elseif($data instanceof DateTime) {
			$cal = $this->context->getTranslationManager()->createCalendar($data);
		} elseif(is_int($data)) {
			$cal = $this->context->getTranslationManager()->createCalendar($locale);
			$cal->setUnixTimestamp($data);
		} elseif(is_array($data)) {
			$cal = $tm->createCalendar();
			$cal->fromArray($data);
		} else {
			// $data is most likely a string a this point (or something which can be 
			// implicitly converted to a string, so there is no explicit is_string check)
			try {
				// maybe it is a date/time string we can parse...
				$cal = $tm->createCalendar(new DateTime($data));
			} catch(Exception $e) {
				// err... no, it isn't. try to use the message as a calendar name
				$cal = $tm->createCalendar($data);
			}
		}
		
		if($tzid != $cal->getTimeZone()->getResolvedId()) {
			// the requested timezone for use in the output differs from the timezone
			// set in the calendar: make the calendar reside in the new timezone
			// so it calculates the correct date values for that timezone
			
			if($calNeedsClone) {
				$cal = clone $cal;
			}
			$cal->setTimeZone($tm->createTimeZone($tzid));
		}
		
		$data = $cal->toArray();
		
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
					$hour = $data[AgaviDateDefinitions::HOUR_OF_DAY];
					$out .= str_pad($hour == 0 ? 24 : $hour, $count, '0', STR_PAD_LEFT);
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
					$value = str_pad($data[AgaviDateDefinitions::MILLISECOND], 3, '0', STR_PAD_LEFT);
					$value = substr($value, 0, $count);
					$out .= $value;
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
					$hour = $data[AgaviDateDefinitions::HOUR];
					$out .= str_pad($hour == 0 ? 12 : $hour, $count, '0', STR_PAD_LEFT);
					break;

				case self::T_HOUR_0_11:
					$out .= str_pad($data[AgaviDateDefinitions::HOUR], $count, '0', STR_PAD_LEFT);
					break;

				case self::T_TIMEZONE:
				case self::T_TIMEZONE_GENERIC:
					if(!$tzid) {
						$out .= $this->getGmtZoneString($data);
					} else {

						$displayString = '';

						if($token[0] == self::T_TIMEZONE_GENERIC) {
							if($count < 4) {
								$displayString = $locale->getTimeZoneShortGenericName($tzid);
							} else {
								$displayString = $locale->getTimeZoneLongGenericName($tzid);
							}
							// if we don't have the generic data available
							if(!$displayString && strlen($tzid) > 2 && strpos($tzid, '/') !== false && strncmp($tzid, 'Etc', 3) != 0) {
								$hasMultiple = false;
								$territory = $tm->getTimeZoneTerritory($tzid, $hasMultiple);
								// TODO: there are lots of rules in the ldml spec which could be covered here too
								$displayString = $tzid;
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
					if($count > 3) {
						$out .= $this->getGmtZoneString($data);
					} else {
						$sign = '+';

						$value = ($data[AgaviDateDefinitions::ZONE_OFFSET] + $data[AgaviDateDefinitions::DST_OFFSET]) / AgaviDateDefinitions::MILLIS_PER_MINUTE;
						if($value < 0) {
							$value = -$value;
							$sign = '-';
						}

						$value = ($value / 3) * 5 + ($value % 60); // minutes => KKmm
						$out .= $sign . str_pad($value, 4, '0', STR_PAD_LEFT);
					}
					break;

				case self::T_QUARTER:
				case self::T_SA_QUARTER:
					$quarter = (int)($data[AgaviDateDefinitions::MONTH] / 3);
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
	protected function getGmtZoneString(array $data)
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
	protected function internalParseFormat($format, array $charToTokenMap)
	{
		if($this->originalFormatString == $format) {
			return;
		}
		$this->originalFormatString = $format;

		$this->tokenList = array();
		$tokenIdx = 0;

		$escapeStr = '';

		$inEscape = false;
		$fLen = strlen($format);
		for($i = 0; $i < $fLen; ++$i) {
			$c = $format[$i];
			$cNext = ($i + 1 < $fLen) ? $format[$i+1] : '';

			if($inEscape) {
				if($c == '\'') {
					if('\'' == $cNext) {
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
							throw new AgaviException('Unknown pattern char "' . $c . '" (#'. ord($c) . ') in format string "' . $format . '" at index ' . $i);
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

	/**
	 * Parses a string into an calendar object. Note that this doesn't fallback to
	 * the English locale.
	 *
	 * @param      string The string containing the date.
	 * @param      mixed The locale name or instance which should be used for
	 *                   parsing local day, month, etc names.
	 * @param      bool Whether the parsing should be strict. (not allowing 
	 *                  numbers to exceed the defined length, not allowing missing
	 *                  additional parts). The returned calendar object will be 
	 *                  non-lenient.
	 *
	 * @return     AgaviCalendar The calendar object.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function parse($dateString, $locale = null, $strict = false)
	{
		if($locale instanceof AgaviLocale) {
			$tm = $locale->getContext()->getTranslationManager();
		} elseif($this->context) {
			$tm = $this->context->getTranslationManager();
			if($locale) {
				$locale = $tm->getLocale($locale);
			} else {
				$locale = $tm->getCurrentLocale();
			}
		} else {
			throw new InvalidArgumentException('This AgaviDateFormat has not been initialize()d. To be able to pass a string as locale you need to call initialize() or create this AgaviDateFormat using AgaviTranslationManager::createDateFormat()');
		}
		
		$cal = $tm->createCalendar($locale);
		// we need to extract the era from the current date and set that 
		// era after reinitialising the calendar because if this is not 
		// done all dates would be BC instead of AD
		$era = $cal->get(AgaviDateDefinitions::ERA);
		$cal->clear();
		$cal->set(AgaviDateDefinitions::ERA, $era);

		if($strict) {
			$cal->setLenient(false);
		}

		// TODO: let user chose calendar type when more calendars exist
		$calendarType = AgaviCalendar::GREGORIAN;
		$datePos = 0;

		$unprocessedTokens = array();
		$data = array();

		$tlCount = count($this->tokenList);
		for($i = 0; $i < $tlCount; ++$i) {
			if($datePos >= strlen($dateString)) {
				if($strict) {
					throw new AgaviException('Input string "' . $dateString . '" is to short');
				}
				break;
			}

			$token = $this->tokenList[$i];
			$type = $this->getTokenType($token);
			// this and the next token are numbers
			if($type == 'number' && $i + 1 < $tlCount && $this->getTokenType($this->tokenList[$i + 1]) == 'number') {
				$unprocessedTokens = array($token);

				// store all abutting numerical tokens for later processing
				do {
					++$i;
					$unprocessedTokens[] = $this->tokenList[$i];
				} while($i + 1 < $tlCount && $this->getTokenType($this->tokenList[$i + 1]) == 'number');

				// retrieve the amount of number characters at our current parse position
				$numberLen = strspn($dateString, '0123456789', $datePos);

				// calculate the length the numbers should have from the tokens
				$tokenReqLen = 0;
				foreach($unprocessedTokens as $tk) {
					$tokenReqLen += $tk[1];
				}

				// we mimic the ICU behaviour here which only decrements the count of 
				// the first token (from ICU:  Take the pattern "HHmmss" as an example.
				// We will try to parse 2/2/2 characters of the input text, then if that
				// fails, 1/2/2.  We only adjust the width of the leftmost field; the
				// others remain fixed.)
				// I'm not sure why they are doing it that way since i think that 
				// decrementing the next tokens when there still aren't enough numbers
				// wouldn't do any harm (ok, maybe the algorithm is a little harder to
				// implement ;) - Dominik

				$diff = $tokenReqLen - $numberLen;
				if($diff > 0) {
					// use a reference here so we can simply store the new token length into $tLen
					$tLen =& $unprocessedTokens[0][1];
					if($diff >= $tLen - 1) {
						$tLen -= $diff;
					} else {
						throw new AgaviException('Not enough digits given in "' . $dateString . '" at pos ' . $datePos . ' (expected ' . $tokenReqLen . ' digits)');
					}
				}

				foreach($unprocessedTokens as $token) {
					$dateField = $this->getDateFieldFromTokenType($token[0]);
					if($dateField === null) {
						throw new AgaviException('Token type ' . $token[0] . ' claims to be numerical but has no date field');
					}

					$number = (int) substr($dateString, $datePos, $token[1]);

					$datePos += $token[1];
					if($dateField == AgaviDateDefinitions::HOUR_OF_DAY && $token[0] == AgaviDateFormat::T_HOUR_1_24 && $number == 24) {
						$number = 0;
					} elseif($dateField == AgaviDateDefinitions::HOUR && $token[0] == AgaviDateFormat::T_HOUR_1_12 && $number == 12) {
						$number = 0;
					} elseif($dateField == AgaviDateDefinitions::MONTH && $number > 0) {
						$number -= 1;
					}

					if(self::T_QUARTER == $token[0] || self::T_SA_QUARTER == $token[0]) {
						// only set the quarter if the date hasn't been set on the calendar object
						if(!$cal->_isSet(AgaviDateDefinitions::MONTH)) {
							$cal->set($dateField, ($number - 1) * 3);
						}
					} else {
						$cal->set($dateField, $number);
					}
				}
			} elseif($type == 'number') {
				$numberLen = strspn($dateString, '0123456789', $datePos);
				$dateField = $this->getDateFieldFromTokenType($token[0]);
				if($dateField === null) {
					throw new AgaviException('Token type ' . $token[0] . ' claims to be numerical but has no date field');
				}
				if($strict && $numberLen > 2 && $numberLen > $token[1]) {
					$numberLen = $token[1];
				}
				if($dateField == AgaviDateDefinitions::MILLISECOND && $numberLen > 3) {
					// we only store in millisecond precision so only use the first 3 digits of the fractional second
					$number = (int) substr($dateString, $datePos, 3);
				} else {
					$number = (int) substr($dateString, $datePos, $numberLen);
				}

				$datePos += $numberLen;
				// since the month is 0-indexed in the calendar and 1-indexed in the
				// cldr we need to subtract 1 from the month
				if($dateField == AgaviDateDefinitions::MONTH && $number > 0) {
					$number -= 1;
				} elseif($dateField == AgaviDateDefinitions::HOUR_OF_DAY && $token[0] == AgaviDateFormat::T_HOUR_1_24 && $number == 24) {
					$number = 0;
				} elseif($dateField == AgaviDateDefinitions::HOUR && $token[0] == AgaviDateFormat::T_HOUR_1_12 && $number == 12) {
					$number = 0;
				} elseif($token[0] == self::T_YEAR && $token[1] == 2 && $numberLen <= 2) {
					if($number >= 40) {
						$number += 1900;
					} else {
						$number += 2000;
					}
				}

				if(self::T_QUARTER == $token[0] || self::T_SA_QUARTER == $token[0]) {
					// only set the quarter if the date hasn't been set on the calendar object
					if(!$cal->_isSet(AgaviDateDefinitions::MONTH)) {
						$cal->set($dateField, ($number - 1) * 3);
					}
				} else {
					$cal->set($dateField, $number);
				}
			} else { // $type == 'text'
				$count = $token[1];
				switch($token[0]) {
					case self::T_TEXT:
						if(substr_compare($dateString, $token[1], $datePos, strlen($token[1])) == 0) {
							$datePos += strlen($token[1]);
						} elseif($i + 1 == $tlCount && !$strict) {
							// when the last text token didn't match we don't do anything in non strict mode
						} else {
							throw new AgaviException('Unknown character in "' . $dateString . '" at pos ' . $datePos . ' (expected: "' . $token[1] . '", got: "' . substr($dateString, $datePos, strlen($token[1])) . '")');
						}
						break;

					case self::T_ERA:
					case self::T_DAY_OF_WEEK:
					case self::T_LOCAL_DAY_OF_WEEK:
					case self::T_SA_LOCAL_DAY_OF_WEEK:
						$funcPrefix = '';

						switch($token[0]) {
							case self::T_ERA:
								$funcPrefix = 'getCalendarEras';
								$dataField = AgaviDateDefinitions::ERA;
								break;
							case self::T_DAY_OF_WEEK:
								$funcPrefix = 'getCalendarDays';
								$dataField = AgaviDateDefinitions::DAY_OF_WEEK;
								break;

							case self::T_LOCAL_DAY_OF_WEEK:
							case self::T_SA_LOCAL_DAY_OF_WEEK:
								$funcPrefix = 'getCalendarDays';
								$dataField = AgaviDateDefinitions::DOW_LOCAL;
								break;
						}

						if($count == 4) {
							$items = $locale->{$funcPrefix . 'Wide'}($calendarType);
						} elseif($count == 5) {
							$items = $locale->{$funcPrefix . 'Narrow'}($calendarType);
						} else {
							$items = $locale->{$funcPrefix . 'Abbreviated'}($calendarType);
						}
						$item = null;
						if($this->matchStringWithFallbacks($dateString, $items, $datePos, $item)) {
							$cal->set($dataField, $item);
						} else {
							throw new AgaviException('Unknown character in "' . $dateString . '" at pos ' . $datePos . ' (expected one of: ' . implode(', ', $items) . ')');
						}
						break;

					case self::T_AM_PM:
						$items = array(
							0 => $locale->getCalendarAm($calendarType),
							1 => $locale->getCalendarPm($calendarType),
						);

						$item = null;
						if($this->matchStringWithFallbacks($dateString, $items, $datePos, $item)) {
							$cal->set(AgaviDateDefinitions::AM_PM, $item);
						} else {
							throw new AgaviException('Unknown character in "' . $dateString . '" at pos ' . $datePos . ' (expected one of: ' . implode(', ', $items) . ')');
						}
						break;

					case self::T_TIMEZONE:
					case self::T_TIMEZONE_GENERIC:
					case self::T_TIMEZONE_RFC:
						$remainder = substr($dateString, $datePos);
						if(preg_match('#^(GMT)?(\+|-)?(\d{1,2}:\d{1,2}|\d{1,2}\d{1,2})#i', $remainder, $match)) {
							$datePos += strlen($match[0]);
							if(strtolower($match[1]) != 'gmt') {
								$remainder = 'GMT' . $match[0];
							} else {
								$remainder = $match[0];
							}
							$tz = $tm->createTimeZone($remainder);
						} else {
							if($i + 1 != $tlCount && preg_match('#^([a-z/]+)#i', $remainder, $match)) {
								$remainder = $match[0];
							}
							if(!($tz = $tm->createTimeZone($remainder))) {
								// try to match a localized timezone string
								$z = 0;
								$localizedTzMap = array();
								$idToTzMap = array();
								$tzNames = $locale->getTimeZoneNames();
								foreach($tzNames as $tzId => $tz) {
									foreach($tz as $type => $names) {
										if(is_array($names)) {
											foreach($names as $name) {
												$localizedTzMap[$z] = $name;
												$idToTzMap[$z] = $tzId;
												++$z;
											}
										}
									}
								}

								$id = 0;
								if($this->matchStringWithFallbacks($dateString, $localizedTzMap, $datePos, $id)) {
									$tz = $tm->createTimeZone($idToTzMap[$id]);
								} else {
									throw new AgaviException('Unknown character in "' . $dateString . '" at pos ' . $datePos . ' (expected one of: ' . implode(', ', $localizedTzMap) . ')');
								}
							} else {
								$datePos += strlen($remainder);
							}
						}

						$cal->setTimeZone($tz);
						break;

					case self::T_MONTH:
					case self::T_SA_MONTH:
						if($count == 3) {
							$months = $locale->getCalendarMonthsAbbreviated($calendarType);
						} elseif($count == 4) {
							$months = $locale->getCalendarMonthsWide($calendarType);
						} elseif($count == 5) {
							$months = $locale->getCalendarMonthsNarrow($calendarType);
						}
						$month = null;
						if($this->matchStringWithFallbacks($dateString, $months, $datePos, $month)) {
							$cal->set(AgaviDateDefinitions::MONTH, $month - 1);
						} else {
							throw new AgaviException('Unknown character in "' . $dateString . '" at pos ' . $datePos . ' (expected one of: ' . implode(', ', $months) . ')');
						}

						break;

					case self::T_QUARTER:
					case self::T_SA_QUARTER:
						if($count == 3) {
							$quarters = $locale->getCalendarQuartersAbbreviated($calendarType);
						} elseif($count == 4) {
							$quarters = $locale->getCalendarQuartersWide($calendarType);
						} elseif($count == 5) {
							$quarters = $locale->getCalendarQuartersNarrow($calendarType);
						}
						$quarter = null;
						if($this->matchStringWithFallbacks($dateString, $quarters, $datePos, $quarter)) {
							if(!$cal->_isSet(AgaviDateDefinitions::MONTH)) {
								$cal->set(AgaviDateDefinitions::MONTH, ($quarter - 1) * 3);
							}
						} else {
							throw new AgaviException('Unknown character in "' . $dateString . '" at pos ' . $datePos . ' (expected one of: ' . implode(', ', $quarters) . ')');
						}
						break;

				}
			}
		}

		// make sure the calendar has it's time calculated, 
		// so there aren't any strange effects when setting a new timezone 
		// or a new date
		$cal->getTime();
		if($strict) {
			// calculate the time to get errors for invalid dates
			if($datePos < strlen($dateString)) {
				throw new AgaviException('Input string "' . $dateString . '" has characters after the date');
			}
		}

		return $cal;
	}

	/**
	 * Returns the date field which is associated with the given token.
	 *
	 * @param      int The type of the token.
	 *
	 * @return     int The date field in the calendar for this token type.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	protected function getDateFieldFromTokenType($type)
	{
		static $typeMap = array(
			self::T_ERA                     => AgaviDateDefinitions::ERA,
			self::T_YEAR                    => AgaviDateDefinitions::YEAR,
			self::T_MONTH                   => AgaviDateDefinitions::MONTH,
			self::T_SA_MONTH                => AgaviDateDefinitions::MONTH,
			self::T_DATE                    => AgaviDateDefinitions::DATE,
			self::T_HOUR_1_24               => AgaviDateDefinitions::HOUR_OF_DAY,
			self::T_HOUR_0_23               => AgaviDateDefinitions::HOUR_OF_DAY,
			self::T_MINUTE                  => AgaviDateDefinitions::MINUTE,
			self::T_SECOND                  => AgaviDateDefinitions::SECOND,
			self::T_FRACTIONAL_SECOND       => AgaviDateDefinitions::MILLISECOND,
			self::T_DAY_OF_WEEK             => AgaviDateDefinitions::DAY_OF_WEEK,
			self::T_DAY_OF_YEAR             => AgaviDateDefinitions::DAY_OF_YEAR,
			self::T_DAY_OF_WEEK_IN_MONTH    => AgaviDateDefinitions::DAY_OF_WEEK_IN_MONTH,
			self::T_WEEK_OF_YEAR            => AgaviDateDefinitions::WEEK_OF_YEAR,
			self::T_WEEK_OF_MONTH           => AgaviDateDefinitions::WEEK_OF_MONTH,
			self::T_AM_PM                   => AgaviDateDefinitions::AM_PM,
			self::T_HOUR_1_12               => AgaviDateDefinitions::HOUR,
			self::T_HOUR_0_11               => AgaviDateDefinitions::HOUR,
			self::T_ISO_YEAR                => AgaviDateDefinitions::YEAR_WOY,
			self::T_LOCAL_DAY_OF_WEEK       => AgaviDateDefinitions::DOW_LOCAL,
			self::T_SA_LOCAL_DAY_OF_WEEK    => AgaviDateDefinitions::DOW_LOCAL,
			self::T_EXTENDED_YEAR           => AgaviDateDefinitions::EXTENDED_YEAR,
			self::T_MODIFIED_JD             => AgaviDateDefinitions::JULIAN_DAY,
			self::T_MS_IN_DAY               => AgaviDateDefinitions::MILLISECONDS_IN_DAY,
			self::T_QUARTER                 => AgaviDateDefinitions::MONTH,
			self::T_SA_QUARTER              => AgaviDateDefinitions::MONTH,
		);

		if(isset($typeMap[$type])) {
			return $typeMap[$type];
		} else {
			return null;
		}
	}

	/**
	 * Returns whether a string matches any of the given possibilities at the 
	 * current offset.
	 *
	 * @param      string The string to be matched in.
	 * @param      array  The possibilities which can match.
	 * @param      int    The offset to match at in the input string.
	 * @param      mixed  The key of the possibilities entry that matched.
	 *
	 * @return     bool Whether any possibility could be matched.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	protected function matchStringWithFallbacks($string, array $possibilities, &$offset, &$matchedKey)
	{
		$strlen = strlen($string);
		// TODO: change this to match to longest match and not the first one.
		foreach($possibilities as $key => $possibility) {
			$possLen = strlen($possibility);
			// avoid warning when $string is not long enough for the possibility
			if($strlen >= ($offset + $possLen) && substr_compare($string, $possibility, $offset, $possLen) == 0) {
				$offset += $possLen;
				$matchedKey = $key;
				return true;
			}
		}

		return false;
	}

	/**
	 * Returns the type of a token
	 *
	 * @param      array The token.
	 *
	 * @return     string either 'text' or 'number'
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	protected function getTokenType($token)
	{
		$type = 'text';
		$tok = $token[0];
		$cnt = $token[1];

		switch($tok) {
			case self::T_YEAR:
			case self::T_DATE:
			case self::T_HOUR_1_24:
			case self::T_HOUR_0_23:
			case self::T_HOUR_1_12:
			case self::T_HOUR_0_11:
			case self::T_MINUTE:
			case self::T_SECOND:
			case self::T_DAY_OF_YEAR:
			case self::T_DAY_OF_WEEK_IN_MONTH:
			case self::T_WEEK_OF_YEAR:
			case self::T_WEEK_OF_MONTH:
			case self::T_ISO_YEAR:
			case self::T_EXTENDED_YEAR:
			case self::T_MODIFIED_JD:
			case self::T_MS_IN_DAY:
			case self::T_FRACTIONAL_SECOND:
				// number
				$type = 'number';
				break;

			case self::T_MONTH:
			case self::T_SA_MONTH:
			case self::T_LOCAL_DAY_OF_WEEK:
			case self::T_SA_LOCAL_DAY_OF_WEEK:
			case self::T_QUARTER:
			case self::T_SA_QUARTER:
				// string > 2
				if($cnt > 2) {
					$type = 'text';
				} else {
					$type = 'number';
				}
				break;

			case self::T_TEXT:
			case self::T_ERA:
			case self::T_DAY_OF_WEEK:
			case self::T_AM_PM:
			case self::T_TIMEZONE:
			case self::T_TIMEZONE_GENERIC:
			case self::T_TIMEZONE_RFC:
				$type = 'text';
				// string
				break;
		}

		return $type;
	}

}

?>
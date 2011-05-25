<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2005-2011 the Agavi Project.                                |
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
 * AgaviDateTimeValidator verifies that a parameter is of a date and or time 
 * format.
 * 
 * Arguments: 
 *   This can be:
 *    * a single argument which will then be parsed with the formats in the 
 *      'formats' parameter.
 *    * multiple arguments with the calendar constants 
 *      (AgaviDateDefinitions::MONTH, etc) as key and the argument field as 
 *      value.
 *    * multiple arguments and the 'arguments_format' parameter defined. This
 *      will use the string in 'arguments_format' as input string to sprintf and
 *      will use the arguments in the given order as argument to sprintf.
 * 
 * Parameters:
 *   'check'       check date if the specified day really exists
 *   'formats'     an array of arrays with these keys:
 *     'type'       The type of the string in 'format'.
 *     'format'     The input string dependent on the type. These types are 
 *                  allowed:
 *                    format:   The value is a date format string.
 *                    time:     The value is a time specifier (full,...) or null
 *                    date:     The value is a date specifier or null
 *                    datetime: The value is a date specifier or null
 *                    translation_domain: The value will be translated in the 
 *                              domain given in the 'translation_domain' key.
 *                    unix:     Always null/empty
 *                    unix_milliseconds: Always null/empty
 *                   
 *     'locale'     The optional locale which will be used for this format.
 *     'translation_domain' Only applicable when the type is translation_domain
 *   'cast_to'     Only useful in combination with the export parameter.
 *                 This can either be a string or an array. If its an string it 
 *                 can be one of 'unix' (converts the date to a unix timestamp),
 *                 'string' (converts it to a string using the default format), 
 *                 'calendar' (will return the AgaviCalendar object),
 *                 'datetime' (case sensitive, will return a PHP DateTime 
 *                 object, requires PHP 5.1.x with DateTime explicitly enabled 
 *                 or >= PHP 5.2).
 *                 If it's an array it can have these keys:
 *     'type'        The type of the format (format, time, date, datetime)
 *     'format'      see in 'formats' above.
 *   'arguments_format' A string which will be used as the format string for 
 *                 sprintf.
 *   'min'         Either an string or an array. When its a string the the 
 *                 its assumed to be in the format 'yyyy-MM-dd[ HH:mm:ss[.S]]'.
 *                 When its an array it will take the minimum value from a 
 *                 request field. These indizes apply:
 *     'format'      A custom format string which should be used when the field 
 *                   is an string.
 *     'field'       The name of the field to use as minimum value (could be a 
 *                   previous exported calendar object). Do NOT use unvalidated 
 *                   fields here. Lax parsing will be used.
 *                 This value is inclusive.
 *   'max'         The same as min except that the max is exclusive.
 *
 * @package    agavi
 * @subpackage validator
 *
 * @author     Dominik del Bondio <ddb@bitxtender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.11.0
 *
 * @version    $Id$
 */
class AgaviDateTimeValidator extends AgaviValidator
{
	/**
	 * Validates the input.
	 * 
	 * @return     bool True if the input was a valid date.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	protected function validate()
	{
		if(!AgaviConfig::get('core.use_translation')) {
			throw new AgaviConfigurationException('The datetime validator can only be used with use_translation on');
		}
		$tm = $this->getContext()->getTranslationManager();
		$cal = null;

		$check = $this->getParameter('check', true);
		$locale = $this->hasParameter('locale') ? $tm->getLocale($this->getParameter('locale')) : $tm->getCurrentLocale();

		if($this->hasMultipleArguments() && !$this->getParameter('arguments_format')) {
			$cal = $tm->createCalendar();
			$cal->clear();
			$cal->setLenient(!$check);
			foreach($this->getArguments() as $calField => $field) {
				$param = $this->getData($field);
				if(defined($calField)) {
					$calField = constant($calField);
				} elseif(!is_numeric($calField)) {
					throw new AgaviValidatorException('Unknown argument name "' . $calField . '" for argument "' . $field . '" supplied. This needs to be one of the constants defined in AgaviDateDefinitions.');
				}
				if(!is_scalar($param)) {
					// everything which is non scalar is ignored, since it couldn't be handled anyways
					continue;
				}

				if($calField == AgaviDateDefinitions::MONTH) {
					$param -= 1;
				}

				$cal->set($calField, (float) $param);
			}

			try {
				$cal->getTime();
			} catch(Exception $e) {
				$this->throwError('check');
				return false;
			}
		} else {
			if($argFormat = $this->getParameter('arguments_format')) {
				$values = array();
				foreach($this->getArguments() as $field) {
					$values[] = $this->getData($field);
				}
				$param = vsprintf($argFormat, $values);
			} else {
				$param = $this->getData($this->getArgument());
				if(!is_scalar($param)) {
					$this->throwError();
					return false;
				}
			}

			$matchedFormat = false;
			foreach((array)$this->getParameter('formats', array()) as $key => $item) {
				if(!is_array($item)) {
					$item = array((is_int($key) ? 'format' : $key) => $item);
				}
				
				$itemLocale = empty($item['locale']) ? $locale : $tm->getLocale($item['locale']);
				$type = empty($item['type']) ? 'format' : $item['type'];

				if($type == 'format') {
					$formatString = $item['format'];
				} elseif($type == 'time' || $type == 'date' || $type == 'datetime') {
					$format = isset($item['format']) ? $item['format'] : null;
					$formatString = AgaviDateFormatter::resolveFormat($format, $itemLocale, $type);
				} elseif($type == 'translation_domain') {
					$td = $item['translation_domain'];
					$formatString = $tm->_($item['format'], $td, $itemLocale);
				} elseif($type == 'unix') {
					$matchedFormat = ($param === (string)(int)$param);
					$cal = $tm->createCalendar($itemLocale);
					$cal->setUnixTimestamp($param);
					if($matchedFormat) {
						try {
							if($cal->getUnixTimestamp() !== (int)$param) {
								$this->throwError('check');
								return false;
							}
						} catch(Exception $e) {
							$matchedFormat = false;
						}
					}
				} elseif($type == 'unix_milliseconds') {
					$matchedFormat = is_numeric($param);
					$cal = $tm->createCalendar($itemLocale);
					$cal->setTime($param);
					if($matchedFormat) {
						try {
							if($cal->getTime() !== (float)$param) {
								$this->throwError('check');
								return false;
							}
						} catch(Exception $e) {
							$matchedFormat = false;
						}
					}
				}

				if(!$cal) {
					try {
						$format = new AgaviDateFormat($formatString);
						$cal = $format->parse($param, $itemLocale, $check);

						// no exception got thrown so the parsing was successful
						$matchedFormat = true;
						break;
					} catch(Exception $e) {
						// nop
					}
				}
			}

			if(!$matchedFormat) {
				$this->throwError('format');
				return false;
			}
		}

		$cal->setLenient(true);
		$value = $cal;

		if($cast = $this->getParameter('cast_to')) {
			// an array means the user wants it custom formatted
			if(is_array($cast)) {
				$type = empty($cast['type']) ? 'format' : $cast['type'];
				if($type == 'format') {
					$formatString = $cast['format'];
				} elseif($type == 'time' || $type == 'date' || $type == 'datetime') {
					$format = isset($cast['format']) ? $cast['format'] : null;
					$formatString = AgaviDateFormatter::resolveFormat($format, $locale, $type);
				}

				$format = new AgaviDateFormat($formatString);
				$value = $format->format($cal, $cal->getType(), $locale);
			} else {
				$cast = strtolower($cast);
				if($cast == 'unix') {
					$value = $cal->getUnixTimestamp();
				} elseif($cast == 'string') {
					$value = $tm->_d($cal);
				} elseif($cast == 'datetime') {
					$value = $cal->getNativeDateTime(); 
				} else {
					$value = $cal;
				}
			}
		}

		$defaultParseFormat = new AgaviDateFormat('yyyy-MM-dd HH:mm:ss.S');

		if($this->hasParameter('min')) {
			$min = $this->getMinOrMaxValue('min', $defaultParseFormat, $locale);

			$isAfterEqual = $cal->after($min) || $cal->equals($min);
			if(!$isAfterEqual) {
				$this->throwError('min');
				return false;
			}
		}

		if($this->hasParameter('max')) {
			$max = $this->getMinOrMaxValue('max', $defaultParseFormat, $locale);

			$isBefore = $cal->before($max);
			if(!$isBefore) {
				$this->throwError('max');
				return false;
			}
		}

		if($this->hasParameter('export')) {
			$export = $this->getParameter('export');
			if(is_string($export)) {
				$this->export($value);
			} elseif(is_array($export)) {
				foreach($export as $calField => $field) {
					if(defined($calField)) {
						$this->export($cal->get(constant($calField)), $field);
					}
				}
			}
		}

		return true;
	}

	/**
	 * Returns the calendar object for a max or min definition.
	 *
	 * @param      string 'min' or 'max'
	 * @param      AgaviDateFormat The default format when parsing strings.
	 * @param      AgaviLocale The locale to use.
	 *
	 * @return     AgaviCalendar The calendar object storing the date.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	protected function getMinOrMaxValue($minMax, $defaultParseFormat, $locale)
	{
		$format = $defaultParseFormat;

		$minMax = $this->getParameter($minMax);
		if(is_array($minMax)) {
			$minMaxValue = $this->validationParameters->getParameter($minMax['field']);
			if(!$minMaxValue instanceof AgaviCalendar) {
				if(isset($minMax['format'])) {
					$format = new AgaviDateFormat($minMax['format']);
				}
				$result = $format->parse($minMaxValue, $locale, false);
			} else {
				$result = $minMaxValue;
			}
		} elseif(strpos($minMax, '.') === false) {
			// a strtotime compatible string does not contain a dot, so all strings with dots are assumed to be
			// strings matching the calendar format and all others are handled with strtotime
			$tz = $locale->getLocaleTimeZone();
			if($tz) {
				// set the timezone for the strtotime call to be the same as for creating the calendar
				$oldDefaultTimezone = date_default_timezone_get();
				date_default_timezone_set($tz);
			}
			// create the calendar in the requested locale/timezone
			$result = $this->getContext()->getTranslationManager()->createCalendar($locale);
			$result->setUnixTimestamp(strtotime($minMax));
			if($tz) {
				// reset the php timezone
				date_default_timezone_set($oldDefaultTimezone);
			}
		} else {
			$result = $format->parse($minMax, $locale, false);
		}

		return $result;
	}

}

?>
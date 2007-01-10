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
 * AgaviDateValidator verifies that a parameter is of a date format.
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
 *                   
 *     'locale'     The optional locale which will be used for this format.
 *     'translation_domain' Only applicable when the type is translation_domain
 *   'cast_to'     Only useful in combination with the export parameter.
 *                 This can either be a string or an array. If its an string it 
 *                 can be one of 'unix' (converts the date to a unix timestamp),
 *                 'string' (converts it to a string using the default format), 
 *                 'calendar' (will return the AgaviCalendar object).
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
 * @copyright  (c) Authors
 * @since      0.11.0
 *
 * @version    $Id$
 */
class AgaviDateValidator extends AgaviValidator
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
		$tm = $this->getContext()->getTranslationManager();
		$cal = null;

		$check = $this->getParameter('check', true);
		$locale = $this->hasParameter('locale') ? $tm->getLocaleFromIdentifier($this->getParameter('locale')) : $tm->getCurrentLocale();

		if($this->hasMultipleArguments() && !$this->getParameter('arguments_format')) {
			$cal = $tm->createCalendar();
			$cal->clear();
			$cal->setLenient(!$check);
			foreach($this->getArguments() as $calField => $field) {
				$param = $this->getData($field);
				if(defined($calField)) {
					$calField = constant($calField);

					if($calField == AgaviDateDefinitions::MONTH) {
						$param -= 1;
					}

					$cal->set($calField, (float) $param);
				}
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
			}

			$matchedFormat = false;
			foreach($this->getParameter('formats', array()) as $item) {
				$itemLocale = empty($item['locale']) ? $locale : $tm->getLocaleFromIdentifier($item['locale']);
				$type = empty($item['type']) ? 'format' : $item['type'];

				try {
					if($type == 'format') {
						$formatString = $item['format'];
					} elseif($type == 'time' || $type == 'date' || $type == 'datetime') {
						$format = isset($item['format']) ? $item['format'] : null;
						$formatString = AgaviDateFormatter::resolveFormat($format, $itemLocale, $type);
					} elseif($type == 'translation_domain') {
						$td = $item['translation_domain'];
						$formatString = $tm->_($item['format'], $td, $itemLocale);
					}

					$format = new AgaviDateFormat($formatString);
					$cal = $format->parse($param, $itemLocale, $check);

					// no exception got thrown so the parsing was successful
					$matchedFormat = true;
					break;
				} catch(Exception $e) {
					// nop
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
				if($cast == 'unix') {
					$value = $cal->getUnixTimestamp();
				} elseif($cast == 'string') {
					$value = $tm->_d($cal);
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
		} else {
			$result = $format->parse($minMax, $locale, false);
		}

		return $result;
	}

}

?>
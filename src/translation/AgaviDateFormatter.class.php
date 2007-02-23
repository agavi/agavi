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
 * The date formatter will dates numbers according to a given format
 *
 * @package    agavi
 * @subpackage util
 *
 * @author     Dominik del Bondio <ddb@bitxtender.com>
 * @author     David Zülke <dz@bitxtender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.11.0
 *
 * @version    $Id$
 */
class AgaviDateFormatter extends AgaviDateFormat implements AgaviITranslator
{
	/**
	 * @var        AgaviContext An AgaviContext instance.
	 */
	protected $context = null;

	/**
	 * @var        AgaviLocale An AgaviLocale instance.
	 */
	protected $locale = null;

	/**
	 * @var        string The type of the formatter (date|time|datetime).
	 */
	protected $type = null;

	/**
	 * @var        string The custom format string (if any).
	 */
	protected $customFormat = null;

	/**
	 * @var        string The translation domain to translate the format (if any).
	 */
	protected $translationDomain = null;

	/**
	 * @see        AgaviITranslator::getContext()
	 */
	public final function getContext()
	{
		return $this->context;
	}

	/**
	 * Initialize this Translator.
	 *
	 * @param      AgaviContext The current application context.
	 * @param      array        An associative array of initialization parameters
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function initialize(AgaviContext $context, array $parameters = array())
	{
		$this->context = $context;
		$type = 'datetime';

		if(isset($parameters['translation_domain'])) {
			$this->translationDomain = $parameters['translation_domain'];
		}
		if(isset($parameters['type']) && in_array($parameters['type'], array('date', 'time'))) {
			$type = $parameters['type'];
		}
		if(isset($parameters['format'])) {
			$this->customFormat = $parameters['format'];
			if(is_array($this->customFormat)) {
				// it's an array, so it contains the translations already, DOMAIN MUST NOT BE SET
				$this->translationDomain = null;
			}
		}
		$this->type = $type;
	}

	/**
	 * Translates a message into the defined language.
	 *
	 * @param      mixed       The message to be translated.
	 * @param      string      The domain of the message.
	 * @param      AgaviLocale The locale to which the message should be 
	 *                         translated.
	 *
	 * @return     string The translated message.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function translate($message, $domain, AgaviLocale $locale = null)
	{
		if($locale) {
			$fmt = clone $this;
			$fmt->localeChanged($locale);
		} else {
			$fmt = $this;
			$locale = $this->locale;
		}
		// HACK! for some reason php decides to separate the zvals of $locale and $this->locale
		// when $this is cloned, so we need to to do this check before we clone
		$localesEqual = $locale === $this->locale;

		if($this->customFormat && $this->translationDomain) {
			if($fmt === $this) {
				$fmt = clone $this;
			}
			
			$td = $this->translationDomain . ($domain ? '.' . $domain : '');
			$format = $this->getContext()->getTranslationManager()->_($this->customFormat, $td, $locale);
			
			if($fmt->isDateSpecifier($format)) {
				$format = $fmt->resolveSpecifier($locale, $format, $this->type);
			}
			
			$fmt->setFormat($format);
		}

		if(is_int($message)) {
			$cal = $this->context->getTranslationManager()->createCalendar($locale);
			$cal->setUnixTimestamp($message);
		} elseif(!($message instanceof AgaviCalendar)) {
			$cal = $this->context->getTranslationManager()->createCalendar($message);
		} else {
			$cal = $message;
		}

		if(($zoneId = $locale->getLocaleTimeZone()) && !$localesEqual) {
			$cal->setTimeZone($this->context->getTranslationManager()->createTimeZone($zoneId));
		}

		return $fmt->format($cal, AgaviCalendar::GREGORIAN, $locale);
	}

	/**
	 * This method gets called by the translation manager when the default locale
	 * has been changed.
	 *
	 * @param      string The new default locale.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function localeChanged($newLocale)
	{
		$this->locale = $newLocale;
		
		$format = null; // ze default
		
		if(is_array($this->customFormat)) {
			$format = AgaviToolkit::getValueByKeyList($this->customFormat, AgaviLocale::getLookupPath($this->locale->getIdentifier()), $format);
		}
		
		$format = $this->resolveSpecifier($this->locale, $format, $this->type);
		
		$this->setFormat($format);
	}

	/**
	 * Resolves a given format (translates it to the given string of one of 
	 * 'full', 'long', 'medium', 'short' or returns the format unmodified 
	 * otherwise.
	 *
	 * @param      string The format string.
	 * @param      AgaviLocale The locale to use for resolving.
	 * @param      string The type (date, time or datetime).
	 *
	 * @return     string The resolved format.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public static function resolveFormat($format, $locale, $type = 'datetime')
	{
		if(self::isDateSpecifier($format)) {
			return self::resolveSpecifier($locale, $format, $type);
		}

		return $format;
	}

	/**
	 * Resolves a given specifier ('full', 'long', 'medium', 'short' or null which
	 * will use the default format).
	 *
	 * @param      AgaviLocale The locale to use for resolving.
	 * @param      string The specifier.
	 * @param      string The type (date, time or datetime).
	 *
	 * @return     string The format.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	protected static function resolveSpecifier($locale, $spec, $type)
	{
		$calendarType = AgaviCalendar::GREGORIAN;
		if(!$type) {
			$type = 'datetime';
		}

		if($type == 'datetime' || $type == 'time') {
			if($spec === null) {
				$formatName = $locale->getCalendarTimeFormatDefaultName($calendarType);
			} else {
				$formatName = $spec;
			}
			$format = $timeFormat = $locale->getCalendarTimeFormatPattern($calendarType, $formatName);
		}

		if($type == 'datetime' || $type == 'date') {
			if($spec === null) {
				$formatName = $locale->getCalendarDateFormatDefaultName($calendarType);
			} else {
				$formatName = $spec;
			}

			$format = $dateFormat = $locale->getCalendarDateFormatPattern($calendarType, $formatName);
		}

		if($type == 'datetime') {
			$formatName = $locale->getCalendarDateTimeFormatDefaultName($calendarType);
			$formatStr = $locale->getCalendarDateTimeFormat($calendarType, $formatName);
			$format = str_replace(array('{0}', '{1}'), array($timeFormat, $dateFormat), $formatStr);
		}

		return $format;
	}

	/**
	 * Checks whether a given string is a date specifier. (One of 'full', 'long',
	 * 'medium', 'short')
	 *
	 * @param      string The specifier.
	 *
	 * @return     bool The result.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	protected static function isDateSpecifier($format)
	{
		static $specifiers = array('full', 'long', 'medium', 'short');

		return in_array($format, $specifiers);
	}
}

?>
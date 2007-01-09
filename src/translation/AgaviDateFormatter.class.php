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
 * The date formatter will dates numbers according to a given format
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
	public function getContext()
	{
		return $this->context;
	}

	/**
	 * @see        AgaviITranslator::initialize()
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
			$format = $parameters['format'];
			$this->customFormat = $format;
		}
		$this->type = $type;
	}

	/**
	 * @see        AgaviITranslator::translate()
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

		if(is_int($message)) {
			// convert unix timestamp to calendar
			$message = $this->context->getTranslationManager()->createCalendar($message);
		}

		if(!($message instanceof AgaviCalendar)) {
			throw new InvalidArgumentException('The date needs to be an int or AgaviCalendar instance');
		}

		if(($zoneId = $locale->getLocaleTimeZone()) && $locale !== $this->locale) {
			$message->setTimeZone($this->context->getTranslationManager()->createTimeZone($zoneId));
		}

		return $fmt->format($message, 'gregorian', $locale);
	}

	/**
	 * @see        AgaviITranslator::localeChanged()
	 */
	public function localeChanged($newLocale)
	{
		$this->locale = $newLocale;

		if($this->customFormat === null) {
			$format = $this->resolveSpecifier($this->locale, null, $this->type);
			$this->setFormat($format);
		} else {
			$format = $this->customFormat;
			if($this->translationDomain !== null) {
				$format = $this->getContext()->getTranslationManager()->_($format, $this->translationDomain, $newLocale);
			}

			if($this->isDateSpecifier($format)) {
				$format = $this->resolveSpecifier($this->locale, $format, $this->type);
			}

			$this->setFormat($format);
		}
	}

	public static function resolveFormat($format, $locale, $type = 'datetime')
	{
		if(self::isDateSpecifier($format)) {
			return self::resolveSpecifier($locale, $format, $type);
		}

		return $format;
	}

	protected static function resolveSpecifier($locale, $spec, $type)
	{
		if(!$type) {
			$type = 'datetime';
		}

		if($type == 'datetime' || $type == 'time') {
			if($spec === null) {
				$formatName = $locale->getCalendarTimeFormatDefaultName('gregorian');
			} else {
				$formatName = $spec;
			}
			$format = $timeFormat = $locale->getCalendarTimeFormatPattern('gregorian', $formatName);
		}

		if($type == 'datetime' || $type == 'date') {
			if($spec === null) {
				$formatName = $locale->getCalendarDateFormatDefaultName('gregorian');
			} else {
				$formatName = $spec;
			}

			$format = $dateFormat = $locale->getCalendarDateFormatPattern('gregorian', $formatName);
		}

		if($type == 'datetime') {
			$formatName = $locale->getCalendarDateTimeFormatDefaultName('gregorian');
			$formatStr = $locale->getCalendarDateTimeFormat('gregorian', $formatName);
			$format = str_replace(array('{0}', '{1}'), array($timeFormat, $dateFormat), $formatStr);
		}

		return $format;
	}

	protected static function isDateSpecifier($format)
	{
		static $specifiers = array('full', 'long', 'medium', 'short');

		return in_array($format, $specifiers);
	}
}

?>
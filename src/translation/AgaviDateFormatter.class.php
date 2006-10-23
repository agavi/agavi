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
class AgaviDateFormatter extends AgaviSimpleDateFormatter implements AgaviITranslator
{
	/**
	 * @var        AgaviContext An AgaviContext instance.
	 */
	protected $context = null;

	protected $locale = null;

	protected $type = null;

	protected $formatType = null;

	protected $customFormat = null;

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
		$formatType = null;
		if(isset($parameters['type']) && in_array($parameters['type'], array('date', 'time'))) {
			$type = $parameters['type'];
		}
		if(isset($parameters['format'])) {
			$format = $parameters['format'];
			if(!in_array($format, array('full', 'long', 'medium', 'short'))) {
				$this->customFormat = $format;
				$this->parseFormatString($format);
			} else {
				$formatType = $format;
			}
		}
		$this->type = $type;
		$this->formatType = $formatType;
	}

	/**
	 * @see        AgaviITranslator::translate()
	 */
	public function translate($message, $domain, AgaviLocale $locale = null)
	{
		if(!$locale) {
			$locale = $this->locale;
		}

		return $this->format($message, 'gregorian', $locale);
	}

	/**
	 * @see        AgaviITranslator::localeChanged()
	 */
	public function localeChanged($newLocale)
	{
		$this->locale = $newLocale;

		if($this->customFormat === null) {
			$formatName = $this->formatType;

			
			if($this->type == 'datetime' || $this->type == 'time') {
				if($this->formatType === null) {
					$formatName = $this->locale->getCalendarTimeFormatDefaultName('gregorian');
				} else {
					$formatName = $this->formatType;
				}
				$format = $timeFormat = $this->locale->getCalendarTimeFormatPattern('gregorian', $formatName);
			}

			if($this->type == 'datetime' || $this->type == 'date') {
				if($this->formatType === null) {
					$formatName = $this->locale->getCalendarDateFormatDefaultName('gregorian');
				} else {
					$formatName = $this->formatType;
				}
				$format = $dateFormat = $this->locale->getCalendarDateFormatPattern('gregorian', $formatName);
			}

			if($this->type == 'datetime') {
				$formatName = $this->locale->getCalendarDateTimeFormatDefaultName('gregorian');
				$formatStr = $this->locale->getCalendarDateTimeFormat('gregorian', $formatName);
				$format = str_replace(array('{0}', '{1}'), array($timeFormat, $dateFormat), $formatStr);
			}

			$this->parseFormatString($format);
		}
	}
}

?>
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
 * The number formatter will format numbers according to a given format
 *
 * @package    agavi
 * @subpackage translation
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
class AgaviNumberFormatter extends AgaviDecimalFormatter implements AgaviITranslator
{
	/**
	 * @var        AgaviContext An AgaviContext instance.
	 */
	protected $context = null;

	/**
	 * @var        AgaviLocale The locale which should be used for formatting.
	 */
	protected $locale = null;

	/**
	 * @var        string The custom format supplied by the user (if any).
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
		if(!empty($parameters['rounding_mode'])) {
			$this->setRoundingMode($this->getRoundingModeFromString($parameters['rounding_mode']));
		}
		if(isset($parameters['translation_domain'])) {
			$this->translationDomain = $parameters['translation_domain'];
		}
		if(isset($parameters['format'])) {
			$this->customFormat = $parameters['format'];
			// if the translation domain is not set we don't have to delay parsing
			if($this->translationDomain !== null) {
				$this->setFormat($parameters['format']);
			}
		}
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
			$fn = clone $this;
			$fn->localeChanged($locale);
		} else {
			$fn = $this;
			$locale = $this->locale;
		}
		
		if($this->customFormat !== null && (($this->translationDomain && $domain) || is_array($this->customFormat))) {
			if($fn === $this) {
				$fn = clone $this;
			}
			
			if(is_array($this->customFormat)) {
				$format = AgaviToolkit::getValueByKeyList($this->customFormat, AgaviLocale::getLookupPath($locale->getIdentifier()), $locale->getDecimalFormat('__default'));
			} else {
				$td = $this->translationDomain . '.' . $domain;
				$format = $this->getContext()->getTranslationManager()->_($this->customFormat, $td, $locale);
			}
			$fn->setFormat($format);
		}

		return $fn->formatNumber($message);
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
		
		$this->groupingSeparator = $this->locale->getNumberSymbolGroup();
		$this->decimalSeparator = $this->locale->getNumberSymbolDecimal();
		if($this->customFormat) {
			if(is_array($this->customFormat)) {
				$this->setFormat(AgaviToolkit::getValueByKeyList($this->customFormat, AgaviLocale::getLookupPath($this->locale->getIdentifier()), $this->locale->getDecimalFormat('__default')));
			} elseif($this->translationDomain !== null) {
				$this->setFormat($this->getContext()->getTranslationManager()->_($this->customFormat, $this->translationDomain, $this->locale));
			}
		} else {
			$this->setFormat($this->locale->getDecimalFormat('__default'));
		}
	}
}

?>
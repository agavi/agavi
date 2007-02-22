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
 * The currency formatter will format numbers according to a given format and 
 * a given currency symbol
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
class AgaviCurrencyFormatter extends AgaviDecimalFormatter implements AgaviITranslator
{
	/**
	 * @var        AgaviContext An AgaviContext instance.
	 */
	protected $context = null;

	/**
	 * @var        string The custom format supplied by the user (if any).
	 */
	protected $customFormat = null;

	/**
	 * @var        string The iso code of the currency to be used for formatting.
	 */
	protected $currencyCode = '';

	/**
	 * @var        AgaviLocale The locale which should be used for formatting.
	 */
	protected $locale = null;

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
		} else {
			$this->setRoundingMode(AgaviDecimalFormatter::ROUND_NONE);
		}
		if(isset($parameters['translation_domain'])) {
			$this->translationDomain = $parameters['translation_domain'];
		}
		if(isset($parameters['format'])) {
			$this->customFormat = $parameters['format'];
			// if the translation domain is not set and the format is not an array of per-locale strings then we don't have to delay parsing
			if($this->translationDomain === null && !is_array($this->customFormat)) {
				$this->setFormat($this->customFormat);
			}
		}
		if(isset($parameters['currency_code'])) {
			$this->currencyCode = $parameters['currency_code'];
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
				$format = AgaviToolkit::getValueByKeyList($this->customFormat, AgaviLocale::getLookupPath($locale->getIdentifier()), $locale->getCurrencyFormat('__default'));
			} else {
				$td = $this->translationDomain . '.' . $domain;
				$format = $this->getContext()->getTranslationManager()->_($this->customFormat, $td, $locale);
			}
			$fn->setFormat($format);
		}

		$code = $this->getCurrencyCode();
		$fraction = $this->getContext()->getTranslationManager()->getCurrencyFraction($code);
		$fn->setFractionDigits($fraction['digits']);

		if($fraction['rounding'] > 0) {
			$roundingUnit = pow(10, -$fraction['digits']) * $fraction['rounding'];
			$message = round($message / $roundingUnit) * $roundingUnit;
		}

		return $fn->formatCurrency($message, $fn->getCurrencySymbol());
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
				$this->setFormat(AgaviToolkit::getValueByKeyList($this->customFormat, AgaviLocale::getLookupPath($this->locale->getIdentifier()), $this->locale->getCurrencyFormat('__default')));
			} elseif($this->translationDomain !== null) {
				$this->setFormat($this->getContext()->getTranslationManager()->_($this->customFormat, $this->translationDomain, $this->locale));
			}
		} else {
			$this->setFormat($this->locale->getCurrencyFormat('__default'));
		}
	}

	/**
	 * Returns the iso code of the currency which should be used when formatting.
	 *
	 * @return     string The currency iso code.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getCurrencyCode()
	{
		$code = $this->currencyCode;
		if(!$code && $this->locale) {
			$code = $this->locale->getLocaleCurrency();
		}

		return $code;
	}

	/**
	 * Returns the currency symbol which should be used when formatting.
	 *
	 * @return     string The currency symbol
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getCurrencySymbol()
	{
		$code = $this->getCurrencyCode();
		if(!$this->locale) {
			return $code;
		}

		$symbol = $this->locale->getCurrencySymbol($code);
		$name = $this->locale->getCurrencyDisplayName($code);
		if($symbol === null) {
			$symbol = $code;
		}
		if($name === null) {
			$name = $code;
		}

		$res = '';

		switch($this->currencyType) {
			case AgaviDecimalFormatter::CURRENCY_SYMBOL:
				return $symbol;
			case AgaviDecimalFormatter::CURRENCY_CODE:
				return $code;
			case AgaviDecimalFormatter::CURRENCY_NAME:
				return $name;
		}

		return null;
	}

	/**
	 * Sets the amount of fractional digits to be shown.
	 *
	 * @param      int The amount of digits.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function setFractionDigits($count)
	{
		$this->maxShowedFractionals = $this->minShowedFractionals = $count;
	}
}

?>
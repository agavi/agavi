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
	 * @var        string The symbol which will be used as currency sign
	 */
	protected $currencySymbol = '';

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
	 * @see        AgaviITranslator::initialize()
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
		if(isset($parameters['currency_symbol'])) {
			$this->currencySymbol = $parameters['currency_symbol'];
		}
	}

	/**
	 * @see        AgaviITranslator::translate()
	 */
	public function translate($message, $domain, AgaviLocale $locale = null)
	{
		if($locale) {
			$fn = clone $this;
			$fn->localeChanged($locale);
		} else {
			$fn = $this;
		}

		if($this->translationDomain && $this->customFormat !== null && $domain) {
			if($fn === $this) {
				$fn = clone $this;
			}

			$td = $this->translationDomain . '.' . $domain;
			$format = $this->getContext()->getTranslationManager()->_($this->customFormat, $td, $locale);
			$fn->setFormat($format);
		}

		return $fn->formatCurrency($message, $fn->getCurrencySymbol());
	}

	/**
	 * @see        AgaviITranslator::localeChanged()
	 */
	public function localeChanged($newLocale)
	{
		$this->groupingSeparator = $newLocale->getNumberSymbolGroup();
		$this->decimalSeparator = $newLocale->getNumberSymbolDecimal();
		if($this->customFormat) {
			if($this->translationDomain !== null) {
				$this->setFormat($this->getContext()->getTranslationManager()->_($this->customFormat, $this->translationDomain, $newLocale));
			}
		} else {
			$this->setFormat($newLocale->getCurrencyFormat('__default'));
		}
		if($currency = $newLocale->getLocaleCurrency()) {
			if($symbol = $newLocale->getCurrencySymbol($currency)) {
				$this->currencySymbol = $symbol;
			} else {
				$this->currencySymbol = $currency;
			}
		}
	}

	/**
	 * Returns the current currency symbol.
	 *
	 * @return     string The currency symbol
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getCurrencySymbol()
	{
		return $this->currencySymbol;
	}
}

?>
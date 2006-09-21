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
 * The locale saves all kind of info about a locale
 *
 * @package    agavi
 * @subpackage translation
 *
 * @author     Dominik del Bondio <ddb@bitxtender.com>
 * @author     Agavi Project <info@agavi.org>
 * @copyright  (c) Authors
 * @since      0.11.0
 *
 * @version    $Id$
 */
class AgaviLocale
{

	protected $context = null;

	/**
	 * @var        array The data
	 */
	protected $data = array();

	/**
	 * @var        string The name of this locale
	 */
	protected $name = null;


	public function initialize(AgaviContext $context, $name, $data = array())
	{
		$this->context = $context;
		$this->name = $name;
		$this->data = $data;
	}

	public function getName()
	{
		return $this->name;
	}

	public function getLanguage($languageId)
	{
		if(!isset($this->data['languages'][$languageId])) {
			if($this->fallbackLocale) {
				$this->fallbackLocale->getLanguage($languageId);
			}
		}
		return $this->data['languages'][$languageId];
	}


	public function getCountry($countryId)
	{
		return $this->data['countries'][$countryId];
	}

	public function getCurrencySymbol($currencyId)
	{
		return $this->data['currencies'][$currencyId]['symbol'];
	}

	public function getCurrency($currencyId)
	{
		return $this->data['currencies'][$currencyId]['name'];
	}

	public function getTerritory($territoryId)
	{
		return $this->data['territories'][$territoryId];
	}

	public function getNumberSymbolDecimal()
	{
		return $this->data['numbers']['symbols']['decimal'];
	}

	public function getNumberSymbolGroup()
	{
		return $this->data['numbers']['symbols']['group'];
	}

	public function getNumberSymbolList()
	{
		return $this->data['numbers']['symbols']['list'];
	}

	public function getNumberSymbolPercent()
	{
		return $this->data['numbers']['symbols']['percentSign'];
	}

	public function getNumberSymbolZeroDigit()
	{
		return $this->data['numbers']['symbols']['nativeZeroDigit'];
	}

	public function getNumberSymbolPatternDigit()
	{
		return $this->data['numbers']['symbols']['patterDigit'];
	}

	public function getNumberSymbolPlus()
	{
		return $this->data['numbers']['symbols']['plusSign'];
	}

	public function getNumberSymbolMinus()
	{
		return $this->data['numbers']['symbols']['minusSign'];
	}

	public function getNumberSymbolExponential()
	{
		return $this->data['numbers']['symbols']['exponential'];
	}

	public function getNumberSymbolPerMille()
	{
		return $this->data['numbers']['symbols']['perMille'];
	}

	public function getNumberSymbolInfinity()
	{
		return $this->data['numbers']['symbols']['infinity'];
	}

	public function getNumberSymbolNaN()
	{
		return $this->data['numbers']['symbols']['nan'];
	}


	public function getCurrencyFormat()
	{
		return $this->data['numbers']['currencyFormat'];
	}


	public function getDecimalFormat()
	{
		return $this->data['numbers']['decimalFormat'];
	}


	public static function parseLocaleIdentifier($identifier)
	{
		// the only important thing here is the forward assertion which is needed
		// so it doesn't match the first character of the territory
		$baseLocaleRx = '(?P<language>[^_@]{2,3})(?:_(?P<script>[^_@](?=@|_|$)|[^_@]{4,}))?(?:_(?P<territory>[^_@]{2,3}))?(?:_(?P<variant>[^@]+))?';
		$optionsRx = '@(?P<options>.*)';

		$localeRx = '#^(' . $baseLocaleRx . ')(' . $optionsRx . ')?$#';

		$localeData = array(
			'language' => null,
			'script' => null,
			'territory' => null,
			'variant' => null,
			'options' => array()
		);

		if(preg_match($localeRx, $identifier, $match)) {
			$localeData['language'] = $match['language'];
			if($match['script']) {
				$localeData['script'] = $match['script'];
			}
			if($match['territory']) {
				$localeData['territory'] = $match['territory'];
			}
			if($match['variant']) {
				$localeData['variant'] = $match['variant'];
			}

			if($match['options']) {
				$options = explode(',', $match['options']);
				foreach($options as $option) {
					$optData = explode('=', $option, 2);
					$localeData['options'][$optData[0]] = $optData[1];
				}
			}
		} else {
			throw new AgaviException('Invalid locale identifier (' . $identifier . ') specified');
		}

		return $localeData;
	}

	public static function getLookupPath($localeIdentifier)
	{
		$localeInfo = self::parseLocaleIdentifier($localeIdentifier);

		
		$path = $localeInfo['language'];
		$paths[] = $path;

		if($localeInfo['script']) {
			$path = '_' . $localeInfo['script'];
			$paths[] = $path;
		}

		if($localeInfo['territory']) {
			$path = '_' . $localeInfo['territory'];
			$paths[] = $path;
		}

		if($localeInfo['variant']) {
			$path = '_' . $localeInfo['variant'];
			$paths[] = $path;
		}

		return array_reverse($paths);
	}
}

?>
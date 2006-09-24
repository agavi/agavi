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
 * The number formatter will format numbers according to a given format
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
class AgaviNumberFormatter extends AgaviDecimalFormatter implements AgaviITranslator
{
	protected $context = null;

	/**
	 * @var        bool Defines whether the formatter was initialized with a 
	 *                  custom format
	 */
	protected $hasCustomFormat = false;


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
		if($parameters instanceof AgaviLocale) {
			$this->localeChanged($parameters);
		} else {
			if(isset($parameters['format'])) {
				$this->parseFormatString($parameters['format']);
				$this->hasCustomFormat = true;
			}
		}
	}

	/**
	 * @see        AgaviITranslator::translate()
	 */
	public function translate($message, $domain, $locale)
	{
		if($locale) {
			$fn = clone $this;
			$fn->localeChange($locale);
		} else {
			$fn = $this;
		}

		return $fn->formatNumber($message);
	}

	/**
	 * @see        AgaviITranslator::localeChanged()
	 */
	public function localeChanged($newLocale)
	{
		$this->groupingSeperator = $newLocale->getNumberSymbolGroup();
		$this->decimalSeperator = $newLocale->getNumberSymbolDecimal();
		if(!$this->hasCustomFormat) {
			$this->parseFormatString($newLocale->getDecimalFormat());
		}
	}
}

?>
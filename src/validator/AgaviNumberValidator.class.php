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
 * AgaviNumberValidator verifies that a parameter is a number and allows you to
 * apply size constraints.
 * 
 * Parameters:
 *   'no_locale' do not use localized number format parsing with translation on
 *   'in_locale' locale to use for parsing rather than the current locale
 *   'type'      number type (int/integer or double/float)
 *   'cast_to'   type to cast to (int/integer or double/float)
 *   'min'       minimum value for the input
 *   'max'       maximum value for the input
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
class AgaviNumberValidator extends AgaviValidator
{
	/**
	 * Validates the input
	 * 
	 * @return     bool The input is valid number according to given parameters.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      0.11.0
	 */
	protected function validate()
	{
		$value =& $this->getData($this->getArgument());

		if(!is_scalar($value)) {
			// non scalar values would cause notices
			$this->throwError();
			return false;
		}

		$hasExtraChars = false;
		if(!is_int($value) && !is_float($value)) {
			$locale = null;
			if(AgaviConfig::get('core.use_translation') && !$this->getParameter('no_locale', false)) {
				if($locale = $this->getParameter('in_locale')) {
					$locale = $this->getContext()->getTranslationManager()->getLocale($locale);
				} else {
					$locale = $this->getContext()->getTranslationManager()->getCurrentLocale();
				}
			}
			
			$parsedValue = AgaviDecimalFormatter::parse($value, $locale, $hasExtraChars);
		} else {
			$parsedValue = $value;
		}
		
		switch(strtolower($this->getParameter('type'))) {
			case 'int':
			case 'integer':
				if(!is_int($parsedValue) || $hasExtraChars) {
					$this->throwError('type');
					return false;
				}
				
				break;
			
			case 'float':
			case 'double':
				if((!is_float($parsedValue) && !is_int($parsedValue)) || $hasExtraChars) {
					$this->throwError('type');
					return false;
				}
				
				break;
			
			default:
				if($parsedValue === false || $hasExtraChars) {
					$this->throwError('type');
					return false;
				}
		}

		if($this->hasParameter('min') && $parsedValue < $this->getParameter('min')) {
			$this->throwError('min');
			return false;
		}

		if($this->hasParameter('max') && $parsedValue > $this->getParameter('max')) {
			$this->throwError('max');
			return false;
		}
		
		switch(strtolower($this->getParameter('cast_to', $this->getParameter('type')))) {
			case 'int':
			case 'integer':
				$parsedValue = (int) $parsedValue;
				break;
			
			case 'float':
			case 'double':
				$parsedValue = (float) $parsedValue;
				break;
		}

		if($this->hasParameter('export')) {
			$this->export($parsedValue);
		} else {
			$value = $parsedValue;
		}
		
		return true;
	}
}

?>
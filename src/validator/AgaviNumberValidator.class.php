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
 * AgaviNumberValidator verifies that a parameter is a number and allows you to
 * apply size constraints.
 * 
 * Parameters:
 *   'type'       number type (int, integer or float)
 *   'type_error' error message if number has wrong type
 *   'min'        number must be at least this
 *   'min_error'  error message if number less then 'min'
 *   'max'        number must not be greater then this
 *   'max_error'  error message if number greater then 'max' 
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
	 * @since      0.11.0
	 */
	protected function validate()
	{
		$value =& $this->getData($this->getArgument());

		$hasExtraChars = false;

		if(!$this->getParameter('no_locale', false)) {
			if($locale = $this->getParameter('in_locale')) {
				$locale = $this->getContext()->getTranslationManager()->getLocale($locale);
			} else {
				$locale = $this->getContext()->getTranslationManager()->getCurrentLocale();
			}

			$value = AgaviDecimalFormatter::parse($value, $locale, $hasExtraChars);
		} else {
			if(is_numeric($value)) {
				if(((int) $value) == $value) {
					$value = (int) $value;
				} else {
					$value = (float) $value;
				}
			}
		}
		

		switch(strtolower($this->getParameter('type'))) {
			case 'int':
			case 'integer':
				if(!is_int($value) || $hasExtraChars) {
					$this->throwError('type');
					return false;
				}
				
				break;
			
			case 'float':
				if((!is_float($value) && !is_int($value)) || $hasExtraChars) {
					$this->throwError('type');
					return false;
				}
				
				break;
		}

		switch(strtolower($this->getParameter('cast_to'))) {
			case 'int':
			case 'integer':
				$value = (int) $value;
				break;

			case 'float':
			case 'double':
				$value = (float) $value;
				break;

		}

		if($this->hasParameter('min') and $value < $this->getParameter('min')) {
			$this->throwError('min');
			return false;
		}

		if($this->hasParameter('max') and $value > $this->getParameter('max')) {
			$this->throwError('max');
			return false;
		}
		
		return true;
	}
}

?>
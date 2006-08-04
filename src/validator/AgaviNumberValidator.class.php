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
 * @author     Uwe Mesecke <uwe@mesecke.net>
 * @copyright  (c) Authors
 * @since      0.11.0
 *
 * @version    $Id$
 */
class AgaviNumberValidator extends AgaviValidator
{
	/**
	 * validates the input
	 * 
	 * @return     bool input is valid number according to given parameters
	 * 
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	protected function validate() {
		$value = $this->getData();
		
		if(!is_numeric($value)) {
			$this->throwError();
			return false;
		}
		
		switch(strtolower($this->getParameter('type'))) {
			case 'int':
			case 'integer':
				if(!is_int($value)) {
					$this->throwError('type_error');
					return false;
				}
				
				break;
			
			case 'float':
				if(!is_float($value)) {
					$this->throwError('type_error');
					return false;
				}
				
				break;
		}
		
		if($$this->hasParameter('min') and $value < $this->getParameter('min')) {
			$this->throwError('min_error');
			return false;
		}
		
		if($$this->hasParameter('max') and $value > $this->getParameter('max')) {
			$this->throwError('max_error');
			return false;
		}
		
		return true;
	}
}

?>
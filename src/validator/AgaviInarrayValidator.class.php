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
 * AgaviInArrayValidator verifies whether an input is one of a set of values
 * 
 * Parameters:
 *   'values'  list of values that form the array
 *   'sep'     separator of values in the list
 *   'case'    verifies case sensitive if true
 *   'strict'  whether or not to do strict type comparisons with in_array()
 *
 * @package    agavi
 * @subpackage validator
 *
 * @author     Dominik del Bondio <ddb@bitxtender.com>
 * @author     Uwe Mesecke <uwe@mesecke.net>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.11.0
 *
 * @version    $Id$
 */
class AgaviInarrayValidator extends AgaviValidator
{
	/**
	 * Validates the input.
	 * 
	 * @return     bool The value is in the array.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	protected function validate()
	{
		$list = $this->getParameter('values');
		if(!is_array($list)) {
			$list = explode($this->getParameter('sep'), $list);
		}
		$value = $this->getData($this->getArgument());
		
		if(!is_scalar($value)) {
			$this->throwError();
			return false;
		}
		
		if(!$this->getParameter('case')) {
			$value = strtolower($value);
			$list = array_map('strtolower', $list);
		}
		
		if(!in_array($value, $list, $this->getParameter('strict', false))) {
			$this->throwError();
			return false;
		}
		
		return true;
	}
}

?>
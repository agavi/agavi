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
 * AgaviInArrayValidator verifies whether an input is one of a set of values
 * 
 * Parameters:
 *   'values'  list of values that form the array
 *   'sep'     seperator of values in the list
 *   'case'    verifies case sensitive if true
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
class AgaviInarrayValidator extends AgaviValidator
{
	/**
	 * validates the input
	 * 
	 * @return     bool the value is in the array
	 */
	protected function validate()
	{
		$list = split($this->getParameter('sep'), $this->getParameter('values'));
		$value = $this->getData();
		
		if (!$this->isBool('case')) {
			$value = strtolower($value);
			$list = array_map(create_function('$a', 'return strtolower($a)'),$list);
		}
		
		if (!in_array($this->getData(), $list)) {
			$this->throwError();
			return false;
		}
		
		return true;
	}
}

?>
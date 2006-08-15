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
 * AgaviStringValidator allows you to apply string-related constraints to a
 * parameter.
 * 
 * Parameters:
 *   'min'       string should be at least this long
 *   'min_error' error message when string is shorter then 'min'
 *   'max'       string should be at most this long
 *   'max_error' error message when string is longer then 'max'
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
class AgaviStringValidator extends AgaviValidator
{
	/**
	 * validates the input
	 * 
	 * @return     bool true, if the string is valid according to the given parameters
	 * 
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	protected function validate()
	{
		$value = (string) $this->getData();

		if($this->hasParameter('min') and strlen($value) < $this->getParameter('min')) {
			$this->throwError('min_error');
			return false;
		}
		
		if($this->hasParameter('max') and strlen($value) > $this->getParameter('max')) {
			$this->throwError('max_error');
			return false;
		}
		
		return true;
	}
}

?>
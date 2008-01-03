<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2005-2008 the Agavi Project.                                |
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
 * @author     Dominik del Bondio <ddb@bitxtender.com>
 * @author     Uwe Mesecke <uwe@mesecke.net>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.11.0
 *
 * @version    $Id$
 */
class AgaviStringValidator extends AgaviValidator
{
	/**
	 * Validates the input.
	 * 
	 * @return     bool True if the string is valid according to the given 
	 *                  parameters
	 * 
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	protected function validate()
	{
		$originalValue = $value = (string) $this->getData($this->getArgument());
		
		if($this->getParameter('utf8', true)) {
			$value = utf8_decode($value);
		}
		
		if($this->hasParameter('min') and strlen($value) < $this->getParameter('min')) {
			$this->throwError('min');
			return false;
		}
		
		if($this->hasParameter('max') and strlen($value) > $this->getParameter('max')) {
			$this->throwError('max');
			return false;
		}

		$this->export($originalValue);

		return true;
	}
}

?>
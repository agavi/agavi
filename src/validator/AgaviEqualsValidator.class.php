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
 * AgaviEqualsValidator verifies if a parameter equals to a given value
 * 
 * The input is compared to a value and the validator fails if they differ.
 * When the parameter 'asparam' is true, the content in 'value' is taken as a
 * parameter name and the check is performed against it's value otherwise the
 * content in 'value' is taken.
 * 
 * Parameters:
 *   'value'   value which the input should equals to
 *   'asparam' takes value in 'value' as name of input in request
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
class AgaviEqualsValidator extends AgaviValidator
{
	/**
	 * validates the input
	 * 
	 * @return     bool the input equals to given value
	 */
	protected function validate()
	{
		$value = ($this->asBool('asparam')) ? $this->getData($this->getParameter('value')) : $this->getParameter('value');
		if ($this->getData() != $value) {
			$this->throwError();
			return false;
		}
		
		return true;
	}
}

?>
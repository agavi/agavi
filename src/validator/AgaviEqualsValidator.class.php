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
 * AgaviEqualsValidator verifies if a parameter equals to a given value
 * 
 * The input is compared to a value and the validator fails if they differ.
 * When the parameter 'asparam' is true, the content in 'value' is taken as a
 * parameter name and the check is performed against it's value otherwise the
 * content in 'value' is taken.
 * 
 * Parameters:
 *   'value'   value which the input should equals to
 *   'asparam' whether the 'value' should be treated as a parameter name 
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
class AgaviEqualsValidator extends AgaviValidator
{
	/**
	 * Validates the input.
	 * 
	 * @return     bool The input equals to given value.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	protected function validate()
	{
		// if we have a value we compare all arguments to that value and report the 
		// individual arguments that failed
		if($this->hasParameter('value')) {
			$value = $this->getParameter('value');
			if($this->getParameter('asparam', false)) { 
				$value = $this->getData($value); 
			}
		} else {
			$value = $this->getData($this->getArgument());
		}

		foreach($this->getArguments() as $key => $argument) {
			if($this->getData($argument) != $value) {
				$this->throwError();
				return false;
			}
		}

		$this->export($value);

		return true;
	}
}

?>
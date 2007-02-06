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
 * AgaviNOTOperatorValidator succeeds if the sub-validator failed
 *
 * Parameters:
 *   'skip_errors' do not submit errors of child validators to validator manager
 *
 * @package    agavi
 * @subpackage validator
 *
 * @author     Uwe Mesecke <uwe@mesecke.net>
 * @copyright  Authors
 * @copyright  The Agavi Project
 * @since      0.11.0
 *
 * @version    $Id$
 */
class AgaviNotOperatorValidator extends AgaviOperatorValidator
{
	/**
	 * Checks if operator has more then one child validator.
	 * 
	 * @throws     <b>AgaviValidatorException</b> If the operator has more then 
	 *                                            one child validator
	 * 
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	protected function checkValidSetup()
	{
		if(count($this->children) != 1) {
			throw new AgaviValidatorException('NOT allows only 1 child validator');
		}
	}

	/**
	 * Validates the operator by returning the inverse result of the child 
	 * validator
	 * 
	 * @return     bool True if the child validator failed.
	 * 
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	protected function validate()
	{
		$result = $this->children[0]->execute($this->validationParameters);
		if($result == AgaviValidator::CRITICAL || $result == AgaviValidator::SUCCESS) {
			$this->result = max(AgaviValidator::ERROR, $result);
			$this->throwError();
			return false;
		} else {
			return true;
		}
	}	
}

?>
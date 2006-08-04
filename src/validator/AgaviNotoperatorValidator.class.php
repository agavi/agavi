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
 * AgaviNOTOperatorValidator succeeds if the sub-validator failed
 *
 * Parameters:
 *   'skip_errors'  do not submit errors of child validators to validator manager
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
class AgaviNotOperatorValidator extends AgaviOperatorValidator
{
	/**
	 * check if operator has more then one child validator
	 * 
	 * @throws     AgaviValidatorException operator has more then 1 child validator
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
	 * validates the operator by returning the inverse result of the child validator
	 * 
	 * @return     bool true, if child validator failed 
	 * 
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	protected function validate()
	{
		$result = $this->children[0]->execute();
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
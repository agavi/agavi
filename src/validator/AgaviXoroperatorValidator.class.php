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
 * AgaviXOROperatorValidator succeeds if only one of two sub-validators succeeded
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
class AgaviXoroperatorValidator extends AgaviOperatorValidator
{
	/**
	 * check if operator has other then exactly two child validators
	 * 
	 * @throws     AgaviValidatorException operator has other then 2 child validators
	 * 
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	protected function checkValidSetup()
	{
		if(count($this->children) != 2) {
			throw new AgaviValidatorException('XOR allows only exact 2 child validators');
		}
	}

	/**
	 * validates the operator by returning the by XOR compined result of the child validators
	 * 
	 * @return     bool true, if child validator failed 
	 * 
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	protected function validate()
	{
		$result1 = $this->children[0]->execute($this->validationParameters);
		if($result1 == AgaviValidator::CRITICAL) {
			$this->result = $result1;
			$this->throwError();
			return false;
		}
		$result2 = $this->children[1]->execute($this->validationParameters);
		if($result2 == AgaviValidator::CRITICAL) {
			$this->result = $result2;
			$this->throwError();
			return false;
		}
		$this->result = max($result1, $result2);

		if(($result1 == AgaviValidator::SUCCESS) xor ($result2 == AgaviValidator::SUCCESS)) {
			return true;
		} else {
			$this->throwError();
			return false;
		}
	}	
}

?>
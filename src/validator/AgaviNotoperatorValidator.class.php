<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2003-2006 the Agavi Project.                                |
// | Based on the Mojavi3 MVC Framework, Copyright (c) 2003-2005 Sean Kerr.    |
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
class AgaviNotOperatorValidator extends AgaviAbstractOperatorValidator
{
	/**
	 * check if operator has more then one child validator
	 * 
	 * @throws     AgaviValidatorException operator has more then 1 child validator
	 */
	protected function checkValidSetup ()
	{
		if (count($this->Children) != 1) {
			throw new AgaviValidatorException('NOT allows only 1 child validator');
		}
	}

	/**
	 * validates the operator by returning the inverse result of the child validator
	 * 
	 * @return     bool true, if child validator failed 
	 */
	protected function validate ()
	{
		if ($this->Children[0]->execute() != AgaviValidator::SUCCESS) {
			return true;
		} else {
			$this->throwError();
			return false;
		}
	}	
}

?>
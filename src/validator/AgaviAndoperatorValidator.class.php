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
 * AgaviANDOperatorValidator only succeeds if all sub-validators succeeded
 * 
 * Parameters:
 *   'skip_errors'  do not submit errors of child validators to validator manager
 *   'break'        break the execution of child validators after first failure
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
class AgaviAndOperatorValidator extends AgaviOperatorValidator
{
	/**
	 * 'validates' the operator by executing the child valdators
	 * 
	 * @return     bool true if all child validators resulted successful
	 * 
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	protected function validate()
	{
		$return = TRUE;
		
		foreach($this->children as $child) {
			if($child->execute() != AgaviValidator::SUCCESS) {
				// if one validator fails, the whole operator fails
				$return = FALSE;
				$this->throwError();
				if($this->getParameter('break')) {
					break;
				}
			}
		}
		
		return $return;
	}	
}

?>
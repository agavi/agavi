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
 * AgaviOROperatorValidator succeeds if at least one sub-validators succeeded
 *
 * Parameters:
 *   'skip_errors'  do not submit errors of child validators to validator manager
 *   'break'        break the execution of child validators after first success
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
class AgaviOroperatorValidator extends AgaviOperatorValidator
{
	/**
	 * executes the child validators
	 * 
	 * @return     bool true, if at least one child validator succeeded
	 * 
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	protected function validate()
	{
		$return = FALSE;
		
		foreach ($this->Children as $child) {
			if ($child->execute() == AgaviValidator::SUCCESS) {
				// if one child validator succeeds, the whole operator succeeds
				$return = TRUE;
				if  ($this->getParameter('break')) {
					break;
				}
			}
		}
		
		if (!$return) {
			$this->throwError();
		}

		return $return;
	}	
}

?>
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
class AgaviOrOperatorValidator extends AgaviAbstractOperatorValidator
{
	/**
	 * executes the child validators
	 * 
	 * @return     bool true, if at least one child validator succeeded
	 */
	protected function validate ()
	{
		$return = FALSE;
		
		foreach ($this->children as $child) {
			if ($child->execute() == AgaviValidator::SUCCESS) {
				// if one child validator succeeds, the whole operator succeeds
				$return = TRUE;
				if  ($this->asBool('break')) {
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
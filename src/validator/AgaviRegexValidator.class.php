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
 * AgaviRegexValidator allows you to match a value against a regular expression
 * pattern.
 * 
 * Parameters:
 *   'pattern'  PCRE to be used in preg_match
 *   'match'    input should match or not
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
class AgaviRegexValidator extends AgaviValidator
{
	/**
	 * validates the input
	 * 
	 * @return     bool true if input matches the pattern or not according to 'match'
	 */
	protected function validate ()
	{
		$result = preg_match($this->getParameter('pattern'), $this->getData());
		
		if ($result != $this->asBool('match')) {
			$this->throwError();
			return false;
		}
		
		return true;
	}
}

?>
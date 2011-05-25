<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2005-2011 the Agavi Project.                                |
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
 *   'export'   string with name of argument to export entire value to, or an
 *              array of subpatterns names as keys and argument names as values
 *              to selectively export one or more parts of the value
 * 
 * @package    agavi
 * @subpackage validator
 *
 * @author     David Zülke <david.zuelke@bitextender.com>
 * @author     Dominik del Bondio <ddb@bitxtender.com>
 * @author     Uwe Mesecke <uwe@mesecke.net>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.11.0
 *
 * @version    $Id$
 */
class AgaviRegexValidator extends AgaviValidator
{
	/**
	 * Validates the input.
	 * 
	 * @return     bool True if input matches the pattern in 'match'.
	 * 
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	protected function validate()
	{
		$data = $this->getData($this->getArgument());
		if(!is_scalar($data)) {
			// non scalar values would cause notices
			$this->throwError();
			return false;
		}
		
		$result = preg_match($this->getParameter('pattern'), $data, $matches);
		
		if($result != $this->getParameter('match')) {
			$this->throwError();
			return false;
		}
		
		if($this->hasParameter('export')) {
			$export = $this->getParameter('export');
			// if the result was positive (makes no sense for negative matches) and "export" is an array...
			if($result && is_array($export)) {
				// ...treat it as a map of subpattern names and argument names for exporting parts of the value
				foreach($export as $subpattern => $argument) {
					if(isset($matches[$subpattern])) {
						$this->export($matches[$subpattern], $argument);
					}
				}
			} else {
				// otherwise, just export the whole input
				$this->export($data);
			}
		}
		
		return true;
	}
}

?>
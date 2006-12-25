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
 * AgaviIssetValidator verifies a parameter is set
 * 
 * The content of the input value is not varified in any manner, it is only
 * checked if the input value exists. (see isset() in PHP)
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
class AgaviIssetValidator extends AgaviValidator
{
	/**
	 * We return true here no matter what because we will check for existance
	 * ourself.
	 *
	 * @param      bool Whether an error should be thrown for each missing 
	 *                  argument if this validator is required.
	 *
	 * @return     bool Whether the arguments are set.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	protected function checkAllArgumentsSet($throwError = true)
	{
		return true;
	}

	/**
	 * Validates the input.
	 * 
	 * @return     bool The value is set.
	 * 
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	protected function validate()
	{
		$params = $this->validationParameters->getParameters();

		foreach($this->getArguments() as $argument) {
			if(!$this->curBase->hasValueByChildPath($argument, $params)) {
				$this->throwError();
				return false;
			}
		}

		return true;
	}
}

?>
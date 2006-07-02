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
 * AgaviTimeValidator verifies a parameter is a valid time
 * 
 * Formats:
 *   * HH:MM:SS, HH:MM, HH
 *   single digits possible, not specified parts are filled with '00', possible
 *   seperators: '.', ':', ' ' and '-'
 *
 * Parameters:
 *   'check'  check if input is valid time
 * 
 * exports time in format HH:MM:SS
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
class AgaviTimeValidator extends AgaviValidator
{
	/**
	 * validates the input
	 * 
	 * @return     bool input is valid time
	 */
	protected function validate()
	{
		if (preg_match('/^(\d{1,2})(?:[.: -](\d{1,2})(?:[.: -](\d{1,2}))?)?$/', $this->getData(), $matches)) {
			$hour = $matches[1];
			if (sizeof($matches) > 2) {
				$minute = $matches[2];
			} else {
				$minute = 0;
			}
			if (sizeof($matches) > 3) {
				$second = $matches[3];
			} else {
				$second = 0;
			}
		} else {
			$this->throwError();
			return false;
		}

		if ($this->asBool('check') and ($hour > 23 or $minute > 59 or $second > 59)) {
			$this->throwError();
			return false;
		}
		
		$this->export(sprintf('%02d:%02d:%02d', $hour, $minute, $second));
		
		return true;
	}
}

?>
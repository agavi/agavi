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
 * AgaviMkTimestampValidator builds a timestamp from date and time inputs
 * 
 * Parameters:
 *   'date'         date input in format YYYY-MM-DD
 *   'time'         time input in format HH:MM:SS
 *   'error'        error message when date and time form no valid timestamp
 *   'past'         check if timestamp is in the past
 *   'past_error'   error message when timestamp not in past
 *   'future'       check if timestamp is in the future
 *   'future_error' error message when timestamp not in future
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
class AgaviMktimestampValidator extends AgaviValidator
{
	/**
	 * validates the input (builds and checks the timestamp)
	 * 
	 * @return     bool the timestamp is valid according to parameters
	 * 
	 * @throws     AgaviValidatorException date or time have invalid format
	 */
	protected function validate() {
		if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $this->getData('date'))) {
			throw new AgaviValidatorException('input date has an invalid format');
		}
		if (!preg_match('/^\d{2}:\d{2}:\d{2}$/', $this->getData('time'))) {
			throw new AgaviValidatorException('input time has an invalid format');
		}
		list($year, $month, $day) = split('-', $this->getData('date'));
		list($hour, $minute, $second) = split(':', $this->getData('time'));
		
		$timestamp = mktime($hour, $minute, $second, $month, $day, $year);
		
		if ($timestamp < 0) {
			$this->throwError();
			return false;
		}
		
		$this->export($timestamp);
		
		if ($this->hasParameter('past') and $timestamp >= time()) {
			$this->throwError('past_error');
			return false;
		}
		
		if ($this->hasParameter('future') and $timestamp <= time()) {
			$this->throwError('future_error');
			return false;
		}
		
		return true;
	}
}

?>
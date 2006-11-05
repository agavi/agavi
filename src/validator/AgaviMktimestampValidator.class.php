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
 *   'date_error'   error message when date input has an invalid format
 *   'time_error'   error message when time input has an invalid format
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
	 * Validates the input (builds and checks the timestamp).
	 * 
	 * @return     bool The timestamp is valid according to parameters.
	 * 
	 * @throws     <b>AgaviValidatorException</b> date or time have invalid format
	 * 
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	protected function validate()
	{
		if(!preg_match('/^\d{4}-\d{2}-\d{2}$/', $this->getData('date'))) {
			$this->throwError('date');
			return false;
		}
		if(!preg_match('/^\d{2}:\d{2}:\d{2}$/', $this->getData('time'))) {
			$this->throwError('time');
			return false;
		}
		list($year, $month, $day) = explode('-', $this->getData('date'));
		list($hour, $minute, $second) = explode(':', $this->getData('time'));
		
		$timestamp = mktime($hour, $minute, $second, $month, $day, $year);
		
		if($timestamp < 0) {
			$this->throwError();
			return false;
		}
		
		$this->export($timestamp);
		
		if($this->getParameter('past') && $timestamp >= time()) {
			$this->throwError('past');
			return false;
		}
		
		if($this->getParameter('future') && $timestamp <= time()) {
			$this->throwError('future');
			return false;
		}
		
		return true;
	}
}

?>
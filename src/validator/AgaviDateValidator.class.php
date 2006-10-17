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
 * AgaviDateValidator verifies that a parameter is of a date format.
 * 
 * This validator checks of the date is in a valid format. Following formats
 * are allowed:
 *   * YYYY-MM-DD, YY-MM-DD, MM-DD
 *   * DD.MM.YYYY, DD.MM.YY, DD.MM., DD., DD (seperators '.' or ' '; day and
 *                                            month also single digit possible)
 *   * MM/DD/YYYY, MM/DD (day and month also single digit possible)
 * Omitted values are set to current infos (e.g. ommitting year sets date('Y')).
 * 
 * If parameter 'check' is true, the date is checked by checkdate() if its a real
 * existing day. Optional the date can be exported in format YYYY-MM-DD.
 * 
 * Parameters:
 *   'check'   check date if the specified day really exists
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
class AgaviDateValidator extends AgaviValidator
{
	/**
	 * validates the input
	 * 
	 * @return     bool true if the input was a valid date
	 * 
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	protected function validate()
	{
		$param = $this->getData($this->getArgument());
		// check YY(YY)-MM-DD
		if(preg_match('/^(?:((?:\d{2})?\d{2})-)?(\d{2})-(\d{2})$/', $param, $matches)) {
			if(count($matches) == 4) {
				$year = $matches[1];
				$month = $matches[2];
				$day = $matches[3];
			} else {
				$year = date('Y');
				$month = $matches[1];
				$day = $matches[2];
			}
		// check DD.MM.YY(YY)
		} elseif(preg_match('/^(\d{1,2})(?:[. ](\d{1,2})(?:[. ]((?:\d{2})?\d{2}))?)?[. ]?$/', $param, $matches)) {
			$day = $matches[1];
			if(isset($matches[2])) {
				$month = $matches[2];
			} else {
				$month = date('m');
			}
			if(isset($matches[3])) {
				$year = $matches[3];
			} else {
				$year = date('Y');
			}
		// check MM/DD/YY(YY)
		} elseif(preg_match('/^(\d{1,2})\/(\d{1,2})(?:\/((?:\d{2})?\d{2}))?$/', $param, $matches)) {
			$month = $matches[1];
			$day = $matches[2];
			if(sizeof($matches) > 3) {
				$year = $matches[3];
			} else {
				$year = date('Y');
			}
		} else {
			$this->throwError();
			return false;
		}
		
		if($year < 70) {
			$year += 2000;
		} elseif($year < 100) {
			$year += 1900;
		}
		
		if($this->getParameter('check') and !checkdate($month, $day, $year)) {
			$this->throwError();
			return false;
		}
		
		$this->export(sprintf('%04d-%02d-%02d', $year, $month, $day));
		
		return true;
	}
}

?>
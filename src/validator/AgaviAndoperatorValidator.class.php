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
 * AgaviANDOperatorValidator only succeeds if all sub-validators succeeded
 * 
 * Parameters:
 *   'skip_errors' do not submit errors of child validators to validator manager
 *   'break'       break the execution of child validators after first failure
 *   'min_fail_severity' minimum child validator severity level to fail on
 *
 * @package    agavi
 * @subpackage validator
 *
 * @author     Dominik del Bondio <ddb@bitxtender.com>
 * @author     Uwe Mesecke <uwe@mesecke.net>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.11.0
 *
 * @version    $Id$
 */
class AgaviAndoperatorValidator extends AgaviOperatorValidator
{
	/**
	 * Validates the operator by executing the child validators.
	 * 
	 * @return     bool True if all child validators resulted successful.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	protected function validate()
	{
		$return = true;
		
		$min_fail_severity = $this->getParameter('min_fail_severity', AgaviValidator::INFO);
		if(strpos($min_fail_severity, '::') && defined($min_fail_severity)) {
			$min_fail_severity = constant($min_fail_severity);
		}
		
		foreach($this->children as $child) {
			$result = $child->execute($this->validationParameters);
			$this->result = max($result, $this->result);
			if($result >= $min_fail_severity) {
				// if one validator fails, the whole operator fails
				$return = false;
				$this->throwError();
				if($this->getParameter('break') || $result == AgaviValidator::CRITICAL) {
					break;
				}
			}
		}
		
		return $return;
	}	
}

?>

<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2005-2008 the Agavi Project.                                |
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
 * AgaviValidationArgumentResult stores the result of a validation run for the given argument.
 *
 * @package    agavi
 * @subpackage validator
 *
 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      1.0.0
 *
 * @version    $Id$
 */
class AgaviValidationArgumentResult
{
	/**
	 * @var        AgaviValidationResult the validation result
	 */
	protected $validationResult;
	/**
	 * @var        AgaviValidationArgument the argument
	 */
	protected $argument;
	
	/**
	 * create a new AgaviValidationArgumentResult
	 * 
	 * @param      AgaviValidationResult the result
	 * @param      AgaviValidationArgument the argument the result is valid for
	 * 
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @copyright  Authors
	 * @copyright  The Agavi Project
	 *
	 * @since      1.0.0
	 */
	public function __construct(AgaviValidationResult $result, AgaviValidationArgument $argument)
	{
		$this->validationResult = $result;
		$this->argument = $argument;
	}
	
	/**
	 * retrieve the argument 
	 * 
	 * @return     AgaviValidationArgument
	 * 
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @copyright  Authors
	 * @copyright  The Agavi Project
	 *
	 * @since      1.0.0
	 */
	public function getArgument()
	{
		return $this->argument;
	}
	
	/**
	 * retrieve the results severity 
	 * 
	 * @return     integer
	 * 
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @copyright  Authors
	 * @copyright  The Agavi Project
	 *
	 * @since      1.0.0
	 */
	public function getSeverity()
	{
		return $this->validationResult->getArgumentErrorSeverity($this->argument);
	}
	
	/**
	 * retrieve the incidents for this instances argument
	 * 
	 * @return     array an array of AgaviValidationIncidents
	 * 
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @copyright  Authors
	 * @copyright  The Agavi Project
	 *
	 * @since      1.0.0
	 */
	public function getIncidents()
	{
		$affectedIncidents = array();
		$incidents = $this->validationResult->getIncidents();
		foreach($incidents as $incident) {
			foreach($incident->getErrors() as $error) {
				if($error->hasArgument($this->argument)) {
					$affectedIncidents[] = $incident;
				}
			}
		}
		return $affectedIncidents;
	}
	
	/**
	 * retrieve the error messages for this instances argument
	 * 
	 * @return     array an array of error messages (strings)
	 * 
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @copyright  Authors
	 * @copyright  The Agavi Project
	 *
	 * @since      1.0.0
	 */
	public function getErrorMessages()
	{
		$errorMessages = array();
		$incidents = $this->validationResult->getIncidents();
		foreach($incidents as $incident) {
			foreach($incident->getErrors() as $error) {
				if($error->hasArgument($this->argument)) {
					$errorMessages = array_merge($errorMessages, $error->getErrorMessages());
				}
			}
		}
		return $errorMessages;
	}
}

?>
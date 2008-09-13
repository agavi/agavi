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
 * AgaviValidationError stores the incidents of an validation run.
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
	protected $validationResult;
	protected $argument;
	
	public function __construct(AgaviValidationResult $result, AgaviValidationArgument $argument)
	{
		$this->validationResult = $result;
		$this->argument = $argument;
	}
	
	public function getArgument()
	{
		return $this->argument;
	}
	
	public function getSeverity()
	{
		return $this->validationResult->getArgumentErrorSeverity($this->argument);
	}
	
	public function getIncidents()
	{
		$affectedIncidents = array();
		$incidents = $this->validationResult->getIncidents();
		foreach($incidents as $incident) {
			foreach($incident->getErrors() as $error) {
				if($error->hasArgument($this->argument)) {
					$affectedIncidents = $incident;
				}
			}
		}
		return $affectedIncidents;
	}
	
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
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
	 * @var        AgaviValidationReport The validation result.
	 */
	protected $validationReport;
	
	/**
	 * @var        AgaviValidationArgument The argument instance.
	 */
	protected $argument;
	
	/**
	 * Create a new AgaviValidationArgumentResult.
	 * 
	 * @param      AgaviValidationReport   The report.
	 * @param      AgaviValidationArgument The argument the result is valid for.
	 * 
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      1.0.0
	 */
	public function __construct(AgaviValidationReport $report, AgaviValidationArgument $argument)
	{
		$this->validationReport = $report;
		$this->argument = $argument;
	}
	
	/**
	 * Retrieve the argument in this result.
	 * 
	 * @return     AgaviValidationArgument The argument.
	 * 
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      1.0.0
	 */
	public function getArgument()
	{
		return $this->argument;
	}
	
	/**
	 * Retrieve the result's severity.
	 * 
	 * @return     int The severity.
	 * 
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      1.0.0
	 */
	public function getSeverity()
	{
		return $this->validationReport->getAuthoritativeArgumentSeverity($this->argument);
	}
	
	/**
	 * Retrieve the incidents of this result.
	 * 
	 * @return     array An array of AgaviValidationIncidents.
	 * 
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      1.0.0
	 */
	public function getIncidents()
	{
		$affectedIncidents = array();
		$incidents = $this->validationReport->getIncidents();
		foreach($incidents as $incident) {
			foreach($incident->getErrors() as $error) {
				if($error->hasArgument($this->argument)) {
					$affectedIncidents[] = $incident;
					break;
				}
			}
		}
		return $affectedIncidents;
	}
	
	/**
	 * Retrieve the error messages of this result.
	 * 
	 * @return     array An array of error message strings.
	 * 
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      1.0.0
	 */
	public function getErrorMessages()
	{
		$errorMessages = array();
		$incidents = $this->validationReport->getIncidents();
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
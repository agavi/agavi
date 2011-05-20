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
 * AgaviValidationReportQuery allows queries against the validation run report.
 *
 * @package    agavi
 * @subpackage validator
 *
 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
 * @author     David Zülke <david.zuelke@bitextender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      1.0.0
 *
 * @version    $Id$
 */
class AgaviValidationReportQuery implements AgaviIValidationReportQuery
{
	/**
	 * @var        AgaviValidationReport
	 */
	protected $report;
	
	/**
	 * @var        array
	 */
	protected $argumentFilter;
	
	/**
	 * @var        array
	 */
	protected $errorNameFilter;
	
	/**
	 * @var        array
	 */
	protected $validatorFilter;
	
	/**
	 * @var        array|int
	 */
	protected $minSeverityFilter;
	
	/**
	 * @var        array|int
	 */
	protected $maxSeverityFilter;
	
	/**
	 * Constructor.
	 * 
	 * @param      AgaviValidationReport The validation report instance.
	 * 
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      1.0.0
	 */
	public function __construct(AgaviValidationReport $report)
	{
		$this->report = $report;
	}
	
	/**
	 * Returns a new AgaviIValidationReportQuery which returns only the incidents
	 * for the given argument (and the other existing filter rules).
	 * 
	 * @param      AgaviValidationArgument|string|array The argument instance, or
	 *                                                  a parameter name, or an
	 *                                                  array of these elements.
	 * 
	 * @return     AgaviIValidationReportQuery
	 * 
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      1.0.0
	 */
	public function byArgument($argument)
	{
		if(is_array($argument)) {
			foreach($argument as &$arg) {
				if(!($arg instanceof AgaviValidationArgument)) {
					$arg = new AgaviValidationArgument($arg);
				}
			}
		} else {
			if(!($argument instanceof AgaviValidationArgument)) {
				$argument = new AgaviValidationArgument($argument);
			}
			$argument = array($argument);
		}
		$obj = clone $this;
		$obj->argumentFilter = $argument;
		return $obj;
	}
	
	/**
	 * Returns a new AgaviIValidationReportQuery which contains only the incidents
	 * for the given validator (and the other existing filter rules).
	 * 
	 * @param      string|array The name of the validator, or an array of names.
	 * 
	 * @return     AgaviIValidationReportQuery
	 * 
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      1.0.0
	 */
	public function byValidator($name)
	{
		if(!is_array($name)) {
			$name = array($name);
		}
		$obj = clone $this;
		$obj->validatorFilter = $name;
		return $obj;
	}
	
	/**
	 * Returns a new AgaviIValidationReportQuery which contains only the incidents
	 * for the given error name (and the other existing filter rules).
	 * 
	 * @param      string|array The name of the error, or an array of names.
	 * 
	 * @return     AgaviIValidationReportQuery
	 * 
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      1.0.0
	 */
	public function byErrorName($name)
	{
		if(!is_array($name)) {
			$name = array($name);
		}
		$obj = clone $this;
		$obj->errorNameFilter = $name;
		return $obj;
	}
	
	/**
	 * Returns a new AgaviIValidationReportQuery which contains only the incidents
	 * of the given severity or higher (and the other existing filter rules).
	 * 
	 * @param      int The minimum severity.
	 * 
	 * @return     AgaviIValidationReportQuery
	 * 
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      1.0.0
	 */
	public function byMinSeverity($minSeverity)
	{
		$obj = clone $this;
		$obj->minSeverityFilter = $minSeverity;
		return $obj;
	}
	
	/**
	 * Returns a new AgaviIValidationReportQuery which contains only the incidents
	 * of the given severity or lower (and the other existing filter rules).
	 * 
	 * @param      int The maximum severity.
	 * 
	 * @return     AgaviIValidationReportQuery
	 * 
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.0
	 */
	public function byMaxSeverity($maxSeverity)
	{
		$obj = clone $this;
		$obj->maxSeverityFilter = $maxSeverity;
		return $obj;
	}
	
	/**
	 * Retrieves the incidents filtered with the current filter rules.
	 * 
	 * @return     array
	 * 
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      1.0.0
	 */
	protected function getFilteredIncidents()
	{
		$incidents = $this->report->getIncidents();
		$resultIncidents = array();
		foreach($incidents as $incident) {
			$matches = true;
			if($this->validatorFilter && $incident->getValidator()) {
				if(!in_array($incident->getValidator()->getName(), $this->validatorFilter)) {
					continue;
				}
			}
			if($this->argumentFilter) {
				$hasArgument = false;
				foreach($incident->getArguments() as $argument) {
					if(in_array($argument, $this->argumentFilter)) {
						$hasArgument = true;
						break;
					}
				}
				if(!$hasArgument) {
					continue;
				}
			}
			
			if($this->errorNameFilter) {
				$hasErrorName = false;
				foreach($incident->getErrors() as $error) {
					if(in_array($error->getName(), $this->errorNameFilter)) {
						$hasErrorName = true;
						break;
					}
				}
				if(!$hasErrorName) {
					continue;
				}
			}
			
			if($this->minSeverityFilter) {
				if($incident->getSeverity() < $this->minSeverityFilter) {
					continue;
				}
			}
			
			if($this->maxSeverityFilter) {
				if($incident->getSeverity() > $this->maxSeverityFilter) {
					continue;
				}
			}
			
			$resultIncidents[] = $incident;
		}
		return $resultIncidents;
	}
	
	/**
	 * Retrieves all incidents which match the currently defined filter rules.
	 * 
	 * @return     array An array of AgaviValidationIncident objects.
	 * 
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      1.0.0
	 */
	public function getIncidents()
	{
		return $this->getFilteredIncidents();
	}
	
	/**
	 * Retrieves all AgaviValidationError objects which match the currently
	 * defined filter rules.
	 * 
	 * @return     array An array of AgaviValidationError objects.
	 * 
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      1.0.0
	 */
	public function getErrors()
	{
		$incidents = $this->getFilteredIncidents();
		$errors = array();
		foreach($incidents as $incident) {
			foreach($incident->getErrors() as $error) {
				if(!$this->errorNameFilter || in_array($error->getName(), $this->errorNameFilter)) {
					$errors[] = $error;
				}
			}
		}
		
		return $errors;
	}
	
	/**
	 * Retrieves all error messages which match the currently defined filter
	 * rules.
	 * 
	 * @return     array An array of message strings.
	 * 
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      1.0.0
	 */
	public function getErrorMessages()
	{
		$errors = $this->getErrors();
		$errorMessages = array();
		foreach($errors as $error) {
			$errorMessages[] = $error->getMessage();
		}
		return $errorMessages;
	}
	
	/**
	 * Retrieves all AgaviValidationArgument objects which match the currently
	 * defined filter rules.
	 * 
	 * @return     array An array of AgaviValidationArgument objects.
	 * 
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      1.0.0
	 */
	public function getArguments()
	{
		$errors = $this->getErrors();
		$arguments = array();
		foreach($errors as $error) {
			foreach($error->getArguments() as $argument) {
				if(!$this->argumentFilter || in_array($argument, $this->argumentFilter)) {
					$arguments[$argument->getHash()] = $argument;
				}
			}
		}
		return array_values($arguments);
	}
	
	/**
	 * Check if there are any incidents matching the currently defined filter
	 * rules.
	 * 
	 * @return     bool Whether or not any incidents exist for the currently
	 *                  defined filter rules.
	 * 
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      1.0.0
	 */
	public function has()
	{
		return $this->count() > 0;
	}
	
	/**
	 * Get the number of incidents matching the currently defined filter rules.
	 * 
	 * @return     int The number of incidents matching the currently defined
	 *                 filter rules.
	 * 
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      1.0.0
	 */
	public function count()
	{
		return count($this->getIncidents());
	}
	
	/**
	 * Retrieves the highest validation result code of the collection composed of
	 * the currently defined filter rules.
	 *
	 * @return     int An AgaviValidator::* severity constant, or null if there is
	 *                 no result for this filter combination. Please remember to
	 *                 do a strict === comparison if you are comparing against
	 *                 AgaviValidator::SUCCESS.
	 *
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 */
	public function getResult()
	{
		// if a filter for error names exist the result can't be success/not processed
		// since if you have an error name the field must have thrown an error
		$results = array();
		
		$arguments = array();
		foreach($this->getArguments() as $argument) {
			$arguments[$argument->getHash()] = $argument;
		}
		
		// lets start by looking at the incidents, if we find any, lets return the max result
		// (because since anything "below" an incident will have the same result as the incident, looking at the incidents is sufficient)
		// if there is no result in the incidents, the field was either not touched at all by validation,
		// or is stored in the argument results of the report, which we will then search instead
		foreach($this->getIncidents() as $incident) {
			$results[] = $incident->getSeverity();
		}
		
		if($results) {
			return max($results);
		} elseif($this->errorNameFilter) {
			return null;
		} else {
			$results = array();
			if(count($this->argumentFilter) == 1) {
				// retrieve the argument filter independent of the key
				$argument = reset($this->argumentFilter);
				if($this->validatorFilter) {
					foreach($this->validatorFilter as $validatorName) {
						$result = $this->report->getAuthoritativeArgumentSeverity($argument, $validatorName);
						if($result !== null) {
							$results[] = $result;
						}
					}
				} else {
					$result = $this->report->getAuthoritativeArgumentSeverity($argument);
					if($result !== null) {
						$results[] = $result;
					}
				}
			} else {
				foreach($this->report->getArgumentResults() as $argumentResults) {
					foreach($argumentResults as $argumentResult) {
						if(
							(!$this->argumentFilter || in_array($argumentResult['argument'], $this->argumentFilter)) &&
							(!$this->validatorFilter || ($argumentResult['validator'] && in_array($argumentResult['validator']->getName(), $this->validatorFilter)))
						) {
							$results[] = $argumentResult['severity'];
						}
					}
				}
			}
			
			if(!$results) {
				return null;
			}
			
			$result = max($results);
			if(($this->minSeverityFilter !== null && $result < $this->minSeverityFilter) || ($this->maxSeverityFilter !== null && $result > $this->maxSeverityFilter)) {
				return null;
			} else {
				return $result;
			}
		}
	}
}

?>
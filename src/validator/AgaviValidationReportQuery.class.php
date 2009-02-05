<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2005-2009 the Agavi Project.                                |
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
	 * Create a new AgaviValidationResultCollection
	 * 
	 * @param      AgaviValidationReport the validation report instance.
	 * 
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      1.0.0
	 */
	public function __construct(AgaviValidationReport $report)
	{
		$this->report = $report;
	}
	
	/**
	 * Returns a new AgaviIValidationReportQuery which contains only the incidents
	 * for the given argument.
	 * 
	 * @param      AgaviValidationArgument|string|array
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
	 * for the given validator.
	 * 
	 * @param      string|array
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
	 * for the given error name.
	 * 
	 * @param      string|array
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
	 * with the given severity or higher.
	 * 
	 * @param      int
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
					if(in_array($error->getMessageIndex(), $this->errorNameFilter)) {
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
			
			$resultIncidents[] = $incident;
		}
		return $resultIncidents;
	}
	
	/**
	 * Retrieves all incidents which match previously set the filters.
	 * 
	 * @return     array
	 * 
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      1.0.0
	 */
	public function getIncidents()
	{
		return $this->getFilteredIncidents();
	}
	
	/**
	 * Retrieves all AgaviValidationErrors which match previously set the filters.
	 * 
	 * @return     array
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
				if(!$this->errorNameFilter || in_array($error->getMessageIndex(), $this->errorNameFilter)) {
					$errors[] = $error;
					break;
				}
			}
		}
		
		return $errors;
	}
	
	/**
	 * Retrieves all error messages which match previously set the filters.
	 * 
	 * @return     array
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
	 * Retrieves all ArgumentResults which match previously set the filters.
	 * 
	 * @return     array
	 * 
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      1.0.0
	 */
	public function getArgumentResults()
	{
		$incidents = $this->getFilteredIncidents();
		$argumentResults = array();
		foreach($incidents as $incident) {
			foreach($incident->getArguments() as $argument) {
				if(!$this->argumentFilter || in_array($argument, $this->argumentFilter)) {
					$argumentResults[$argument->getHash()] = new AgaviValidationArgumentResult($this->report, $argument);
				}
			}
		}
		return array_values($argumentResults);
	}
	
	/**
	 * I Can Has Cheezburger?
	 * 
	 * @return     bool
	 * 
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      1.0.0
	 */
	public function has()
	{
		return $this->count() > 0;
	}
	
	/**
	 * Retrieves the number of incidents matching the previously set filters.
	 * 
	 * @return     int
	 * 
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      1.0.0
	 */
	public function count()
	{
		return count($this->getIncidents());
	}
	
	/**
	 * Retrieves the highest result code in the collection defined by the filters.
	 *
	 * @return     int An AgaviValidator::* severity constant.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 */
	public function getResult()
	{
		$results = array();
		
		foreach($this->getArgumentResults() as $argumentResult) {
			$results[] = $argument->getSeverity();
		}
		
		if($results) {
			return max($results);
		} else {
			return null;
		}
	}
}

?>
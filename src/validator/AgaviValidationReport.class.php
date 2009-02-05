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
 * AgaviValidationReport stores the result of a validation run.
 *
 * @package    agavi
 * @subpackage validator
 *
 * @author     Dominik del Bondio <ddb@bitextender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      1.0.0
 *
 * @version    $Id$
 */
class AgaviValidationReport implements AgaviIValidationReportQuery
{
	/**
	 * @var        array A List of result severities for each argument which has been validated.
	 */
	protected $argumentResults = array();
	
	/**
	 * @var        int The highest error severity thrown by the validation run.
	 */
	protected $result = AgaviValidator::NOT_PROCESSED;
	
	/**
	 * @var        array The incidents which were thrown by the validation run.
	 */
	protected $incidents = array();
	
	/**
	 * Returns the final validation result.
	 * 
	 * @return     int The validation result (as severity)
	 * 
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      1.0.0
	 */
	public function getResult()
	{
		return $this->result;
	}
	
	/**
	 * Sets the validation result
	 * 
	 * @param      int The new validation result
	 * 
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      1.0.0
	 */
	public function setResult($result)
	{
		$this->result = $result;
	}
	
	/**
	 * Adds an incident to the validation result. This will automatically adjust
	 * the argument result table (which is required because one can still 
	 * manually add errors either via addError or by directly using this method)
	 *
	 * @param      AgaviValidationIncident The incident.
	 *
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      1.0.0
	 */
	public function addIncident(AgaviValidationIncident $incident)
	{
		// we need to add the fields to our fieldresults if they don't exist there 
		// yet and adjust our result if needed (which only happens when this method
		// is called not from a validator)
		$severity = $incident->getSeverity();
		if($severity > $this->result) {
			$this->result = $severity;
		}
		// store the result for the argument if it's not stored yet
		foreach($incident->getArguments() as $argument) {
			$argumentSeverity = $this->getAuthoritativeArgumentSeverity($argument);
			if($argumentSeverity === null || $argumentSeverity < $severity) {
				$this->addArgumentResult($argument, $incident->getSeverity());
			}
		}
		$name = $incident->getValidator() ? $incident->getValidator()->getName() : '';
		$this->incidents[$name][] = $incident;
	}
	
	/**
	 * Checks if any incidents occured Returns all arguments which succeeded 
	 * in the validation. Includes arguments which were not processed (happens
	 *  when the argument is "not set" and the validator is not required)
	 *
	 * @return     bool The result.
	 *
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      1.0.0
	 */
	public function hasIncidents()
	{
		return count($this->incidents) > 0;
	}
	
	/**
	 * Returns all incidents which happened during the execution of the 
	 * validation.
	 *
	 * @return     array The incidents.
	 *
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      1.0.0
	 */
	public function getIncidents()
	{
		$incidents = array();
		foreach($this->incidents as $validatorIncidents) {
			$incidents = array_merge($incidents, $validatorIncidents);
		}
		return $incidents;
	}
	
	/**
	 * Adds a intermediate result of an validator for the given argument
	 *
	 * @param      AgaviValidationArgument The argument
	 * @param      int    The arguments result.
	 * @param      AgaviValidator The validator (if the error was cause inside 
	 *                            a validator).
	 *
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      1.0.0
	 */
	public function addArgumentResult(AgaviValidationArgument $argument, $result, $validator = null)
	{
		$this->argumentResults[$argument->getHash()][] = array(
			'argument' => $argument,
			'severity' => $result,
			'validator' => $validator,
		);
	}
	
	/**
	 * Will return the highest error severity for an argument. If the field was
	 * not "touched" by a validator null is returned. Can optionally be resticted
	 * to the severity of just one specific validator.
	 *
	 * @param      AgaviValidationArgument The argument.
	 * @param      string                  Optional name of a specific validator
	 *                                     to get a result for.
	 *
	 * @return     int The error severity.
	 *
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      1.0.0
	 */
	public function getAuthoritativeArgumentSeverity(AgaviValidationArgument $argument, $validatorName = null)
	{
		if(!isset($this->argumentResults[$argument->getHash()])) {
			return null;
		}

		$severity = AgaviValidator::NOT_PROCESSED;
		foreach($this->argumentResults[$argument->getHash()] as $result) {
			if($validatorName === null || ($result['validator'] instanceof AgaviValidator && $result['validator']->getName() == $validatorName)) {
				$severity = max($severity, $result['severity']);
			}
		}

		return $severity;
	}
	
	/**
	 * Checks whether an argument has been processed by a validator (this 
	 * includes arguments which were skipped because their value was not set 
	 * and the validator was not required)
	 *
	 * @param      AgaviValidationArgument The argument.
	 *
	 * @return     bool Whether the argument was validated.
	 *
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      1.0.0
	 */
	public function isArgumentValidated(AgaviValidationArgument $argument)
	{
		return isset($this->argumentResults[$argument->getHash()]);
	}
	
	/**
	 * Checks whether an argument has failed in any validator.
	 *
	 * @param      AgaviValidationArgument The argument.
	 *
	 * @return     bool Whether the validating that argument has failed.
	 *
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      1.0.0
	 */
	public function isArgumentFailed(AgaviValidationArgument $argument)
	{
		$severity = $this->getAuthoritativeArgumentSeverity($argument);
		return ($severity > AgaviValidator::SUCCESS);
	}
	
	/**
	 * Returns all arguments which succeeded in the validation. Includes 
	 * arguments which were not processed (happens when the argument is 
	 * "not set" and the validator is not required)
	 *
	 * @param      string The source for which the fields should be returned.
	 *
	 * @return     array An array of AgaviValidationArguments.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getSucceededArguments($source = null)
	{
		$arguments = array();
		foreach($this->argumentResults as $results) {
			$hasInSource = false;
			$severity = AgaviValidator::SUCCESS;
			foreach($results as $result) {
				if($source === null || $result['argument']->getSource() == $source) {
					$hasInSource = true;
					$severity = max($severity, $result['severity']);
				}
			}
			if($hasInSource && $severity <= AgaviValidator::INFO) {
				$argument = $results[0]['argument'];
				$arguments[$argument->getHash()] = $argument;
			}
		}

		return $arguments;
	}
	
	/**
	 * Returns an argument result for the given argument.
	 * 
	 * @param      string The name of the argument or an instance of an AgaviValidationArgument
	 * @param      string The source. Only used when the first parameter is a string
	 * 
	 * @return     AgaviValidationArgumentResult
	 * 
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      1.0.0
	 */
	public function getArgumentResult($nameOrArgument, $source = null)
	{
		if(!($nameOrArgument instanceof AgaviValidationArgument)) {
			$nameOrArgument = new AgaviValidationArgument($nameOrArgument, $source);
		}
		return new AgaviValidationArgumentResult($this, $nameOrArgument);
	}
	
	/**
	 * Returns an validator result for the given validator.
	 * 
	 * @param      string The name of the validator
	 * 
	 * @return     AgaviValidationValidatorResult
	 * 
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      1.0.0
	 */
	public function getValidatorResult($name)
	{
		return new AgaviValidationValidatorResult($this, $name);
	}
	
	/**
	 * Create a new AgaviValidationReportQuery for this report.
	 *
	 * @return     AgaviIValidationReportQuery
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.0
	 */
	public function createQuery()
	{
		return new AgaviValidationReportQuery($this);
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
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.0
	 */
	public function byArgument($argument)
	{
		return $this->createQuery()->byArgument($argument);
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
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.0
	 */
	public function byValidator($name)
	{
		return $this->createQuery()->byValidator($name);
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
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.0
	 */
	public function byErrorName($name)
	{
		return $this->createQuery()->byErrorName($name);
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
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.0
	 */
	public function byMinSeverity($minSeverity)
	{
		return $this->createQuery()->byMinSeverity($minSeverity);
	}
	
	/**
	 * Retrieves all AgaviValidationError objects which match the previously set
	 * filters.
	 * 
	 * @return     array
	 * 
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.0
	 */
	public function getErrors()
	{
		return $this->createQuery()->getErrors();
	}
	
	/**
	 * Retrieves all error messages which match the previously set filters.
	 * 
	 * @return     array
	 * 
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.0
	 */
	public function getErrorMessages()
	{
		return $this->createQuery()->getErrorMessages();
	}
	
	/**
	 * Retrieves all AgaviValidationArgumentResult objectss which match the 
	 * previously set filters.
	 * 
	 * @return     array
	 * 
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.0
	 */
	public function getArgumentResults()
	{
		return $this->createQuery()->getArgumentResults();
	}
	
	/**
	 * I Can Has Cheezburger?
	 * 
	 * @return     bool
	 * 
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.0
	 */
	public function has()
	{
		return $this->createQuery()->has();
	}
	
	/**
	 * Retrieves the number of incidents matching the previously set filters.
	 * 
	 * @return     int
	 * 
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.0
	 */
	public function count()
	{
		return $this->createQuery()->count();
	}
}

?>
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
 * @author     Dominik del Bondio <ddb@bitextender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      1.0.0
 *
 * @version    $Id$
 */
class AgaviValidationResult
{
	/**
	 * @var        array A List of result severities for each argument which has been validated.
	 */
	protected $argumentResults;
	/**
	 * @var        int The highest error severity thrown by the validation run.
	 */
	protected $result;
	/**
	 * @var        array The incidents which were thrown by the validation run.
	 */
	protected $incidents;
	
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
			$argumentSeverity = $this->getArgumentErrorSeverity($argument);
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
	 * @param      int The minimum severity which shall be checked for.
	 *
	 * @return     bool The result.
	 *
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      1.0.0
	 */
	public function hasIncidents($minSeverity = null)
	{
		if($minSeverity === null) {
			return count($this->incidents) > 0;
		} else {
			foreach($this->incidents as $validatorIncidents) {
				foreach($validatorIncidents as $incident) {
					if($incident->getSeverity() >= $minSeverity) {
						return true;
					}
				}
			}

			return false;
		}
	}
	
	/**
	 * Returns all incidents which happened during the execution of the 
	 * validation.
	 *
	 * @param      int The minimum severity a returned incident needs to have.
	 *
	 * @return     array The incidents.
	 *
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      1.0.0
	 */
	public function getIncidents($minSeverity = null)
	{
		$incidents = array();

		foreach($this->incidents as $validatorIncidents) {
			if($minSeverity === null) {
				$incidents = array_merge($incidents, $validatorIncidents);
			} else {
				foreach($validatorIncidents as $incident) {
					if($incident->getSeverity() >= $minSeverity) {
						$incidents[] = $incident;
					}
				}
			}
		}
		return $incidents;
	}
	
	/**
	 * Returns all incidents of a given validator.
	 *
	 * @param      string The name of the validator.
	 * @param      int The minimum severity a returned incident needs to have.
	 *
	 * @return     array The incidents.
	 *
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      1.0.0
	 */
	public function getValidatorIncidents($validatorName, $minSeverity = null)
	{
		if(!isset($this->incidents[$validatorName])) {
			return array();
		}

		if($minSeverity === null) {
			return $this->incidents[$validatorName];
		} else {
			$incidents = array();
			foreach($this->incidents[$validatorName] as $incident) {
				if($incident->getSeverity() >= $minSeverity) {
					$incidents[] = $incident;
				}
			}
			return $incidents;
		}
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
		$this->argumentResults[$argument->__getHash()][] = array($argument, $result, $validator);
	}
	
	/**
	 * Will return the highest error severity for a argument. This can be optionally 
	 * limited to the highest error severity of an validator. If the field was not 
	 * "touched" by a validator null is returned.
	 *
	 * @param      AgaviValidationArgument The argument.
	 * @param      string The Validator name.
	 *
	 * @return     int The error severity.
	 *
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      1.0.0
	 */
	// getArgumentSeverity ?
	public function getArgumentErrorSeverity(AgaviValidationArgument $argument, $validatorName = null)
	{
		if(!isset($this->argumentResults[$argument->__getHash()])) {
			return null;
		}

		$severity = AgaviValidator::NOT_PROCESSED;
		foreach($this->argumentResults[$argument->__getHash()] as $result) {
			if($validatorName === null || ($result[2] instanceof AgaviValidator && $result[2]->getName() == $validatorName)) {
				$severity = max($severity, $result[1]);
			}
		}

		return $severity;
	}
	
	/**
	 * Returns all errors of a given argument in a given validator.
	 *
	 * @param      AgaviValidationArgument The argument.
	 * @param      int The minimum severity the error needs to have.
	 *
	 * @return     array A list of AgaviValidationErrors.
	 *
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      1.0.0
	 */
	// getValidatorArgumentErrors ?
	public function getArgumentErrorsForValidator(AgaviValidationArgument $argument, $validatorName, $minSeverity = null)
	{
		$errors = array();
		foreach($this->getValidatorIncidents($validatorName, $minSeverity) as $incident) {
			$errors = array_merge($errors, $incident->getArgumentErrors($argument));
		}

		return $errors;
	}
	
	/**
	 * Returns all errors of a given argument.
	 *
	 * @param      AgaviValidationArgument The argument.
	 * @param      int The minimum severity the error needs to have.
	 *
	 * @return     array A list of AgaviValidationErrors.
	 *
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      1.0.0
	 */
	public function getArgumentErrors(AgaviValidationArgument $argument, $minSeverity = null)
	{
		$errors = array();
		foreach($this->getIncidents($minSeverity) as $incident) {
			$errors = array_merge($errors, $incident->getArgumentErrors($argument));
		}

		return $errors;
	}
	
	/**
	 * Returns all incidents of a given argument.
	 *
	 * @param      AgaviValidationArgument The argument.
	 * @param      int The minimum severity a returned incident needs to have.
	 *
	 * @return     array The incidents.
	 *
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      1.0.0
	 */
	public function getArgumentIncidents(AgaviValidationArgument $argument, $minSeverity = null)
	{
		$incidents = array();
		foreach($this->getIncidents($minSeverity) as $incident) {
			if($incident->hasArgumentError($argument)) {
				$incidents[] = $incident;
			}
		}

		return $incidents;
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
		return isset($this->argumentResults[$argument->__getHash()]);
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
		$severity = $this->getArgumentErrorSeverity($argument);
		return ($severity > AgaviValidator::SUCCESS);
	}
	
	/**
	 * Returns all failed arguments (this are all fields including those with 
	 * severity none and notice).
	 *
	 * @param      string The source which the arguments needs to have.
	 * @param      int The minimum severity an error needs to have
	 *
	 * @return     array An array of AgaviValidationArguments.
	 * 
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      1.0.0
	 */
	// argument order ?
	public function getFailedArguments($source = null, $minSeverity = null)
	{
		$arguments = array();
		foreach($this->getIncidents($minSeverity) as $incident) {
			foreach($incident->getArguments as $argument) {
				if($source === null || $argument->getSource() == $source) {
					$arguments[$argument->__getHash()] = $argument;
				}
			}
		}

		return $arguments;
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
				if($source === null || $result[0]->getSource() == $source) {
					$hasInSource = true;
					$severity = max($severity, $result[1]);
				}
			}
			if($hasInSource && $severity <= AgaviValidator::INFO) {
				$arguments[] = $results[0][0];
			}
		}

		return $arguments;
	}
}

?>
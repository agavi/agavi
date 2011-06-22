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
	protected $result = null;
	
	/**
	 * @var        array The incidents which were thrown by the validation run.
	 */
	protected $incidents = array();
	
	/**
	 * Retrieves the highest validation result code in this report.
	 *
	 * @return     int An AgaviValidator::* severity constant, or null if there is
	 *                 no result. Please remember to do a strict === comparison if
	 *                 you are comparing against AgaviValidator::SUCCESS.
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
		$validator = $incident->getValidator();
		if($severity > $this->result || null === $this->result) {
			$this->result = $severity;
		}
		// store the result for the argument if it's not stored yet
		foreach($incident->getArguments() as $argument) {
			$this->addArgumentResult($argument, $severity, $validator);
		}
		$this->incidents[$validator ? $validator->getName() : ''][] = $incident;
	}
	
	/**
	 * Checks if any incidents occurred Returns all arguments which succeeded 
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
	public function addArgumentResult(AgaviValidationArgument $argument, $result, AgaviValidator $validator = null)
	{
		$this->argumentResults[$argument->getHash()][] = array(
			'argument' => $argument,
			'severity' => $result,
			'validator' => $validator,
		);
	}
	
	/**
	 * Retrieve the internal array (indexed by argument hash) of
	 * argument/severity/validator tuples.
	 * This method exposes an internal data structure that may change at any time.
	 * You shouldn't have to use this method.
	 * Don't even think about using it to harm cute little animals, or you shall
	 * suffer the wrath of an angry god.
	 *
	 * @return     array An array of argument result info arrays.
	 *
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.0
	 */
	public function getArgumentResults()
	{
		return $this->argumentResults;
	}
	
	/**
	 * Will return the highest error severity for an argument. If the field was
	 * not "touched" by a validator null is returned. Can optionally be restricted
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

		$severity = null;
		
		foreach($this->argumentResults[$argument->getHash()] as $result) {
			if($validatorName === null || ($result['validator'] instanceof AgaviValidator && $result['validator']->getName() == $validatorName)) {
				if(null === $severity) {
					$severity = AgaviValidator::NOT_PROCESSED;
				}
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
	 * Returns all arguments which validated successfully.
	 *
	 * @param      string Optional source name to limit the list of arguments to.
	 *
	 * @return     array An array of AgaviValidationArgument objects.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getSucceededArguments($source = null)
	{
		$arguments = array();
		foreach($this->argumentResults as $results) {
			$hasInSource = false;
			$severity = AgaviValidator::NOT_PROCESSED;
			foreach($results as $result) {
				if($source === null || $result['argument']->getSource() == $source) {
					$hasInSource = true;
					$severity = max($severity, $result['severity']);
				}
			}
			if($hasInSource && $severity >= AgaviValidator::SUCCESS && $severity <= AgaviValidator::INFO) {
				$argument = $results[0]['argument'];
				$arguments[$argument->getHash()] = $argument;
			}
		}

		return $arguments;
	}
	
	/**
	 * Returns all arguments which failed in the validation.
	 *
	 * @param      string Optional source name to limit the list of arguments to.
	 *
	 * @return     array An array of AgaviValidationArgument objects.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.1
	 */
	public function getFailedArguments($source = null)
	{
		// shortcut if validation was successful - there won't be failed args in that case
		if($this->getResult() <= AgaviValidator::INFO) {
			return array();
		}
		
		$arguments = array();
		foreach($this->argumentResults as $results) {
			$hasInSource = false;
			$severity = AgaviValidator::NOT_PROCESSED;
			foreach($results as $result) {
				if($source === null || $result['argument']->getSource() == $source) {
					$hasInSource = true;
					$severity = max($severity, $result['severity']);
				}
			}
			if($hasInSource && $severity > AgaviValidator::INFO) {
				$argument = $results[0]['argument'];
				$arguments[$argument->getHash()] = $argument;
			}
		}

		return $arguments;
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
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.0
	 */
	public function byArgument($argument)
	{
		return $this->createQuery()->byArgument($argument);
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
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.0
	 */
	public function byValidator($name)
	{
		return $this->createQuery()->byValidator($name);
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
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.0
	 */
	public function byErrorName($name)
	{
		return $this->createQuery()->byErrorName($name);
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
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.0
	 */
	public function byMinSeverity($minSeverity)
	{
		return $this->createQuery()->byMinSeverity($minSeverity);
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
		return $this->createQuery()->byMaxSeverity($maxSeverity);
	}
	
	/**
	 * Retrieves all AgaviValidationError objects in this report.
	 * 
	 * @return     array An array of AgaviValidationError objects.
	 * 
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.0
	 */
	public function getErrors()
	{
		return $this->createQuery()->getErrors();
	}
	
	/**
	 * Retrieves all error messages in this report.
	 * 
	 * @return     array An array of message strings.
	 * 
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.0
	 */
	public function getErrorMessages()
	{
		return $this->createQuery()->getErrorMessages();
	}
	
	/**
	 * Retrieves all AgaviValidationArgument objects in this report.
	 * 
	 * @return     array An array of AgaviValidationArgument objects.
	 * 
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.0
	 */
	public function getArguments()
	{
		return $this->createQuery()->getArguments();
	}
	
	/**
	 * Check if there are any incidents matching the currently defined filter
	 * rules.
	 * 
	 * @return     bool Whether or not any incidents exist in this report.
	 * 
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.0
	 */
	public function has()
	{
		return $this->createQuery()->has();
	}
	
	/**
	 * Get the number of incidents matching the currently defined filter rules.
	 * 
	 * @return     int The number of incidents in this report.
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
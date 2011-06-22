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
 * AgaviValidationManager provides management for request parameters and their
 * associated validators.
 *
 * @package    agavi
 * @subpackage validator
 *
 * @author     Uwe Mesecke <uwe@mesecke.net>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.11.0
 *
 * @version    $Id$
 */
class AgaviValidationManager extends AgaviParameterHolder implements AgaviIValidatorContainer
{
	/**
	 * @var        AgaviDependencyManager The dependency manager.
	 */
	protected $dependencyManager = null;

	/**
	 * @var        array An array of child validators.
	 */
	protected $children = array();

	/**
	 * @var        AgaviContext The context instance.
	 */
	protected $context = null;

	/**
	 * @var        AgaviValidationReport The report container storing the validation results.
	 */
	protected $report = null;

	/**
	 * All request variables are always available.
	 */
	const MODE_RELAXED = 'relaxed';

	/**
	 * All request variables are available when no validation defined else only 
	 * validated request variables are available.
	 */
	const MODE_CONDITIONAL = 'conditional';

	/**
	 * Only validated request variables are available.
	 */
	const MODE_STRICT = 'strict';

	/**
	 * initializes the validator manager.
	 *
	 * @param      AgaviContext The context instance.
	 * @param      array        The initialization parameters.
	 *
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	public function initialize(AgaviContext $context, array $parameters = array())
	{
		if(isset($parameters['mode'])) {
			if(!in_array($parameters['mode'], array(self::MODE_RELAXED, self::MODE_CONDITIONAL, self::MODE_STRICT))) {
				throw new AgaviConfigurationException('Invalid validation mode "' . $parameters['mode'] . '" specified');
			}
		} else {
			$parameters['mode'] = self::MODE_STRICT;
		}

		$this->context = $context;
		$this->setParameters($parameters);

		$this->dependencyManager = new AgaviDependencyManager();
		$this->report = new AgaviValidationReport();
		$this->children = array();
	}

	/**
	 * Retrieve the current application context.
	 *
	 * @return     AgaviContext The current Context instance.
	 *
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	public final function getContext()
	{
		return $this->context;
	}
	
	/**
	 * Retrieve the validation result report container of the last validation run.
	 *
	 * @return     AgaviValidationReport The result report container.
	 *
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      1.0.0
	 */
	public function getReport()
	{
		return $this->report;
	}

	/**
	 * Creates a new validator instance.
	 *
	 * @param      string The name of the class implementing the validator.
	 * @param      array The argument names.
	 * @param      array The error messages.
	 * @param      array The validator parameters.
	 * @param      AgaviIValidatorContainer The parent (will use the validation 
	 *                                      manager if null is given)
	 * @return     AgaviValidator
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function createValidator($class, array $arguments, array $errors = array(), $parameters = array(), AgaviIValidatorContainer $parent = null)
	{
		if($parent === null) {
			$parent = $this;
		}
		$obj = new $class;
		$obj->initialize($this->getContext(), $parameters, $arguments, $errors);
		$parent->addChild($obj);

		return $obj;
	}

	/**
	 * Clears the validation manager for reuse
	 *
	 * clears the validator manager by resetting the dependency and error
	 * manager and removing all validators after calling their shutdown
	 * method so they can do a save shutdown.
	 *
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	public function clear()
	{
		$this->dependencyManager->clear();

		$this->report = new AgaviValidationReport();

		foreach($this->children as $child) {
			$child->shutdown();
		}
		$this->children = array();
	}

	/**
	 * Adds a new child validator.
	 *
	 * @param      AgaviValidator The new child validator.
	 *
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	public function addChild(AgaviValidator $validator)
	{
		$name = $validator->getName();
		if(isset($this->children[$name])) {
			throw new InvalidArgumentException('A validator with the name "' . $name . '" already exists');
		}

		$this->children[$name] = $validator;
		$validator->setParentContainer($this);
	}

	/**
	 * Returns a named child validator.
	 *
	 * @param      AgaviValidator The child validator.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getChild($name)
	{
		if(!isset($this->children[$name])) {
			throw new InvalidArgumentException('A validator with the name "' . $name . '" does not exist');
		}

		return $this->children[$name];
	}

	/**
	 * Returns all child validators.
	 *
	 * @return     array An array of AgaviValidator instances.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getChilds()
	{
		return $this->children;
	}

	/**
	 * Returns the dependency manager.
	 *
	 * @return     AgaviDependencyManager The dependency manager instance.
	 *
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	public function getDependencyManager()
	{
		return $this->dependencyManager;
	}

	/**
	 * Gets the base path of the validator.
	 *
	 * @return     AgaviVirtualArrayPath The base path.
	 *
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	public function getBase()
	{
		return new AgaviVirtualArrayPath($this->getParameter('base', ''));
	}

	/**
	 * Starts the validation process.
	 *
	 * @param      AgaviRequestDataHolder The data which should be validated.
	 *
	 * @return     bool true, if validation succeeded.
	 *
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	public function execute(AgaviRequestDataHolder $parameters)
	{
		$success = true;
		$this->report = new AgaviValidationReport();
		$result = AgaviValidator::SUCCESS;
		
		$req = $this->context->getRequest();

		$executedValidators = 0;
		foreach($this->children as $validator) {
			++$executedValidators;

			$validatorResult = $validator->execute($parameters);
			$result = max($result, $validatorResult);

			switch($validatorResult) {
				case AgaviValidator::SUCCESS:
					continue 2;
				case AgaviValidator::INFO:
					continue 2;
				case AgaviValidator::SILENT:
					continue 2;
				case AgaviValidator::NOTICE:
					continue 2;
				case AgaviValidator::ERROR:
					$success = false;
					continue 2;
				case AgaviValidator::CRITICAL:
					$success = false;
					break 2;
			}
		}
		$this->report->setResult($result);

		$ma = $req->getParameter('module_accessor');
		$aa = $req->getParameter('action_accessor');
		$umap = $req->getParameter('use_module_action_parameters');

		$mode = $this->getParameter('mode');

		if($executedValidators == 0 && $mode == self::MODE_STRICT) {
			// strict mode and no validators executed -> clear the parameters
			if($umap) {
				$maParam = $parameters->getParameter($ma);
				$aaParam = $parameters->getParameter($aa);
			}
			$parameters->clearAll();
			if($umap) {
				if($maParam) {
					$parameters->setParameter($ma, $maParam);
				}
				if($aaParam) {
					$parameters->setParameter($aa, $aaParam);
				}
			}
		}

		if($mode == self::MODE_STRICT || ($executedValidators > 0 && $mode == self::MODE_CONDITIONAL)) {
			
			// first, we explicitly unset failed arguments
			// the primary purpose of this is to make sure that arrays that failed validation themselves (e.g. due to array length validation, or due to use of operator validators with an argument base) are removed
			// that's of course only necessary if validation failed
			$failedArguments = $this->report->getFailedArguments();
			foreach($failedArguments as $argument) {
				$parameters->remove($argument->getSource(), $argument->getName());
			}
			
			// next, we remove all arguments from the request data that are not in the list of succeeded arguments
			// this will also remove any arguments that didn't have validation rules defined
			$succeededArguments = $this->report->getSucceededArguments();
			foreach($parameters->getSourceNames() as $source) {
				$sourceItems = $parameters->getAll($source);
				foreach(AgaviArrayPathDefinition::getFlatKeyNames($sourceItems) as $name) {
					if(!isset($succeededArguments[$source . '/' . $name]) && (!$umap || ($source != AgaviRequestDataHolder::SOURCE_PARAMETERS || ($name != $ma && $name != $aa)))) {
						$parameters->remove($source, $name);
					}
				}
			}
		}

		return $success;
	}

	/**
	 * Shuts the validation system down.
	 *
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	public function shutdown()
	{
		foreach($this->children as $child) {
			$child->shutdown();
		}
	}

	/**
	 * Registers multiple validators.
	 *
	 * @param      array An array of validators.
	 *
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	public function registerValidators(array $validators)
	{
		foreach($validators as $validator) {
			$this->addChild($validator);
		}
	}
	
	/**
	 * Adds an incident to the validation result. This will automatically adjust
	 * the field result table (which is required because one can still manually
	 * add errors either via AgaviRequest::addError or by directly using this 
	 * method)
	 *
	 * @param      AgaviValidationIncident The incident.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function addIncident(AgaviValidationIncident $incident)
	{
		return $this->report->addIncident($incident);
	}
	
	
	/////////////////////////////////////////////////////////////////////////////
	////////////////////////////// Deprecated Parts /////////////////////////////
	/////////////////////////////////////////////////////////////////////////////
	
	
	/**
	 * Returns the final validation result.
	 *
	 * @return     int The result of the validation process.
	 *
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      0.11.0
	 *
	 * @deprecated 1.0.0
	 */
	public function getResult()
	{
		$result = $this->report->getResult();
		
		if(null === $result) {
			$result = AgaviValidator::NOT_PROCESSED;
		}
		
		return $result;
	}

	/**
	 * Adds a validation result for a given field.
	 *
	 * @param      AgaviValidator The validator.
	 * @param      string The name of the field which has been validated.
	 * @param      int    The result of the validation.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 *
	 * @deprecated 1.0.0
	 */
	public function addFieldResult($validator, $fieldname, $result)
	{
		$argument = new AgaviValidationArgument($fieldname);
		return $this->report->addArgumentResult($argument, $result, $validator);
	}

	/**
	 * Adds a intermediate result of an validator for the given argument
	 *
	 * @param      AgaviValidationArgument The argument
	 * @param      int                     The arguments result.
	 * @param      AgaviValidator          The validator (if the error was caused
	 *                                     inside a validator).
	 *
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      1.0.0
	 */
	public function addArgumentResult(AgaviValidationArgument $argument, $result, $validator = null)
	{
		return $this->report->addArgumentResult($argument, $result, $validator);
	}

	/**
	 * Will return the highest error code for a field. This can be optionally 
	 * limited to the highest error code of an validator. If the field was not 
	 * "touched" by a validator null is returned.
	 *
	 * @param      string The name of the field.
	 * @param      string The Validator name
	 *
	 * @return     int The error code.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 *
	 * @deprecated 1.0.0
	 */
	public function getFieldErrorCode($fieldname, $validatorName = null)
	{
		return $this->report->getAuthoritativeArgumentSeverity(new AgaviValidationArgument($fieldname), $validatorName);
	}

	/**
	 * Checks whether a field has failed in any validator.
	 *
	 * @param      string The name of the field.
	 *
	 * @return     bool Whether the field has failed.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 *
	 * @deprecated 1.0.0
	 */
	public function isFieldFailed($fieldname)
	{
		return $this->report->isArgumentFailed(new AgaviValidationArgument($fieldname));
	}

	/**
	 * Checks whether a field has been processed by a validator (this includes
	 * fields which were skipped because their value was not set and the validator
	 * was not required)
	 *
	 * @param      string The name of the field.
	 *
	 * @return     bool Whether the field was validated.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 *
	 * @deprecated 1.0.0
	 */
	public function isFieldValidated($fieldname)
	{
		return $this->report->isArgumentValidated(new AgaviValidationArgument($fieldname));
	}

	/**
	 * Returns all fields which succeeded in the validation. Includes fields which
	 * were not processed (happens when the field is "not set" and the validator 
	 * is not required)
	 *
	 * @param      string The source for which the fields should be returned.
	 *
	 * @return     array An array of field names.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 *
	 * @deprecated 1.0.0
	 */
	public function getSucceededFields($source)
	{
		$names = array();
		$arguments = $this->report->getSucceededArguments($source);
		foreach($arguments as $argument) {
			$names[] = $argument->getName();
		}
		
		return $names;
	}
	
	/**
	 * Checks if any incidents occurred Returns all fields which succeeded in the 
	 * validation. Includes fields which were not processed (happens when the 
	 * field is "not set" and the validator is not required)
	 *
	 * @param      int The minimum severity which shall be checked for.
	 *
	 * @return     bool The result.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 *
	 * @deprecated 1.0.0
	 */
	public function hasIncidents($minSeverity = null)
	{
		return count($this->getIncidents($minSeverity)) > 0;
	}

	/**
	 * Returns all incidents which happened during the execution of the validation.
	 *
	 * @param      int The minimum severity a returned incident needs to have.
	 *
	 * @return     array The incidents.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 *
	 * @deprecated 1.0.0
	 */
	public function getIncidents($minSeverity = null)
	{
		$incidents = array();
		if($minSeverity === null) {
			return $this->report->getIncidents();
		} else {
			foreach($this->report->getIncidents() as $incident) {
				if($incident->getSeverity() >= $minSeverity) {
					$incidents[] = $incident;
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
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 *
	 * @deprecated 1.0.0
	 */
	public function getValidatorIncidents($validatorName, $minSeverity = null)
	{
		$incidents = $this->report->byValidator($validatorName)->getIncidents();
		
		if($minSeverity === null) {
			return $incidents;
		} else {
			$matchingIncidents = array();
			foreach($incidents as $incident) {
				if($incident->getSeverity() >= $minSeverity) {
					$matchingIncidents[] = $incident;
				}
			}
			return $matchingIncidents;
		}
	}
	/**
	 * Returns all incidents of a given field.
	 *
	 * @param      string The name of the field.
	 * @param      int The minimum severity a returned incident needs to have.
	 *
	 * @return     array The incidents.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 *
	 * @deprecated 1.0.0
	 */
	public function getFieldIncidents($fieldname, $minSeverity = null)
	{
		$incidents = $this->report->byArgument($fieldname)->getIncidents();
		
		if($minSeverity === null) {
			return $incidents;
		} else {
			$matchingIncidents = array();
			foreach($incidents as $incident) {
				if($incident->getSeverity() >= $minSeverity) {
					$matchingIncidents[] = $incident;
				}
			}
			return $matchingIncidents;
		}
	}

	/**
	 * Returns all errors of a given field.
	 *
	 * @param      string The name of the field.
	 * @param      int The minimum severity a returned incident of the error 
	 *                 needs to have.
	 *
	 * @return     array The errors.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 *
	 * @deprecated 1.0.0
	 */
	public function getFieldErrors($fieldname, $minSeverity = null)
	{
		$incidents = $this->getFieldIncidents($fieldname, $minSeverity);
		$errors = array();
		foreach($incidents as $incident) {
			$errors = array_merge($errors, $incident->getErrors());
		}
		return $errors;
	}

	/**
	 * Returns all errors of a given field in a given validator.
	 *
	 * @param      string The name of the field.
	 * @param      int The minimum severity a returned incident of the error 
	 *                 needs to have.
	 *
	 * @return     array The incidents.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 *
	 * @deprecated 1.0.0
	 */
	public function getValidatorFieldErrors($validatorName, $fieldname, $minSeverity = null)
	{
		$incidents = $this->getFieldIncidents($fieldname, $minSeverity);
		$matchingIncidents = array();
		foreach($incidents as $incident) {
			$validator = $incident->getValidator();
			if($validator && $validator->getName() == $validatorName) {
				$matchingIncidents[] = $incident;
			}
		}
		return $matchingIncidents;
	}

	/**
	 * Returns all failed fields (this are all fields including those with 
	 * severity none and notice).
	 *
	 * @return     array The names of the fields.
	 * @param      int The minimum severity a field needs to have.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 *
	 * @deprecated 1.0.0
	 */
	public function getFailedFields($minSeverity = null)
	{
		$fields = array();
		foreach($this->getIncidents($minSeverity) as $incident) {
			$fields = array_merge($fields, $incident->getFields());
		}
		
		return array_values(array_unique($fields));
	}
	
	/**
	 * Retrieve an error message.
	 *
	 * @param      string An error name.
	 *
	 * @return     string The error message.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.9.0
	 *
	 * @deprecated 1.0.0
	 */
	public function getError($name)
	{
		$incidents = $this->getFieldIncidents($name, AgaviValidator::NOTICE);

		if(count($incidents) == 0) {
			return null;
		}

		$errors = $incidents[0]->getErrors();
		return $errors[0]->getMessage();
	}

	/**
	 * Retrieve an array of error names.
	 *
	 * @return     array An indexed array of error names.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.9.0
	 *
	 * @deprecated 1.0.0
	 */
	public function getErrorNames()
	{
		return $this->getFailedFields();
	}

	/**
	 * Retrieve an array of errors.
	 *
	 * @param      string An optional error name.
	 *
	 * @return     array An associative array of errors(if no name was given) as
	 *                   an array with the error messages (key 'messages') and
	 *                   the validators (key 'validators') which failed.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.9.0
	 *
	 * @deprecated 1.0.0
	 */
	public function getErrors($name = null)
	{
		$errors = array();

		foreach($this->getIncidents(AgaviValidator::NOTICE) as $incident) {
			$validator = $incident->getValidator();
			foreach($incident->getErrors() as $error) {
				$msg = $error->getMessage();
				foreach($error->getFields() as $field) {
					if(!isset($errors[$field])) {
						$errors[$field] = array('messages' => array(), 'validators' => array());
					}
					$errors[$field]['messages'][] = $msg;
					if($validator) {
						$errors[$field]['validators'][] = $validator->getName();
					}
				}
			}
		}

		if($name === null) {
			return $errors;
		} else {
			return isset($errors[$name]) ? $errors[$name] : null;
		}
	}

	/**
	 * Retrieve an array of error Messages.
	 *
	 * @param      string An optional error name.
	 *
	 * @return     array An indexed array of error messages (if a name was given)
	 *                   or an indexed array in this format:
	 *                   array('message' => string, 'errors' => array(string))
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 *
	 * @deprecated 1.0.0
	 */
	public function getErrorMessages($name = null)
	{

		if($name !== null) {
			$incidents = $this->getFieldIncidents($name, AgaviValidator::NOTICE);
			$msgs = array();
			foreach($incidents as $incident) {
				foreach($incident->getErrors() as $error) {
					$msgs[] = $error->getMessage();
				}
			}
			return $msgs;
		} else {
			$msgs = array();

			$incidents = $this->getIncidents(AgaviValidator::NOTICE);
			$msgs = array();
			foreach($incidents as $incident) {
				foreach($incident->getErrors() as $error) {
					$msgs[] = array('message' => $error->getMessage(), 'errors' => $error->getFields());
				}
			}
			return $msgs;
		}
	}

	/**
	 * Indicates whether or not a field has an error.
	 *
	 * @param      string A field name.
	 *
	 * @return     bool true, if the field has an error, false otherwise.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      0.9.0
	 *
	 * @deprecated 1.0.0
	 */
	public function hasError($name)
	{
		$ec = $this->getFieldErrorCode($name);
		// greater than or equal to notice cause that's when we need to show an error (this is different to hasErrors() behavior due to legacy)
		return ($ec >= AgaviValidator::NOTICE);
	}

	/**
	 * Indicates whether or not any errors exist.
	 *
	 * @return     bool true, if any error exist, otherwise false.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.9.0
	 *
	 * @deprecated 1.0.0
	 */
	public function hasErrors()
	{
		// anything above notice. just notice means validation didn't fail, although a notice is considered an error itself. but notices only "show up" if other validators with higher severity (error, fatal) failed
		return $this->getResult() > AgaviValidator::NOTICE;
	}

	/**
	 * Set an error.
	 *
	 * @param      string An error name.
	 * @param      string An error message.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.9.0
	 *
	 * @deprecated 1.0.0
	 */
	public function setError($name, $message)
	{
		$name = new AgaviValidationArgument($name);
		$incident = new AgaviValidationIncident(null, AgaviValidator::ERROR);
		$incident->addError(new AgaviValidationError($message, null, array($name)));
		$this->addIncident($incident);
	}

	/**
	 * Set an array of errors
	 *
	 * If an existing error name matches any of the keys in the supplied
	 * array, the associated message will be appended to the messages array.
	 *
	 * @param      array An associative array of errors and their associated
	 *                   messages.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.9.0
	 *
	 * @deprecated 1.0.0
	 */
	public function setErrors(array $errors)
	{
		$incident = new AgaviValidationIncident(null, AgaviValidator::ERROR);
		foreach($errors as $name => $error) {
			$name = new AgaviValidationArgument($name);
			$incident->addError(new AgaviValidationError($error, null, array($name)));
		}

		$this->addIncident($incident);
	}
}
?>
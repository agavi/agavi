<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2003-2006 the Agavi Project.                                |
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
 * @copyright  (c) Authors
 * @since      0.11.0
 *
 * @version    $Id$
 */

class AgaviValidationManager extends AgaviParameterHolder implements AgaviIValidationManager, AgaviIValidatorContainer
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
	 * @var        array The results for each field which has been validated.
	 */
	protected $fieldResults = array();

	/**
	 * @var        int The highest error severity in the container.
	 */
	protected $result = AgaviValidator::SUCCESS;

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
			$parameters['mode'] = self::MODE_RELAXED;
		}

		$this->context = $context;
		$this->setParameters($parameters);

		$this->dependencyManager = new AgaviDependencyManager();
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
	public function getContext()
	{
		return $this->context;
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
		$this->fieldResults = array();
		$this->incidents = array();
		$this->result = AgaviValidator::SUCCESS;


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
			throw new IllegalArgumentException('A validator with the name "' . $name . '" already exists');
		}

		$this->children[$name] = $validator;
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
			throw new IllegalArgumentException('A validator with the name "' . $name . '" does not exist');
		}

		return $this->children[$name];
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
	 * @param      AgaviRequestDataHolder The datawhich should be validated.
	 *
	 * @return     bool true, if validation succeeded.
	 *
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	public function execute(AgaviRequestDataHolder $parameters)
	{
		$result = true;
		$this->result = AgaviValidator::SUCCESS;

		$requestMethod = $this->getContext()->getRequest()->getMethod();
		$executedValidators = 0;
		foreach($this->children as $validator) {
			if(!$validator->validatesInMethod($requestMethod)) {
				continue;
			}

			++$executedValidators;

			$v_ret = $validator->execute($parameters);
			$this->result = max($this->result, $v_ret);

			switch($v_ret) {
				case AgaviValidator::SUCCESS:
					continue 2;
				case AgaviValidator::NONE:
					continue 2;
				case AgaviValidator::NOTICE:
					continue 2;
				case AgaviValidator::ERROR:
					$result = false;
					continue 2;
				case AgaviValidator::CRITICAL:
					$result = false;
					break 2;
			}
		}

		$ma = $this->getContext()->getRequest()->getModuleAccessor();
		$aa = $this->getContext()->getRequest()->getActionAccessor();

		$mode = $this->getParameter('mode');

		if($executedValidators == 0 && $mode == self::MODE_STRICT) {
			// strict mode and no validators executed -> clear the parameters
			$maParam = $parameters->getParameter($ma);
			$aaParam = $parameters->getParameter($aa);
			// FIXME: AgaviRequestDataHolder needs clearAll() method
			$parameters->clearParameters();
			if($maParam) {
				$parameters->setParameter($ma, $maParam);
			}
			if($aaParam) {
				$parameters->setParameter($aa, $aaParam);
			}
		}

		if($mode == self::MODE_STRICT || ($executedValidators > 0 && $mode == self::MODE_CONDITIONAL)) {
			$asf = array_flip($this->getSucceededFields());
			// FIXME: Bad news... this system must handle more than just "parameters" :S
			foreach($parameters->getFlatParameterNames() as $name) {
				if(!isset($asf[$name]) && $name != $ma && $name != $aa) {
					$parameters->removeParameter($name);
				}
			}
		}

		return $result;
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
	 * Returns the result from the error manager
	 *
	 * @return     int The result of the validation process.
	 *
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	public function getResult()
	{
		return $this->result;
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
	 */
	public function addFieldResult($validator, $fieldname, $result)
	{
		$this->fieldResults[$fieldname][] = array($validator, $result);
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
	 */
	public function getFieldErrorCode($fieldname, $validatorName = null)
	{
		if(!isset($this->fieldResults[$fieldname])) {
			return null;
		}

		$ec = AgaviValidator::NOT_PROCESSED;
		foreach($this->fieldResults[$fieldname] as $result) {
			if($validatorName === null || ($result[0] instanceof AgaviValidator && $result[0]->getName() == $validatorName)) {
				$ec = max($ec, $result[1]);
			}
		}

		return $ec;
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
	 */
	public function isFieldFailed($fieldname)
	{
		$ec = $this->getFieldErrorCode($fieldname);
		return ($ec > AgaviValidator::SUCCESS);
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
	 */
	public function isFieldValidated($fieldname)
	{
		return isset($this->fieldResults[$fieldname]);
	}

	/**
	 * Returns all fields which succeeded in the validation. Includes fields which
	 * were not processed (happens when the field is "not set" and the validator 
	 * is not required)
	 *
	 * @return     array An array of field names.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getSucceededFields()
	{
		$names = array();
		foreach($this->fieldResults as $name => $results) {
			$ec = AgaviValidator::SUCCESS;
			foreach($results as $result) {
				$ec = max($ec, $result[1]);
			}
			if($ec <= AgaviValidator::SUCCESS) {
				$names[] = $name;
			}
		}

		return $names;
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
		// we need to add the fields to our fieldresults if they don't exist there 
		// yet and adjust our result if needed (which only happens when this method
		// is called not from a validator)
		$severity = $incident->getSeverity();
		if($severity > $this->result) {
			$this->result = $severity;
		}
		foreach($incident->getFields() as $field) {
			if(!isset($this->fieldResults[$field]) || $this->getFieldErrorCode($field) < $severity) {
				$this->addFieldResult(null, $field, $incident->getSeverity());
			}
		}
		$name = $incident->getValidator() ? $incident->getValidator()->getName() : '';
		$this->incidents[$name][] = $incident;
	}

	/**
	 * Checks if any incidents occured Returns all fields which succeeded in the 
	 * validation. Includes fields which were not processed (happens when the 
	 * field is "not set" and the validator is not required)
	 *
	 * @param      int The minimum severity which shall be checked for.
	 *
	 * @return     bool The result.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function hasIncidents($minSeverity = null)
	{
		if($minErrorCode === null) {
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
	 * Returns all incidents which happened during the execution of the validaion.
	 *
	 * @param      int The minimum severity a returned incident needs to have.
	 *
	 * @return     array The incidents.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
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
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
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
	 * Returns all incidents of a given field.
	 *
	 * @param      string The name of the field.
	 * @param      int The minimum severity a returned incident needs to have.
	 *
	 * @return     array The incidents.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getFieldIncidents($fieldname, $minSeverity = null)
	{
		$incidents = array();
		foreach($this->getIncidents($minSeverity) as $incident) {
			if($incident->hasFieldError($fieldname)) {
				$incidents[] = $incident;
			}
		}

		return $incidents;
	}

	/**
	 * Returns all errors of a given field.
	 *
	 * @param      string The name of the field.
	 * @param      int The minimum severity a returned incident of the error 
	 *                 needs to have.
	 *
	 * @return     array The incidents.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getFieldErrors($fieldname, $minSeverity = null)
	{
		$errors = array();
		foreach($this->getIncidents($minSeverity) as $incident) {
			$errors = array_merge($errors, $incident->getFieldErrors($fieldname));
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
	 */
	public function getValidatorFieldErrors($validatorName, $fieldname, $minSeverity = null)
	{
		$errors = array();
		foreach($this->getValidatorIncidents($validatorName, $minSeverity) as $incident) {
			$errors = array_merge($errors, $incident->getFieldErrors($fieldname));
		}

		return $errors;
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
	 */
	public function getFailedFields($minSeverity = null)
	{
		$fields = array();
		foreach($this->getIncidents($minSeverity) as $incident) {
			$fields = array_merge($fields, $incident->getFields());
		}

		return array_values(array_unique($fields));
	}
}
?>
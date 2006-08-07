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
 * AgaviValidatorManager provides management for request parameters and their
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

class AgaviValidatorManager extends AgaviParameterHolder implements AgaviIValidatorManager, AgaviIValidatorContainer
{
	/**
	 * @var        AgaviDependencyManager dependency manager
	 */
	protected $dependencyManager = null;

	/**
	 * @var        array array of child validators
	 */
	protected $children = array();

	/**
	 * @var        AgaviContext context
	 */
	protected $context = null;

	/**
	 * @var        array array of errors
	 */
	protected $errors = array();

	/**
	 * @var        int highest error severity in the container
	 */
	protected $result = AgaviValidator::SUCCESS;

	/**
	 * initializes the manager
	 * 
	 * @param      AgaviContext contest
	 * @param      array        parameters
	 * 
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	public function initialize(AgaviContext $context, $parameters = array())
	{
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
	 * clears the validation manager for reuse
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
		$this->errors = array();
		$this->result = AgaviValidator::SUCCESS;

		
		foreach($this->children as $child) {
			$child->shutdown();
		}
		
		$this->children = array();
	}
	
	/**
	 * adds a new child validator
	 * 
	 * @param      AgaviValidator new child validator
	 * 
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	public function addChild(AgaviValidator $validator)
	{
		$this->children[] = $validator;
	}
	
	/**
	 * returns the request
	 * 
	 * @return     AgaviRequest request
	 * 
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	public function getRequest()
	{
		return $this->context->getRequest();
	}
	
	/**
	 * returns the dependency manager
	 * 
	 * @return     AgaviDependencyManager dependency manager
	 * 
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	public function getDependencyManager()
	{
		return $this->dependencyManager;
	}

	/**
	 * get the base path of the validator
	 * 
	 * @return     string base path
	 * 
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	public function getBase() {
		return new AgaviVirtualArrayPath($this->getParameter('base', ''));
	}

	/**
	 * starts the validation process
	 * 
	 * @param      AgaviParameterHolder The parameters which should be validated
	 * 
	 * @return     bool true, if validation succeeded
	 * 
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	public function execute(AgaviParameterHolder $parameters)
	{
		$result = true;
		$this->result = AgaviValidator::SUCCESS;

		$allSucceededFields = array();
		foreach($this->children as $validator) {
			$affectedFields = $validator->getAffectedFields();
			$v_ret = $validator->execute($parameters);
			$this->result = max($this->result, $v_ret);

			if($v_ret == AgaviValidator::SUCCESS) {
				$allSucceededFields = array_merge($affectedFields, $allSucceededFields);
			}

			switch($v_ret) {
				case AgaviValidator::SUCCESS:
					continue 2;
				case AgaviValidator::NONE:
					continue 2;
				case AgaviValidator::ERROR:
					$result = false;
					continue 2;
				case AgaviValidator::CRITICAL:
					$result = false;
					break 2;
			}
		}

		$mode = $this->getParameter('mode', 'normal');
		if(count($this->children) == 0 && $mode == 'strict') {
			// strict mode and no validators defined -> clear the parameters
			$parameters->clearParameters();
		}

		if($mode == 'strict' || $mode == 'tainted') {
			$ma = $this->getContext()->getRequest()->getModuleAccessor();
			$aa = $this->getContext()->getRequest()->getActionAccessor();
			$asf = array_flip($allSucceededFields);
			foreach($parameters->getParameters() as $name => $param) {
				if(!isset($asf[$name]) && $name != $ma && $name != $aa) {
					$parameters->removeParameter($name);
				}
			}
		}

		$errors = $this->getErrorArrayByInput();
		$errorsByValidator = $this->getErrorArrayByValidator();
		$ns = 'org.agavi.validation.result';
		$this->getContext()->getRequest()->setAttribute('errors', $errors, $ns);
		$this->getContext()->getRequest()->setAttribute('errorsByValidator', $errorsByValidator, $ns);
		return $result;
	}
	
	/**
	 * shuts down the validation system
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
	 * registers an array of validators
	 * 
	 * @param      array array of validators
	 * 
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	public function registerValidators($validators)
	{
		foreach($validators as $validator) {
			$this->addChild($validator);
		}
	}
	
	/**
	 * returns the array of errors sorted by validator names
	 * 
	 * Format:
	 * 
	 * array(
	 *   <i>validatorName</i> => array(
	 *     'error'  => <i>error</i>,
	 *     'fields' => <i>array of field names</i>
	 *   )
	 * 
	 * @return     array array of errors
	 * 
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	public function getErrorArrayByValidator()
	{
		$errors = array();
		foreach($this->errors as $error) {
			$errors[$error[0]->getName()] = array($error[1], $error[0]->getAffectedFields());
		}

		return $errors;
	}
	
	/**
	 * returns the array of errors sorted by input names
	 * 
	 * Format:
	 * 
	 * array(
	 *   <i>fieldName</i> => array(
	 *     'messages'    => array(
	 *       <i>error message</i>
	 *     )
	 *     'validators' => array(
	 *       <i>validatorName</i> => <i>validator</i>
	 *     )
	 * )
	 * 
	 * <i>error message</i> is the first submitted error with type string.
	 * 
	 * @return     array array of errors
	 * 
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	public function getErrorArrayByInput()
	{
		$errors = array();
		foreach($this->errors as $error) {
			$affectedFields = $error[0]->getAffectedFields();
			if(count($affectedFields) == 0) {
				if(!isset($errors[''])) {
					$errors[$fieldName] = array('messages' => array(), 'validators' => array());
				}

				if($error[1]) {
					$errors['']['messages'][] = $error[1];
				}
				$errors['']['validators'][] = $error[0];
			} else {
				foreach($affectedFields as $fieldName) {
					if(!isset($errors[$fieldName])) {
						$errors[$fieldName] = array('messages' => array(), 'validators' => array());
					}
					if($error[1]) {
						$errors[$fieldName]['messages'][] = $error[1];
					}
					$errors[$fieldName]['validators'][] = $error[0];
				}
			}
		}

		return $errors;
	}
	
	/**
	 * returns the result from the error manager
	 * 
	 * @return     int result of the validation process
	 * 
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	public function getResult()
	{
		return $this->result;
	}

	/**
	 * reports an error to the parent container
	 * 
	 * @param      AgaviValidator The validator where the error occured
	 * @param      string         An error message
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 * @see        AgaviIValidatorContainer::reportError
	 */
	public function reportError(AgaviValidator $validator, $errorMsg)
	{
		$this->errors[$validator->getName()] = array($validator, $errorMsg);
	}
}
?>
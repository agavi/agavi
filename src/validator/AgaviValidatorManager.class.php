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
	 * @return     bool true, if validation succeeded
	 * 
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	public function execute()
	{
		$result = true;
		$this->result = AgaviValidator::SUCCESS;

		foreach($this->children as $validator) {
			$v_ret = $validator->execute();
			$this->result = max($this->result, $v_ret);

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
	 *     'message'    => <i>error message</i>,
	 *     'validators' => array(
	 *       <i>validatorName</i> => <i>error</i>
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
			foreach($error[0]->getAffectedFields() as $fieldName) {
				if(!isset($errors[$fieldName])) {
					$errors[$fieldName] = array('message' => $error[1], 'validators' => array());
				}
				$errors[$fieldName]['validators'][$error[0]->getName()] = $error[1];
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
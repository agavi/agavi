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
	private $DependManager = null;

	/**
	 * @var        AgaviRequest request
	 */
	private $Request = null;

	/**
	 * @var        AgaviErrorManager error manager
	 */
	private $ErrorManager = null;

	/**
	 * @var        array array of child validators
	 */
	private $Children = array();

	/**
	 * @var        AgaviContext context
	 */
	private $Context = null;

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
		$this->Context = $context;
		$this->setParameters($parameters);
		
		$this->Request = $this->Context->getRequest();
		$this->DependManager = new AgaviDependencyManager;
		$this->ErrorManager = new AgaviErrorManager;
	}
	
	/**
	 * clears the validation manager for reuse
	 * 
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	public function clear()
	{
		$this->DependencyManager->clear();
		$this->ErrorManager->clear();
		
		foreach($this->Children as $child) {
			$child->clear();
		}
	}
	
	/**
	 * adds a new child validator
	 * 
	 * @param      AgaviValidator new child validator
	 * 
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	public function addChild (AgaviValidator $validator)
	{
		array_push($this->Children, $validator);
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
		return $this->Request;
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
		return $this->DependManager;
	}
	
	/**
	 * returns the error manager
	 * 
	 * @return     AgaviErrorManager error manager
	 * 
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	public function getErrorManager()
	{
		return $this->ErrorManager;
	}

	/**
	 * get the base path of the validator
	 * 
	 * @return     string base path
	 * 
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	public function getBase () {
		return ($this->hasParameter('base')) ? $this->getParameter('base') : '/';
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

		foreach($this->Children as $validator) {
			$v_ret = $validator->execute();
			switch($v_ret) {
				case AgaviValidator::SUCCESS:
					continue 1;
				case AgaviValidator::NONE:
					continue 1;
				case AgaviValidator::ERROR:
					$result = false;
					continue 1;
				case AgaviValidator::CRITICAL:
					$result = false;
					break 1;
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
		foreach($this->Children as $child) {
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
		foreach($validators AS $validator) {
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
	public function getErrorArrayByValidator() {
		return $this->ErrorManager->getErrorArrayByValidator();
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
	public function getErrorArrayByInput() {
		return $this->ErrorManager->getErrorArrayByInput();
	}
	
	/**
	 * fetches the error message from the error manager
	 * 
	 * @return     string error message
	 * 
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	public function getErrorMessage() {
		return $this->ErrorManager->getErrorMessage();
	}
}
?>
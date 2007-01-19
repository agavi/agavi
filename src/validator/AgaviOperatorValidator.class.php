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
 * AgaviOperatorValidator
 * 
 * Operators group a couple if validators...
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
abstract class AgaviOperatorValidator extends AgaviValidator implements AgaviIValidatorContainer
{
	/**
	 * @var        array The child validators.
	 */
	protected $children = array();

	/**
	 * @var        array The errors of the child validators.
	 */
	protected $errors = array();
	
	/**
	 * @var        int The highest error severity in the container.
	 */
	protected $result = AgaviValidator::SUCCESS;
	
	/**
	 * constructor
	 * 
	 * @param      AgaviIValidatorContainer The parent ValidatorContainer
	 *                                      (mostly the ValidationManager)
	 * @param      array                    The parameters from the config file.
	 *
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	public function __construct(AgaviIValidatorContainer $parent, array $arguments, array $errors = array(), array $parameters = array(), $name = '')
	{
		parent::__construct($parent, $arguments, $errors, $parameters, $name);
		
		if($this->getParameter('skip_errors')) {
			/*
			 * if the operator is configured to skip errors of the
			 * child validators, a new error manager is created
			 */
		} else {
			// else the parent's error manager is taken
		}
	}

	/**
	 * Method for checking the validity of child validators.
	 * 
	 * Some operators (XOR and NOT) need a specific quantity of child
	 * validators so they implement an algorithm that checks of the setup
	 * is valid. This method is run first when execute() is invoked and
	 * should throw an exception if the setup is invalid.
	 * 
	 * @throws     <b>AgaviValidatorException<b> If the  quantity of child 
	 *                                           validators is invalid
	 *
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	protected function checkValidSetup()
	{
	}
	
	/**
	 * Shutdown method, for shutting down the model etc.
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
	 * Adds new child validator.
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
	 * Registers an array of validators.
	 * 
	 * @param      array The array of validators.
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
	 * Gets parent's dependency manager.
	 * 
	 * @return     AgaviDependencyManager The parent's dependency manager.
	 *
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	public function getDependencyManager()
	{
		return $this->parentContainer->getDependencyManager();
	}

	/**
	 * Returns the result from the error manager.
	 * 
	 * @return     int The result of the validation process.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getResult()
	{
		return $this->result;
	}

	/**
	 * Executes the validator.
	 * 
	 * Executes the operators validate()-Method after checking the quantity
	 * of child validators with checkValidSetup().
	 * 
	 * @param      AgaviParameterHolder The parameters which should be validated.
	 *
	 * @return     int The result of validation (SUCCESS, NONE, NOTICE, ERROR, CRITICAL).
	 *
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	public function execute(AgaviParameterHolder $parameters)
	{
		// check if we have a valid setup of validators
		$this->checkValidSetup();
		
		$result = parent::execute($parameters);
		if($result != AgaviValidator::SUCCESS && !$this->getParameter('skip_errors') && $this->result == AgaviValidator::CRITICAL) {
			/*
			 * one of the child validators resulted with CRITICAL
			 * we change our operator's result to CRITICAL, too so the
			 * surrounding validator container is aware of the critical
			 * result and can abort further validation... 
			 */
			$result = AgaviValidator::CRITICAL;
		}
		
		return $result;
	}
}

?>
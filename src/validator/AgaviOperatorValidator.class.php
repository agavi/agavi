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
	 * local error manager
	 * 
	 * When the operator is configured to skip all errors produced by child validators then
	 * an own instance of ErrorManager is created and given to the validators for reporting
	 * errors instead of giving them the parent ValidatorContainer's error manager.
	 * 
	 * @var        AgaviErrorManager ErrorManager for child validators
	 */
	protected $errorManager = null;

	/**
	 * @var        array child validators
	 */
	protected $children = array();
	
	/**
	 * constructor
	 * 
	 * @param      AgaviIValidatorContainer parent ValidatorContainer
	 *                                      (mostly the ValidatorManager)
	 * @param      array                    parameters from the config file
	 *
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	public function __construct(AgaviIValidatorContainer $parent, $parameters = array())
	{
		parent::__construct($parent, $parameters);
		
		if($this->getParameter('skip_errors')) {
			/*
			 * if the operator is configured to skip errors of the
			 * child validators, a new error manager is created
			 */
			$this->errorManager = new AgaviErrorManager();
		} else {
			// else the parent's error manager is taken
			$this->errorManager = $this->parentContainer->getErrorManager();
		}
	}

	/**
	 * method for checking if the setup of child validators is valid
	 * 
	 * Some operators (XOR and NOT) need a specific quantity of child
	 * validators so they implement an algorithm that checks of the setup
	 * is valid. This method is run first when execute() is invoked and
	 * should throw an exception if the setup is invalid.
	 * 
	 * @throws     AgaviValidatorException quantity of child validators is
	 *                                     invalid
	 *
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	protected function checkValidSetup()
	{
	}
	
	/**
	 * shutdown method, for shutting down the model etc.
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
	 * adds new child validator
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
	 * get Request from parent
	 * 
	 * @return     AgaviRequest parent's request
	 *
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	public function getRequest()
	{
		return $this->parentContainer->getRequest();
	}
	
	/**
	 * get parent's dependency manager
	 * 
	 * @return     AgaviDependencyManager parent's dependency manager
	 *
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	public function getDependencyManager()
	{
		return $this->parentContainer->getDependencyManager();
	}
	
	/**
	 * get error manager
	 * 
	 * If the parameter 'skip_errors' is true, then a local created error
	 * manager is returned and the parent will not be aware of thrown errors
	 * 
	 * @return     AgaviErrorManager parent's or local error manager
	 *
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	public function getErrorManager()
	{
		return $this->errorManager;
	}
	
	/**
	 * executes the validator
	 * 
	 * Eexecutes the operators validate()-Method after checking the quantity
	 * of child validators with checkValidSetup().
	 * 
	 * @return     int result of validation (SUCCESS, NONE, ERROR, CRITICAL)
	 *
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	public function execute()
	{
		// check if we have a valid setup of validators
		$this->checkValidSetup();
		
		$result = parent::execute();
		if($result != AgaviValidator::SUCCESS and !$this->getParameter('skip_errors') and $this->getErrorManager()->getResult() == AgaviValidator::CRITICAL) {
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
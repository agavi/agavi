<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2003-2006 the Agavi Project.                                |
// | Based on the Mojavi3 MVC Framework, Copyright (c) 2003-2005 Sean Kerr.    |
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
 * AgaviAbstractOperatorValidator
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
abstract class AgaviAbstractOperatorValidator extends AgaviValidator
{
	/**
	 * local error manager
	 * 
	 * When the operator is configured to skip all errors produced by child validators then
	 * an own instance of ErrorManager is created and given to the validators for reporting
	 * errors instead of giving them the parent ValidatorContainer's error manager.
	 * 
	 * @var        ErrorManager ErrorManager for child validators
	 */
	protected $ErrorManager = null;

	/**
	 * @var        array child validators
	 */
	protected $Children = array();
	
	/**
	 * initializes the operator
	 * 
	 * @param      AgaviIValidatorContainer $parent parent ValidatorContainer
	 *                                              (mostly the ValidatorManager)
	 * @param      array                    $parameters parameters from the
	 *                                                  config file
	 */
	public function initialize (AgaviIfValidatorContainer $parent, $parameters = array())
	{
		parent::initialize($parent, $parameters);
		
		if ($this->asBool('skip_errors')) {
			/*
			 * if the operator is configured to skip errors of the
			 * child validators, a new error manager is created
			 */
			$this->ErrorManager = new ErrorManager;
		} else {
			// else the parent's error manager is taken
			$this->ErrorManager = $this->ParentContainer->getErrorManager();
		}
	}

	/**
	 * get the base path of the validator
	 * 
	 * @return     string base path
	 */
	public function getBase () {
		// enfoce returning as string
		return $this->CurBase->__asString();
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
	 */
	protected function checkValidSetup ()
	{
	}
	
	/**
	 * shutdown method, for shutting down the model etc.
	 */
	public function shutdown ()
	{
		foreach ($this->Children as $child) {
			$child->shutdown();
		}
	}
	
	/**
	 * clears the self created error manager
	 */
	public function clear ()
	{
		if ($this->asBool('skip_errors')) {
			$this->ErrorManager->clear();
		}
		
		foreach ($this->Children as $child) {
			$child->clear();
		}
	}
	
	/**
	 * adds new child validator
	 * 
	 * @param      AgaviValidator $validator new child validator
	 */
	public function addChild (AgaviValidator $validator)
	{
		array_push($this->Children, $validator);
	}
	
	/**
	 * get Request from parent
	 * 
	 * @return     AgaviRequest parent's request
	 */
	public function getRequest ()
	{
		return $this->ParentContainer->getRequest();
	}
	
	/**
	 * get parent's dependency manager
	 * 
	 * @return     AgaviDependencyManager parent's dependency manager
	 */
	public function getDependencyManager ()
	{
		return $this->ParentContainer->getDependencyManager();
	}
	
	/**
	 * get error manager
	 * 
	 * If the parameter 'skip_errors' is true, then a local created error
	 * manager is returned and the parent will not be aware of thrown errors
	 * 
	 * @return     AgaviErrorManager parent's or local error manager
	 */
	public function getErrorManager ()
	{
		return $this->ErrorManager;
	}
	
	/**
	 * executes the validator
	 * 
	 * Eexecutes the operators validate()-Method after checking the quantity
	 * of child validators with checkValidSetup().
	 * 
	 * @return     int Result of validation (SUCCESS, NONE, ERROR, CRITICAL)
	 */
	public function execute ()
	{
		// check if we have a valid setup of validators
		$this->checkValidSetup();
		
		return parent::execute();
	}
}

?>
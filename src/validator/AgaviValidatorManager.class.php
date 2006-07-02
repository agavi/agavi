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

class AgaviValidatorManager extends AgaviAbstractValidatorManager implements AgaviIValidatorContainer
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
	 * @param      AgaviContext $context contest
	 * @param      array        $parameters parameters
	 */
	public function initialize (AgaviContext $context, $parameters = array())
	{
		$this->Context = $context;
		$this->setParameters($parameters);
		
		$this->Request = $this->Context->getRequest();
		$this->DependManager = new AgaviDependencyManager;
		$this->ErrorManager = new AgaviErrorManager;
	}
	
	/**
	 * clears the validation manager for reuse
	 */
	public function clear ()
	{
		$this->DependencyManager->clear();
		$this->ErrorManager->clear();
		
		foreach ($this->Children as $child) {
			$child->clear();
		}
	}
	
	/**
	 * adds a new child validator
	 * 
	 * @param      AgaviValidator $validator new child validator
	 */
	public function addChild (AgaviValidator $validator)
	{
		array_push($this->Children, $validator);
	}
	
	/**
	 * returns the request
	 * 
	 * @return     AgaviRequest request
	 */
	public function getRequest ()
	{
		return $this->Request;
	}
	
	/**
	 * returns the dependency manager
	 * 
	 * @return     AgaviDependencyManager dependency manager
	 */
	public function getDependencyManager ()
	{
		return $this->DependManager;
	}
	
	/**
	 * returns the error manager
	 * 
	 * @return     AgaviErrorManager error manager
	 */
	public function getErrorManager ()
	{
		return $this->ErrorManager;
	}

	/**
	 * get the base path of the validator
	 * 
	 * @return     string base path
	 */
	public function getBase () {
		return ($this->hasParameter('base')) ? $this->getParameter('base') : '/';
	}

	/**
	 * starts the validation process
	 * 
	 * @return     bool true, if validation succeeded
	 */
	public function execute ()
	{
		$result = true;

		foreach ($this->Children as $validator) {
			$v_ret = $validator->execute();
			switch ($v_ret) {
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
	 */
	public function shutdown ()
	{
		foreach ($this->Children as $child) {
			$child->shutdown();
		}
	}
	
	/**
	 * registers an array of validators
	 * 
	 * @param      array $validators array of validators
	 */
	public function registerValidators ($validators)
	{
		foreach ($validators AS $validator) {
			$this->addChild($validator);
		}
	}
	
	/**
	 * returns the path to the validation config for a specific action
	 * 
	 * @param      string $module name of module
	 * @param      string $action name of action
	 * 
	 * @return     string path to config file
	 */
	public static function getConfigFilename ($module, $action) {
		return AgaviConfig::get('agavi.webapp_dir') . '/modules/' . $module . '/validate/' . $action . '.xml';
	}
	
	/**
	 * fetches the error array from the error manager
	 * 
	 * @return     array error array
	 */
	public function getErrorArray () {
		return $this->ErrorManager->getErrorArray();
	}
	
	/**
	 * fetches the error message from the error manager
	 * 
	 * @return     string error message
	 */
	public function getErrorMessage () {
		return $this->ErrorManager->getErrorMessage();
	}
}
?>
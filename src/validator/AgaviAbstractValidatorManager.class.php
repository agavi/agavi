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
 * abstract superclass for ValidatorManagers
 * 
 * AgaviAbstractValidatorManager is the abstract superclass for all ValidatorManagers
 * which control validation of request parameters, provide error messages and handle
 * the creation and management of the validators
 * 
 * @package    agavi
 * @subpackage validator
 * 
 * @author     Uwe Mesecke <uwe@mesecke.net>
 * @copyright  (c) Authors
 * @since      0.11
 * 
 * @version:   $Id$  
 */
abstract class AgaviAbstractValidatorManager extends AgaviParameterHolder {
	/**
	 * @var        AgaviContext the context
	 */
	protected $Context = NULL;
	
	/**
	 * initializes the validator manager
	 * 
	 * @param      AgaviContext $context the context
	 * @param      array $parameters parameters for the validator manager 
	 */
	public abstract function initialize (AgaviContext $context, $parameters = array());

	/**
	 * clears the validator manager for reuse
	 */
	public abstract function clear ();

	/**
	 * starts the validation process and returns the result
	 * 
	 * @return     bool result of validation process
	 */
	public abstract function execute ();

	/**
	 * returns the path of a validation config for a specific action
	 * 
	 * @param      string $module name of module
	 * @param      string $action name of action
	 * 
	 * @return     string path to config file
	 */
	public static abstract function getConfigFilename ($module, $action); 
}

?>
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
 * Action allows you to separate application and business logic from your
 * presentation. By providing a core set of methods used by the framework,
 * automation in the form of security and validation can occur.
 *
 * @package    agavi
 * @subpackage action
 *
 * @author     Sean Kerr <skerr@mojavi.org>
 * @copyright  (c) Authors
 * @since      0.9.0
 *
 * @version    $Id$
 */
abstract class AgaviAction
{

	// +-----------------------------------------------------------------------+
	// | PRIVATE VARIABLES                                                     |
	// +-----------------------------------------------------------------------+
	
	private
		$context = null;

	/**
	 * Retrieve the current application context.
	 *
	 * @return     AgaviContext The current AgaviContext instance.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public final function getContext()
	{
		return $this->context;
	}

	/**
	 * Retrieve the credential required to access this action.
	 *
	 * @return     mixed Data that indicates the level of security for this 
	 *                   action.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function getCredentials()
	{
		return null;
	}

	/**
	 * Execute any post-validation error application logic.
	 *
	 * @return     mixed A string containing the view name associated with this
	 *                   action.
	 *                   Or an array with the following indices:
	 *                   - The parent module of the view that will be executed.
	 *                   - The view that will be executed.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function handleError(AgaviParameterHolder $parameters)
	{
		return 'Error';
	}

	/**
	 * Initialize this action.
	 *
	 * @param      AgaviContext The current application context.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function initialize(AgaviContext $context)
	{
		$this->context = $context;
	}

	/**
	 * Indicates that this action requires security.
	 *
	 * @return     bool true, if this action requires security, otherwise false.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function isSecure()
	{
		return false;
	}

	/**
	 * Manually register validators for this action.
	 *
	 * @param      AgaviValidatorManager A ValidatorManager instance.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function registerValidators($validatorManager)
	{
	}

	/**
	 * Manually validate files and parameters.
	 *
	 * @return     bool true, if validation completes successfully, otherwise 
	 *                  false.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function validate()
	{
		return true;
	}
	
	/**
	 * Get the default View name if this Action doesn't serve the Request method.
	 *
	 * @return     string A View name
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getDefaultViewName()
	{
		return 'Input';
	}

}

?>
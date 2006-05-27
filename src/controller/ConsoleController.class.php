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
 * AgaviConsoleController allows you to centralize your entry point in your web
 * application, but at the same time allow for any module and action combination
 * to be requested.
 *
 * @package    agavi
 * @subpackage controller
 *
 * @author     Sean Kerr <skerr@mojavi.org>
 * @author     Agavi Project <info@agavi.org>
 * @copyright  (c) Authors
 * @since      0.9.0
 *
 * @version    $Id$
 */
class AgaviConsoleController extends AgaviController
{
	/**
	 * Initialize this controller.
	 *
	 * @return     void
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function initialize(AgaviContext $context)
	{

		// initialize parent
		parent::initialize($context);

		ini_set('arg_separator.output', AgaviConfig::get('php.arg_separator.output', '&'));
	}
	
	/**
	 * Dispatch a request.
	 *
	 * This will determine which module and action to use by request parameters
	 * specified by the user.
	 *
	 * @return     void
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     Agavi Project <info@agavi.org>
	 * @since      0.9.0
	 */
	public function dispatch ($params = array())
	{

		try {

			if (is_array($params)) {
				$this->setParametersByRef($params);
			}

			// determine our module and action
			$moduleName = (AgaviConfig::has('console.module') ? AgaviConfig::get('console.module') : AgaviConfig::get('actions.default_module'));
			$actionName = (AgaviConfig::has('console.action') ? AgaviConfig::get('console.action') : null);

			if ($actionName == null) {

				// no action has been specified
				if ($moduleName == AgaviConfig::get('actions.default_module')) {

					$actionName = AgaviConfig::get('actions.default_action');

				} else if ($this->actionExists($moduleName, 'Index')) {

					// an Index action exists
					$actionName = 'Index';

				}
			}
			
			$request = $this->context->getRequest();

			// set the module and action in the Request parameters
			$request->setParameter($request->getModuleAccessor(), $moduleName);
			$request->setParameter($request->getActionAccessor(), $actionName);

			parent::dispatch($parameters);

		} catch (Exception $e) {
			AgaviException::printStackTrace($e, $this->getContext());
		}

	}

}

?>
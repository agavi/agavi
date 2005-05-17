<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Authors                                                                   |
// |  Sean Kerr (skerr@mojavi.org)                                             |
// |  Agavi Foundation (info@agavi.org)                                        |
// | Copyright (c) 2003-2005  Authors                                          |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE   |
// | file that was distributed with this source code. You can also view the    |
// | LICENSE file online at http://www.agavi.org.                              |
// +---------------------------------------------------------------------------+

/**
 * ConsoleController allows you to centralize your entry point in your web
 * application, but at the same time allow for any module and action combination
 * to be requested.
 *
 * @package    agavi
 * @subpackage controller
 *
 * @author    Sean Kerr (skerr@mojavi.org)
 * @author    Agavi Foundation (info@agavi.org)
 * @copyright (c) Author
 * @since     3.0.0
 * @version   $Id$
 */
class ConsoleController extends Controller
{

	// +-----------------------------------------------------------------------+
	// | METHODS                                                               |
	// +-----------------------------------------------------------------------+

	/**
	 * Dispatch a request.
	 *
	 * This will determine which module and action to use by request parameters
	 * specified by the user.
	 *
	 * @return void
	 *
	 * @author Sean Kerr (skerr@mojavi.org)
	 * @author Agavi Foundation (info@agavi.org)
	 * @since  3.0.0
	 */
	public function dispatch ($params=null)
	{

		try {

			// set the console parameters
			$this->setParametersByRef($params);

			// initialize the controller
			$this->initialize();

			// get the application context
			$context = $this->getContext();

			// determine our module and action
			$moduleName = (defined('AG_CONSOLE_MODULE') ? AG_CONSOLE_MODULE : MO_DEFAULT_MODULE);
			$actionName = (defined('AG_CONSOLE_ACTION') ? AG_CONSOLE_ACTION : null);

			if ($actionName == null) {

				// no action has been specified
				if ($moduleName == MO_DEFAULT_MODULE) {

					$actionName = MO_DEFAULT_ACTION;

				} else if ($this->actionExists($moduleName, 'Index')) {

					// an Index action exists
					$actionName = 'Index';

				}
			}

			// set the module and action in the Request parameters
			$this->getContext()->getRequest()->setParameter(MO_MODULE_ACCESSOR, $moduleName);
			$this->getContext()->getRequest()->setParameter(MO_ACTION_ACCESSOR, $actionName);

			// make the first request
			$this->forward($moduleName, $actionName);

		} catch (AgaviException $e) {

			$e->printStackTrace();

		} catch (Exception $e) {

			// most likely an exception from a third-party library
			$e = new AgaviException($e->getMessage());

			$e->printStackTrace();

		}

	}

}

?>

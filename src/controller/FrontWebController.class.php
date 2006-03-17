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
 * AgaviFrontWebController allows you to centralize your entry point in your web
 * application, but at the same time allow for any module and action combination
 * to be requested.
 *
 * @package    agavi
 * @subpackage controller
 *
 * @author     Sean Kerr <skerr@mojavi.org>
 * @copyright  (c) Authors
 * @since      0.9.0
 *
 * @version    $Id$
 */
class AgaviFrontWebController extends AgaviWebController
{

	/**
	 * Dispatch a request.
	 *
	 * This will determine which module and action to use by request parameters
	 * specified by the user.
	 *
	 * @return     void
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function dispatch ()
	{

		try
		{

			// so setting the headers works
			ob_start();
			
			// determine our module and action
			$moduleName = $this->context->getRequest()->getParameter(AgaviConfig::get('request.module_accessor'));
			$actionName = $this->context->getRequest()->getParameter(AgaviConfig::get('request.action_accessor'));

			if ($moduleName == null)
			{

				// no module has been specified
				$moduleName = AgaviConfig::get('actions.default_module');

			}

			if ($actionName == null)
			{

				// no action has been specified
				if ($this->actionExists($moduleName, 'Index'))
				{

				    // an Index action exists
				    $actionName = 'Index';

				} else
				{

				    // use the default action
				    $actionName = AgaviConfig::get('actions.default_action');

				}

			}

			// make the first request
			$this->forward($moduleName, $actionName);

			// output all headers for the response
			$this->sendHTTPResponseHeaders();

		} catch (AgaviException $e)
		{

			$e->printStackTrace();

		} catch (Exception $e)
		{

			// most likely an exception from a third-party library
			$e = new AgaviException($e->getMessage());

			$e->printStackTrace();

		}

	}

}

?>
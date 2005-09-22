<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2003-2005  Sean Kerr.                                       |
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
 * FrontWebController allows you to centralize your entry point in your web
 * application, but at the same time allow for any module and action combination
 * to be requested.
 *
 * @package    agavi
 * @subpackage controller
 *
 * @author    Sean Kerr (skerr@mojavi.org)
 * @copyright (c) Sean Kerr, {@link http://www.mojavi.org}
 * @since     0.9.0
 * @version   $Id$
 */
class FrontWebController extends WebController
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
	 * @since  0.9.0
	 */
	public function dispatch ()
	{

		try
		{
			// determine our module and action
			$moduleName = $this->context->getRequest()->getParameter(AG_MODULE_ACCESSOR);
			$actionName = $this->context->getRequest()->getParameter(AG_ACTION_ACCESSOR);

			if ($moduleName == null)
			{

				// no module has been specified
				$moduleName = AG_DEFAULT_MODULE;

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
				    $actionName = AG_DEFAULT_ACTION;

				}

			}

			// make the first request
			$this->forward($moduleName, $actionName);

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

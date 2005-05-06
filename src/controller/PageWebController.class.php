<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2003, 2004 Agavi Foundation.                                |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE   |
// | file that was distributed with this source code. You can also view the    |
// | LICENSE file online at http://www.agavi.org.                              |
// +---------------------------------------------------------------------------+

/**
 * PageWebController allows you to dispatch a request by specifying a module
 * and action name in the dispatch() method.
 *
 * @package    agavi
 * @subpackage controller
 *
 * @author    Agavi Foundation (info@agavi.org)
 * @copyright (c) Agavi Foundation, {@link http://www.agavi.org}
 * @since     3.0.0
 * @version   $Id$
 */
class PageWebController extends WebController
{

	// +-----------------------------------------------------------------------+
	// | METHODS                                                               |
	// +-----------------------------------------------------------------------+

	/**
	 * Dispatch a request.
	 *
	 * @param string A module name.
	 * @param string An action name.
	 * @param array  An associative array of parameters to be set.
	 *
	 * @return void
	 *
	 * @author Agavi Foundation (info@agavi.org)
	 * @since  3.0.0
	 */
	public function dispatch ($moduleName, $actionName, $parameters = null)
	{

		try
		{

			// initialize the controller
			$this->initialize();

			// set parameters
			if ($parameters != null)
			{

				$this->getContext()
				     ->getRequest()
				     ->setParametersByRef($parameters);

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

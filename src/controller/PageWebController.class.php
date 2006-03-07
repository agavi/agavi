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
 * AgaviPageWebController allows you to dispatch a request by specifying a 
 * module and action name in the dispatch() method.
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
class AgaviPageWebController extends AgaviWebController
{

	/**
	 * Dispatch a request.
	 *
	 * @param      string A module name.
	 * @param      string An action name.
	 * @param      array  An associative array of parameters to be set.
	 *
	 * @return     void
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function dispatch ($moduleName, $actionName, $parameters = null)
	{

		try {
			
			// so setting the headers works
			ob_start();
			
			if ($parameters != null) {
				$this->context->getRequest()->setParametersByRef($parameters);
			}

			// make the first request
			$this->forward($moduleName, $actionName);
			
			$this->sendHTTPResponseHeaders();
			
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
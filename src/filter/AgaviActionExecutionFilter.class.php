<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2005-2011 the Agavi Project.                                |
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
 * AgaviActionExecutionFilter ultimately performs execution of the Action.
 *
 * @package    agavi
 * @subpackage filter
 *
 * @author     David Zülke <david.zuelke@bitextender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      1.1.0
 *
 * @version    $Id$
 */
class AgaviActionExecutionFilter extends AgaviFilter
{
	/**
	 * Execute this filter.
	 *
	 * The AgaviActionExecutionFilter executes the Action instance.
	 *
	 * @param      AgaviFilterChain        The filter chain.
	 * @param      AgaviExecutionContainer The current execution container.
	 *
	 * @throws     <b>AgaviFilterException</b> If an error occurs during execution.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.1.0
	 */
	public function execute(AgaviFilterChain $filterChain, AgaviExecutionContainer $container)
	{
		$actionInstance = $container->getActionInstance();
		$request = $this->getContext()->getRequest();
		$method = $request->getMethod();
		$requestData = $container->getRequestData();
		
		$useGenericMethods = false;
		$executeMethod = 'execute' . $method;
		if(!is_callable(array($actionInstance, $executeMethod))) {
			$executeMethod = 'execute';
			$useGenericMethods = true;
		}
		
		if($actionInstance->isSimple() || ($useGenericMethods && !is_callable(array($actionInstance, $executeMethod)))) {
			// this action will skip validation/execution for this method
			// get the default view
			$key = $request->toggleLock();
			try {
				$viewName = $actionInstance->getDefaultViewName();
			} catch(Exception $e) {
				// we caught an exception... unlock the request and rethrow!
				$request->toggleLock($key);
				throw $e;
			}
			$request->toggleLock($key);
		} else {
			if($container->getValidationManager()->getReport()->getResult() < AgaviValidator::ERROR) {
				// execute the action
				// prevent access to Request::getParameters()
				$key = $request->toggleLock();
				try {
					$viewName = $actionInstance->$executeMethod($requestData);
				} catch(Exception $e) {
					// we caught an exception... unlock the request and rethrow!
					$request->toggleLock($key);
					throw $e;
				}
				$request->toggleLock($key);
			} else {
				// validation failed
				$handleErrorMethod = 'handle' . $method . 'Error';
				if(!is_callable(array($actionInstance, $handleErrorMethod))) {
					$handleErrorMethod = 'handleError';
				}
				$key = $request->toggleLock();
				try {
					$viewName = $actionInstance->$handleErrorMethod($requestData);
				} catch(Exception $e) {
					// we caught an exception... unlock the request and rethrow!
					$request->toggleLock($key);
					throw $e;
				}
				$request->toggleLock($key);
			}
		}

		list($viewModuleName, $viewName) = $container->resolveViewName($viewName, $container->getActionName(), $container->getModuleName());
		$container->setViewModuleName($viewModuleName);
		$container->setViewName($viewName);
	}
}

?>
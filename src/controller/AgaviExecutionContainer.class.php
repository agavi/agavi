<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2003-2006 the Agavi Project.                                |
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
 * A container used for each forward() call that holds action information, the
 * response etc.
 * 
 * @package    agavi
 * @subpackage controller
 * 
 * @author     Agavi Project <info@agavi.org>
 * @author     David Zuelke <dz@bitxtender.com>
 * @since      0.11.0
 *
 * @version    $Id$
 */
class AgaviExecutionContainer extends AgaviAttributeHolder
{
	/**
	 * @var        AgaviAction The Action instance that belongs to this container.
	 */
	private $actionInstance = null;
	
	/**
	 * @var        string The name of the Action.
	 */
	private $actionName = null;
	
	/**
	 * @var        float The microtime at which this container was initialized.
	 */
	private $microtime = null;
	
	/**
	 * @var        AgaviContext The context instance.
	 */
	private $context = null;
	
	/**
	 * @var        string The name of the Action's Module.
	 */
	private $moduleName = null;
	
	/**
	 * @var        AgaviResponse A response instance holding the Action's output.
	 */
	private $response = null;
	
	/**
	 * @var        array Information about the next Action to be executed, if any.
	 */
	private $next = null;
	
	/**
	 * @var        string The name of the View returned by the Action.
	 */
	private $viewName = null;
	
	/**
	 * @var        string Name of the module of the View returned by the Action.
	 */
	private $viewModuleName = null;
	
	/**
	 * Initialize the container.
	 *
	 * This will create an instance of the action and merge in request arguments.
	 *
	 * @param      AgaviContext The current Context instance.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function initialize(AgaviContext $context, $moduleName, $actionName, array $parameters = array())
	{
		$this->setModuleName($moduleName);
		$this->setActionName($actionName);
		
		$this->setParameters($parameters);
		
		$this->microtime = microtime(true);
		
		$this->context = $context;
		
		// create a new response instance for this action
		$rfi = $this->context->getFactoryInfo('response');
		$this->response = new $rfi['class'];
		$this->response->initialize($this->context, $rfi['parameters']);
	}
	
	public function execute()
	{
		$controller = $this->context->getController();
		$request = $this->context->getRequest();
		
		$this->actionInstance = $controller->getAction($this->moduleName, $this->actionName);
		
		$this->parameters = array_merge($request->getParameters(), $this->parameters);
		
		$controller->incNumForwards();
		
		$moduleName = $this->getModuleName();
		$actionName = $this->getActionName();
		
		if(!AgaviConfig::get('core.available', false)) {
			// application is unavailable
			$request->setAttributes(array(
				'requested_module' => $moduleName,
				'requested_action' => $actionName
			), 'org.agavi.controller.forwards.unavailable');
			$moduleName = AgaviConfig::get('actions.unavailable_module');
			$actionName = AgaviConfig::get('actions.unavailable_action');

			try {
				$actionName = $controller->resolveAction($moduleName, $actionName);
			} catch(AgaviControllerException $e) {
				$error = 'Invalid configuration settings: actions.unavailable_module "%s", actions.unavailable_action "%s"';
				$error = sprintf($error, $moduleName, $actionName);
				throw new AgaviConfigurationException($error);
			}

		} else {
			try {
				$actionName = $controller->resolveAction($moduleName, $actionName);
			} catch(AgaviControllerException $e) {
				// track the requested module so we have access to the data
				// in the error 404 page
				$request->setAttributes(array(
					'requested_module' => $moduleName,
					'requested_action' => $actionName
				), 'org.agavi.controller.forwards.error_404');

				// switch to error 404 action
				$moduleName = AgaviConfig::get('actions.error_404_module');
				$actionName = AgaviConfig::get('actions.error_404_action');

				try {
					$actionName = $controller->resolveAction($moduleName, $actionName);
				} catch(AgaviControllerException $e) {
					// cannot find unavailable module/action
					$error = 'Invalid configuration settings: actions.error_404_module "%s", actions.error_404_action "%s"';
					$error = sprintf($error, $moduleName, $actionName);

					throw new AgaviConfigurationException($error);
				}
			}
		}
		
		$this->setModuleName($moduleName);
		$this->setActionName($actionName);
		
		$actionInstance = $this->getActionInstance();
		
		// include the module configuration
		// laoded only once due to the way import() works
		if(is_readable(AgaviConfig::get('core.module_dir') . '/' . $moduleName . '/config/module.xml')) {
			AgaviConfigCache::import(AgaviConfig::get('core.module_dir') . '/' . $moduleName . '/config/module.xml', $this->context->getName());
		} else {
			AgaviConfig::set('modules.' . strtolower($moduleName) . '.enabled', true);
		}

		// save autoloads so we can restore them later
		$oldAutoloads = Agavi::$autoloads;
		
		static $moduleAutoloads = array();
		if(!isset($moduleAutoloads[$moduleName])) {
			$moduleAutoloads[$moduleName] = array();
			$moduleAutoload = AgaviConfig::get('core.module_dir') . '/' . $moduleName . '/config/autoload.xml';
			if(is_readable($moduleAutoload)) {
				include(AgaviConfigCache::checkConfig($moduleAutoload));
				$moduleAutoloads[$moduleName] = Agavi::$autoloads;
			}
		} else {
			Agavi::$autoloads = array_merge($moduleAutoloads[$moduleName], Agavi::$autoloads);
		}
		
		if(AgaviConfig::get('modules.' . strtolower($moduleName) . '.enabled')) {
			// check for a module config.php
			$moduleConfig = AgaviConfig::get('core.module_dir') . '/' . $moduleName . '/config.php';
			if(is_readable($moduleConfig)) {
				require_once($moduleConfig);
			}

			// initialize the action
			$actionInstance->initialize($this);
			
			// create a new filter chain
			$fcfi = $this->context->getFactoryInfo('filter_chain');
			$filterChain = new $fcfi['class']();
			$filterChain->initialize($this->context, $fcfi['parameters']);

			if(AgaviConfig::get('core.available', false)) {
				// the application is available so we'll register
				// global and module filters, otherwise skip them

				// does this action require security?
				if(AgaviConfig::get('core.use_security', false) && $actionInstance->isSecure()) {
					// register security filter
					$filterChain->register($controller->getFilter('security'));
				}

				// load filters
				$controller->loadFilters($filterChain, 'action');
				$controller->loadFilters($filterChain, 'action', $moduleName);
			}

			// register the execution filter
			$filterChain->register($controller->getFilter('execution'));

			// process the filter chain
			$filterChain->execute($this);
			
			// clear the global request attribute namespace containing attributes for the View
			$request->removeAttributeNamespace($request->getDefaultNamespace());
			
			// restore autoloads
			Agavi::$autoloads = $oldAutoloads;

		} else {
			
			$request->setAttributes(array(
				'requested_module' => $moduleName,
				'requested_action' => $actionName
			), 'org.agavi.controller.forwards.disabled');
			$moduleName = AgaviConfig::get('actions.module_disabled_module');
			$actionName = AgaviConfig::get('actions.module_disabled_action');

			try {
				$actionName = $controller->resolveAction($moduleName, $actionName);
			} catch(AgaviControllerException $e) {
				// cannot find mod disabled module/action
				$error = 'Invalid configuration settings: actions.module_disabled_module "%s", actions.module_disabled_action "%s"';
				$error = sprintf($error, $moduleName, $actionName);
				throw new AgaviConfigurationException($error);
			}
			
			// TODO. this will be pretty difficult, I guess...
			$controller->forward($moduleName, $actionName);
		}
	}
	
	public function getContext()
	{
		return $this->context;
	}
	
	/**
	 * Retrieve this container's action name.
	 *
	 * @return     string An action name.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getActionName()
	{
		return $this->actionName;
	}
	
	/**
	 * Retrieve this container's action instance.
	 *
	 * @return     AgaviAction An action implementation instance.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getActionInstance()
	{
		return $this->actionInstance;
	}
	
	/**
	 * Retrieve this container's microtime.
	 *
	 * @return     string A string representing the microtime this container was
	 *                    initialized.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getMicrotime()
	{
		return $this->microtime;
	}
	
	/**
	 * Retrieve this container's module name.
	 *
	 * @return     string A module name.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getModuleName()
	{
		return $this->moduleName;
	}
	
	/**
	 * Retrieve this container's view name.
	 *
	 * @return     string A view name.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getViewName()
	{
		return $this->viewName;
	}
	
	/**
	 * Retrieve this container's view module name. This is the name of the module of 
	 * the View returned by the Action.
	 *
	 * @return     string A view module name.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getViewModuleName()
	{
		return $this->viewModuleName;
	}
	
	/**
	 * Set the module name for this container.
	 *
	 * @return     string A module name.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function setModuleName($moduleName)
	{
		$this->moduleName = preg_replace('/[^a-z0-9\-_]+/i', '', $moduleName);
	}
	
	/**
	 * Set the action name for this container.
	 *
	 * @return     string An action name.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function setActionName($actionName)
	{
		$this->actionName = preg_replace(array('/\./', '/[^a-z0-9\-_\/]+/i'), array('/', ''), $actionName);
	}
	
	/**
	 * Set the module name for this container.
	 *
	 * @return     string A view name.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function setViewName($viewName)
	{
		$this->viewName = $viewName;
	}
	
	/**
	 * Set the view module name for this container.
	 *
	 * @return     string A view module name.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function setViewModuleName($viewModuleName)
	{
		$this->viewModuleName = $viewModuleName;
	}
	
	/**
	 * Retrieve this container's rendered view presentation.
	 *
	 * @return     AgaviResponse The Response instance for this action.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getResponse()
	{
		return $this->response;
	}
	
	/**
	 * Set the rendered presentation for this action.
	 *
	 * @param      AgaviResponse A response holding the rendered presentation.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function setResponse(AgaviResponse $response)
	{
		$this->response = $response;
	}
	
	/**
	 * Set the next container that will be run after this Action finished.
	 *
	 * @param      string The Module name of the Action to execute next.
	 * @param      string The name of the Action to execute next.
	 * @param      mixed  An AgaviParameterHolder instance or an array holding
	 *                    request parameters to pass to that Action.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function setNext(AgaviExecutionContainer $container)
	{
		$this->next = $container;
	}
	
	/**
	 * Check if this Action or a View specified another Action to run next.
	 *
	 * @return     bool Whether or not a next Action has been set.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function hasNext()
	{
		return $this->next instanceof AgaviExecutionContainer;
	}
	
	/**
	 * Get the Action that should be run after this one finished execution.
	 *
	 * @return     AgaviExecutionContainer The container for the next action run.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getNext()
	{
		return $this->next;
	}
}

?>
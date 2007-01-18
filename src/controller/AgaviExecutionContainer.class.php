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
 * A container used for each action execution that holds neecessary information,
 * such as the output type, the response etc.
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
	 * @var        AgaviContext The context instance.
	 */
	protected $context = null;
	
	/**
	 * @var        AgaviRequestData A request data instance with request info.
	 */
	protected $requestData = null;
	
	/**
	 * @var        AgaviResponse A response instance holding the Action's output.
	 */
	protected $response = null;
	
	/**
	 * @var        AgaviOutputType The output type for this container.
	 */
	protected $outputType = null;
	
	/**
	 * @var        float The microtime at which this container was initialized.
	 */
	protected $microtime = null;
	
	/**
	 * @var        AgaviAction The Action instance that belongs to this container.
	 */
	protected $actionInstance = null;
	
	/**
	 * @var        string The name of the Action's Module.
	 */
	protected $moduleName = null;
	
	/**
	 * @var        string The name of the Action.
	 */
	protected $actionName = null;
	
	/**
	 * @var        string Name of the module of the View returned by the Action.
	 */
	protected $viewModuleName = null;
	
	/**
	 * @var        string The name of the View returned by the Action.
	 */
	protected $viewName = null;
	
	/**
	 * @var        AgaviExecutionContainer The next container to execute.
	 */
	protected $next = null;
	
	/**
	 * Initialize the container. This will create a response instance.
	 *
	 * @param      AgaviContext The current Context instance.
	 * @param      array        An array of initialization parameters.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function initialize(AgaviContext $context, array $parameters = array())
	{
		$this->microtime = microtime(true);
		
		$this->context = $context;
		
		$this->parameters = $parameters;
		
		// create a new response instance for this action
		$rfi = $this->context->getFactoryInfo('response');
		$this->response = new $rfi['class'];
		$this->response->initialize($this->context, $rfi['parameters']);
	}
	
	/**
	 * Creates a new container instance with the same output type as this one.
	 *
	 * @param      string          The name of the module.
	 * @param      string          The name of the action.
	 * @param      array           Optional additional parameters.
	 * @param      AgaviOutputType Optional name of an initial output type to set.
	 *
	 * @return     AgaviExecutionContainer A new execution container instance,
	 *                                     fully initialized.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function createExecutionContainer($moduleName = null, $actionName = null, array $parameters = array(), AgaviOutputType $outputType = null)
	{
		// TODO: parameters... there's more now
		if($outputType === null) {
			$outputType = $this->getOutputType();
		}
		return $this->context->getController()->createExecutionContainer($moduleName, $actionName, $parameters, $outputType);
	}
	
	/**
	 * Start execution.
	 *
	 * This will create an instance of the action and merge in request parameters.
	 *
	 * This method returns a response. It is not necessarily the same response as
	 * the one of this container, but instead the one that contains the actual
	 * content that should be used for output etc, since the container's own
	 * response might be empty or invalid due to a "next" container that has been
	 * set and executed.
	 *
	 * @return     AgaviResponse The "real" response.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function execute()
	{
		$controller = $this->context->getController();
		
		$request = $this->context->getRequest();
		
		// copy request data
		// FIXME: problem: can't set info before execute() since requestData is null
		$this->requestData = clone $request->getRequestData();

		// TODO: merge in request data. request holders need a method for that. 
		// $this->parameters = array_merge($request->getParameters(), $this->parameters);
		
		$controller->countExecution();
		
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
		
		$this->actionInstance = $controller->createActionInstance($this->moduleName, $this->actionName);
		
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
			$this->actionInstance->initialize($this);
			
			// create a new filter chain
			$fcfi = $this->context->getFactoryInfo('filter_chain');
			$filterChain = new $fcfi['class']();
			$filterChain->initialize($this->context, $fcfi['parameters']);

			if(AgaviConfig::get('core.available', false)) {
				// the application is available so we'll register
				// global and module filters, otherwise skip them

				// does this action require security?
				if(AgaviConfig::get('core.use_security', false) && $this->actionInstance->isSecure()) {
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
			$this->setNext($controller->createExecutionContainer($moduleName, $actionName));
		}
		
		if($this->next !== null) {
			return $this->next->execute();
		} else {
			return $this->getResponse();
		}
	}
	
	/**
	 * Get the Context.
	 *
	 * @return     AgaviContext The Context.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getContext()
	{
		return $this->context;
	}
	
	/**
	 * Retrieve this container's request data holder instance.
	 *
	 * @return     AgaviRequestDataHolder The request data holder.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getRequestData()
	{
		return $this->requestData;
	}
	
	/**
	 * Retrieve this container's response instance.
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
	 * Set a new response.
	 *
	 * @param      AgaviResponse A new Response instance.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function setResponse(AgaviResponse $response)
	{
		$this->response = $response;
	}
	
	/**
	 * Retrieve the output type of this container.
	 *
	 * @return     AgaviOutputType The output type object.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getOutputType()
	{
		return $this->outputType;
	}
	
	/**
	 * Set a different output type for this container.
	 *
	 * @param      AgaviOutputType An output type object.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function setOutputType(AgaviOutputType $outputType)
	{
		$this->outputType = $outputType;
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
	 * Check if a "next" container has been set.
	 *
	 * @return     bool True, if a container for eventual execution has been set.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function hasNext()
	{
		return $this->next !== null;
	}
	
	/**
	 * Get the "next" container.
	 *
	 * @return     AgaviExecutionContainer The "next" container, of null if unset.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getNext()
	{
		return $this->next;
	}
	
	/**
	 * Set the container that should be executed once this one finished running.
	 *
	 * @param      AgaviExcecutionContainer An execution container instance.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function setNext(AgaviExecutionContainer $container)
	{
		$this->next = $container;
	}
	
	/**
	 * Remove a possibly set "next" container.
	 *
	 * @return     AgaviExecutionContainer The removed "next" container, or null
	 *                                     if none had been set.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function clearNext()
	{
		$retval = $this->next;
		$this->next = null;
		return $retval;
	}
}

?>
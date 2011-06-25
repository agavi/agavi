<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2005-2011 the Agavi Project.                                |
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
 * AgaviController directs application flow.
 *
 * @package    agavi
 * @subpackage controller
 *
 * @author     Sean Kerr <skerr@mojavi.org>
 * @author     David Zülke <dz@bitxtender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.9.0
 *
 * @version    $Id$
 */
class AgaviController extends AgaviParameterHolder
{
	/**
	 * @var        int The number of execution containers run so far.
	 */
	protected $numExecutions = 0;
	
	/**
	 * @var        AgaviContext An AgaviContext instance.
	 */
	protected $context = null;
	
	/**
	 * @var        AgaviResponse The global response.
	 */
	protected $response = null;
	
	/**
	 * @var        array An array of filter instances for reuse.
	 */
	protected $filters = array(
		'global' => array(),
		'action' => array(
			'*' => null
		),
		'dispatch' => null,
		'execution' => null,
		'security' => null
	);
	
	/**
	 * @var        string The default Output Type.
	 */
	protected $defaultOutputType = null;
	
	/**
	 * @var        array An array of registered Output Types.
	 */
	protected $outputTypes = array();
	
	/**
	 * @var        array Ref to the request data object from the request.
	 */
	private $requestData = null;
	
	/**
	 * Increment the execution counter.
	 * Will throw an exception if the maximum amount of runs is exceeded.
	 *
	 * @throws     AgaviControllerException If too many execution runs were made.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function countExecution()
	{
		$maxExecutions = $this->getParameter('max_executions');
		
		if(++$this->numExecutions > $maxExecutions && $maxExecutions > 0) {
			throw new AgaviControllerException('Too many execution runs have been detected for this Context.');
		}
	}
	
	/**
	 * Create and initialize new execution container instance.
	 *
	 * @param      string                 The name of the module.
	 * @param      string                 The name of the action.
	 * @param      AgaviRequestDataHolder A RequestDataHolder with additional
	 *                                    request arguments.
	 * @param      string                 Optional name of an initial output type
	 *                                    to set.
	 * @param      string                 Optional name of the request method to
	 *                                    be used in this container.
	 *
	 * @return     AgaviExecutionContainer A new execution container instance,
	 *                                     fully initialized.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function createExecutionContainer($moduleName = null, $actionName = null, AgaviRequestDataHolder $arguments = null, $outputType = null, $requestMethod = null)
	{
		// create a new execution container
		$container = $this->context->createInstanceFor('execution_container');
		$container->setModuleName($moduleName);
		$container->setActionName($actionName);
		$container->setRequestData($this->requestData);
		if($arguments !== null) {
			$container->setArguments($arguments);
		}
		$container->setOutputType($this->context->getController()->getOutputType($outputType));
		if($requestMethod === null) {
			$requestMethod = $this->context->getRequest()->getMethod();
		}
		$container->setRequestMethod($requestMethod);
		return $container;
	}
	
	/**
	 * Initialize a module and load its autoload, module config etc.
	 *
	 * @param      string The name of the module to initialize.
	 *
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 * @since      1.0.0
	 */
	public function initializeModule($moduleName)
	{
		$lowerModuleName = strtolower($moduleName);
		
		if(null === AgaviConfig::get('modules.' . $lowerModuleName . '.enabled')) {
			// set some defaults first
			AgaviConfig::fromArray(array(
				'modules.' . $lowerModuleName . '.agavi.action.path' => '%core.module_dir%/${moduleName}/actions/${actionName}Action.class.php',
				'modules.' . $lowerModuleName . '.agavi.cache.path' => '%core.module_dir%/${moduleName}/cache/${actionName}.xml',
				'modules.' . $lowerModuleName . '.agavi.template.directory' => '%core.module_dir%/${module}/templates',
				'modules.' . $lowerModuleName . '.agavi.validate.path' => '%core.module_dir%/${moduleName}/validate/${actionName}.xml',
				'modules.' . $lowerModuleName . '.agavi.view.path' => '%core.module_dir%/${moduleName}/views/${viewName}View.class.php',
				'modules.' . $lowerModuleName . '.agavi.view.name' => '${actionName}${viewName}',
			));
			// include the module configuration
			// loaded only once due to the way load() (former import()) works
			if(is_readable(AgaviConfig::get('core.module_dir') . '/' . $moduleName . '/config/module.xml')) {
				include_once(AgaviConfigCache::checkConfig(AgaviConfig::get('core.module_dir') . '/' . $moduleName . '/config/module.xml'));
			} else {
				AgaviConfig::set('modules.' . $lowerModuleName . '.enabled', true);
			}
			
			$moduleAutoload = AgaviConfig::get('core.module_dir') . '/' . $moduleName . '/config/autoload.xml';
			if(is_readable($moduleAutoload)) {
				Agavi::$autoloads = array_merge(Agavi::$autoloads, include(AgaviConfigCache::checkConfig($moduleAutoload)));
			}
			
			if(AgaviConfig::get('modules.' . $lowerModuleName . '.enabled')) {
				$moduleConfigHandlers = AgaviConfig::get('core.module_dir') . '/' . $moduleName . '/config/config_handlers.xml';
				if(is_readable($moduleConfigHandlers)) {
					AgaviConfigCache::addConfigHandlersFile($moduleConfigHandlers);
				}
			}
		}
		
		if(!AgaviConfig::get('modules.' . $lowerModuleName . '.enabled')) {
			throw new AgaviDisabledModuleException(sprintf('The module "%1$s" is disabled.', $moduleName));
		}
		
		// check for a module config.php
		$moduleConfig = AgaviConfig::get('core.module_dir') . '/' . $moduleName . '/config.php';
		if(is_readable($moduleConfig)) {
			require_once($moduleConfig);
		}
	}
	
	/**
	 * Dispatch a request
	 *
	 * @param      AgaviRequestDataHolder  An optional request data holder object
	 *                                     with additional request data.
	 * @param      AgaviExecutionContainer An optional execution container that,
	 *                                     if given, will be executed right away,
	 *                                     skipping routing execution.
	 *
	 * @return     AgaviResponse The response produced during this dispatch call.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.9.0
	 */
	public function dispatch(AgaviRequestDataHolder $arguments = null, AgaviExecutionContainer $container = null)
	{
		try {
			
			$rq = $this->context->getRequest();
			$rd = $rq->getRequestData();
			
			if($container === null) {
				// match routes and assign returned initial execution container
				$container = $this->context->getRouting()->execute();
			}
			
			if($container instanceof AgaviExecutionContainer) {
				// merge in any arguments given. they need to have precedence over what the routing found
				if($arguments !== null) {
					$rd->merge($arguments);
				}
				
				// next, we have to see if the routing did anything useful, i.e. whether or not it was enabled.
				$moduleName = $container->getModuleName();
				$actionName = $container->getActionName();
				if(!$moduleName) {
					// no module has been specified; that means the routing did not run, as it would otherwise have the 404 action's module name
					
					// lets see if our request data has values for module and action
					$ma = $rq->getParameter('module_accessor');
					$aa = $rq->getParameter('action_accessor');
					if($rd->hasParameter($ma) && $rd->hasParameter($aa)) {
						// yup. grab those
						$moduleName = $rd->getParameter($ma);
						$actionName = $rd->getParameter($aa);
					} else {
						// nope. then its time for the default action
						$moduleName = AgaviConfig::get('actions.default_module');
						$actionName = AgaviConfig::get('actions.default_action');
					}
					
					// so by now we hopefully have something reasonable for module and action names - let's set them on the container
					$container->setModuleName($moduleName);
					$container->setActionName($actionName);
				}
				
				if(!AgaviConfig::get('core.available', false)) {
					$container = $container->createSystemActionForwardContainer('unavailable');
				}
				
				// create a new filter chain
				$filterChain = $this->context->createInstanceFor('filter_chain');
				
				$this->loadFilters($filterChain, 'global');
				
				// register the dispatch filter
				$filterChain->register($this->filters['dispatch']);
				
				// go, go, go!
				$filterChain->execute($container);
				
				$response = $container->getResponse();
			} elseif($container instanceof AgaviResponse) {
				// the routing returned a response!
				$response = $container;
				// set $container to null so AgaviException::render() won't think it is a container if an exception happens later!
				$container = null;
			} else {
				throw new AgaviException('AgaviRouting::execute() returned neither AgaviExecutionContainer nor AgaviResponse object.');
			}
			$response->merge($this->response);
			
			if($this->getParameter('send_response')) {
				$response->send();
			}
			
			return $response;
			
		} catch(Exception $e) {
			AgaviException::render($e, $this->context, $container);
		}
	}
	
	/**
	 * Get the global response instance.
	 *
	 * @return     AgaviResponse The global response.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getGlobalResponse()
	{
		return $this->response;
	}
	
	
	/**
	 * Indicates whether or not a module has a specific action file.
	 * 
	 * Please note that this is only a cursory check and does not 
	 * check whether the file actually contains the proper class
	 *
	 * @param      string A module name.
	 * @param      string An action name.
	 *
	 * @return     mixed  the path to the action file if the action file 
	 *                    exists and is readable, false in any other case
	 *
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 * @since      1.0.0
	 */
	public function checkActionFile($moduleName, $actionName)
	{
		$this->initializeModule($moduleName);
		
		$actionName = AgaviToolkit::canonicalName($actionName);
		$file = AgaviToolkit::evaluateModuleDirective(
			$moduleName,
			'agavi.action.path',
			array(
				'moduleName' => $moduleName,
				'actionName' => $actionName,
			)
		);
		
		if(is_readable($file) && substr($actionName, 0, 1) !== '/') {
			return $file;
		}
		
		return false;
	}
	
	/**
	 * Retrieve an Action implementation instance.
	 *
	 * @param      string A module name.
	 * @param      string An action name.
	 *
	 * @return     AgaviAction An Action implementation instance
	 *
	 * @throws     AgaviException if the action could not be found.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     Mike Vincent <mike@agavi.org>
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.9.0
	 */
	public function createActionInstance($moduleName, $actionName)
	{
		$this->initializeModule($moduleName);
		
		$actionName = AgaviToolkit::canonicalName($actionName);
		$longActionName = str_replace('/', '_', $actionName);
		
		$class = $moduleName . '_' . $longActionName . 'Action';
		
		if(!class_exists($class)) {
			if(false !== ($file = $this->checkActionFile($moduleName, $actionName))) {
				require($file);
			} else {
				throw new AgaviException('Could not find file for Action "' . $actionName . '" in module "' . $moduleName . '"');
			}
			
			if(!class_exists($class, false)) {
				throw new AgaviException('Could not find Action "' . $longActionName . '" for module "' . $moduleName . '"');
			}
		} 
		
		return new $class();
	}

	/**
	 * Retrieve the current application context.
	 *
	 * @return     AgaviContext An AgaviContext instance.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public final function getContext()
	{
		return $this->context;
	}


	
	/**
	 * Indicates whether or not a module has a specific view file.
	 * 
	 * Please note that this is only a cursory check and does not 
	 * check whether the file actually contains the proper class
	 *
	 * @param      string A module name.
	 * @param      string A view name.
	 *
	 * @return     mixed  the path to the view file if the view file 
	 *                    exists and is readable, false in any other case
	 * 
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 * @since      1.0.0
	 */
	public function checkViewFile($moduleName, $viewName)
	{
		$this->initializeModule($moduleName);
		
		$viewName = AgaviToolkit::canonicalName($viewName);
		$file = AgaviToolkit::evaluateModuleDirective(
			$moduleName,
			'agavi.view.path',
			array(
				'moduleName' => $moduleName,
				'viewName' => $viewName,
			)
		);
		
		if(is_readable($file) && substr($viewName, 0, 1) !== '/') {
			return $file;
		}
		
		return false;
	}
	
	/**
	 * Retrieve a View implementation instance.
	 *
	 * @param      string A module name.
	 * @param      string A view name.
	 *
	 * @return     AgaviView A View implementation instance,
	 *
	 * @throws     AgaviException if the view could not be found.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     Mike Vincent <mike@agavi.org>
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.9.0
	 */
	public function createViewInstance($moduleName, $viewName)
	{
		try {
			$this->initializeModule($moduleName);
		} catch(AgaviDisabledModuleException $e) {
			// views from disabled modules should be usable by definition
			// swallow
		}
		
		$viewName = AgaviToolkit::canonicalName($viewName);
		$longViewName = str_replace('/', '_', $viewName);
		
		$class = $moduleName . '_' . $longViewName . 'View';
		
		if(!class_exists($class)) {
			
			if(false !== ($file = $this->checkViewFile($moduleName, $viewName))) {
				require($file);
			} else {
				throw new AgaviException('Could not find file for View "' . $viewName . '" in module "' . $moduleName . '"');
			}
			
			if(!class_exists($class, false)) {
				throw new AgaviException('Could not find View "' . $longViewName . '" for module "' . $moduleName . '"');
			}
		} 
		
		return new $class();
	}

	/**
	 * Constructor.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function __construct()
	{
		parent::__construct();
		$this->setParameters(array(
			'max_executions' => 20,
			'send_response' => true,
		));
	}
	
	/**
	 * Initialize this controller.
	 *
	 * @param      AgaviContext An AgaviContext instance.
	 * @param      array        An array of initialization parameters.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.9.0
	 */
	public function initialize(AgaviContext $context, array $parameters = array())
	{
		$this->context = $context;
		
		$this->setParameters($parameters);
		
		$this->response = $this->context->createInstanceFor('response');
		
		$cfg = AgaviConfig::get('core.config_dir') . '/output_types.xml';
		require(AgaviConfigCache::checkConfig($cfg, $this->context->getName()));
		
		if(AgaviConfig::get('core.use_security', false)) {
			$this->filters['security'] = $this->context->createInstanceFor('security_filter');
		}
		
		$this->filters['dispatch'] = $this->context->createInstanceFor('dispatch_filter');
		
		$this->filters['execution'] = $this->context->createInstanceFor('execution_filter');
		
		$this->filters['action_execution'] = $this->context->createInstanceFor('action_execution_filter');
		
		$this->filters['authorization'] = $this->context->createInstanceFor('authorization_filter');
		
		$this->filters['validation'] = $this->context->createInstanceFor('validation_filter');
	}
	
	/**
	 * Get a filter.
	 *
	 * @param      string The name of the filter list section.
	 *
	 * @return     AgaviFilter A filter instance, or null.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getFilter($which)
	{
		return (isset($this->filters[$which]) ? $this->filters[$which] : null);
	}
	
	/**
	 * Load filters.
	 *
	 * @param      AgaviFilterChain A FilterChain instance.
	 * @param      string           "global" or "action".
	 * @param      string           A module name, or "*" for the generic config.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function loadFilters(AgaviFilterChain $filterChain, $which = 'global', $module = null)
	{
		if($module === null) {
			$module = '*';
		}
		
		if(($which != 'global' && !isset($this->filters[$which][$module])) || $which == 'global' && $this->filters[$which] == null) {
			if($which == 'global') {
				$this->filters[$which] = array();
				$filters =& $this->filters[$which];
			} else {
				$this->filters[$which][$module] = array();
				$filters =& $this->filters[$which][$module];
			}
			$config = ($module == '*' ? AgaviConfig::get('core.config_dir') : AgaviConfig::get('core.module_dir') . '/' . $module . '/config') . '/' . $which . '_filters.xml';
			if(is_readable($config)) {
				require(AgaviConfigCache::checkConfig($config, $this->context->getName()));
			}
		} else {
			if($which == 'global') {
				$filters =& $this->filters[$which];
			} else {
				$filters =& $this->filters[$which][$module];
			}
		}
		
		foreach($filters as $filter) {
			$filterChain->register($filter);
		}
	}

	/**
	 * Indicates whether or not a module has a specific model.
	 *
	 * @param      string A module name.
	 * @param      string A model name.
	 *
	 * @return     bool true, if the model exists, otherwise false.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function modelExists($moduleName, $modelName)
	{
		$modelName = AgaviToolkit::canonicalName($modelName);
		$file = AgaviConfig::get('core.module_dir') . '/' . $moduleName . '/models/' . $modelName .	'Model.class.php';
		return is_readable($file);
	}

	/**
	 * Indicates whether or not a module exists.
	 *
	 * @param      string A module name.
	 *
	 * @return     bool true, if the module exists, otherwise false.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function moduleExists($moduleName)
	{
		$file = AgaviConfig::get('core.module_dir') . '/' . $moduleName . '/config/module.xml';
		return is_readable($file);
	}

	/**
	 * Do any necessary startup work after initialization.
	 *
	 * This method is not called directly after initialize().
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function startup()
	{
		// grab a pointer to the request data
		$this->requestData = $this->context->getRequest()->getRequestData();
	}

	/**
	 * Execute the shutdown procedure for this controller.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function shutdown()
	{
	}

	/**
	 * Indicates whether or not a module has a specific action.
	 *
	 * @param      string A module name.
	 * @param      string A view name.
	 *
	 * @return     bool true, if the action exists, otherwise false.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.1
	 */
	public function actionExists($moduleName, $actionName)
	{
		return $this->checkActionFile($moduleName, $actionName) !== false;
	}

	/**
	 * Indicates whether or not a module has a specific view.
	 *
	 * @param      string A module name.
	 * @param      string A view name.
	 *
	 * @return     bool true, if the view exists, otherwise false.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function viewExists($moduleName, $viewName)
	{
		return $this->checkViewFile($moduleName, $viewName) !== false;
	}
	
	/**
	 * Retrieve an Output Type object
	 *
	 * @param      string The optional output type name.
	 *
	 * @return     AgaviOutputType An Output Type object.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getOutputType($name = null)
	{
		if($name === null) {
			$name = $this->defaultOutputType;
		}
		if(isset($this->outputTypes[$name])) {
			return $this->outputTypes[$name];
		} else {
			throw new AgaviException('Output Type "' . $name . '" has not been configured.');
		}
	}
}

?>
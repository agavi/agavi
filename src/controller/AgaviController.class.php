<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2003-2007 the Agavi Project.                                |
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
class AgaviController
{
	/**
	 * @var        int The number of execution containers run so far.
	 */
	protected $numExecutions = 0;
	
	/**
	 * @var        int The maximum number of execution container runs allowed.
	 */
	protected $maxExecutions = 20;
	
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
	 * Indicates whether or not a module has a specific action.
	 *
	 * @param      string A module name.
	 * @param      string An action name.
	 *
	 * @return     string The actual name of the action (might be modified).
	 *
	 * @throws     AgaviControllerException if the action could not be found.
	 * @author     David Zülke <dz@bitxtender.com>
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.11.0
	 */
	public function resolveAction($moduleName, $actionName)
	{
		$actionName = str_replace('.', '/', $actionName);
		$file = AgaviConfig::get('core.module_dir') . '/' . $moduleName . '/actions/' . $actionName . 'Action.class.php';
		if(is_readable($file)) {
			return $actionName;
		}
		throw new AgaviControllerException(sprintf('Action "%s" in Module "%s" could not be found.', $actionName, $moduleName));
	}
	
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
		if(++$this->numExecutions > $this->maxExecutions && $this->maxExecutions > 0) {
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
	 *
	 * @return     AgaviExecutionContainer A new execution container instance,
	 *                                     fully initialized.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function createExecutionContainer($moduleName = null, $actionName = null, AgaviRequestDataHolder $arguments = null, $outputType = null)
	{
		// create a new execution container
		$ecfi = $this->context->getFactoryInfo('execution_container');
		$container = new $ecfi['class']();
		$container->initialize($this->context, $ecfi['parameters']);
		$container->setModuleName($moduleName);
		$container->setActionName($actionName);
		if($arguments !== null) {
			$container->setArguments($arguments);
		}
		$container->setOutputType($this->context->getController()->getOutputType($outputType));
		return $container;
	}
	
	/**
	 * Dispatch a request
	 *
	 * @param      AgaviRequestDataHolder A RequestDataHolder with additional
	 *                                    request arguments.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.9.0
	 */
	public function dispatch(AgaviRequestDataHolder $arguments = null)
	{
		try {
			
			$requestData = $this->context->getRequest()->getRequestData();
			if($arguments !== null) {
				$requestData->merge($arguments);
			}
			
			// match routes and assign returned initial execution container
			$container = $this->context->getRouting()->execute();
			
			$moduleName = $container->getModuleName();
			$actionName = $container->getActionName();
			
			if($moduleName == null) {
				// no module has been specified
				$container->setModuleName(AgaviConfig::get('actions.default_module'));
				$container->setActionName(AgaviConfig::get('actions.default_action'));
			}
			
			// create a new filter chain
			$fcfi = $this->context->getFactoryInfo('filter_chain');
			$filterChain = new $fcfi['class']();
			$filterChain->initialize($this->context, $fcfi['parameters']);
			
			$this->loadFilters($filterChain, 'global');
			
			// register the dispatch filter
			$filterChain->register($this->filters['dispatch']);
			
			// go, go, go!
			$filterChain->execute($container);
			
			$response = $container->getResponse();
			$response->merge($this->response);
			$response->send($container->getOutputType());
			
		} catch(Exception $e) {
			if(isset($container) && $container instanceof AgaviExecutionContainer) {
				AgaviException::printStackTrace($e, $this->context, $container);
			} else {
				AgaviException::printStackTrace($e, $this->context);
			}
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
	 * Retrieve an Action implementation instance.
	 *
	 * @param      string A module name.
	 * @param      string An action name.
	 *
	 * @return     AgaviAction An Action implementation instance, if the action 
	 *                         exists, otherwise null.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     Mike Vincent <mike@agavi.org>
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.9.0
	 */
	public function createActionInstance($moduleName, $actionName)
	{
		static $loaded = array();
		
		$file = AgaviConfig::get('core.module_dir') . '/' . $moduleName . '/actions/' . $actionName . 'Action.class.php';

		if(!isset($loaded[$file]) && file_exists($file)) {
			require($file);
			$loaded[$file] = true;
		}

		$longActionName = $actionName;

		// Nested action check?
		$position = strrpos($actionName, '/');
		if($position > -1) {
			$longActionName = str_replace('/', '_', $actionName);
			$actionName = substr($actionName, $position + 1);
		}

		if(class_exists($moduleName . '_' . $longActionName . 'Action', false)) {
			$class = $moduleName . '_' . $longActionName . 'Action';
		} elseif(class_exists($moduleName . '_' . $actionName . 'Action', false)) {
			$class = $moduleName . '_' . $actionName . 'Action';
		} elseif(class_exists($longActionName . 'Action', false)) {
			$class = $longActionName . 'Action';
		} elseif(class_exists($actionName . 'Action', false)) {
			$class = $actionName . 'Action';
		} else {
			throw new AgaviException('Could not find Action "' . $longActionName . '" for module "' . $moduleName . '"');
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
	 * Retrieve a View implementation instance.
	 *
	 * @param      string A module name.
	 * @param      string A view name.
	 *
	 * @return     AgaviView A View implementation instance, if the model exists,
	 *                       otherwise null.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     Mike Vincent <mike@agavi.org>
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.9.0
	 */
	public function createViewInstance($moduleName, $viewName)
	{
		static $loaded;
		
		$viewName = str_replace('.', '/', $viewName);
		$file = AgaviConfig::get('core.module_dir') . '/' . $moduleName . '/views/' . $viewName . 'View.class.php';

		if(!isset($loaded[$file]) && file_exists($file)) {
			require($file);
			$loaded[$file] = true;
		}

		$longViewName = $viewName;

		$position = strrpos($viewName, '/');
		if($position > -1) {
			$longViewName = str_replace('/', '_', $viewName);
			$viewName = substr($viewName, $position + 1);
		}

		if(class_exists($moduleName . '_' . $longViewName . 'View', false)) {
			$class = $moduleName . '_' . $longViewName . 'View';
		} elseif(class_exists($moduleName . '_' . $viewName . 'View', false)) {
			$class = $moduleName . '_' . $viewName . 'View';
		} elseif(class_exists($longViewName . 'View', false)) {
			$class = $longViewName . 'View';
		} elseif(class_exists($viewName . 'View', false)) {
			$class = $viewName . 'View';
		} else {
			throw new AgaviException('Could not find View "' . $longViewName . '" for module "' . $moduleName . '"');
		}

		return new $class();
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
		
		$rfi = $context->getFactoryInfo('response');
		$this->response = new $rfi["class"](); 
		$this->response->initialize($context, $rfi["parameters"]);
		
		$this->maxExecutions = isset($parameters['max_executions']) ? $parameters['max_executions'] : 20;
		
		$cfg = AgaviConfig::get('core.config_dir') . '/output_types.xml';
		require(AgaviConfigCache::checkConfig($cfg, $this->context->getName()));
		
		if(AgaviConfig::get('core.use_security', false)) {
			$sffi = $this->context->getFactoryInfo('security_filter');
			$this->filters['security'] = new $sffi['class']();
			$this->filters['security']->initialize($this->context, $sffi['parameters']);
		}
		
		$dffi = $this->context->getFactoryInfo('dispatch_filter');
		$this->filters['dispatch'] = new $dffi['class']();
		$this->filters['dispatch']->initialize($this->context, $dffi['parameters']);
		
		$effi = $this->context->getFactoryInfo('execution_filter');
		$this->filters['execution'] = new $effi['class']();
		$this->filters['execution']->initialize($this->context, $effi['parameters']);
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
	 * Execute the shutdown procedure for this controller.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function shutdown()
	{
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
		$viewName = str_replace('.', '/', $viewName);
		$file = AgaviConfig::get('core.module_dir') . '/' . $moduleName . '/views/' . $viewName . 'View.class.php';
		return is_readable($file);
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
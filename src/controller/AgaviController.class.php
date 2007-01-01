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
 * AgaviController directs application flow.
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
abstract class AgaviController extends AgaviParameterHolder
{
	/**
	 * @var        int The number of forward() calls done so far.
	 */
	protected $numForwards = 0;
	
	/**
	 * @var        int The maximum number of times this Controller will forward().
	 */
	protected $maxForwards  = 20;
	
	/**
	 * @var        AgaviContext An AgaviContext instance.
	 */
	protected $context = null;
	
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
	 * @var        string The currently set Output Type.
	 */
	protected $outputType = null;
	
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
	 * @return     mixed  The actual name of the action (might be auto-resolved).
	 *
	 * @throws     AgaviControllerException if the action could not be found.
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.9.0
	 */
	public function resolveAction($moduleName, $actionName = null)
	{
		$actionName = str_replace('.', '/', $actionName);
		$file = AgaviConfig::get('core.module_dir') . '/' . $moduleName . '/actions/' . $actionName . 'Action.class.php';
		if(is_readable($file)) {
			return $actionName;
		} else {
			// maybe it's a sub-action with the last portion omitted
			$actionName .= '/Index';
			$file = AgaviConfig::get('core.module_dir') . '/' . $moduleName . '/actions/' . $actionName . 'Action.class.php';
			if(is_readable($file)) {
				return $actionName;
			}
		}
		throw new AgaviControllerException(sprintf('Action "%s" in Module "%s" could not be found.', $actionName, $moduleName));
	}
	
	public function incNumForwards()
	{
		if(++$this->numForwards > $this->maxForwards && $this->maxForwards > 0) {
			throw new AgaviForwardException('Too many forwards have been detected for this Context.');
		}
	}
	
	/**
	 * Create and initialize new execution container instance.
	 *
	 * @param      string The name of the module.
	 * @param      string The name of the action.
	 * @param      array  Optional additional parameters.
	 *
	 * @return     AgaviExecutionContainer A new execution container instance,
	 *                                     fully initialized.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function createExecutionContainer($moduleName, $actionName, array $parameters = array())
	{
		// create a new filter chain
		$ecfi = $this->context->getFactoryInfo('execution_container');
		$container = new $ecfi['class']();
		$container->initialize($this->context, $ecfi['parameters']);
		$container->setModuleName($moduleName);
		$container->setActionName($actionName);
		$container->setParameters($parameters);
		return $container;
	}
	
	/**
	 * Dispatch a request
	 *
	 * @param      array An associative array of parameters to be set.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.9.0
	 */
	public function dispatch(array $parameters = array())
	{
		try {
			
			$request = $this->context->getRequest();
			
			// match routes and set matched routes as request attributes
			$request->setAttribute('matchedRoutes', $this->context->getRouting()->execute(), 'org.agavi.routing');
		
			$request->setParameters($parameters);
		
			// determine our module and action
			$moduleName = $request->getParameter($request->getModuleAccessor());
			$actionName = $request->getParameter($request->getActionAccessor());
		
			if($moduleName == null) {
				// no module has been specified
				$moduleName = AgaviConfig::get('actions.default_module');
				$request->setParameter($request->getModuleAccessor(), $moduleName);
			}
			if($actionName == null) {
				// no action has been specified
				if($this->actionExists($moduleName, 'Index')) {
					// an Index action exists
					$actionName = 'Index';
				} else {
					// use the default action
					$actionName = AgaviConfig::get('actions.default_action');
				}
				$request->setParameter($request->getActionAccessor(), $actionName);
			}
			
			$container = $this->createExecutionContainer($moduleName, $actionName);
			
			// create a new filter chain
			$fcfi = $this->context->getFactoryInfo('filter_chain');
			$filterChain = new $fcfi['class']();
			$filterChain->initialize($this->context, $fcfi['parameters']);
			
			$this->loadFilters($filterChain, 'global');
		
			// register the dispatch filter
			$filterChain->register($this->filters['dispatch']);
		
			// go, go, go!
			$filterChain->execute($container);
			
			$container->getResponse()->send();
			
		} catch(Exception $e) {
			if(isset($container) && $container instanceof AgaviExecutionContainer && $container->getResponse() instanceof AgaviResponse) {
				AgaviException::printStackTrace($e, $this->context, $response);
			} else {
				AgaviException::printStackTrace($e, $this->context);
			}
		}
	}
	
	/**
	 * Redirect externally.
	 *
	 * @param      mixed Where to redirect.
	 *
	 * @return     AgaviResponse A reponse to work with since the others will be
	 *                           locked down.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	abstract public function redirect($to);

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
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.9.0
	 */
	public function getAction($moduleName, $actionName)
	{
		static $loaded = array();
		
		$actionName = str_replace('.', '/', $actionName);
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
	public function getContext()
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
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.9.0
	 */
	public function getView($moduleName, $viewName)
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
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     Mike Vincent <mike@agavi.org>
	 * @since      0.9.0
	 */
	public function initialize(AgaviResponse $response, array $parameters = array())
	{
		$this->maxForwards = isset($parameters['max_fowards']) ? $parameters['max_forwards'] : 20;
		
		$this->response = $response;
		
		$this->context = $response->getContext();
		
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
	 * @author     David Zuelke <dz@bitxtender.com>
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
	 * Sets an output type for this response.
	 *
	 * @param      string The output type name.
	 *
	 * @throws     <b>AgaviConfigurationException</b> If the given output type 
	 *                                                doesnt exist.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function setOutputType($outputType)
	{
		if(isset($this->outputTypes[$outputType])) {
			$this->outputType = $outputType;
		} else {
			throw new AgaviConfigurationException('Output Type "' . $outputType . '" has not been configured.');
		}
	}
	
	/**
	 * Retrieves the output type name set for this response.
	 *
	 * @return     string The name of the output type.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getOutputType()
	{
		return $this->outputType;
	}
	
	/**
	 * Retrieve configuration details about an output type.
	 *
	 * @param      string The output type name.
	 *
	 * @return     array An associative array of output type settings and params.
	 *
	 * @throws     <b>AgaviConfigurationException</b> If the given output type 
	 *                                                doesnt exist.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getOutputTypeInfo($outputType = null)
	{
		if($outputType === null) {
			$outputType = $this->outputType;
		}
		if(isset($this->outputTypes[$outputType])) {
			return $this->outputTypes[$outputType];
		} else {
			throw new AgaviConfigurationException('Output Type "' . $outputType . '" has not been configured.');
		}
	}
}

?>
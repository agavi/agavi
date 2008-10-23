<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2005-2008 the Agavi Project.                                |
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
 * AgaviRouting allows you to centralize your entry point urls in your web
 * application.
 *
 * @package    agavi
 * @subpackage routing
 *
 * @author     Dominik del Bondio <ddb@bitxtender.com>
 * @author     David Zülke <dz@bitxtender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.11.0
 *
 * @version    $Id$
 */
abstract class AgaviRouting extends AgaviParameterHolder
{
	const ANCHOR_NONE = 0;
	const ANCHOR_START = 1;
	const ANCHOR_END = 2;

	/**
	 * @var        array An array of route information
	 */
	protected $routes = array();

	/**
	 * @var        AgaviContext An AgaviContext instance.
	 */
	protected $context = null;

	/**
	 * @var        string Route input.
	 */
	protected $input = null;

	/**
	 * @var        array An array of AgaviRoutingArraySource.
	 */
	protected $sources = array();

	/**
	 * @var        string Route prefix to use with gen()
	 */
	protected $prefix = '';

	/**
	 * @var        array An array of default options for gen()
	 */
	protected $defaultGenOptions = array();

	/**
	 * @var        array An array of default options presets for gen()
	 */
	protected $genOptionsPresets = array();

	/**
	 * Constructor.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function __construct()
	{
		// for now, we still use this setting as default.
		// will be removed in 1.1
		$this->setParameter('enabled', AgaviConfig::get('core.use_routing', true));
		
		$this->defaultGenOptions = array_merge($this->defaultGenOptions, array(
			'relative' => true,
			'refill_all_parameters' => false,
			'omit_defaults' => false,
		));
	}
	
	/**
	 * Initialize the routing instance.
	 *
	 * @param      AgaviContext The Context.
	 * @param      array        An array of initialization parameters.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function initialize(AgaviContext $context, array $parameters = array())
	{
		$this->context = $context;

		$this->setParameters($parameters);

		$this->defaultGenOptions = array_merge(
			$this->defaultGenOptions,
			$this->getParameter('default_gen_options', array())
		);

		$this->genOptionsPresets = array_merge(
			$this->genOptionsPresets,
			$this->getParameter('gen_options_presets', array())
		);
		
		// and load the config.
		$this->loadConfig();
	}

	/**
	 * Load the routing.xml configuration file.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	protected function loadConfig()
	{
		$cfg = AgaviConfig::get("core.config_dir") . "/routing.xml";
		// allow missing routing.xml when routing is not enabled
		if($this->isEnabled() || is_readable($cfg)) {
			include(AgaviConfigCache::checkConfig($cfg, $this->context->getName()));
		}
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
		$this->sources['_ENV'] = new AgaviRoutingArraySource($_ENV);

		$this->sources['_SERVER'] = new AgaviRoutingArraySource($_SERVER);

		if(AgaviConfig::get('core.use_security')) {
			$this->sources['user'] = new AgaviRoutingUserSource($this->context->getUser());
		}
	}

	/**
	 * Execute the shutdown procedure.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function shutdown()
	{
	}

	/**
	 * Check if this routing instance is enabled.
	 *
	 * @return     bool Whether or not routing is enabled.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      1.0.0
	 */
	public function isEnabled()
	{
		return $this->getParameter('enabled') === true;
	}

	/**
	 * Retrieve the current application context.
	 *
	 * @return     AgaviContext A Context instance.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public final function getContext()
	{
		return $this->context;
	}

	/**
	 * Retrieve the info about a named route for this routing instance.
	 *
	 * @return     mixed The route info or null if the route doesn't exist.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public final function getRoute($name)
	{
		if(!isset($this->routes[$name])) {
			return null;
		}
		return $this->routes[$name];
	}

	/**
	 * Retrieve the input for this routing instance.
	 *
	 * @return     string The input.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public final function getInput()
	{
		return $this->input;
	}

	/**
	 * Retrieve the prefix for this routing instance.
	 *
	 * @return     string The prefix.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public final function getPrefix()
	{
		return $this->prefix;
	}

	/**
	 * Adds a route to this routing instance.
	 *
	 * @param      string A string with embedded regexp.
	 * @param      array An array with options. The array can contain following
	 *                   items:
	 *                   <ul>
	 *                    <li>name</li>
	 *                    <li>stop</li>
	 *                    <li>output_type</li>
	 *                    <li>module</li>
	 *                    <li>action</li>
	 *                    <li>parameters</li>
	 *                    <li>ignores</li>
	 *                    <li>defaults</li>
	 *                    <li>childs</li>
	 *                    <li>callback</li>
	 *                    <li>imply</li>
	 *                    <li>cut</li>
	 *                    <li>source</li>
	 *                   </ul>
	 * @param      string The name of the parent route (if any).
	 *
	 * @return     string The name of the route.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function addRoute($route, array $options = array(), $parent = null)
	{
		// catch the old options from the route which has to be overwritten
		if(isset($options['name']) && isset($this->routes[$options['name']])) {
			$defaultOpts = $this->routes[$options['name']]['opt'];

			// when the parent is set and differs from the parent of the route to be overwritten bail out
			if($parent !== null && $defaultOpts['parent'] != $parent) {
				throw new AgaviException('You are trying to overwrite a route but are not staying in the same hierarchy');
			}

			if($parent === null) {
				$parent = $defaultOpts['parent'];
			} else {
				$defaultOpts['parent'] = $parent;
			}
		} else {
			$defaultOpts = array('name' => AgaviToolkit::uniqid(), 'stop' => true, 'output_type' => null, 'module' => null, 'action' => null, 'parameters' => array(), 'ignores' => array(), 'defaults' => array(), 'childs' => array(), 'callback' => null, 'imply' => false, 'cut' => null, 'source' => null, 'method' => null, 'constraint' => array(), 'locale' => null, 'pattern_parameters' => array(), 'optional_parameters' => array(), 'parent' => $parent, 'reverseStr' => '', 'nostops' => array(), 'anchor' => self::ANCHOR_NONE);
		}

		if(isset($options['defaults'])) {
			foreach($options['defaults'] as $name => &$value) {
				$val = $pre = $post = '';
				if(preg_match('#(.*)\{(.*)\}(.*)#', $value, $match)) {
					$pre = $match[1];
					$val = $match[2];
					$post = $match[3];
				} else {
					$val = $value;
				}

				$value = array(
					'pre' => $pre,
					'val' => $val,
					'post' => $post,
				);
			}
		}

		// set the default options + user opts
		$options = array_merge($defaultOpts, $options);
		list($regexp, $options['reverseStr'], $routeParams, $options['anchor']) = $this->parseRouteString($route);

		$params = array();

		// transfer the parameters and fill available automatic defaults
		foreach($routeParams as $name => $param) {
			$params[] = $name;

			if($param['is_optional']) {
				$options['optional_parameters'][$name] = true;
			}

			if(!isset($options['defaults'][$name]) && ($param['pre'] || $param['val'] || $param['post'])) {
				unset($param['is_optional']);
				$options['defaults'][$name] = $param;
			}
		}

		$options['pattern_parameters'] = $params;

		// remove all ignore from the parameters in the route
		foreach($options['ignores'] as $ignore) {
			if(($key = array_search($ignore, $params)) !== false) {
				unset($params[$key]);
			}
		}

		$routeName = $options['name'];

		// parse all the setting values for dynamic variables
		// check if 2 nodes with the same name in the same execution tree exist
		foreach($this->routes as $name => $route) {
			// if a route with this route as parent exist check if its really a child of our route
			if($route['opt']['parent'] == $routeName && !in_array($name, $options['childs'])) {
				throw new AgaviException('The route ' . $routeName . ' specifies a child route with the same name');
			}
		}

		// direct childs/parents with the same name arent caught by the above check
		if($routeName == $parent) {
			throw new AgaviException('The route ' . $routeName . ' specifies a child route with the same name');
		}

		// if we are a child route, we need add this route as a child to the parent
		if($parent !== null) {
			foreach($this->routes[$parent]['opt']['childs'] as $name) {
				$route = $this->routes[$name];
				if(!$route['opt']['stop']) {
					$options['nostops'][] = $name;
				}
			}
			$this->routes[$parent]['opt']['childs'][] = $routeName;
		} else {
			foreach($this->routes as $name => $route) {
				if(!$route['opt']['stop'] && !$route['opt']['parent']) {
					$options['nostops'][] = $name;
				}
			}
		}

		$route = array('rxp' => $regexp, 'par' => $params, 'opt' => $options, 'matches' => array());
		$this->routes[$routeName] = $route;

		return $routeName;
	}

	/**
	 * Retrieve the internal representation of the route info.
	 *
	 * @return     array The info about all routes.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function exportRoutes()
	{
		return $this->routes;
	}

	/**
	 * Sets the internal representation of the route info.
	 *
	 * @param      array The info about all routes.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function importRoutes(array $routes)
	{
		$this->routes = $routes;
	}

	/**
	 * Retrieves the routes which need to be taken into account when generating
	 * the reverse string of a routing to be generated.
	 *
	 * @param      string  The route name(s, delimited by +) to calculate.
	 *
	 * @return     array A list of names of affected routes.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getAffectedRoutes($route)
	{
		$routes = explode('+', $route);

		$route = $routes[0];
		unset($routes[0]);

		$myRoutes = array();
		foreach($routes as $r) {
			$myRoutes[$r] = true;
		}

		$affectedRoutes = array();

		if(isset($this->routes[$route])) {
			$parent = $route;
			do {
				$affectedRoutes[] = $parent;
				$r = $this->routes[$parent];

				foreach(array_reverse($r['opt']['nostops']) as $noStop) {
					$myR = $this->routes[$noStop];
					if(isset($myRoutes[$noStop])) {
						unset($myRoutes[$noStop]);
					} elseif(!$myR['opt']['imply']) {
						continue;
					}

					$affectedRoutes[] = $noStop;
				}

				$parent = $r['opt']['parent'];

			} while($parent);
		} else {
			// TODO: error handling - route with the given name does not exist
		}

		if(count($myRoutes)) {
			// TODO: error handling - we couldn't find some of the nonstopping rules
		}

		return $affectedRoutes;
	}

	/**
	 * Get a complete list of gen() options based on the given, probably
	 * incomplete, options array, or options preset name.
	 *
	 * @param      mixed An array of gen options or the name of an options preset.
	 *
	 * @return     array A complete array of options.
	 *
	 * @throws     AgaviException If the given preset name doesn't exist.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	protected function resolveGenOptions($input = array())
	{
		if(is_string($input)) {
			if(isset($this->genOptionsPresets[$input])) {
				return array_merge($this->defaultGenOptions, $this->genOptionsPresets[$input]);
			}
		} elseif(is_array($input)) {
			return array_merge($this->defaultGenOptions, $input);
		}
		throw new AgaviException('Undefined Routing gen() options preset "' . $input . '"');
	}
	
	protected function onGenerateParamDiffCallback($a, $b)
	{
		return ($a === $b) ? 0 : 1;
	}
	
	protected function assembleRoutes(array $options, array $routeNames, array $params)
	{
		$uri = '';
		$defaultParams = array();
		$availableParams = array();
		$matchedParams = array(); // the merged incoming matched params of implied routes
		$optionalParams = array();
		$firstRoute = true;
		foreach($routeNames as $routeName) {
			$r = $this->routes[$routeName];

			$myDefaults = $r['opt']['defaults'];

			if(isset($r['opt']['callback'])) {
				if(!isset($r['cb'])) {
					$cb = $r['opt']['callback'];
					$r['cb'] = new $cb();
					$r['cb']->initialize($this->context, $r);
				}
				$paramsCopy = $params;
				if(!$r['cb']->onGenerate($myDefaults, $params, $options)) {
					continue;
				}
				// TODO: discuss using an interface again.
				$diff = array_diff_uassoc($params, $paramsCopy, array($this, 'onGenerateParamDiffCallback'));
				if(count($diff)) {
					$diffKeys = array_keys($diff);
					foreach($diffKeys as $key) {
						$value =& $params[$key];
						if(is_array($value) && array_key_exists('pre', $value) && array_key_exists('val', $value) && array_key_exists('post', $value)) {
							$value = new AgaviRoutingValue($value['val'], $value['pre'], $value['post']);
						} elseif(!($value instanceof AgaviRoutingValue)) {
							$value = new AgaviRoutingValue($value, isset($myDefaults[$key]['pre']) ? $myDefaults[$key]['pre'] : null, isset($myDefaults[$key]['post']) ? $myDefaults[$key]['post'] : null, true);
						}
						// TODO: when setting a AgaviRoutingValue from a callback you have to insert the defaults yourself, needs discussion
					}
				}
			}

			// if the route has a source we shouldn't put its stuff in the generated string
			if($r['opt']['source']) {
				continue;
			}

			$matchedParams = array_merge($matchedParams, $r['matches']);
			$optionalParams = array_merge($optionalParams, $r['opt']['optional_parameters']);

			$availableParams = array_merge($availableParams, array_reverse($r['opt']['pattern_parameters']));

			if($firstRoute || $r['opt']['cut'] || (count($r['opt']['childs']) && $r['opt']['cut'] === null)) {
				if($r['opt']['anchor'] & self::ANCHOR_START || $r['opt']['anchor'] == self::ANCHOR_NONE) {
					$uri = $r['opt']['reverseStr'] . $uri;
				} else {
					$uri = $uri . $r['opt']['reverseStr'];
				}
			}

			$defaultParams = array_merge($defaultParams, $myDefaults);
			$firstRoute = false;
		}
		
		$availableParams = array_reverse($availableParams);
		
		return array(
			'uri' => $uri,
			'user_parameters' => $params,
			'available_parameters' => $availableParams,
			'matched_parameters' => $matchedParams,
			'optional_parameters' => $optionalParams,
			'default_parameters' => $defaultParams,
		);
	}
	
	protected function refillAllMatchedParameters(array $options, array $params, array $matchedParams)
	{
		if(!empty($options['refill_all_parameters'])) {
			foreach($matchedParams as $name => $value) {
				if(!(isset($params[$name]) || array_key_exists($name, $params))) {
					$params[$name] = $value;
				}
			}
		}
		
		return $params;
	}
	
	protected function refillMatchedAndDefaultParameters(array $options, array $params, array $availableParams, array $matchedParams, array $optionalParams, array $defaultParams)
	{
		$refillValue = true;
		$finalParams = array();
		foreach($availableParams as $name) {
			// loop all params and fill all with the matched parameters
			// until a user (not callback) supplied parameter is encountered.
			// After that only check defaults. Parameters supplied from the user
			// or via callback always have precedence

			// keep track if a user supplied has already been encountered
			if($refillValue && (isset($params[$name]) || array_key_exists($name, $params))) {
				$refillValue = false;
			}

			// these 'aliases' are just for readability of the lower block
			$isOptional = isset($optionalParams[$name]);
			$hasMatched = isset($matchedParams[$name]);
			$hasDefault = isset($defaultParams[$name]);
			$hasUserCallbackParam = (isset($params[$name]) || array_key_exists($name, $params));

			if($hasUserCallbackParam) {
				// anything a user or callback supplied has precedence
				// and since the user params are handled afterwards, skip them here
			} elseif($refillValue && $hasMatched) {
				// Use the matched input
				if($hasDefault && ($defaultParams[$name]['pre'] || $defaultParams[$name]['post'])) {
					$finalParams[$name] = new AgaviRoutingValue($matchedParams[$name], $defaultParams[$name]['pre'], $defaultParams[$name]['post']);
				} else {
					$finalParams[$name] = $matchedParams[$name];
				}
			} elseif($hasDefault) {
				// now we just need to check if there are defaults for this available param and fill them in if applicable
				$default = $defaultParams[$name];
				if(!$isOptional || strlen($default['val']) > 0) {
					$finalParams[$name] = new AgaviRoutingValue($default['val'], $default['pre'], $default['post']);
				} elseif($isOptional) {
					// there is no default or incoming match for this optional param, so remove it
					$finalParams[$name] = null;
				}
			}
		}
		
		return $finalParams;
	}
	
	protected function fillUserParameters(array $options, array $params, array $finalParams, array $availableParams, array $optionalParams, array $defaultParams)
	{
		$availableParamsAsKeys = array_flip($availableParams);

		foreach($params as $name => $param) {
			if(!(isset($finalParams[$name]) || array_key_exists($name, $finalParams))) {
				if($param === null && isset($optionalParams[$name])) {
					$finalParams[$name] = $param;
				} else {
					if(isset($defaults[$name])) {
						$param = $param !== null ? $param : $defaultParams[$name]['val'];
						if($defaultParams[$name]['pre'] || $defaultParams[$name]['post']) {
							$param = new AgaviRoutingValue($param, $defaultParams[$name]['pre'], $defaultParams[$name]['post']);
						}
						$finalParams[$name] = $param;
					} elseif(isset($availableParamsAsKeys[$name]) || array_key_exists($name, $availableParamsAsKeys) || $param === null) {
						// when the parameter was available in one of the routes or has explicitly been unset
						$finalParams[$name] = $param;
					}
				}
			}
		}
		
		return $finalParams;
	}

	protected function removeMatchingDefaults(array $options, array $finalParams, array $availableParams, array $optionalParams, array $defaultParams)
	{
		if(!empty($options['omit_defaults'])) {
			// remove the optional parameters from the right to the left from the pattern when they match
			// their default
			foreach(array_reverse($availableParams) as $name) {
				if(isset($optionalParams[$name])) {
					// the isset() could be replaced by
					// "!array_key_exists($name, $finalParams) || $finalParams[$name] === null"
					// to clarify that null is explicitly allowed here
					if(!isset($finalParams[$name]) || (
							isset($defaultParams[$name]) && (
								($finalParams[$name] instanceof AgaviRoutingValue && $finalParams[$name]->equals($defaultParams[$name])) ||
								(!($finalParams[$name] instanceof AgaviRoutingValue) && $finalParams[$name] == $defaultParams[$name]['val'])
							)
						)
					) {
						$finalParams[$name] = null;
					} else {
						break;
					}
				} else {
					break;
				}
			}
		}
		
		return $finalParams;
	}
	
	protected function encodeParameters(array $options, array $params)
	{
		foreach($params as &$param) {
			$param = $this->escapeOutputParameter($param);
		}
		return $params;
	}
	
	protected function encodeParameter($parameter)
	{
		if($parameter instanceof AgaviRoutingValue) {
			return sprintf('%s%s%s', 
				$parameter->isPrefixEncoded()  ? $parameter->getPrefix()  : $this->escapeOutputParameter($parameter->getPrefix()),
				$parameter->isValueEncoded()   ? $parameter->getValue()   : $this->escapeOutputParameter($parameter->getValue()),
				$parameter->isPostfixEncoded() ? $parameter->getPostfix() : $this->escapeOutputParameter($parameter->getPostfix())
			);
		} else {
			return $this->escapeOutputParameter($parameter);
		}
	}

	/**
	 * Generate a formatted Agavi URL.
	 *
	 * @param      string A route name.
	 * @param      array  An associative array of parameters.
	 * @param      mixed  An array of options, or the name of an options preset.
	 *
	 * @return     array An array containing the generated route path, the
	 *                   (possibly modified) parameters, and the (possibly
	 *                   modified) options.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function gen($route, array $params = array(), $options = array())
	{
		// we need to store the original params since we will be trying to fill the
		// parameters up to the first user supplied parameter
		$originalParams = $params;
		
		$routes = $route;
		if(is_string($route)) {
			$routes = $this->getAffectedRoutes($routes);
		}
		
		$assembledInformation = $this->assembleRoutes($options, $routes, $params);
		
		$params = $assembledInformation['user_parameters'];

		$params = $this->refillAllMatchedParameters($options, $params, $assembledInformation['matched_parameters']);
		$finalParams = $this->refillMatchedAndDefaultParameters($options, $params, $assembledInformation['available_parameters'], $assembledInformation['matched_parameters'], $assembledInformation['optional_parameters'], $assembledInformation['default_parameters']);
		$finalParams = $this->fillUserParameters($options, $params, $finalParams, $assembledInformation['available_parameters'], $assembledInformation['optional_parameters'], $assembledInformation['default_parameters']);
		$finalParams = $this->removeMatchingDefaults($options, $finalParams, $assembledInformation['available_parameters'], $assembledInformation['optional_parameters'], $assembledInformation['default_parameters']);

		// remember the params that are not in any pattern (could be extra query params, for example, set by a callback)
		$extras = array_diff_key($params, $finalParams);

		$params = $finalParams;

		$params = $this->encodeParameters($options, $params);

		$from = array();
		$to = array();
		

		// remove not specified available parameters
		foreach(array_unique($assembledInformation['available_parameters']) as $name) {
			if(!isset($params[$name])) {
				$from[] = '(:' . $name . ':)';
				$to[] = '';
			}
		}

		foreach($params as $n => $p) {
			$from[] = '(:' . $n . ':)';
			$to[] = $p;
		}

		$uri = str_replace($from, $to, $assembledInformation['uri']);
		return array($this->prefix . $uri, $params, $options, $extras);
	}
	
	
	/**
	 * Escapes an argument to be used in an generated route.
	 *
	 * @param      string The argument to be escaped.
	 *
	 * @return     string The escaped argument.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function escapeOutputParameter($string)
	{
		return $string;
	}

	/**
	 * Matches the input against the routing info and sets the info as request
	 * parameter.
	 *
	 * @return     AgaviExecutionContainer An execution container holding all of the
	 *                                     matched routes.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function execute()
	{
		$req = $this->context->getRequest();

		$reqData = $req->getRequestData();

		$container = $this->context->getController()->createExecutionContainer();

		if(!$this->isEnabled()) {
			// routing disabled, just bail out here
			return $container;
		}

		$matchedRoutes = array();

		$input = $this->input;

		$vars = array();
		$ot = null;
		$locale = null;
		$method = null;
		$ma = $req->getParameter('module_accessor');
		$aa = $req->getParameter('action_accessor');
		$requestMethod = $req->getMethod();

		$routes = array();
		// get all top level routes
		foreach($this->routes as $name => $route) {
			if(!$route['opt']['parent']) {
				$routes[] = $name;
			}
		}

		// prepare the working stack with the root routes
		$routeStack = array($routes);

		do {
			$routes = array_pop($routeStack);
			foreach($routes as $key) {
				$route =& $this->routes[$key];
				$opts =& $route['opt'];
				if(count($opts['constraint']) == 0 || in_array($requestMethod, $opts['constraint'])) {
					if($opts['callback'] && !isset($route['cb'])) {
						$cb = $opts['callback'];
						$route['cb'] = new $cb();
						$route['cb']->initialize($this->context, $route);
					}

					$match = array();
					if($this->parseInput($route, $input, $match)) {
						$ign = array();
						if(count($opts['ignores']) > 0) {
							$ign = array_flip($opts['ignores']);
						}

						foreach($opts['defaults'] as $key => $value) {
							if(!isset($ign[$key]) && $value['val']) {
								$vars[$key] = $value['val'];
							}
						}

						foreach($route['par'] as $param) {
							if(isset($match[$param]) && $match[$param][1] != -1) {
								$vars[$param] = $match[$param][0];
							}
						}

						foreach($match as $name => $m) {
							if(is_string($name) && $m[1] != -1) {
								$route['matches'][$name] = $m[0];
							}
						}

						if($opts['callback']) {
							if(count($opts['ignores']) > 0) {
								$cbVars = array();
								// add ignored variables to the callback vars
								foreach($vars as $name => &$var) {
									$cbVars[$name] =& $var;
								}
								foreach($opts['ignores'] as $ignore) {
									if(isset($match[$ignore]) && $match[$ignore][1] != -1) {
										$cbVars[$ignore] = $match[$ignore][0];
									}
								}
							} else {
								$cbVars =& $vars;
							}
							if(!$route['cb']->onMatched($cbVars, $container)) {
								// reset the matches array. it must be populated by the time onMatched() is called so matches can be modified in a callback
								$route['matches'] = array();
								continue;
							}
						}

						$matchedRoutes[] = $opts['name'];

						// matches are arrays with value and offset due to PREG_OFFSET_CAPTURE, and we want index 0, the value, which current() will give us
						$matchvals = array_map('current', $match);

						if($opts['module']) {
							$vars[$ma] = AgaviToolkit::expandVariables($opts['module'], $matchvals);
						}

						if($opts['action']) {
							$vars[$aa] = AgaviToolkit::expandVariables($opts['action'], $matchvals);
						}

						if($opts['output_type']) {
							$ot = AgaviToolkit::expandVariables($opts['output_type'], $matchvals);
						}

						if($opts['locale']) {
							$locale = AgaviToolkit::expandVariables($opts['locale'], $matchvals);
						}

						if($opts['method']) {
							$method = AgaviToolkit::expandVariables($opts['method'], $matchvals);
						}

						if($opts['cut'] || (count($opts['childs']) && $opts['cut'] === null)) {
							if($route['opt']['source'] !== null) {
								$s =& $this->sources[$route['opt']['source']];
							} else {
								$s =& $input;
							}

							$ni = '';
							// if the route didn't match from the start of the input preserve the 'prefix'
							if($match[0][1] > 0) {
								$ni = substr($s, 0, $match[0][1]);
							}
							$ni .= substr($s, $match[0][1] + strlen($match[0][0]));
							$s = $ni;
						}

						if(count($opts['childs'])) {
							// our childs need to be processed next and stop processing 'afterwards'
							$routeStack[] = $opts['childs'];
							break;
						}

						if($opts['stop']) {
							break;
						}

					} else {
						if($opts['callback']) {
							$route['cb']->onNotMatched($container);
						}
					}
				}
			}
		} while(count($routeStack) > 0);

		// set the output type if necessary
		if($ot !== null) {
			$container->setOutputType($this->context->getController()->getOutputType($ot));
		}

		// set the locale if necessary
		if($locale) {
			$this->context->getTranslationManager()->setLocale($locale);
		}

		// set the request method if necessary
		if($method) {
			$req->setMethod($method);
			// and on the already created container, too!
			$container->setRequestMethod($method);
		}

		// put the vars into the request
		$reqData->setParameters($vars);

		if(!$reqData->hasParameter($ma) || !$reqData->hasParameter($aa)) {
			// no route which supplied the required parameters matched, use 404 action
			$reqData->setParameters(array(
				$ma => AgaviConfig::get('actions.error_404_module'),
				$aa => AgaviConfig::get('actions.error_404_action')
			));
		}

		$container->setModuleName($reqData->getParameter($ma));
		$container->setActionName($reqData->getParameter($aa));

		// set the list of matched route names as a request attribute
		$req->setAttribute('matched_routes', $matchedRoutes, 'org.agavi.routing');

		// return a list of matched route names
		return $container;
	}

	/**
	 * Performs as match of the route against the input
	 *
	 * @param      array The route info array.
	 * @param      string The input.
	 * @param      array The array where the matches will be stored to.
	 *
	 * @return     bool Whether the regexp matched.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	protected function parseInput(array $route, $input, &$matches)
	{
		if($route['opt']['source'] !== null) {
			$parts = AgaviArrayPathDefinition::getPartsFromPath($route['opt']['source']);
			$partArray = $parts['parts'];
			$count = count($partArray);
			if($count > 0 && isset($this->sources[$partArray[0]])) {
				$input = $this->sources[$partArray[0]];
				if($count > 1) {
					array_shift($partArray);
					if(is_array($input)) {
						$input = AgaviArrayPathDefinition::getValue($partArray, $input);
					} elseif($input instanceof AgaviIRoutingSource) {
						$input = $input->getSource($partArray);
					}
				}
			}
		}
		return preg_match($route['rxp'], $input, $matches, PREG_OFFSET_CAPTURE);
	}

	/**
	 * Parses a route pattern string.
	 *
	 * @param      string The route pattern.
	 *
	 * @return     array The info for this route pattern.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	protected function parseRouteString($str)
	{
		$vars = array();
		$rxStr = '';
		$reverseStr = '';

		$anchor = 0;
		$anchor |= (substr($str, 0, 1) == '^') ? self::ANCHOR_START : 0;
		$anchor |= (substr($str, -1) == '$') ? self::ANCHOR_END : 0;

		$str = substr($str, (int)$anchor & self::ANCHOR_START, $anchor & self::ANCHOR_END ? -1 : strlen($str));

		$rxChars = implode('', array('.', '\\', '+', '*', '?', '[', '^', ']', '$', '(', ')', '{', '}', '=', '!', '<', '>', '|', ':'));

		$len = strlen($str);
		$state = 'start';
		$tmpStr = '';
		$inEscape = false;

		$rxName = '';
		$rxInner = '';
		$rxPrefix = '';
		$rxPostfix = '';
		$parenthesisCount = 0;
		$bracketCount = 0;
		$hasBrackets = false;
		// whether the regular expression is clean of any regular expression
		// so we can reverse generate it
		$cleanRx = true;

		for($i = 0; $i < $len; ++$i) {
			$atEnd = $i + 1 == $len;

			$c = $str[$i];

			if(!$atEnd && !$inEscape && $c == '\\') {
				$cNext = $str[$i + 1];

				if(
					($cNext == '\\') ||
					($state == 'start' && $cNext == '(') ||
					($state == 'rxStart' && in_array($cNext, array('(',')','{','}')))
				) {
					$inEscape = true;
					continue;
				}
				if($state == 'afterRx' && $cNext == '?') {
					$inEscape = false;
					$state = 'start';
					continue;
				}
			} elseif($inEscape) {
				$tmpStr .= $c;
				$inEscape = false;
				continue;
			}

			if($state == 'start') {
				// start of regular expression block
				if($c == '(') {
					$rxStr .= preg_quote($tmpStr, '#');
					$reverseStr .= $tmpStr;

					$tmpStr = '';
					$state = 'rxStart';
					$rxName = $rxInner = $rxPrefix = $rxPostfix = '';
					$parenthesisCount = 1;
					$bracketCount = 0;
					$hasBrackets = false;
				} else {
					$tmpStr .= $c;
				}

				if($atEnd) {
					$rxStr .= preg_quote($tmpStr, '#');
					$reverseStr .= $tmpStr;
				}
			} elseif($state == 'rxStart') {
				if($c == '{') {
					++$bracketCount;
					if($bracketCount == 1) {
						$hasBrackets = true;
						$rxPrefix = $tmpStr;
						$tmpStr = '';
					} else {
						$tmpStr .= $c;
					}
				} elseif($c == '}') {
					--$bracketCount;
					if($bracketCount == 0) {
						list($rxName, $rxInner) = $this->parseParameterDefinition($tmpStr);
						$tmpStr = '';
					} else {
						$tmpStr .= $c;
					}
				} elseif($c == '(') {
					++$parenthesisCount;
					$tmpStr .= $c;
				} elseif($c == ')') {
					--$parenthesisCount;
					if($parenthesisCount > 0) {
						$tmpStr .= $c;
					} else {
						if($parenthesisCount < 0) {
							throw new AgaviException('The pattern ' . $str . ' contains an unbalanced set of parentheses!');
						}

						if(!$hasBrackets) {
							list($rxName, $rxInner) = $this->parseParameterDefinition($tmpStr);
						} else {
							if($bracketCount != 0) {
								throw new AgaviException('The pattern ' . $str . ' contains an unbalanced set of brackets!');
							}
							$rxPostfix = $tmpStr;
						}

						if(!$rxName) {
							$myRx = $rxPrefix . $rxInner . $rxPostfix;
							// if the entire regular expression doesn't contain any regular expression character we can savely append it to the reverseStr
							//if(strlen($myRx) == strcspn($myRx, $rxChars)) {
							if(strpbrk($myRx, $rxChars) === false) {
								$reverseStr .= $myRx;
							}
							$rxStr .= str_replace('#', '\#', sprintf('(%s)', $myRx));
						} else {
							$rxStr .= str_replace('#', '\#', sprintf('(%s(?P<%s>%s)%s)', $rxPrefix, $rxName, $rxInner, $rxPostfix));
							$reverseStr .= sprintf('(:%s:)', $rxName);

							if(!isset($vars[$rxName])) {
								if(strpbrk($rxPrefix, $rxChars) !== false) {
									$rxPrefix = '';
								}
								if(strpbrk($rxInner, $rxChars) !== false) {
									$rxInner = '';
								}
								if(strpbrk($rxPostfix, $rxChars) !== false) {
									$rxPostfix = '';
								}

								$vars[$rxName] = array('pre' => $rxPrefix, 'val' => $rxInner, 'post' => $rxPostfix, 'is_optional' => false);
							}
						}

						$tmpStr = '';
						$state = 'afterRx';
					}
				} else {
					$tmpStr .= $c;
				}

				if($atEnd && $parenthesisCount != 0) {
					throw new AgaviException('The pattern ' . $str . ' contains an unbalanced set of parentheses!');
				}
			} elseif($state == 'afterRx') {
				if($c == '?') {
					// only record the optional state when the pattern had a name
					if(isset($vars[$rxName])) {
						$vars[$rxName]['is_optional'] = true;
					}
					$rxStr .= $c;
				} else {
					// let the start state parse the char
					--$i;
				}

				$state = 'start';
			}
		}

		$rxStr = sprintf('#%s%s%s#', $anchor & self::ANCHOR_START ? '^' : '', $rxStr, $anchor & self::ANCHOR_END ? '$' : '');
		return array($rxStr, $reverseStr, $vars, $anchor);
	}

	/**
	 * Parses an embedded regular expression in the route pattern string.
	 *
	 * @param      string The definition.
	 *
	 * @return     array The name and the regexp.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	protected function parseParameterDefinition($def)
	{
		$name = '';
		$rx = '';

		preg_match('#([a-z0-9_-]+:)?(.*)#i', $def, $match);
		return array(substr($match[1], 0, -1), $match[2]);
	}
}

?>
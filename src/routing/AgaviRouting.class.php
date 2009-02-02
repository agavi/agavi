<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2005-2009 the Agavi Project.                                |
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
			$this->importRoutes(unserialize(file_get_contents(AgaviConfigCache::checkConfig($cfg, $this->context->getName()))));
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
	 *                    <li>callbacks</li>
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
			$defaultOpts = array('name' => AgaviToolkit::uniqid(), 'stop' => true, 'output_type' => null, 'module' => null, 'action' => null, 'parameters' => array(), 'ignores' => array(), 'defaults' => array(), 'childs' => array(), 'callbacks' => array(), 'imply' => false, 'cut' => null, 'source' => null, 'method' => null, 'constraint' => array(), 'locale' => null, 'pattern_parameters' => array(), 'optional_parameters' => array(), 'parent' => $parent, 'reverseStr' => '', 'nostops' => array(), 'anchor' => self::ANCHOR_NONE);
		}
		// retain backwards compatibility to 0.11
		if(isset($options['callback'])) {
			$options['callbacks'] = array(array('class' => $options['callback'], 'parameters' => array()));
			unset($options['callback']);
		}

		if(isset($options['defaults'])) {
			foreach($options['defaults'] as $name => &$value) {
				$val = $pre = $post = null;
				if(preg_match('#(.*)\{(.*)\}(.*)#', $value, $match)) {
					$pre = $match[1];
					$val = $match[2];
					$post = $match[3];
				} else {
					$val = $value;
				}

				$value = $this->createValue($val)->setPrefix($pre)->setPostfix($post);
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
				$options['defaults'][$name] = $this->createValue($param['val'])->setPrefix($param['pre'])->setPostfix($param['post']);
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
	 * @param      string The route name(s, delimited by +) to calculate.
	 *
	 * @return     array A list of names of affected routes.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getAffectedRoutes($route)
	{
		$includedRoutes = array();
		$excludedRoutes = array();
		
		if($route === null) {
			$includedRoutes = array_reverse($this->getContext()->getRequest()->getAttribute('matched_routes', 'org.agavi.routing', array()));
		} elseif(strlen($route) > 0) {
			if($route[0] == '-' || $route[0] == '+') {
				$includedRoutes = array_reverse($this->getContext()->getRequest()->getAttribute('matched_routes', 'org.agavi.routing', array()));
			}
			
			$routeParts = preg_split('#(-|\+)#', $route, -1, PREG_SPLIT_DELIM_CAPTURE);
			$prevDelimiter = '+';
			foreach($routeParts as $part) {
				if($part == '+' || $part == '-') {
					$prevDelimiter = $part;
				}
				
				if($prevDelimiter == '+') {
					$includedRoutes[] = $part;
				} else { // $prevDelimiter == '-'
					$excludedRoutes[] = $part;
				}
			}
		}
		
		$excludedRoutes = array_flip($excludedRoutes);

		if($includedRoutes) {
			$route = $includedRoutes[0];
			// TODO: useful comment here
			unset($includedRoutes[0]);
		}
		
		$myRoutes = array();
		foreach($includedRoutes as $r) {
			$myRoutes[$r] = true;
		}

		$affectedRoutes = array();

		if(isset($this->routes[$route])) {
			$parent = $route;
			do {
				if(!isset($excludedRoutes[$parent])) {
					$affectedRoutes[] = $parent;
				}
				$r = $this->routes[$parent];

				foreach(array_reverse($r['opt']['nostops']) as $noStop) {
					$myR = $this->routes[$noStop];
					if(isset($myRoutes[$noStop])) {
						unset($myRoutes[$noStop]);
					} elseif(!$myR['opt']['imply']) {
						continue;
					}

					if(!isset($excludedRoutes[$noStop])) {
						$affectedRoutes[] = $noStop;
					}
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
	
	/**
	 * Builds the routing information (result string, all kinds of parameters)
	 * for the given routes.
	 * 
	 * @param      array The options
	 * @param      array The names of the routes to generate
	 * @param      array The parameters supplied by the user
	 * 
	 * @return     array
	 * 
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      1.0.0
	 */
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

			if(count($r['opt']['callbacks']) > 0) {
				if(!isset($r['callback_instances'])) {
					foreach($r['opt']['callbacks'] as $key => $callback) {
						$instance = new $callback['class']();
						$instance->initialize($this->context, $r);
						$instance->setParameters($callback['parameters']);
						$r['callback_instances'][$key] = $instance;
					}
				}
				foreach($r['callback_instances'] as $callbackInstance) {
					$paramsCopy = $params;
					$isLegacyCallback = false;
					if($callbackInstance instanceof AgaviILegacyRoutingCallback) {
						$isLegacyCallback = true;
						// convert all routing values to strings so legacy callbacks don't break
						$defaultsCopy = $myDefaults;
						foreach($paramsCopy as &$param) {
							if($param instanceof AgaviIRoutingValue) {
								$param = $param->getValue();
							}
						}
						foreach($defaultsCopy as &$default) {
							if($default instanceof AgaviIRoutingValue) {
								$default = array(
									'pre' => $default->getPrefix(),
									'val' => $default->getValue(),
									'post' => $default->getPostfix(),
								);
							}
						}
						$changedParamsCopy = $paramsCopy;
						if(!$callbackInstance->onGenerate($defaultsCopy, $paramsCopy, $options)) {
							continue 2;
						}
						// find all params that were changed by the callback
						$diff = array();
						foreach($paramsCopy as $key => $value) {
							if(!array_key_exists($key, $changedParamsCopy) || $changedParamsCopy[$key] !== $value) {
								$diff[$key] = $value;
							}
						}
						// do *not* use this instead, it will segfault in PHP < 5.2.6:
						// $diff = array_udiff_assoc($paramsCopy, $changedParamsCopy, array($this, 'onGenerateParamDiffCallback'));
						// likely caused by http://bugs.php.net/bug.php?id=42838 / http://cvs.php.net/viewvc.cgi/php-src/ext/standard/array.c?r1=1.308.2.21.2.51&r2=1.308.2.21.2.52
					} else {
						if(!$callbackInstance->onGenerate($myDefaults, $params, $options)) {
							continue 2;
						}
						// find all params changed in the callback
						$diff = array();
						foreach($params as $key => $value) {
							if(!array_key_exists($key, $paramsCopy) || $paramsCopy[$key] !== $value) {
								$diff[$key] = $value;
							}
						}
						// do *not* use this instead, it will segfault in PHP < 5.2.6:
						// $diff = array_udiff_assoc($params, $paramsCopy, array($this, 'onGenerateParamDiffCallback'));
						// likely caused by http://bugs.php.net/bug.php?id=42838 / http://cvs.php.net/viewvc.cgi/php-src/ext/standard/array.c?r1=1.308.2.21.2.51&r2=1.308.2.21.2.52
					}
					
					if(count($diff)) {
						$diffKeys = array_keys($diff);
						foreach($diffKeys as $key) {
							// NEVER assign this value as a reference, as PHP will go completely bonkers if we use a reference here (it marks the entry in the array as a reference, so modifying the value in $params in a callback means it gets modified in $paramsCopy as well)
							// if the callback was a legacy callback, the array to read the values from is different (since everything was cast to strings before running the callback)
							$value = $isLegacyCallback ? $paramsCopy[$key] : $params[$key];
							if($value !== null && !($value instanceof AgaviIRoutingValue)) {
								$routingValue = $this->createValue($value, false);
								if(isset($myDefaults[$key])) {
									if($myDefaults[$key] instanceof AgaviIRoutingValue) {
										// clone the default value so pre and postfix are preserved
										$routingValue = clone $myDefaults[$key];
										// BC: When setting a value in a callback it was supposed to be already encoded
										$routingValue->setValue($value)->setValueNeedsEncoding(false);
									} else {
										// $myDefaults[$key] can only be an array at this stage
										$routingValue->setPrefix($myDefaults[$key]['pre'])->setPrefixNeedsEncoding(false);
										$routingValue->setPostfix($myDefaults[$key]['post'])->setPostfixNeedsEncoding(false);
									}
								}
								$value = $routingValue;
							}
							// for writing no legacy check mustn't be done, since that would mean the changed value would get lost
							$params[$key] = $value;
						}
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
	
	/**
	 * Adds all matched parameters to the supplied parameters. Will not overwrite
	 * already existing parameters.
	 * 
	 * @param      array The options
	 * @param      array The parameters supplied by the user
	 * @param      array The parameters which matched in execute()
	 * 
	 * @return     array The $params with the added matched parameters
	 * 
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      1.0.0
	 */
	protected function refillAllMatchedParameters(array $options, array $params, array $matchedParams)
	{
		if(!empty($options['refill_all_parameters'])) {
			foreach($matchedParams as $name => $value) {
				if(!(isset($params[$name]) || array_key_exists($name, $params))) {
					$params[$name] = $this->createValue($value, true);
				}
			}
		}
		
		return $params;
	}
	
	/**
	 * Adds all parameters which were matched in the incoming routes to the 
	 * generated route up the first user supplied parameter (from left to right)
	 * Also adds the default value for all non optional parameters the user 
	 * didn't supply.
	 * 
	 * @param      array The options
	 * @param      array The parameters originally passed to gen()
	 * @param      array The parameters
	 * @param      array A list of parameter names available for the route
	 * @param      array The matched parameters from execute() for the route
	 * @param      array the optional parameters for the route
	 * @param      array the default parameters for the route
	 * 
	 * @return     array The 'final' parameters
	 * 
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      1.0.0
	 */
	protected function refillMatchedAndDefaultParameters(array $options, array $originalUserParams, array $params, array $availableParams, array $matchedParams, array $optionalParams, array $defaultParams)
	{
		$refillValue = true;
		$finalParams = array();
		foreach($availableParams as $name) {
			// loop all params and fill all with the matched parameters
			// until a user (not callback) supplied parameter is encountered.
			// After that only check defaults. Parameters supplied from the user
			// or via callback always have precedence

			// keep track if a user supplied parameter has already been encountered
			if($refillValue && (isset($originalUserParams[$name]) || array_key_exists($name, $originalUserParams))) {
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
				$finalParams[$name] = $this->createValue($matchedParams[$name], true);
			} elseif($hasDefault) {
				// now we just need to check if there are defaults for this available param and fill them in if applicable
				$default = $defaultParams[$name];
				if(!$isOptional || strlen($default->getValue()) > 0) {
					$finalParams[$name] = clone $default;
				} elseif($isOptional) {
					// there is no default or incoming match for this optional param, so remove it
					$finalParams[$name] = null;
				}
			}
		}

		return $finalParams;
	}
	
	/**
	 * Adds the user supplied parameters to the 'final' parameters for the route.
	 * 
	 * @param      array The options
	 * @param      array The user parameters
	 * @param      array The 'final' parameters 
	 * @param      array A list of parameter names available for the route
	 * @param      array the optional parameters for the route
	 * @param      array the default parameters for the route
	 * 
	 * @return     array The 'final' parameters
	 * 
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      1.0.0
	 */
	protected function fillUserParameters(array $options, array $params, array $finalParams, array $availableParams, array $optionalParams, array $defaultParams)
	{
		$availableParamsAsKeys = array_flip($availableParams);

		foreach($params as $name => $param) {
			if(!(isset($finalParams[$name]) || array_key_exists($name, $finalParams))) {
				if($param === null && isset($optionalParams[$name])) {
					// null was set for an optional parameter
					$finalParams[$name] = $param;
				} else {
					if(isset($defaultParams[$name])) {
						if($param === null || ($param instanceof AgaviIRoutingValue && $param->getValue() === null)) {
							// the user set the parameter to null, to signal that the default value should be used
							$param = clone $defaultParams[$name];
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

	/**
	 * Adds the user supplied parameters to the 'final' parameters for the route.
	 * 
	 * @param      array The options
	 * @param      array The user parameters
	 * @param      array The 'final' parameters 
	 * @param      array A list of parameter names available for the route
	 * @param      array the optional parameters for the route
	 * @param      array the default parameters for the route
	 * 
	 * @return     array The 'final' parameters
	 * 
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      1.0.0
	 */
	protected function removeMatchingDefaults(array $options, array $finalParams, array $availableParams, array $optionalParams, array $defaultParams)
	{
		// if omit_defaults is set, we should not put optional values into the result string in case they are equal to their default value - even if they were given as a param
		if(!empty($options['omit_defaults'])) {
			// remove the optional parameters from the pattern beginning from right to the left, in case they are equal to their default
			foreach(array_reverse($availableParams) as $name) {
				if(isset($optionalParams[$name])) {
					// the isset() could be replaced by
					// "!array_key_exists($name, $finalParams) || $finalParams[$name] === null"
					// to clarify that null is explicitly allowed here
					if(!isset($finalParams[$name]) ||
							(
								isset($defaultParams[$name]) && 
								$finalParams[$name]->getValue() == $defaultParams[$name]->getValue() &&
								(!$finalParams[$name]->hasPrefix() || $finalParams[$name]->getPrefix() == $defaultParams[$name]->getPrefix()) && 
								(!$finalParams[$name]->hasPostfix() || $finalParams[$name]->getPostfix() == $defaultParams[$name]->getPostfix())
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
	
	/**
	 * Updates the pre and postfixes in the final params from the default
	 * pre and postfix if available and if it hasn't been set yet by the user.
	 * 
	 * @param      array The 'final' parameters 
	 * @param      array the default parameters for the route
	 * 
	 * @return     array The 'final' parameters
	 * 
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      1.0.0
	 */
	protected function updatePrefixAndPostfix(array $finalParams, array $defaultParams)
	{
		foreach($finalParams as $name => $param) {
			if($param === null) {
				continue;
			}
			
			if(isset($defaultParams[$name])) {
				// update the pre- and postfix from the default if they are not set in the routing value
				$default = $defaultParams[$name];
				if(!$param->hasPrefix() && $default->hasPrefix()) {
					$param->setPrefix($default->getPrefix());
				}
				if(!$param->hasPostfix() && $default->hasPostfix()) {
					$param->setPostfix($default->getPostfix());
				}
			}
		}
		return $finalParams;
	}
	
	/**
	 * Encodes all 'final' parameters.
	 * 
	 * @param      array The 'final' parameters 
	 * @param      array the default parameters for the route
	 * 
	 * @return     array The 'final' parameters
	 * 
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      1.0.0
	 */
	protected function encodeParameters(array $options, array $params)
	{
		foreach($params as &$param) {
			$param = $this->encodeParameter($param);
		}
		return $params;
	}
	
	/**
	 * Encodes a single parameter.
	 * 
	 * @param      mixed An AgaviIRoutingValue object or a string
	 * 
	 * @return     string The encoded parameter
	 * 
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      1.0.0
	 */
	protected function encodeParameter($parameter)
	{
		if($parameter instanceof AgaviIRoutingValue) {
			return sprintf('%s%s%s', 
				$parameter->getPrefixNeedsEncoding()  ? $this->escapeOutputParameter($parameter->getPrefix())  : $parameter->getPrefix(),
				$parameter->getValueNeedsEncoding()   ? $this->escapeOutputParameter($parameter->getValue())   : $parameter->getValue(),
				$parameter->getPostfixNeedsEncoding() ? $this->escapeOutputParameter($parameter->getPostfix()) : $parameter->getPostfix()
			);
		} else {
			return $this->escapeOutputParameter($parameter);
		}
	}
	
	/**
	 * Converts all members of an array to AgaviIRoutingValues.
	 * 
	 * @param      array The parameters
	 * 
	 * @return     array An array containing all parameters as AgaviIRoutingValues
	 * 
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      1.0.0
	 */
	protected function convertParametersToRoutingValues(array $parameters)
	{
		if(count($parameters)) {
			// make sure everything in $parameters is a routing value
			foreach($parameters as &$param) {
				if(!$param instanceof AgaviIRoutingValue) {
					if($param !== null) {
						$param = $this->createValue($param);
					}
				} else {
					// make sure the routing value the user passed to gen() is not modified
					$param = clone $param;
				}
			}
			return $parameters;
		} else {
			return array();
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
		$params = $this->convertParametersToRoutingValues($params);
		// we need to store the original params since we will be trying to fill the
		// parameters up to the first user supplied parameter
		$originalParams = $params;
		
		$routes = $route;
		if(is_string($route) || $route === null) {
			$routes = $this->getAffectedRoutes($routes);
		}
		
		$assembledInformation = $this->assembleRoutes($options, $routes, $params);
		
		$params = $assembledInformation['user_parameters'];
		
		$params = $this->refillAllMatchedParameters($options, $params, $assembledInformation['matched_parameters']);
		$finalParams = $this->refillMatchedAndDefaultParameters($options, $originalParams, $params, $assembledInformation['available_parameters'], $assembledInformation['matched_parameters'], $assembledInformation['optional_parameters'], $assembledInformation['default_parameters']);
		$finalParams = $this->fillUserParameters($options, $params, $finalParams, $assembledInformation['available_parameters'], $assembledInformation['optional_parameters'], $assembledInformation['default_parameters']);
		$finalParams = $this->removeMatchingDefaults($options, $finalParams, $assembledInformation['available_parameters'], $assembledInformation['optional_parameters'], $assembledInformation['default_parameters']);
		$finalParams = $this->updatePrefixAndPostfix($finalParams, $assembledInformation['default_parameters']);

		// remember the params that are not in any pattern (could be extra query params, for example, set by a callback)
		$extras = array_diff_key($originalParams, $finalParams);
		// but since the values are expected as plain values and not routing values, convert the routing values back to 
		// 'plain' values
		foreach($extras as &$extra) {
			$extra = $extra->getValue();
		}

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
					if(count($opts['callbacks']) > 0 && !isset($route['callback_instances'])) {
						foreach($opts['callbacks'] as $key => $callback) {
							$instance = new $callback['class']();
							$instance->initialize($this->context, $route);
							$instance->setParameters($callback['parameters']);
							$route['callback_instances'][$key] = $instance;
						}
					}

					$match = array();
					if($this->parseInput($route, $input, $match)) {
						$varsBackup = $vars;
						
						$ign = array();
						if(count($opts['ignores']) > 0) {
							$ign = array_flip($opts['ignores']);
						}

						foreach($opts['defaults'] as $key => $value) {
							if(!isset($ign[$key]) && $value->getValue()) {
								$vars[$key] = $value->getValue();
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

						// /* ! Only use the parameters from this route for expandVariables !
						// matches are arrays with value and offset due to PREG_OFFSET_CAPTURE, and we want index 0, the value, which reset() will give us. Long story short, this removes the offset from the individual match
						$matchvals = array_map('reset', $match);
						// */
						/* ! Use the parameters from ALL routes for expandVariables !
						$matchvals = $vars;
						// ignores need of the current route need to be added
						$foreach($opts['ignores'] as $ignore) {
							if(isset($match[$ignore]) && $match[$ignore][1] != -1) {
								$matchvals[$ignore] = $match[$ignore][0];
							}
						}
						// */

						if($opts['module']) {
							$module = AgaviToolkit::expandVariables($opts['module'], $matchvals);
							$container->setModuleName($module);
							$vars[$ma] = $module;
						}

						if($opts['action']) {
							$action = AgaviToolkit::expandVariables($opts['action'], $matchvals);
							$container->setActionName($action);
							$vars[$aa] = $action;
						}

						if($opts['output_type']) {
							// set the output type if necessary
							// here no explicit check is done, since in 0.11 this is compared against null
							// which can never be the result of expandVariables
							$ot = AgaviToolkit::expandVariables($opts['output_type'], $matchvals);
							$container->setOutputType($this->context->getController()->getOutputType($ot));
						}

						if($opts['locale']) {
							// set the locale if necessary
							if($locale = AgaviToolkit::expandVariables($opts['locale'], $matchvals)) {
								// the if is here for bc reasons, since if $opts['locale'] only contains variable parts
								// expandVariables could possibly return an empty string in which case the pre 1.0 routing
								// didn't set the variable
								$this->context->getTranslationManager()->setLocale($locale);
							}
						}

						if($opts['method']) {
							// set the request method if necessary
							if($method = AgaviToolkit::expandVariables($opts['method'], $matchvals)) {
								// the if is here for bc reasons, since if $opts['method'] only contains variable parts
								// expandVariables could possibly return an empty string in which case the pre 1.0 routing
								// didn't set the variable
								$req->setMethod($method);
								// and on the already created container, too!
								$container->setRequestMethod($method);
							}
						}

						if(count($opts['callbacks']) > 0) {
							$oldContainer = $container;
							$container = clone $container;
							if(count($opts['ignores']) > 0) {
								// add ignored variables to the callback vars
								foreach($vars as $name => &$var) {
									$vars[$name] =& $var;
								}
								foreach($opts['ignores'] as $ignore) {
									if(isset($match[$ignore]) && $match[$ignore][1] != -1) {
										$vars[$ignore] = $match[$ignore][0];
									}
								}
							}
							$callbackSuccess = true;
							foreach($route['callback_instances'] as $callbackInstance) {
								// backup stuff which could be changed in the callback so we are 
								// able to determine which values were changed in the callback
								$oldModule = $container->getModuleName();
								$oldAction = $container->getActionName();
								$oldOutputTypeName = $container->getOutputType() ? $container->getOutputType()->getName() : null;
								$oldLocale = $this->context->getTranslationManager()->getCurrentLocaleIdentifier();
								$oldRequestMethod = $req->getMethod();
								$oldContainerMethod = $container->getRequestMethod();
								
								// call onMatched on all callbacks until one of them returns false, then
								// call onNotMatched for all following callbacks of that route
								if($callbackSuccess) {
									if(!$callbackInstance->onMatched($vars, $container)) {
										$callbackSuccess = false;
									}
								} else {
									$callbackInstance->onNotMatched($container);
								}
								
								// /* ! Only use the parameters from this route for expandVariables !
								$expandVars = $vars;
								$routeParamsAsKey = array_flip($route['par']);
								// only use parameters which are defined in this route or are new
								foreach($expandVars as $name => $value) {
									if(!isset($routeParamsAsKey[$name]) && array_key_exists($name, $varsBackup)) {
										unset($expandVars[$name]);
									}
								} 
								// */
								/* ! Use the parameters from ALL routes for expandVariables !
								$expandVars = $vars;
								// */
								
								
								// if the callback didn't change the value execute expandVariables again since 
								// the validator could have changed one of the values which expandVariables uses
								if($opts['module'] && $oldModule == $container->getModuleName()) {
									$module = AgaviToolkit::expandVariables($opts['module'], $expandVars);
									$container->setModuleName($module);
								}
								if($opts['action'] && $oldAction == $container->getActionName()) {
									$action = AgaviToolkit::expandVariables($opts['action'], $expandVars);
									$container->setActionName($action);
								}
								if($opts['output_type'] && $oldOutputTypeName == ($container->getOutputType() ? $container->getOutputType()->getName() : null)) {
									$ot = AgaviToolkit::expandVariables($opts['output_type'], $expandVars);
									$container->setOutputType($this->context->getController()->getOutputType($ot));
								}
								if($opts['locale'] && $oldLocale == $this->context->getTranslationManager()->getCurrentLocaleIdentifier()) {
									if($locale = AgaviToolkit::expandVariables($opts['locale'], $expandVars)) {
										// see above for the reason of the if
										$this->context->getTranslationManager()->setLocale($locale);
									}
								}
								if($opts['method']) {
									if($oldRequestMethod == $req->getMethod() && $oldContainerMethod == $container->getRequestMethod()) {
										if($method = AgaviToolkit::expandVariables($opts['method'], $expandVars)) {
											// see above for the reason of the if
											$req->setMethod($method);
											$container->setRequestMethod($method);
										}
									} elseif($oldContainerMethod != $container->getRequestMethod()) {
										// copy the request method to the request (a method set on the container 
										// in a callback always has precedence over request methods set on the request)
										$request->setMethod($container->getRequestMethod());
									} elseif($oldRequestMethod != $req->getMethod()) {
										// copy the request method to the container
										$container->setRequestMethod($req->getMethod());
									}
								}
							}
							if(!$callbackSuccess) {
								// reset the matches array. it must be populated by the time onMatched() is called so matches can be modified in a callback
								$route['matches'] = array();
								// restore the variables from the variables which were set before this route matched
								$vars = $varsBackup;
								// reset all relevant container data we already set in the container for this (now non matching) route
								$container = $oldContainer;
								continue;
							} else {
								// We added the ignores to the route variables so the callback receives them, so restore them from vars backup.
								// Restoring them from the backup is necessary since otherwise a value which has been set before this route
								// and which was ignored in this route would take the ignored value instead of keeping the old one.
								// And variables which have not been set in an earlier routes need to be removed again
								foreach($opts['ignores'] as $ignore) {
									if(array_key_exists($ignore, $varsBackup)) {
										$vars[$ignore] = $varsBackup[$ignore];
									} else {
										unset($vars[$ignore]);
									}
								}
							}
						}

						$matchedRoutes[] = $opts['name'];

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
						if(count($opts['callbacks']) > 0) {
							foreach($route['callback_instances'] as $callbackInstance) {
								$callbackInstance->onNotMatched($container);
							}
						}
					}
				}
			}
		} while(count($routeStack) > 0);

		// put the vars into the request
		$reqData->setParameters($vars);

		if(!isset($vars[$ma]) || !isset($vars[$aa])) {
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
	
	/**
	 * Creates and initializes a new AgaviIRoutingValue.
	 * 
	 * @param      mixed The value of the returned routing value.
	 * @param      bool  Whether the $value needs to be encoded.
	 * 
	 * @return     AgaviIRoutingValue
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      1.0.0
	 */
	public function createValue($value, $valueNeedsEncoding = true)
	{
		$value = new AgaviRoutingValue($value, $valueNeedsEncoding);
		$value->initialize($this->context);
		return $value;
	}
	
}

?>
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
 * AgaviRouting allows you to centralize your entry point urls in your web
 * application.
 *
 * @package    agavi
 * @subpackage routing
 *
 * @author     Dominik del Bondio <ddb@bitxtender.com>
 * @author     David Zuelke <dz@bitxtender.com>
 * @copyright  (c) Authors
 * @since      0.11.0
 *
 * @version    $Id$
 */
class AgaviRouting
{
	const ANCHOR_NONE = 0;
	const ANCHOR_START = 1;
	const ANCHOR_END = 2;
	protected $routes = array();
	private $context = null;
	
	public function initialize(AgaviContext $context)
	{
		$this->context = $context;
	}
	
	public final function getContext()
	{
		return $this->context;
	}

	public function addRoute($route, $options = array(), $parent = null)
	{
		$defaultOpts = array('name' => uniqid (rand()), 'stopping' => true, 'output_type' => null, 'module' => null, 'action' => null, 'parameters' => array(), 'ignores' => array(), 'defaults' => array(), 'childs' => array(), 'callback' => null, 'imply' => false, 'cut' => false, 'parent' => $parent, 'reverseStr' => '', 'nostops' => array(), 'anchor' => self::ANCHOR_NONE);

		// set the default options + user opts
		$options = array_merge($defaultOpts, $options);
		list($regexp, $options['reverseStr'], $params, $options['anchor']) = $this->parseRouteString($route);

		// remove all ignore from the parameters in the route
		foreach($options['ignores'] as $ignore) {
			if(($key = array_search($ignore, $params)) !== false) {
				unset($params[$key]);
			}
		}

		$routeName = $options['name'];
		
		// if we are a child route, we need add this route as a child to the parent
		if($parent !== null) {
			foreach($this->routes[$parent]['opt']['childs'] as $name) {
				$route = $this->routes[$name];
				if(!$route['opt']['stopping']) {
					$options['nostops'][] = $name;
				}
			}
			$this->routes[$parent]['opt']['childs'][] = $routeName;
		} else {
			foreach($this->routes as $name => $route) {
				if(!$route['opt']['stopping'] && !$route['opt']['parent']) {
					$options['nostops'][] = $name;
				}
			}
		}


		$route = array('rxp' => $regexp, 'par' => $params, 'opt' => $options);
		$this->routes[$routeName] = $route;

		return $routeName;
	}


	public function exportRoutes()
	{
		return $this->routes;
	}

	public function importRoutes($routes)
	{
		$this->routes = $routes;
	}

	public function genUrl($route, $params = array())
	{
		$routes = explode('+', $route);
		$route = $routes[0];
		unset($routes[0]);
		$myRoutes = array();
		foreach($routes as $r) {
			$myRoutes[$r] = true;
		}

		$url = '';
		$defaults = array();
		if(isset($this->routes[$route])) {
			$parent = $route;
			do {
				$r = $this->routes[$parent];
				$defaults = array_merge($defaults, $r['opt']['defaults']);

				if($r['opt']['anchor'] & self::ANCHOR_START || $r['opt']['anchor'] == self::ANCHOR_NONE) {
					$url = $r['opt']['reverseStr'] . $url;
				} else {
					$url = $url . $r['opt']['reverseStr'];
				}

				foreach(array_reverse($r['opt']['nostops']) as $noStop) {
					$myR = $this->routes[$noStop];
					if(isset($myRoutes[$noStop])) {
						unset($myRoutes[$noStop]);
					} elseif(!$myR['opt']['imply']) {
						continue;
					}

					$defaults = array_merge($defaults, $myR['opt']['defaults']);
					if($myR['opt']['anchor'] & self::ANCHOR_START || $myR['opt']['anchor'] == self::ANCHOR_NONE) {
						$url = $myR['opt']['reverseStr'] . $url;
					} else {
						$url = $url . $myR['opt']['reverseStr'];
					}
				}

				$parent = $r['opt']['parent'];

				//if($r['opt]['stopping']
			} while($parent);

		} else {
			// TODO: error handling - route with the given name does not exist
		}

		if(count($myRoutes)) {
			// TODO: error handling - we couldn't find some of the nonstopping rules
		}

		$params = array_merge($defaults, $params);

		$from = array();
		$to = array();
		foreach($params as $n => $p) {
			$from[] = '(:' . $n . ':)';
			$to[] = $p;
		}

		$url = str_replace($from, $to, $url);
		return $url;
	}

	public function matchRoute($input)
	{
		$vars = array();
		$ot = null;
		$matchedRoutes = array();
		
		$ma = $this->context->getRequest()->getModuleAccessor();
		$aa = $this->context->getRequest()->getActionAccessor();

//		$routes = array_keys($this->routes);

		// get all top level routes
		foreach($this->routes as $name => $route) {
			if(!$route['opt']['parent']) {
				$routes[] = $name;
			}
		}

		// prepare the working stack with the root routes
		$routeStack = array($routes);

		do
		{
			$routes = array_pop($routeStack);
			foreach($routes as $key) {
				$route = $this->routes[$key];
				$opts = $route['opt'];
				if(preg_match($route['rxp'], $input, $match, PREG_OFFSET_CAPTURE)) {
					$matchedRoutes[] = $route['opt']['name'];

					foreach($match as $name => $m) {
						if(is_string($name) && !isset($opts['defaults'][$name])) {
							$this->routes[$key]['opt']['defaults'][$name] = $m[0];
						}
					}

					foreach($route['par'] as $param) {
						$vars[$param] = $match[$param][0];
					}
					
					if($opts['module']) {
						$vars[$ma] = $opts['module'];
					}
					
					if($opts['action']) {
						$vars[$aa] = $opts['action'];
					}
					
					foreach($opts['parameters'] as $key => $value) {
						$vars[$key] = $value;
					}

					if($opts['output_type']) {
						$ot = $opts['output_type'];
					}

					if($opts['cut']) {
						$ni = '';
						// if the route didn't match from the start of the input preserve the 'prefix'
						if($match[0][1] > 0) {
							$ni = substr($input, 0, $match[0][1]);
						}
						$ni .= substr($input, $match[0][1] + strlen($match[0][0]));
						$input = $ni;
					}

					if(count($opts['childs'])) {
						// our childs need to be processed next and stop processing 'afterwards'
						$routeStack[] = $opts['childs'];
						break;
					}

					if($opts['stopping']) {
						break;
					}

				}
			}
		} while(count($routeStack) > 0);
		
		// set the output type if necessary
		if($ot !== null) {
			$this->getContext()->getController()->setOutputType($ot);
		}
		
		// put the vars into the request
		$this->getContext()->getRequest()->setParameters($vars);
		
		// return a list of matched route names
		return $matchedRoutes;
	}


	protected function parseRouteString($str)
	{
		$vars = array();
		$rxStr = '';
		$reverseStr = '';

		$anchor = 0;
		$anchor |= (substr($str, 0, 1) == '^') ? self::ANCHOR_START : 0;
		$anchor |= (substr($str, -1) == '$') ? self::ANCHOR_END : 0;

		$str = substr($str, (int)$anchor & self::ANCHOR_START, $anchor & self::ANCHOR_END ? -1 : strlen($str));

		if(preg_match_all('#\\(([^:]*):([^)]+)\\)#', $str, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE)) {
			$lastOffset = 0;
			$ret = '';
			foreach($matches as $matchSet) {
				$matchLen = strlen($matchSet[0][0]);
				$matchOffset = $matchSet[0][1];
				$prefixStr = substr($str, $lastOffset, $matchOffset - $lastOffset);
				$lastOffset = $matchOffset + $matchLen;

				$paramName = $matchSet[1][0];
				if(strlen($paramName) > 0) {
					$rxStr .= preg_quote($prefixStr) . sprintf('(?P<%s>%s)', $paramName, $matchSet[2][0]);
					$reverseStr .= $prefixStr . sprintf('(:%s:)', $paramName);

					if(!in_array($paramName, $vars))
						$vars[] = $paramName;
				} else {
					$rxStr .= preg_quote($prefixStr) . sprintf('%s', $matchSet[2][0]);
					$reverseStr .= $prefixStr;
				}
			}

			if($lastOffset < strlen($str)) {
				$rxStr .= preg_quote(substr($str, $lastOffset));
				$reverseStr .= substr($str, $lastOffset);
			}
		} else {
			$rxStr = preg_quote($str);
			$reverseStr = $str;
		}

		$rxStr = sprintf('!%s%s%s!', $anchor & self::ANCHOR_START ? '^' : '', $rxStr, $anchor & self::ANCHOR_END ? '$' : '');
		return array($rxStr, $reverseStr, $vars, $anchor);
	}

}

?>
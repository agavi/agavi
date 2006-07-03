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
abstract class AgaviRouting
{
	const ANCHOR_NONE = 0;
	const ANCHOR_START = 1;
	const ANCHOR_END = 2;
	protected $routes = array(),
						$context = null,
						$input = null,
						$prefix = '';
	
	public function initialize(AgaviContext $context, $parameters = array())
	{
		$this->context = $context;
		$cfg = AgaviConfig::get("core.config_dir") . "/routing.xml";
		// allow missing routing.xml when routing is not enabled
		if(AgaviConfig::get("core.use_routing", false) || is_readable($cfg)) {
			include(AgaviConfigCache::checkConfig($cfg, $context->getName()));
		}
	}
	
	public final function getContext()
	{
		return $this->context;
	}

	public final function getInput()
	{
		return $this->input;
	}

	public final function getPrefix()
	{
		return $this->prefix;
	}

	public function addRoute($route, $options = array(), $parent = null)
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
			$defaultOpts = array('name' => uniqid (rand()), 'stopping' => true, 'output_type' => null, 'module' => null, 'action' => null, 'parameters' => array(), 'ignores' => array(), 'defaults' => array(), 'childs' => array(), 'callback' => null, 'imply' => false, 'cut' => false, 'parent' => $parent, 'reverseStr' => '', 'nostops' => array(), 'anchor' => self::ANCHOR_NONE);
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
					'pos' => $post,
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

			if(!isset($options['defaults'][$name]) && ($param['pre'] || $param['val'] || $param['pos'])) {
				$options['defaults'][$name] = $param;
			}
		}

		// remove all ignore from the parameters in the route
		foreach($options['ignores'] as $ignore) {
			if(($key = array_search($ignore, $params)) !== false) {
				unset($params[$key]);
			}
		}

		$routeName = $options['name'];



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

	public function gen($route, $params = array())
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
		$availableParams = array();
		if(isset($this->routes[$route])) {
			$parent = $route;
			do {
				$r =& $this->routes[$parent];
				$myDefaults = $r['opt']['defaults'];
				$availableParams += $r['par'] + $r['opt']['ignores'];

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

					$myDefaults = array_merge($myDefaults, $myR['opt']['defaults']);
					$availableParams += $myR['par'] + $myR['opt']['ignores'];
					if($myR['opt']['anchor'] & self::ANCHOR_START || $myR['opt']['anchor'] == self::ANCHOR_NONE) {
						$url = $myR['opt']['reverseStr'] . $url;
					} else {
						$url = $url . $myR['opt']['reverseStr'];
					}
				}

				if(isset($r['opt']['callback'])) {
					if(!isset($r['cb'])) {
						$cb = $r['opt']['callback'];
						$r['cb'] = new $cb();
						$r['cb']->initialize($this->getContext(), $r);
					}
					$myDefaults = $r['cb']->onGenerate($myDefaults);
				}

				$defaults = array_merge($defaults, $myDefaults);

				$parent = $r['opt']['parent'];

			} while($parent);

		} else {
			// TODO: error handling - route with the given name does not exist
		}

		if(count($myRoutes)) {
			// TODO: error handling - we couldn't find some of the nonstopping rules
		}

		$np = array();

		foreach($defaults as $name => $val) {
			if(isset($params[$name])) {
				$np[$name] = $val['pre'] . $params[$name] . $val['pos'];
			} elseif($val['val']) {
				// more then just pre or postfix
				$np[$name] = $val['pre'] . $val['val'] . $val['pos'];
			}
		}
		// get the remaining params too
		$params = array_merge($params, $np);

		// $params = array_merge($defaults, $params);

		$from = array();
		$to = array();

		// remove not specified available parameters
		foreach(array_unique($availableParams) as $name) {
			if(!isset($params[$name])) {
				$from[] = '(:' . $name . ':)';
				$to[] = '';
			}
		}

		foreach($params as $n => $p) {
			$from[] = '(:' . $n . ':)';
			$to[] = $p;
		}

		$url = str_replace($from, $to, $url);
		return $this->prefix . $url;
	}

	public function execute()
	{
		$req = $this->context->getRequest();
		
		$input = $this->input;

		$vars = array();
		$ot = null;
		$matchedRoutes = array();
		
		if(!AgaviConfig::get('core.use_routing', false)) {
			// routing disabled, bail out
			return $matchedRoutes;
		}
		
		$ma = $req->getModuleAccessor();
		$aa = $req->getActionAccessor();

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
				$opts =& $route['opt'];
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

					if($opts['callback']) {
						if(!$route['cb']->onMatched($vars)) {
							continue;
						}
					}

					$matchedRoutes[] = $opts['name'];

					foreach($match as $name => $m) {
						if(is_string($name) && !isset($opts['defaults'][$name])) {
							$route['opt']['defaults'][$name]['val'] = $m[0];
						}
					}

					if($opts['module']) {
						$vars[$ma] = $opts['module'];
					}
					
					if($opts['action']) {
						$vars[$aa] = $opts['action'];
					}


					if($opts['output_type']) {
						$ot = $opts['output_type'];
					}

					if($opts['cut'] || count($opts['childs'])) {
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

				} else {
					if($opts['callback']) {
						$route['cb']->onNotMatched();
					}
				}
			}
		} while(count($routeStack) > 0);
		
		// set the output type if necessary
		if($ot !== null) {
			$this->context->getController()->setOutputType($ot);
		}

		// put the vars into the request
		$req->setParameters($vars);
		
		if(count($matchedRoutes) == 0) {
			// no routes matched, use 404 action
			$req->setParameters(array(
				$ma => AgaviConfig::get('actions.error_404_module'),
				$aa => AgaviConfig::get('actions.error_404_action')
			));
		}
		// return a list of matched route names
		return $matchedRoutes;
	}


	protected function parseInput($route, $input, &$matches)
	{
		return preg_match($route['rxp'], $input, $matches, PREG_OFFSET_CAPTURE);
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

		$rxChars = implode('', array('.', '\\', '+', '*', '?', '[', '^', ']', '$', '(', ')', '{', '}', '=', '!', '<', '>', '|', ':'));

		$len = strlen($str);
		$state = 'start';
		$tmpStr = '';

		$rxName = '';
		$rxInner = '';
		$rxPrefix = '';
		$rxPostfix = '';
		$parenthesisCount = 0;
		$bracketCount = 0;
		$hasBrackets = false;
		// whether the regular expression is clean of any regular expression (\o/)
		$cleanRx = true;

		for($i = 0; $i < $len; ++$i) {
			$atEnd = $i + 1 == $len;
			$c = $str[$i];

			if($state == 'start') {
				// start of regular expression block
				if($c == '(') {
					$rxStr .= preg_quote($tmpStr);
					$reverseStr .= $tmpStr;

					$tmpStr = '';
					$state = 'rxStart';
					$rxName = $rxInner = $rxPrefix = $rxPostFix = '';
					$parenthesisCount = 1;
					$bracketCount = 0;
					$hasBrackets = false;
				} else {
					$tmpStr .= $c;
				}

				if($atEnd) {
					$rxStr .= preg_quote($tmpStr);
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
							$rxStr .= $rxPrefix . $rxInner . $rxPostfix;
						} else {
							$rxStr .= sprintf('(%s(?P<%s>%s)%s)', $rxPrefix, $rxName, $rxInner, $rxPostfix);
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

								$vars[$rxName] = array('pre' => $rxPrefix, 'val' => $rxInner, 'pos' => $rxPostfix);
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

	protected function parseParameterDefinition($def)
	{
		$name = '';
		$rx = '';

		preg_match('#([a-z0-9_-]+:)?(.*)#i', $def, $match);
		return array(substr($match[1], 0, -1), $match[2]);
	}
}

?>
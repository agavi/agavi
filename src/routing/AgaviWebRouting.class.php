<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2003-2007 the Agavi Project.                                |
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
 * AgaviWebRouting sets the prefix and input with some magic from the request
 * uri and path_info
 *
 * @package    agavi
 * @subpackage routing
 *
 * @author     Dominik del Bondio <ddb@bitxtender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.11.0
 *
 * @version    $Id$
 */
class AgaviWebRouting extends AgaviRouting
{
	/**
	 * @var        string The path to the application's root with trailing slash.
	 */
	protected $basePath = '';

	/**
	 * @var        string The URL to the application's root with trailing slash.
	 */
	protected $baseHref = '';

	/**
	 * @var        array The GET parameters that were passed in the URL.
	 */
	protected $inputParameters = array();
	
	/**
	 * Constructor.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function __construct()
	{
		parent::__construct();
		
		$this->defaultGenOptions = array_merge($this->defaultGenOptions, array(
			// separator, typically &amp; for HTML, & otherwise
			'separator' => '&amp;',
			// whether or not to append the SID if necessary
			'use_trans_sid' => false,
			// scheme, or true to include, or false to block
			'scheme' => null,
			// authority, or true to include, or false to block
			'authority' => null,
			// host, or true to include, or false to block
			'host' => null,
			// port, or true to include, or false to block
			'port' => null,
			// fragment identifier (#foo)
			'fragment' => null,
		));
	}

	/**
	 * Initialize the routing instance.
	 *
	 * @param      AgaviContext The Context.
	 * @param      array        An array of initialization parameters.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @author     Veikko Mäkinen <veikko@veikkomakinen.com>
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function initialize(AgaviContext $context, array $parameters = array())
	{
		parent::initialize($context, $parameters);
		
		$rq = $this->context->getRequest();
		
		$rd = $rq->getRequestData();
		
		$ru = parse_url($rq->getRequestUri());
		if(!isset($ru['path'])) {
			$ru['path'] = '';
		}
		if(!isset($ru['query'])) {
			$ru['query'] = '';
		} else {
			$ru['query'] = preg_replace('/&$/', '', $ru['query']);
		}
		
		$qs = (isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '');
		
		$rewritten = ($qs !== $ru['query']);
		
		if(AgaviConfig::get("core.use_routing", false) && $rewritten) {
			$this->input = preg_replace('/' . preg_quote('&' . $ru['query'], '/') . '$/', '', $qs);
			
			if(!isset($_SERVER['SERVER_SOFTWARE']) || strpos($_SERVER['SERVER_SOFTWARE'], 'Apache') === false) {
				// don't do that for Apache, it's already rawurldecode()d there
				$this->input = rawurldecode($this->input);
			}
			
			$this->basePath = $this->prefix = preg_replace('/' . preg_quote($this->input, '/') . '$/', '', rawurldecode($ru['path']));
			
			// that was easy. now clean up $_GET and the Request
			$parsedRuQuery = $parsedInput = '';
			parse_str($ru['query'], $parsedRuQuery);
			parse_str($this->input, $parsedInput);
			foreach(array_diff(array_keys($parsedInput), array_keys($parsedRuQuery)) as $unset) {
				unset($_GET[$unset]);
				if(!isset($_POST[$unset])) {
					$rd->removeParameter($unset);
				}
			}
		} else {
			$sn = $_SERVER['SCRIPT_NAME'];
			$path = rawurldecode($ru['path']);
			
			$appendFrom = 0;
			$this->prefix = AgaviToolkit::stringBase($sn, $path, $appendFrom);
			$this->prefix .= substr($sn, $appendFrom + 1);
			
			$this->input = substr($path, $appendFrom + 1);
			if(!isset($_SERVER['SERVER_SOFTWARE']) || strpos($_SERVER['SERVER_SOFTWARE'], 'Microsoft-IIS') === false || isset($_SERVER['HTTP_X_REWRITE_URL']) || !isset($_SERVER['GATEWAY_INTERFACE']) || strpos($_SERVER['GATEWAY_INTERFACE'], 'CGI') === false) {
				// don't do that for IIS-CGI, it's already rawurldecode()d there
				$this->input = rawurldecode($this->input);
			}
			
			$this->basePath = str_replace('\\', '/', dirname($this->prefix));
		}
		
		$this->inputParameters = $_GET;
		
		if(!$this->input) {
			$this->input = "/";
		}
		
		if(substr($this->basePath, -1, 1) != '/') {
			$this->basePath .= '/';
		}
		
		$this->baseHref = $rq->getUrlScheme() . '://' . $rq->getUrlAuthority() . $this->basePath;
	}

	/**
	 * Retrieve the base path where the application's root sits
	 *
	 * @return     string A path string, including a trailing slash.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getBasePath()
	{
		return $this->basePath;
	}

	/**
	 * Retrieve the full URL to the application's root.
	 *
	 * @return     string A URL string, including the protocol, the server port
	  *                   (if necessary) and the path including a trailing slash.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getBaseHref()
	{
		return $this->baseHref;
	}

	/**
	 * Generate a formatted Agavi URL.
	 *
	 * @param      string A route name.
	 * @param      array  An associative array of parameters.
	 * @param      mixed  An array of options, or the name of an options preset.
	 *
	 * @return     string The generated URL.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function gen($route, array $params = array(), $options = array())
	{
		if(substr($route, -1) == '*') {
			$options['refill_all_parameters'] = true;
			$route = substr($route, 0, -1);
		}

		$req = $this->context->getRequest();
		
		$options = $this->resolveGenOptions($options);

		if(defined('SID') && SID !== '' && $options['use_trans_sid'] === true) {
			$params = array_merge($params, array(session_name() => session_id()));
		}
		
		if($route === null) {
			if(AgaviConfig::get('core.use_routing')) {
				$routes = array_reverse($req->getAttribute('matchedRoutes', 'org.agavi.routing'));
				$route = join('+', $routes);
				$routeMatches = array();
				foreach($routes as $myRoute) {
					$r = $this->routes[$myRoute];
					$routeMatches = array_merge($routeMatches, $r['matches']);
				}
				$params = array_merge($routeMatches, $params);
			}
			$params = array_merge($this->inputParameters, $params);
		}

		$routes = $this->getAffectedRoutes($route);

		if(count($routes)) {
			if(AgaviConfig::get('core.use_routing')) {
				// the route exists and routing is enabled, the parent method handles it

				$append = '';

				// get the parameters which are not defined in this route an append them as query string
				$p = $params;
				foreach($routes as $myRoute) {
					foreach($this->routes[$myRoute]['opt']['pattern_parameters'] as $param) {
						if(array_key_exists($param, $p)) {
							unset($p[$param]);
						}
					}
				}
				
				$genParams = array_diff_key($params, $p);

				if(count($p) > 0) {
					$append = '?' . http_build_query($p);
				}

				list($path, $changedParams, $options) = parent::gen($routes, array_merge(array_map('rawurlencode', $genParams), array_filter($params, 'is_null')), $options);
			} else {
				// the route exists, but we must create a normal index.php?foo=bar URL.

				// we collect the default parameters from the route and make sure
				// new parameters don't overwrite already defined parameters
				$defaults = array();
				foreach($routes as $route) {
					if(isset($this->routes[$route])) {
						$r = $this->routes[$route];
						$myDefaults = array();

						foreach($r['opt']['defaults'] as $key => $default) {
							$myDefaults[$key] = $default['val'];
						}
						if($r['opt']['module']) {
							$myDefaults[$req->getModuleAccessor()] = $r['opt']['module'];
						}
						if($r['opt']['action']) {
							$myDefaults[$req->getActionAccessor()] = $r['opt']['action'];
						}

						$defaults = array_merge($myDefaults, $defaults);
					}
				}

				$params = array_merge($defaults, $params);
				$route = null;
			}
		}
		// the route does not exist. we generate a normal index.php?foo=bar URL.

		if($route === null) {
			$path = $_SERVER['SCRIPT_NAME'];
			$append = '?' . http_build_query($params);
		} else {
			if(!isset($path)) {
				$path = $route;
			}
			if(!isset($append)) {
				$append = '?' . http_build_query($params);
			}
		}

		$aso = ini_get('arg_separator.output');
		if($options['separator'] != $aso) {
			// replace arg_separator.output's with given separator
			$append = str_replace($aso, $options['separator'], $append);
		}

		$retval = $path . $append;

		if(
			!$options['relative'] || 
			($options['relative'] && (
				$options['scheme'] !== null || 
				$options['authority'] !== null || 
				$options['host'] !== null || 
				$options['port'] !== null
			))
		) {
			$req = $this->context->getRequest();
			
			$scheme = null;
			if($options['scheme'] !== false) {
				$scheme = ($options['scheme'] === null ? $req->getUrlScheme() : $options['scheme']);
			}
			
			$authority = '';
			
			if($options['authority'] === null) {
				if(
					($options['host'] !== null && $options['host'] !== false) && 
					($options['port'] !== null && $options['port'] !== false)
				) {
					$authority = $req->getUrlAuthority();
				} else {
					if($options['host'] !== null && $options['host'] !== false) {
						$authority = $options['host'];
					} elseif($options['host'] === false) {
						$authority = '';
					} else {
						$authority = $req->getUrlHost();
					}
					$port = null;
					if($options['port'] !== null && $options['port'] !== false) {
						if(AgaviToolkit::isPortNecessary($options['scheme'] !== null && $options['scheme'] !== false ? $options['scheme'] : $req->getUrlScheme(), $options['port'])) {
							$port = $options['port'];
						} else {
							$port = null;
						}
					} elseif($options['port'] === false) {
						$port = null;
					} elseif($options['scheme'] === null) {
						if(!AgaviToolkit::isPortNecessary($req->getUrlScheme(), $port = $req->getUrlPort())) {
							$port = null;
						}
					}
					if($port !== null) {
						$authority .= ':' . $port;
					}
				}
			} elseif($options['authority'] !== false) {
				$authority = $options['authority'];
			}
			
			$retval = ($scheme === null ? '' : $scheme . '://') . $authority . $retval;
		}

		if($options['fragment'] !== null) {
			$retval .= '#' . $options['fragment'];
		}
		
		return $retval;
	}
}

?>
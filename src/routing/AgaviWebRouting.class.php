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
 * AgaviWebRouting sets the prefix and input with some magic from the request
 * uri and path_info
 *
 * @package    agavi
 * @subpackage routing
 *
 * @author     Dominik del Bondio <ddb@bitxtender.com>
 * @copyright  (c) Authors
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
	 * @var        array An array of default options for gen()
	 */
	protected $defaultGenOptions = array(
		'relative' => true,
		'separator' => '&amp;',
		'use_trans_sid' => false
	);

	/**
	 * Initialize the routing instance.
	 *
	 * @param      AgaviContext A Context instance.
	 * @param      array        An array of initialization parameters.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function initialize(AgaviContext $context, array $parameters = array())
	{
		parent::initialize($context, $parameters);
		
		if(!AgaviConfig::get("core.use_routing", false)) {
			return;
		}
		
		$rq = $this->context->getRequest();
		
		$ru = parse_url($rq->getRequestUri());
		if(!isset($ru['path'])) {
			$ru['path'] = '';
		}
		if(!isset($ru['query'])) {
			$ru['query'] = '';
		}
		
		$qs = (isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '');
		
		$rewritten = ($qs !== $ru['query']);
		
		if($rewritten) {
			$this->input = preg_replace('/' . preg_quote('&' . $ru['query'], '/') . '$/', '', $qs);
			
			if(!isset($_SERVER['SERVER_SOFTWARE']) || strpos($_SERVER['SERVER_SOFTWARE'], 'Apache') === false) {
				// don't do that for Apache, it's already rawurldecode()d there
				$this->input = rawurldecode($this->input);
			}
			
			$this->basePath = $this->prefix = preg_replace('/' . preg_quote($this->input, '/') . '$/', '', rawurldecode($ru['path']));
			
			// that was easy. now clean up $_GET and the Request
			parse_str($ru['query'], $parsedRuQuery);
			parse_str($this->input, $parsedInput);
			foreach(array_diff(array_keys($parsedInput), array_keys($parsedRuQuery)) as $unset) {
				unset($_GET[$unset]);
				if(!isset($_POST[$unset])) {
					$rq->removeParameter($unset);
				}
			}
		} else {
			$sn = $_SERVER['SCRIPT_NAME'];
			
			$this->prefix = AgaviToolkit::stringBase($sn, $ru['path'], $appendFrom);
			$this->prefix .= substr($sn, $appendFrom + 1);
			
			$this->input = substr($ru['path'], $appendFrom + 1);
			if(!isset($_SERVER['SERVER_SOFTWARE']) || strpos($_SERVER['SERVER_SOFTWARE'], 'Microsoft-IIS') === false || isset($_SERVER['HTTP_X_REWRITE_URL']) || !isset($_SERVER['GATEWAY_INTERFACE']) || strpos($_SERVER['GATEWAY_INTERFACE'], 'CGI') === false) {
				// don't do that for IIS-CGI, it's already rawurldecode()d there
				$this->input = rawurldecode($this->input);
			}
			
			$this->basePath = str_replace('\\', '/', dirname($this->prefix));
		}
		
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
	 * @author     David Zuelke <dz@bitxtender.com>
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
	 * @author     David Zuelke <dz@bitxtender.com>
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
	 * @param      array  An array of options.
	 *
	 * @return     string
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function gen($route, array $params = array(), array $options = array())
	{
		$options = array_merge($this->defaultGenOptions, $options);

		if(defined('SID') && SID !== '' && $options['use_trans_sid'] === true) {
			$params = array_merge($params, array(session_name() => session_id()));
		}

		$routes = $this->getAffectedRoutes($route);

		if(count($routes)) {
			if(AgaviConfig::get('core.use_routing')) {
				// the route exists and routing is enabled, the parent method handles it

				$append = '';

				// get the parameters which are not defined in this route an append them as query string
				$p = $params;
				foreach($routes as $myRoute) {
					foreach($this->routes[$myRoute]['par'] as $param) {
						if(isset($p[$param])) {
							unset($p[$param]);
						}
					}
				}

				if(count($p) > 0) {
					$append = '?' . http_build_query($p);
				}

				$path = parent::gen($routes, array_merge(array_map('rawurlencode', $params), array_filter($params, 'is_null')));
			} else {
				// the route exists, but we must create a normal index.php?foo=bar URL.

				$req = $this->context->getRequest();

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

		if($options['relative']) {
			return $path . $append;
		} else {
			$req = $this->context->getRequest();
			return $req->getUrlScheme() . '://'. $req->getUrlAuthority() . $path . $append;
		}
	}

	public function execute()
	{
		$req = $this->getContext()->getRequest();

		// merge GET parameters
		$req->setParameters($_GET);
		// merge POST parameters
		$req->setParameters($_POST);

		// the real deal
		return parent::execute();
	}
}

?>

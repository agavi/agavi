<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2005-2011 the Agavi Project.                                |
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
	 * @var        array arg_separator.input as defined in php.ini, exploded
	 */
	protected $argSeparatorInput = array('&');

	/**
	 * @var        string arg_separator.output as defined in php.ini
	 */
	protected $argSeparatorOutput = '&amp;';

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
		
		$this->argSeparatorInput = str_split(ini_get('arg_separator.input'));
		$this->argSeparatorOutput = ini_get('arg_separator.output');
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

		// 'scheme://authority' is necessary so parse_url doesn't stumble over '://' in the request URI
		$ru = array_merge(array('path' => '', 'query' => ''), parse_url('scheme://authority' . $rq->getRequestUri()));

		if(isset($_SERVER['QUERY_STRING'])) {
			$qs = $_SERVER['QUERY_STRING'];
		} else {
			$qs = '';
		}

		// when rewriting, apache strips one (not all) trailing ampersand from the end of QUERY_STRING... normalize:
		$rewritten = (preg_replace('/&+$/D', '', $qs) !== preg_replace('/&+$/D', '', $ru['query']));

		if($this->isEnabled() && $rewritten) {
			// strip the one trailing ampersand, see above
			$queryWasEmptied = false;
			if($ru['query'] !== '' && isset($_SERVER['SERVER_SOFTWARE']) && strpos($_SERVER['SERVER_SOFTWARE'], 'Apache') !== false) {
				$ru['query'] = preg_replace('/&$/D', '', $ru['query']);
				if($ru['query'] == '') {
					$queryWasEmptied = true;
				}
			}

			$stripFromQuery = '&' . $ru['query'];
			if($ru['query'] == '' && !$queryWasEmptied && isset($_SERVER['SERVER_SOFTWARE']) && strpos($_SERVER['SERVER_SOFTWARE'], 'Apache') !== false) {
				// if the query is empty, simply give apache2 nothing instead of an "&", since that could kill a real trailing ampersand in the path, as Apache strips those from the query string (which has the rewritten path), but not the request uri
				$stripFromQuery = '';
			}
			$this->input = preg_replace('/' . preg_quote($stripFromQuery, '/') . '$/D', '', $qs);

			if(isset($_SERVER['SERVER_SOFTWARE']) && strpos($_SERVER['SERVER_SOFTWARE'], 'Apache/2') !== false) {
				$sru = $_SERVER['REQUEST_URI'];
				
				if(($fqmp = strpos($sru, '?')) !== false && ($fqmp == strlen($sru)-1)) {
					// strip a trailing question mark, but only if it really is the query string separator (i.e. the only question mark in the URI)
					$sru = substr($sru, 0, -1);
				} elseif($ru['query'] !== '' || $queryWasEmptied) {
					// if there is a trailing ampersand (in query string or path, whatever ends the URL), strip it (but just one)
					$sru = preg_replace('/&$/D', '', $sru);
				}
				
				// multiple consecutive slashes got lost in our input thanks to an apache bug
				// let's fix that
				$cqs = preg_replace('#/{2,}#', '/', rawurldecode($ru['query']));
				$cru = preg_replace('#/{2,}#', '/', rawurldecode($sru));
				$tmp = preg_replace('/' . preg_quote($this->input . (($cqs != '' || $queryWasEmptied) ? '?' . $cqs : ''), '/') . '$/D', '', $cru);
				$input = preg_replace('/^' . preg_quote($tmp, '/') . '/', '', $sru);
				if($ru['query'] !== '' || $queryWasEmptied) {
					$input = preg_replace('/' . preg_quote('?' . $ru['query'], '/') . '$/D', '', $input);
				}
				$this->input = $input;
			}

			if(!(isset($_SERVER['SERVER_SOFTWARE']) && (strpos($_SERVER['SERVER_SOFTWARE'], 'Apache/1') !== false || (strpos($_SERVER['SERVER_SOFTWARE'], 'Microsoft-IIS') !== false && isset($_SERVER['UNENCODED_URL']))))) {
				// don't do that for Apache 1 or IIS 7 with URL Rewrite Module, it's already rawurldecode()d there
				$this->input = rawurldecode($this->input);
			}

			$xrup = rawurldecode($ru['path']);
			$this->basePath = $this->prefix = preg_replace('/' . preg_quote($this->input, '/') . '$/D', '', rawurldecode($ru['path']));

			// that was easy. now clean up $_GET and the Request
			$parsedRuQuery = $parsedInput = '';
			parse_str($ru['query'], $parsedRuQuery);
			parse_str($this->input, $parsedInput);
			if(get_magic_quotes_gpc()) {
				$parsedRuQuery = AgaviWebRequest::clearMagicQuotes($parsedRuQuery);
				$parsedInput = AgaviWebRequest::clearMagicQuotes($parsedInput, false /* start on the first level */);
			}
			foreach(array_diff(array_keys($parsedInput), array_keys($parsedRuQuery)) as $unset) {
				// our element is in $_GET
				unset($_GET[$unset]);
				unset($GLOBALS['HTTP_GET_VARS'][$unset]);
				// if it is not also in $_POST, then we need to remove it from the request params
				if(!isset($_POST[$unset])) {
					$rd->removeParameter($unset);
					// and from $_REQUEST, too!
					unset($_REQUEST[$unset]);
				}
			}
		} else {
			$sn = $_SERVER['SCRIPT_NAME'];
			$path = rawurldecode($ru['path']);

			$appendFrom = 0;
			$this->prefix = AgaviToolkit::stringBase($sn, $path, $appendFrom);
			$this->prefix .= substr($sn, $appendFrom);

			$this->input = substr($path, $appendFrom);
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
	 * Callback for array_walk_recursive.
	 *
	 * @param      mixed The value to decode, passed as a reference.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.1
	 */
	protected function rawurldecodeCallback(&$value)
	{
		$value = rawurldecode($value);
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
		$req = $this->context->getRequest();

		if(substr($route, -1) == '*') {
			$options['refill_all_parameters'] = true;
			$route = substr($route, 0, -1);
		}

		$options = $this->resolveGenOptions($options);

		$aso = $this->argSeparatorOutput;
		if($options['separator'] != $aso) {
			$aso = $options['separator'];
		}

		if($options['use_trans_sid'] === true && defined('SID') && SID !== '') {
			$params = array_merge($params, array(session_name() => session_id()));
		}

		if($route === null && empty($params)) {
			$retval = $req->getRequestUri();
			$retval = str_replace(array('[', ']', '\''), array('%5B', '%5D', '%27'), $retval);
			// much quicker than str_replace($this->argSeparatorInput, array_fill(0, count($this->argSeparatorInput), $aso), $retval)
			foreach($this->argSeparatorInput as $char) {
				$retval = str_replace($char, $aso, $retval);
			}
		} else {
			if($this->isEnabled()) {
				// the route exists and routing is enabled, the parent method handles it

				$append = '';

				list($path, $usedParams, $options, $extraParams, $isNullRoute) = parent::gen($route, $params, $options);
				
				if($isNullRoute) {
					// add the incoming parameters from the request uri for gen(null) and friends
					$extraParams = array_merge($this->inputParameters, $extraParams);
				}
				if(count($extraParams) > 0) {
					$append = http_build_query($extraParams, '', $aso);
					if($append !== '') {
					  $append = '?' . $append;
					}
				}
			} else {
				// the route exists, but we must create a normal index.php?foo=bar URL.

				$isNullRoute = false;
				$routes = $this->getAffectedRoutes($route, $isNullRoute);
				if($isNullRoute) {
					$params = array_merge($this->inputParameters, $params);
				}
				if(count($routes) == 0) {
					$path = $route;
				}

				// we collect the default parameters from the route and make sure
				// new parameters don't overwrite already defined parameters
				$defaults = array();

				$ma = $req->getParameter('module_accessor');
				$aa = $req->getParameter('action_accessor');

				foreach($routes as $route) {
					if(isset($this->routes[$route])) {
						$r = $this->routes[$route];
						$myDefaults = array();

						foreach($r['opt']['defaults'] as $key => $default) {
							$myDefaults[$key] = $default->getValue();
						}
						if($r['opt']['module']) {
							$myDefaults[$ma] = $r['opt']['module'];
						}
						if($r['opt']['action']) {
							$myDefaults[$aa] = $r['opt']['action'];
						}

						$defaults = array_merge($myDefaults, $defaults);
					}
				}

				$params = array_merge($defaults, $params);
			}
			
			if(!isset($path)) {
				// the route does not exist. we generate a normal index.php?foo=bar URL.
				$path = $_SERVER['SCRIPT_NAME'];
			}
			
			if(!isset($path)) {
				// routing was off; the name of the route is the input
			}
			if(!isset($append)) {
				$append = '?' . http_build_query($params, '', $aso);
			}

			$retval = $path . $append;
		}

		if(
			!$options['relative'] ||
			($options['relative'] && (
				$options['scheme'] !== null ||
				$options['authority'] !== null ||
				$options['host'] !== null ||
				$options['port'] !== null
			))
		) {
			$scheme = false;
			if($options['scheme'] !== false) {
				$scheme = ($options['scheme'] === null ? $req->getUrlScheme() : $options['scheme']);
			}

			$authority = '';

			if($options['authority'] === null) {
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
			} elseif($options['authority'] !== false) {
				$authority = $options['authority'];
			}

			if($scheme === false) {
				// nothing at all, e.g. when displaying a URL without the "http://" prefix
				$scheme = '';
			} elseif(trim($scheme) === '') {
				// a protocol-relative URL (see #1224)
				$scheme = '//';
			} else {
				// given scheme plus "://"
				$scheme = $scheme . '://';
			}
			
			$retval = $scheme . $authority . $retval;
		}

		if($options['fragment'] !== null) {
			$retval .= '#' . $options['fragment'];
		}

		return $retval;
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
		return rawurlencode($string);
	}

}

?>
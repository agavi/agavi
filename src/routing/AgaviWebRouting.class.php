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


	protected $parseOptions = array();


	/**
	 * Initialize the routing instance.
	 *
	 * @param      AgaviContext A Context instance.
	 * @param      array        An array of initialization parameters.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function initialize(AgaviContext $context, $parameters = array())
	{
		parent::initialize($context, $parameters);

		if(isset($parameters['path_info_parameter'])) {
			$this->parseOptions['path_info_parameter'] = $parameters['path_info_parameter'];
		}

		parent::initialize($context, $parameters);

		$parsingMethod = $this->determineMethod($parameters);

		if (!method_exists($this, $parsingMethod)) {
			throw new AgaviException('Trying to use non-existent method ('.$parsingMethod.') for routing information parsing.');
		}

		$this->$parsingMethod($parameters);

	}

	/**
	 * Returns the base for two strings (the part at the beginning of both which
	 * is equal)
	 *
	 * @param      string The base string.
	 * @param      string The string which should be compared to the base string.
	 * @param      int    The number of characters which are equal.
	 *
	 * @return     string The equal part at the beginning of both strings.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	protected function getStringBase($baseString, $compString, &$equalAmount = 0)
	{
		$base = '';
		for($i = 0; isset($baseString[$i]) && isset($compString[$i]) && $baseString[$i] == $compString[$i]; ++$i) {
			$base .= $baseString[$i];
			$equalAmount = $i;
		}

		return $base;
	}

	protected function getIsRewritten()
	{
		return (isset($_SERVER['REDIRECT_URL']) && isset($_SERVER['PATH_INFO']))
			|| (isset($_SERVER['HTTP_X_REWRITE_URL']) && ($_SERVER['HTTP_X_REWRITE_URL'] != $_SERVER['ORIG_PATH_INFO']))
			|| isset($this->parseOptions['path_info_parameter']);
	}

	public function determineMethod($parameters)
	{
		$isRewritten = $this->getIsRewritten();
		$serverApi = '';

		//figure out server api
		if(isset($_SERVER['HTTP_X_REWRITE_URL'])) {
			$serverApi = 'MsIis'; // Microsoft IIS with ISAPI_Rewrite
		}
		elseif(empty($_SERVER['PATH_INFO']) && empty($_SERVER['PATH_TRANSLATED'])) {
			$serverApi = 'ApacheCgi'; // Apache with CGI SAPI
			$isRewritten = isset($this->parseOptions['path_info_parameter']) && strpos($_SERVER['argv'][0], $this->parseOptions['path_info_parameter'])==0;
		} elseif(isset($_SERVER['ORIG_PATH_INFO'])) {
			$serverApi = 'MsIis'; // Microsoft IIS
		} else {
			$serverApi = 'ApacheModule'; // Apache
		}

		$parsingMethod = 'parse'.$serverApi . ($isRewritten ? '' : 'No') . 'Rewrite';

		return $parsingMethod;
	}

	protected function serverUrl()
	{
		$protocol = 'http' . (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on' ? 's' : '');
		$name = $_SERVER['SERVER_NAME'];
		$port = (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on' ? ($_SERVER['SERVER_PORT'] != 443 ? ':' . $_SERVER['SERVER_PORT'] : '') : ($_SERVER['SERVER_PORT'] != 80 ? ':' . $_SERVER['SERVER_PORT'] : ''));
		return $protocol . '://' . $name . $port;
	}

	protected function parseApacheModuleRewrite()
	{

		$ru = $_SERVER['REQUEST_URI'];

		if(($p = strpos($ru, '?')) !== false) {
			$ru = substr($ru, 0, $p);
		}
		$ru = urldecode($ru);

		$this->prefix =  substr($ru, 0, -strlen($_SERVER['PATH_INFO']));
		$this->input = substr($ru, strlen($this->prefix));

		if(!$this->input) {
			$this->input = "/";
		}

		$this->sources = array_merge($this->sources, $_SERVER);

		$this->basePath = $this->prefix;

		if(substr($this->basePath, -1, 1) != '/') {
			$this->basePath .= '/';
		}

		$this->baseHref = $this->serverUrl() . $this->basePath;
	}

	protected function parseApacheModuleNoRewrite()
	{

		$ru = $_SERVER['REQUEST_URI'];

		if(($p = strpos($ru, '?')) !== false) {
			$ru = substr($ru, 0, $p);
		}
		$ru = urldecode($ru);

		$sn = $_SERVER['SCRIPT_NAME'];

		$this->prefix = $this->getStringBase($sn, $ru, $appendFrom);

		$this->prefix .= substr($sn, $appendFrom + 1);
		$this->input = substr($ru, $appendFrom + 1);

		if(!$this->input) {
			$this->input = "/";
		}

		$this->sources = array_merge($this->sources, $_SERVER);

		$this->basePath = str_replace('\\', '/', dirname($this->prefix));

		if(substr($this->basePath, -1, 1) != '/') {
			$this->basePath .= '/';
		}

		$this->baseHref = $this->serverUrl() . $this->basePath;

	}

	protected function parseApacheCgiRewrite()
	{
		$reqUri = $_SERVER['REQUEST_URI'];

		$pathParameter = $this->parseOptions['path_info_parameter'];

		$pathStart = strpos($_SERVER['argv'][0], '=') + 1;
		$firstAmpersand = strpos($_SERVER['argv'][0], '&');
		if($firstAmpersand) {
			$this->input = substr($_SERVER['argv'][0], $pathStart, $firstAmpersand-$pathStart);
		}
		else {
			$this->input = substr($_SERVER['argv'][0], $pathStart);
		}

		if(!$this->input) {
			$this->input = '/';
		}

		$sn = $_SERVER['SCRIPT_NAME'];

		$this->prefix = $this->getStringBase($sn, $reqUri);

		if(substr($this->prefix, -1, 1) == '/') {
			$this->prefix = substr($this->prefix, 0, strlen($this->prefix)-1);
		}

		$this->basePath = $this->prefix;

		if(substr($this->basePath, -1, 1) != '/') {
			$this->basePath .= '/';
		}

		$this->baseHref = $this->serverUrl() . $this->basePath;

	}

	protected function parseApacheCgiNoRewrite()
	{

		$ru = $_SERVER['REQUEST_URI'];

		if(($p = strpos($ru, '?')) !== false) {
			$ru = substr($ru, 0, $p);
		}
		$ru = urldecode($ru);

		$sn = $_SERVER['SCRIPT_NAME'];

		$this->prefix = $this->getStringBase($sn, $ru, $appendFrom);

		$this->prefix .= substr($sn, $appendFrom + 1);
		$this->input = substr($ru, $appendFrom + 1);

		if(!$this->input) {
			$this->input = '/';
		}

		$this->sources = array_merge($this->sources, $_SERVER);

		$this->basePath = str_replace('\\', '/', dirname($this->prefix));

		if(substr($this->basePath, -1, 1) != '/') {
			$this->basePath .= '/';
		}

		$this->baseHref = $this->serverUrl() . $this->basePath;

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
	public function gen($route, $params = array(), $options = array())
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

				$path = parent::gen($routes, array_map('rawurlencode', $params));
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
		$req->setParametersByRef($_GET);
		// merge POST parameters
		$req->setParametersByRef($_POST);

		// the real deal
		return parent::execute();
	}
}

?>

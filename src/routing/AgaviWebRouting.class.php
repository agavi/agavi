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
		parent::initialize($context);
		if(isset($_SERVER['HTTP_X_REWRITE_URL'])) {
			// Microsoft IIS with ISAPI_Rewrite
			$ru = $_SERVER['HTTP_X_REWRITE_URL'];
		} elseif(isset($_SERVER['ORIG_PATH_INFO'])) {
			// Microsoft IIS
			$ru = $_SERVER['ORIG_PATH_INFO'];
		} else {
			// Apache
			$ru = $_SERVER['REQUEST_URI'];
		}
		
		if(($p = strpos($ru, '?')) !== false) {
			$ru = substr($ru, 0, $p);
		}
		$ru = urldecode($ru);

		if(isset($_SERVER['PATH_INFO'])) {
			$this->prefix =  substr($ru, 0, -strlen($_SERVER['PATH_INFO']));
			$this->input = substr($ru, strlen($this->prefix));
		} else {
			$sn = $_SERVER['SCRIPT_NAME'];

			$this->prefix = '';
			for($i = 0; isset($sn[$i]) && isset($ru[$i]) && $sn[$i] == $ru[$i]; ++$i) {
				$this->prefix .= $sn[$i];
				$appendFrom = $i;
			}
			$this->prefix .= substr($_SERVER['SCRIPT_NAME'], $appendFrom + 1);
			$this->input = substr($ru, $i);
		}
		if(!$this->input) {
			$this->input = "/";
		}
		
		if(isset($_SERVER['REDIRECT_URL']) || isset($_SERVER['HTTP_X_REWRITE_URL'])) {
			// a rewrite happened
			$this->basePath = $this->prefix . '/';
		} else {
			$this->basePath = dirname($this->prefix) . '/';
		}
		$this->basePath;
		$this->baseHref = 
			'http' . (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on' ? 's' : '')  . '://' . 
			$_SERVER['SERVER_NAME'] . 
			(isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on' ? ($_SERVER['SERVER_PORT'] != 443 ? ':' . $_SERVER['SERVER_PORT'] : '') : ($_SERVER['SERVER_PORT'] != 80 ? ':' . $_SERVER['SERVER_PORT'] : '')) . 
			$this->basePath;
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
	 * @param      array  An associative array of URL parameters.
	 *
	 * @return     string
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function gen($route, $params = array())
	{
		if(isset($this->routes[$route])) {
			if(AgaviConfig::get('core.use_routing')) {
				// the route exists and routing is enabled, the parent method handles it

				$append = '';

				// get the parameters which are not defined in this route an append them as query string
				$p = $params;
				foreach($this->routes[$route]['par'] as $param) {
					if(isset($p[$param])) {
						unset($p[$param]);
					}
				}
				if(count($p) > 0) {
					$append = '?' . http_build_query($p);
				}

				return parent::gen($route, $params) . $append;
			} else {
				// the route exists, but we must create a normal index.php?foo=bar URL.

				$req = $this->context->getRequest();

				if(isset($this->routes[$route])) {
					$defaults = array();
					$parent = $route;
					do {
						$r =& $this->routes[$parent];
						$myDefaults = $r['opt']['defaults'];
						$myDefaults[$req->getModuleAccessor()] = $r['opt']['module'];
						$myDefaults[$req->getActionAccessor()] = $r['opt']['action'];

						foreach(array_reverse($r['opt']['nostops']) as $noStop) {
							$myR = $this->routes[$noStop];
							if(!$myR['opt']['imply']) {
								continue;
							}

							$myDefaults = array_merge($myDefaults, $myR['opt']['defaults']);
						}

						$defaults = array_merge($defaults, $myDefaults);
						$parent = $r['opt']['parent'];

					} while($parent);

					$params = array_merge($defaults, $params);
					$route = null;

				}
			}
		}
		// the route does not exist. we generate a normal index.php?foo=bar URL.

		$url = $route;

		if ($url == null) {
			$url = $_SERVER['SCRIPT_NAME'];
		}

		// use GET format
		$divider  = '&';
		$equals   = '=';
		$url     .= '?';

		// loop through the parameters
		foreach ($params as $key => $value) {
			$url .= urlencode($key) . $equals . urlencode($value) . $divider;
		}

		// strip off last divider character
		$url = rtrim($url, $divider);

		// replace &'s with &amp;
		$url = str_replace('&', '&amp;', $url);

		return $url;
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
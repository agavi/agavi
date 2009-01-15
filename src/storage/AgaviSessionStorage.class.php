<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2005-2008 the Agavi Project.                                |
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
 * AgaviSessionStorage allows you to store persistent Agavi data in the user
 * session.
 *
 * <b>Optional parameters:</b>
 *
 * # <b>auto_start</b>              - [Yes]   - Should session_start() automatically be
 *                                              called?
 * # <b>session_name</b>            - [Agavi] - The name of the session.
 * # <b>session_id</b>              - []      - Session id to set (see {@link http://www.php.net/session_id}).
 * # <b>session_cookie_lifetime</b> - []      - The lifetime of the session cookie in seconds. 0 for unlimited.
 * # <b>session_cookie_path</b>     - []      - The path to set to the session cookie.
 * # <b>session_cookie_domain</b>   - []      - The domain to set to the session cookie.
 * # <b>session_cookie_secure</b>   - []      - Whether or not the cookie should only be sent over secure connections
 *
 * All cookie parameters default to whatever PHP would otherwise use 
 * (ie. what's set in php.ini, .htaccess or elsewhere)
 * (see {@link http://www.php.net/session-set-cookie-params})
 *
 * @package    agavi
 * @subpackage storage
 *
 * @author     Sean Kerr <skerr@mojavi.org>
 * @author     Veikko Mäkinen <mail@veikkomakinen.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.9.0
 *
 * @version    $Id$
 */
class AgaviSessionStorage extends AgaviStorage
{
	/**
	 * Starts the session.
	 * The method must be called after initialize().
	 * This code cannot be run in initialize(), because initialization has to
	 * finish completely, for all instances, before a session can be created:
	 * A Database Session Storage must initialize the parent, then itself, and
	 * may only then call startup() to auto-start the session.
	 * Also, the routing must be fully initialized, too.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @author     Veikko Mäkinen <mail@veikkomakinen.com>
	 * @since      0.11.0
	 */
	public function startup()
	{
		if($this->hasParameter('session_save_path')) {
			session_save_path($this->getParameter('session_save_path'));
		}
		
		session_name($this->getParameter('session_name', 'Agavi'));
		
		if($this->hasParameter('session_id')) {
			session_id($this->getParameter('session_id'));
		}
		
		if(session_id() === '') {
			$cookieDefaults = session_get_cookie_params();
			
			$routing = $this->context->getRouting();
			if($routing instanceof AgaviWebRouting) {
				// set path to true if the default path from php.ini is "/". this will, in startup(), trigger the base href as the path.
				if($cookieDefaults['path'] == '/') {
					$cookieDefaults['path'] = true;
				}
			}
			
			$lifetime = $this->getParameter('session_cookie_lifetime', $cookieDefaults['lifetime']);
			if(is_numeric($lifetime)) {
				$lifetime = (int) $lifetime;
			} else {
				$lifetime = strtotime($lifetime, 0);
			}
			$path = $this->getParameter('session_cookie_path', $cookieDefaults['path']);
			if($path === true) {
				$path = $this->context->getRouting()->getBasePath();
			}
			$domain = $this->getParameter('session_cookie_domain', $cookieDefaults['domain']);
			$secure = (bool) $this->getParameter('session_cookie_secure', $cookieDefaults['secure']);
			$httpOnly = (bool) $this->getParameter('session_cookie_httponly', $cookieDefaults['httponly']);
			
			session_set_cookie_params($lifetime, $path, $domain, $secure, $httpOnly);
			
			session_start();
			
			if($lifetime !== 0) {
				setcookie(session_name(), session_id(), time() + $lifetime, $path, $domain, $secure, $httpOnly);
			}
		}
	}

	/**
	 * Read data from this storage.
	 *
	 * The preferred format for a key is directory style so naming conflicts can
	 * be avoided.
	 *
	 * @param      string A unique key identifying your data.
	 *
	 * @return     mixed Data associated with the key.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function read($key)
	{
		if(isset($_SESSION[$key])) {
			return $_SESSION[$key];
		}
		return null;
	}

	/**
	 * Remove data from this storage.
	 *
	 * The preferred format for a key is directory style so naming conflicts can
	 * be avoided.
	 *
	 * @param      string A unique key identifying your data.
	 *
	 * @return     mixed Data associated with the key.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function remove($key)
	{
		$retval = null;

		if(isset($_SESSION[$key])) {
			$retval = $_SESSION[$key];
			unset($_SESSION[$key]);
		}

		return $retval;
	}

	/**
	 * Execute the shutdown procedure.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function shutdown()
	{
		session_write_close();
	}

	/**
	 * Write data to this storage.
	 *
	 * The preferred format for a key is directory style so naming conflicts can
	 * be avoided.
	 *
	 * @param      string A unique key identifying your data.
	 * @param      mixed  Data associated with your key.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function write($key, $data)
	{
		$_SESSION[$key] = $data;
	}
}

?>
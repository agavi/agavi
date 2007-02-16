<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2003-2007 the Agavi Project.                                |
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
	 * Initialize this Storage.
	 *
	 * @param      AgaviContext An AgaviContext instance.
	 * @param      array        An associative array of initialization parameters.
	 *
	 * @throws     <b>AgaviInitializationException</b> If an error occurs while
	 *                                                 initializing this Storage.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     Veikko Mäkinen <mail@veikkomakinen.com>
	 * @since      0.9.0
	 */
	public function initialize(AgaviContext $context, array $parameters = array())
	{
		parent::initialize($context, $parameters);

		$sessionName = $this->getParameter('session_name', 'Agavi');
		session_name($sessionName);

		if($sessionId = $this->getParameter('session_id')) {
			session_id($sessionId);
		}
		
		$cookieDefaults = session_get_cookie_params();
		
		// set path to null if the default path from php.ini is "/". this will, much later, trigger the base href as the path.
		if($cookieDefaults['path'] == '/') {
			$cookieDefaults['path'] = null;
		}
		
		$lifetime = $this->getParameter('session_cookie_lifetime', $cookieDefaults['lifetime']);
		$path     = $this->getParameter('session_cookie_path', $cookieDefaults['path']);
		$domain   = $this->getParameter('session_cookie_domain', $cookieDefaults['domain']);
		$secure   = (bool) $this->getParameter('session_cookie_secure', $cookieDefaults['secure']);

		if(version_compare(phpversion(), '5.2', 'ge')) {
			$httpOnly = $this->getParameter('session_cookie_httponly', $cookieDefaults['httponly']);
			session_set_cookie_params($lifetime, $path, $domain, $secure, $httpOnly);
		} else {
			session_set_cookie_params($lifetime, $path, $domain, $secure);
		}
	}

	/**
	 * Starts a session unless a session has already been started.
	 * The method will be called after initialization if auto_start is true.
	 * If auto_start is false, this method must be called manually.
	 * This code cannot be run in initialize(), because initialization has to
	 * finish completely, for all instances, before a session can be created:
	 * A Database Session Storage must initialize the parent, then itself, and
	 * may only then call startup() to auto-start the session.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function startup()
	{
		if(session_id() === '') {
			// first: grab cookie params
			$params = session_get_cookie_params() + array('httponly' => false);
			
			// second: start session
			session_start();
			
			// third: send a custom session cookie (yes, must be setcookie()) with false as value to delete PHP's. remember, we're sending the cookie ourselves
			
			if(version_compare(phpversion(), '5.2', 'ge')) {
				setcookie(session_name(), false, time() - 100000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
			} else {
				setcookie(session_name(), false, time() - 100000, $params['path'], $params['domain'], $params['secure']);
			}
			
			// fourth: fix the cookie path if necessary
			if($params['path'] === '') {
				$params['path'] = null;
			}
			
			$res = $this->context->getController()->getGlobalResponse();
			if($res instanceof AgaviWebResponse) {
				// send ze session cookie. yes, yes, yes, we want to do this, even though php would send it itself. we need this for non-infinite-lifetime cookies (zomg), plus a "null" path will set the routing's base href
				$res->setCookie(session_name(), session_id(), $params['lifetime'], $params['path'], $params['domain'], $params['secure'], $params['httponly']);
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
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
 * AgaviWebController provides web specific methods to Controller such as, url
 * redirection.
 *
 * @package    agavi
 * @subpackage controller
 *
 * @author     Sean Kerr <skerr@mojavi.org>
 * @author     David Zuelke <dz@bitxtender.com>
 * @copyright  (c) Authors
 * @since      0.9.0
 *
 * @version    $Id$
 */
class AgaviWebController extends AgaviController
{

	protected
		$httpStatusCodes = array(
			'100' => "HTTP/1.1 100 Continue",
			'101' => "HTTP/1.1 101 Switching Protocols",
			'200' => "HTTP/1.1 200 OK",
			'201' => "HTTP/1.1 201 Created",
			'202' => "HTTP/1.1 202 Accepted",
			'203' => "HTTP/1.1 203 Non-Authoritative Information",
			'204' => "HTTP/1.1 204 No Content",
			'205' => "HTTP/1.1 205 Reset Content",
			'206' => "HTTP/1.1 206 Partial Content",
			'300' => "HTTP/1.1 300 Multiple Choices",
			'301' => "HTTP/1.1 301 Moved Permanently",
			'302' => "HTTP/1.1 302 Found",
			'303' => "HTTP/1.1 303 See Other",
			'304' => "HTTP/1.1 304 Not Modified",
			'305' => "HTTP/1.1 305 Use Proxy",
			'307' => "HTTP/1.1 307 Temporary Redirect",
			'400' => "HTTP/1.1 400 Bad Request",
			'401' => "HTTP/1.1 401 Unauthorized",
			'402' => "HTTP/1.1 402 Payment Required",
			'403' => "HTTP/1.1 403 Forbidden",
			'404' => "HTTP/1.1 404 Not Found",
			'405' => "HTTP/1.1 405 Method Not Allowed",
			'406' => "HTTP/1.1 406 Not Acceptable",
			'407' => "HTTP/1.1 407 Proxy Authentication Required",
			'408' => "HTTP/1.1 408 Request Time-out",
			'409' => "HTTP/1.1 409 Conflict",
			'410' => "HTTP/1.1 410 Gone",
			'411' => "HTTP/1.1 411 Length Required",
			'412' => "HTTP/1.1 412 Precondition Failed",
			'413' => "HTTP/1.1 413 Request Entity Too Large",
			'414' => "HTTP/1.1 414 Request-URI Too Large",
			'415' => "HTTP/1.1 415 Unsupported Media Type",
			'416' => "HTTP/1.1 416 Requested range not satisfiable",
			'417' => "HTTP/1.1 417 Expectation Failed",
			'500' => "HTTP/1.1 500 Internal Server Error",
			'501' => "HTTP/1.1 501 Not Implemented",
			'502' => "HTTP/1.1 502 Bad Gateway",
			'503' => "HTTP/1.1 503 Service Unavailable",
			'504' => "HTTP/1.1 504 Gateway Time-out",
			'505' => "HTTP/1.1 505 HTTP Version not supported",
		),
		$httpStatusCode = null,
		$httpHeaders = array(),
		$cookieConfig = array(),
		$cookies = array();
		
	/**
	 * Acts as a front web controller unless module and action names are given as
	 * parameters.
	 *
	 * @see        Controller::dispatch()
	 */
	public function dispatch($parameters = array())
	{
		// so setting the headers works
		ob_start();
		
		parent::dispatch($parameters);
		
		// output all headers for the response
		$this->sendHTTPResponseHeaders();
	}

	/**
	 * Retrieve the content type set for the response.
	 *
	 * @return     string A content type, or null if none is set.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.9.0
	 */
	public function getContentType()
	{
		$retval = $this->getHttpHeader('Content-Type');
		if(is_array($retval) && count($retval)) {
			return $retval[0];
		} else {
			return null;
		}
	}
	
	/**
	 * Sets a HTTP status code for the response.
	 *
	 * @param      string A numeric HTTP status code between 100 and 505.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function setHTTPStatusCode($code) {
		$code = strval($code);
		if(isset($this->httpStatusCodes[$code])) {
			$this->httpStatusCode = $code;
		}
	}

	/**
	 * Gets the HTTP status code set for the response.
	 *
	 * @return     string A numeric HTTP status code between 100 and 505, or null
	                      if no status code has been set.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getHTTPStatusCode() {
		return $this->httpStatusCode;
	}

	/**
	 * Normalizes a HTTP header names
	 *
	 * @param      string A HTTP header name
	 *
	 * @return     string A normalized HTTP header name
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function normalizeHTTPHeaderName($name)
	{
		if(strtolower($name) == "etag") {
			return "ETag";
		} elseif(strtolower($name) == "www-authenticate") {
			return "WWW-Authenticate";
		} else {
			return str_replace(' ', '-', ucwords(str_replace('-', ' ', strtolower($name))));
		}
	}

	/**
	 * Retrieve the HTTP header values set for the response.
	 *
	 * @param      string A HTTP header field name.
	 *
	 * @return     array All values set for that header, or null if no headers set
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getHTTPHeader($name)
	{
		$name = $this->normalizeHTTPHeaderName($name);
		$retval = null;
		if(isset($this->headers[$name])) {
			$retval = $this->headers[$name];
		}
		return $retval;
	}

	/**
	 * Retrieve the HTTP headers set for the response.
	 *
	 * @return     array An associative array of HTTP header names and values.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getHTTPHeaders()
	{
		return $this->headers;
	}

	/**
	 * Check if an HTTP header has been set for the response.
	 *
	 * @param      string A HTTP header field name.
	 *
	 * @return     array All values set for that header, or null if no headers set
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function hasHTTPHeader($name)
	{
		$name = $this->normalizeHTTPHeaderName($name);
		$retval = false;
		if(isset($this->headers[$name])) {
			$retval = true;
		}
		return $retval;
	}

	/**
	 * Set a HTTP header for the response
	 *
	 * @param      string A HTTP header field name.
	 * @param      array  A HTTP header field value, of an array of values.
	 * @param      bool   If true, a header with that name will be oberwritten,
	 *                    otherwise, the value will be appended.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function setHTTPHeader($name, $value, $replace = true)
	{
		$name = $this->normalizeHTTPHeaderName($name);
		if(!isset($this->headers[$name]) || $replace) {
			$this->headers[$name] = array();
		}
		if(is_array($value)) {
			$this->headers[$name] = array_merge($this->headers[$name], $value);
		} else {
			$this->headers[$name][] = $value;
		}
	}

	/**
	 * Send a cookie. Note that cookies are sent as HTTP headers and thus
	 * must be sent before any output from the application.
	 *
	 * @param      string A cookie name.
	 * @param      mixed Data to store into a cookie. If null or empty cookie
	 *                   will be tried to be removed.
	 * @param      array Cookie parameters (parameters from config or defaults
	 *                   are used for any missing parameters).
	 *
	 * @author     Veikko Makinen <mail@veikkomakinen.com>
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function setCookie($name, $value, $lifetime = null, $path = null, $domain = null, $secure = null)
	{
		$lifetime = isset($lifetime) ? $lifetime : $this->cookieConfig['lifetime'];
		$path     = isset($path)     ? $path     : $this->cookieConfig['path'];
		$domain   = isset($domain)   ? $domain   : $this->cookieConfig['domain'];
		$secure   = isset($secure)   ? $secure   : $this->cookieConfig['secure'];

		//do we want to set expiration time or not?
		$expire = ($lifetime != 0) ? time() + $lifetime : 0;

		$this->cookies[$name] = array(
			'value' => $value,
			'expire' => $expire,
			'path' => $path,
			'domain' => $domain,
			'secure' => $secure
		);
	}

	/**
	 * Remove the HTTP header set for the response
	 *
	 * @param      string A HTTP header field name.
	 *
	 * @return     mixed The removed header's value or null if header was not set.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function removeHTTPHeader($name)
	{
		$name = $this->normalizeHTTPHeaderName($name);
		$retval = null;
		if(isset($this->headers[$name])) {
			$retval = $this->headers[$name];
			unset($this->headers[$name]);
		}
		return $retval;
	}

	/**
	 * Clears the HTTP headers set for this response.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function clearHTTPHeaders()
	{
		$this->headers = array();
	}
	
	/**
	 * Sends HTTP Status code, headers and cookies
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function sendHTTPResponseHeaders()
	{
		// send HTTP status code
		if(isset($this->httpStatusCode) && isset($this->httpStatusCodes[$this->httpStatusCode])) {
			header($this->httpStatusCodes[$this->httpStatusCode]);
		}
		
		if($this->getContentType() === null && isset($this->outputTypes[$this->outputType]['parameters']['Content-Type'])) {
			$this->setContentType($this->outputTypes[$this->outputType]['parameters']['Content-Type']);
		}
		
		// send headers
		foreach($this->headers as $name => $values) {
			foreach($values as $key => $value) {
				if($key == 0) {
					header($name . ': ' . $value);
				} else {
					header($name . ': ' . $value, false);
				}
			}
		}
		
		// send cookies
		foreach($this->cookies as $name => $values) {
			setcookie($name, $values['value'], $values['expire'], $values['path'], $values['domain'], $values['secure']);
		}
	}

	/**
	 * Initialize this controller.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.9.0
	 */
	public function initialize(AgaviContext $context, $parameters = array())
	{
		// initialize parent
		parent::initialize($context, $parameters);

		ini_set('arg_separator.output', AgaviConfig::get('php.arg_separator.output', '&amp;'));

		$this->cookieConfig = array();
		$this->cookieConfig['lifetime'] = isset($parameters['cookie_lifetime']) ? $parameters['cookie_lifetime'] : 0;
		$this->cookieConfig['path']     = isset($parameters['cookie_path'])     ? $parameters['cookie_path']     : "/";
		$this->cookieConfig['domain']   = isset($parameters['cookie_domain'])   ? $parameters['cookie_domain']   : "";
		$this->cookieConfig['secure']   = isset($parameters['cookie_secure'])   ? $parameters['cookie_secure']   : 0;
	}

	/**
	 * Redirect the request to another URL.
	 *
	 * @param      string An existing URL.
	 * @param      int    A delay in seconds before redirecting. This only works 
	 *                    on browsers that do not support the PHP header.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function redirect ($url, $delay = 0)
	{

		// shutdown the controller
		$this->shutdown();

		// redirect
		header('Location: ' . $url);

		$echo = '<html>' .
				'<head>' .
				'<meta http-equiv="refresh" content="%d;url=%s"/>' .
				'</head>' .
				'</html>';

		$echo = sprintf($echo, $delay, $url);

		echo $echo;

		exit;

	}

	/**
	 * Set the content type for the response.
	 *
	 * @param      string A content type.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.9.0
	 */
	public function setContentType($type)
	{
		$this->setHTTPHeader('Content-Type', $type);
	}

}

?>

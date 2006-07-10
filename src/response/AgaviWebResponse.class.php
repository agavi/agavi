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
 * AgaviWebResponse handles HTTP responses.
 *
 * @package    agavi
 * @subpackage response
 *
 * @author     David Zuelke <dz@bitxtender.com>
 * @copyright  (c) Authors
 * @since      0.11.0
 *
 * @version    $Id$
 */
class AgaviWebResponse extends AgaviResponse
{
	/**
	 * @var        array An array of all HTTP status codes and their message.
	 */
	protected $httpStatusCodes = array(
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
		'408' => "HTTP/1.1 408 Request Timeout",
		'409' => "HTTP/1.1 409 Conflict",
		'410' => "HTTP/1.1 410 Gone",
		'411' => "HTTP/1.1 411 Length Required",
		'412' => "HTTP/1.1 412 Precondition Failed",
		'413' => "HTTP/1.1 413 Request Entity Too Large",
		'414' => "HTTP/1.1 414 Request-URI Too Long",
		'415' => "HTTP/1.1 415 Unsupported Media Type",
		'416' => "HTTP/1.1 416 Requested Range Not Satisfiable",
		'417' => "HTTP/1.1 417 Expectation Failed",
		'500' => "HTTP/1.1 500 Internal Server Error",
		'501' => "HTTP/1.1 501 Not Implemented",
		'502' => "HTTP/1.1 502 Bad Gateway",
		'503' => "HTTP/1.1 503 Service Unavailable",
		'504' => "HTTP/1.1 504 Gateway Timeout",
		'505' => "HTTP/1.1 505 HTTP Version Not Supported",
	);
	
	/**
	 * @var        string The HTTP status code to send for the response.
	 */
	protected $httpStatusCode = '200';
	
	/**
	 * @var        array The HTTP headers scheduled to be sent with the response.
	 */
	protected $httpHeaders = array();
	
	/**
	 * @var        array The Cookie settings for this Request instance.
	 */
	protected $cookieConfig = array();
	
	/**
	 * @var        array The Cookies scheduled to be sent with the response.
	 */
	protected $cookies = array();
	
	/**
	 * Initialize this Response.
	 *
	 * @param      AgaviContext An AgaviContext instance.
	 * @param      array        An array of initialization parameters.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function initialize(AgaviContext $context, $parameters = array())
	{
		parent::initialize($context, $parameters);
		
		$this->cookieConfig = array(
			'lifetime' => isset($parameters['cookie_lifetime']) ? $parameters['cookie_lifetime'] : 0,
			'path'     => isset($parameters['cookie_path'])     ? $parameters['cookie_path']     : "/",
			'domain'   => isset($parameters['cookie_domain'])   ? $parameters['cookie_domain']   : "",
			'secure'   => isset($parameters['cookie_secure'])   ? $parameters['cookie_secure']   : 0
		);
	}
	
	/**
	 * Send all response data to the client.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function send()
	{
		if($this->dirty) {
			$this->sendHttpResponseHeaders();
		
			$this->sendContent();
		
			$this->dirty = false;
		}
	}
	
	/**
	 * Clear all reponse data.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function clear()
	{
		if(!$this->locked) {
			$this->clearContent();
			$this->httpHeaders = array();
			$this->cookies = array();
			$this->dirty = false;
		}
	}
	
	/**
	 * Export the contents of this response.
	 *
	 * @return     array An array of data.
	 *
	 * @author     David Zuelke <du@bitxtender.com>
	 * @since      0.11.0
	 */
	public function export()
	{
		return array_merge(parent::export(), array('httpStatusCode' => $this->getHttpStatusCode(), 'httpHeaders' => $this->getHttpHeaders(), 'cookies' => $this->cookies));
	}
	
	/**
	 * Export the information data (e.g. HTTP Headers, Cookies) for this response.
	 *
	 * @return     array An array of data.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function exportInfo()
	{
		return array_merge(parent::exportInfo(), array('httpStatusCode' => $this->getHttpStatusCode(), 'httpHeaders' => $this->getHttpHeaders(), 'cookies' => $this->cookies));
	}
	
	/**
	 * Import data for this response.
	 *
	 * @param      array An array of data.
	 *
	 * @return     bool Whether or not the operation was successful.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function import($data)
	{
		if(!$this->locked) {
			if(isset($data['httpStatusCode'])) {
				$this->httpStatusCode = $data['httpStatusCode'];
			}
			if(isset($data['httpHeaders'])) {
				$this->httpHeaders = $data['httpHeaders'];
			}
			if(isset($data['cookies'])) {
				$this->cookies = $data['cookies'];
			}
			return parent::import($data) && true;
		}
		parent::import($data);
		return false;
	}
	
	/**
	 * Merge in data for this response.
	 *
	 * @param      array An array of data.
	 *
	 * @return     bool Whether or not the operation was successful.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function merge($data)
	{
		$retval = parent::merge($data);
		if(!$this->locked) {
			if(isset($data['cookies'])) {
				$this->cookies = array_merge($data['cookies'], $this->cookies);
			}
			return $retval && true;
		}
		return $retval && false;
	}
	
	/**
	 * Append data to this response.
	 *
	 * @param      array An array of data.
	 *
	 * @return     bool Whether or not the operation was successful.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function append($data)
	{
		$retval = parent::append($data);
		if(!$this->locked) {
			if(isset($data['httpStatusCode'])) {
				$this->httpStatusCode = $data['httpStatusCode'];
			}
			if(isset($data['httpHeaders'])) {
				$this->httpHeaders = array_merge($this->httpHeaders, $data['httpHeaders']);
			}
			if(isset($data['cookies'])) {
				$this->cookies = array_merge($this->cookies, $data['cookies']);
			}
			return $retval && true;
		}
		return $retval && false;
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
		$this->setHttpHeader('Content-Type', $type);
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
	public function setHttpStatusCode($code) {
		$code = strval($code);
		if(isset($this->httpStatusCodes[$code])) {
			$this->httpStatusCode = $code;
		} else {
			throw new AgaviException('Invalid HTTP Status code: ' . $code);
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
	public function getHttpStatusCode() {
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
	public function normalizeHttpHeaderName($name)
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
	public function getHttpHeader($name)
	{
		$name = $this->normalizeHttpHeaderName($name);
		$retval = null;
		if(isset($this->httpHeaders[$name])) {
			$retval = $this->httpHeaders[$name];
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
	public function getHttpHeaders()
	{
		return $this->httpHeaders;
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
	public function hasHttpHeader($name)
	{
		$name = $this->normalizeHttpHeaderName($name);
		$retval = false;
		if(isset($this->httpHeaders[$name])) {
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
	public function setHttpHeader($name, $value, $replace = true)
	{
		$name = $this->normalizeHttpHeaderName($name);
		if(!isset($this->httpHeaders[$name]) || $replace) {
			$this->httpHeaders[$name] = array();
		}
		if(is_array($value)) {
			$this->httpHeaders[$name] = array_merge($this->httpHeaders[$name], $value);
		} else {
			$this->httpHeaders[$name][] = $value;
		}
	}

	/**
	 * Send a cookie.
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

		$this->cookies[$name] = array(
			'value' => $value,
			'lifetime' => $lifetime,
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
	public function removeHttpHeader($name)
	{
		$name = $this->normalizeHttpHeaderName($name);
		$retval = null;
		if(isset($this->httpHeaders[$name])) {
			$retval = $this->httpHeaders[$name];
			unset($this->httpHeaders[$name]);
		}
		return $retval;
	}

	/**
	 * Clears the HTTP headers set for this response.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function clearHttpHeaders()
	{
		$this->httpHeaders = array();
	}
	
	/**
	 * Sends HTTP Status code, headers and cookies
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function sendHttpResponseHeaders()
	{
		$file = $line = '';
		if(headers_sent($file, $line)) {
			throw new AgaviException('Headers already sent, output started in "' . $file . '" on line "' . $line . '"');
		} else {
			unset($file, $line);
		}
		
		// send HTTP status code
		if(isset($this->httpStatusCode) && isset($this->httpStatusCodes[$this->httpStatusCode])) {
			header($this->httpStatusCodes[$this->httpStatusCode]);
		}
		
		$oti = $this->context->getController()->getOutputTypeInfo();
		
		if($this->getContentType() === null && isset($oti['parameters']['Content-Type'])) {
			$this->setContentType($oti['parameters']['Content-Type']);
		}
		
		// send headers
		foreach($this->httpHeaders as $name => $values) {
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
			//do we want to set expiration time or not?
			$expire = ($values['lifetime'] != 0) ? time() + $values['lifetime'] : 0;

			if($values['value'] === false || $values['value'] === null || $values['value'] === '') {
				$expire = time() - 3600 * 24;
			}
			
			setcookie($name, $values['value'], $expire, $values['path'], $values['domain'], $values['secure']);
		}
	}

}

?>
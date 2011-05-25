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
 * AgaviSoapRequestDataHolder provides methods for retrieving client request 
 * information parameters and headers of SOAP calls.
 *
 * @package    agavi
 * @subpackage request
 *
 * @author     David Zülke <dz@bitxtender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.11.0
 *
 * @version    $Id$
 */
class AgaviSoapRequestDataHolder extends AgaviWebserviceRequestDataHolder implements AgaviIHeadersRequestDataHolder
{
	/**
	 * @constant   Constant for source name of HTTP headers.
	 */
	const SOURCE_HEADERS = 'headers';
	
	/**
	 * @var        array An array of headers sent with the request.
	 */
	protected $headers = array();
	
	/**
	 * Constructor
	 *
	 * @param      array An associative array of request data source names and
	 *                   data arrays.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function __construct(array $data = array())
	{
		// SOAP headers
		$this->registerSource(self::SOURCE_HEADERS, $this->headers);
		
		// call the parent ctor which handles the actual loading of the data
		parent::__construct($data);
	}
	
	/**
	 * Clear all headers.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function clearHeaders()
	{
		$this->headers = array();
	}

	/**
	 * Retrieve all HTTP headers.
	 *
	 * @return     array A list of SOAP headers.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function &getHeaders()
	{
		return $this->headers;
	}
	
	/**
	 * Get a HTTP header.
	 *
	 * @param      string Case-insensitive name of a header, using either a hyphen
	 *                    or an underscore as a separator.
	 * @param      mixed  A default value.
	 *
	 * @return     string The header value, or null if header wasn't set.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function &getHeader($name, $default = null)
	{
		if(isset($this->headers[$name])) {
			return $this->headers[$name];
		}

		return $default;
	}
	
	/**
	 * Check if a HTTP header exists.
	 *
	 * @param      string Case-insensitive name of a header, using either a hyphen
	 *                    or an underscore as a separator.
	 *
	 * @return     bool True if the header was sent with the current request.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function hasHeader($name)
	{
		return isset($this->headers[$name]);
	}
	
	/**
	 * Checks if there is a value of a header is empty or not set.
	 *
	 * @param      string The header name.
	 *
	 * @return     bool The result.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function isHeaderValueEmpty($name)
	{
		return ($this->getHeader($name) === null);
	}
	/**
	 * Set a header.
	 *
	 * The header name is normalized before storing it.
	 *
	 * @param      string A header name.
	 * @param      mixed  A header value.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function setHeader($name, $value)
	{
		$this->headers[$name] = $value;
	}

	/**
	 * Set an array of headers.
	 *
	 * @param      array An associative array of headers and their values.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function setHeaders(array $headers)
	{
		$this->headers = array_merge($this->headers, $headers);
	}

	/**
	 * Remove a HTTP header.
	 *
	 * @param      string Case-insensitive name of a header, using either a hyphen
	 *                    or an underscore as a separator.
	 *
	 * @return     string The value of the removed header, if it had been set.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function &removeHeader($name)
	{
		$retval = null;
		if(isset($this->headers[$name])) {
			$retval =& $this->headers[$name];
			unset($this->headers[$name]);
		}
		return $retval;
	}
	
	/**
	 * Retrieve an array of header names.
	 *
	 * @return     array An indexed array of header names in original PHP format.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getHeaderNames()
	{
		return array_keys($this->headers);
	}
	
	/**
	 * Merge in Headers from another request data holder.
	 *
	 * @param      AgaviRequestDataHolder The other request data holder.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function mergeHeaders(AgaviRequestDataHolder $other)
	{
		if($other instanceof AgaviIHeadersRequestDataHolder) {
			$this->setHeaders($other->getHeaders());
		}
	}
}

?>
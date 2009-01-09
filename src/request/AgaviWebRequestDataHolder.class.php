<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2005-2008 the Agavi Project.                                |
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
 * AgaviWebRequestDataHolder provides methods for retrieving client request 
 * information parameters.
 *
 * @package    agavi
 * @subpackage request
 *
 * @author     Dominik del Bondio <ddb@bitxtender.com>
 * @author     David Zülke <dz@bitxtender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.11.0
 *
 * @version    $Id$
 */
class AgaviWebRequestDataHolder extends AgaviRequestDataHolder implements AgaviICookiesRequestDataHolder, AgaviIFilesRequestDataHolder, AgaviIHeadersRequestDataHolder
{
	/**
	 * @constant   Constant for source name of cookies.
	 */
	const SOURCE_COOKIES = 'cookies';
	
	/**
	 * @constant   Constant for source name of files.
	 */
	const SOURCE_FILES = 'files';
	
	/**
	 * @constant   Constant for source name of HTTP headers.
	 */
	const SOURCE_HEADERS = 'headers';
	
	/**
	 * @var        array An (proper) array of files uploaded during the request.
	 */
	protected $files = array();
	
	/**
	 * @var        array An array of cookies set in the request.
	 */
	protected $cookies = array();
	
	/**
	 * @var        array An array of headers sent with the request.
	 */
	protected $headers = array();
	
	/**
	 * @var        string The name of the AgaviUploadedFile implementation to use.
	 */
	protected $uploadedFileClass = 'AgaviUploadedFile';

	/**
	 * Checks if there is a value of a parameter is empty or not set.
	 *
	 * @param      string The field name.
	 *
	 * @return     bool The result.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function isParameterValueEmpty($field)
	{
		$value = $this->getParameter($field);
		return ($value === null || $value === '');
	}

	/**
	 * Clear all cookies.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function clearCookies()
	{
		$this->cookies = array();
	}

	/**
	 * Indicates whether or not a Cookie exists.
	 *
	 * @param      string A cookie name.
	 *
	 * @return     bool True, if a cookie with that name exists, otherwise false.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function hasCookie($name)
	{
		if(isset($this->cookies[$name]) || array_key_exists($name, $this->cookies)) {
			return true;
		}
		try {
			return AgaviArrayPathDefinition::hasValue($name, $this->cookies);
		} catch(InvalidArgumentException $e) {
			return false;
		}
	}

	/**
	 * Checks if there is a value of a cookie is empty or not set.
	 *
	 * @param      string The cookie name.
	 *
	 * @return     bool The result.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function isCookieValueEmpty($name)
	{
		return ($this->getCookie($name) === null);
	}

	/**
	 * Retrieve a value stored into a cookie.
	 *
	 * @param      string A cookie name.
	 * @param      mixed  A default value.
	 *
	 * @return     mixed The value from the cookie, if such a cookie exists,
	 *                   otherwise null.
	 *
	 * @author     Veikko Mäkinen <mail@veikkomakinen.com>
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function &getCookie($name, $default = null)
	{
		if(isset($this->cookies[$name]) || array_key_exists($name, $this->cookies)) {
			return $this->cookies[$name];
		}
		try {
			return AgaviArrayPathDefinition::getValue($name, $this->cookies, $default);
		} catch(InvalidArgumentException $e) {
			return $default;
		}
	}

	/**
	 * Set a cookie.
	 *
	 * If a cookie with the name already exists the value will be overridden.
	 *
	 * @param      string A cookie name.
	 * @param      mixed  A cookie value.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function setCookie($name, $value)
	{
		$this->cookies[$name] = $value;
	}

	/**
	 * Set an array of cookies.
	 *
	 * If an existing cookie name matches any of the keys in the supplied
	 * array, the associated value will be overridden.
	 *
	 * @param      array An associative array of cookies and their values.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function setCookies(array $cookies)
	{
		$this->cookies = array_merge($this->cookies, $cookies);
	}

	/**
	 * Remove a cookie.
	 *
	 * @param      string The cookie name
	 *
	 * @return     string The value of the removed cookie, if it had been set.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function &removeCookie($name)
	{
		if(isset($this->cookies[$name]) || array_key_exists($name, $this->cookies)) {
			$retval =& $this->cookies[$name];
			unset($this->cookies[$name]);
			return $retval;
		}
		try {
			return AgaviArrayPathDefinition::unsetValue($name, $this->cookies);
		} catch(InvalidArgumentException $e) {
		}
	}

	/**
	 * Retrieve all cookies.
	 *
	 * @return     array The cookies.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function &getCookies()
	{
		return $this->cookies;
	}

	/**
	 * Retrieve an array of cookie names.
	 *
	 * @return     array An indexed array of cookie names.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getCookieNames()
	{
		return array_keys($this->cookies);
	}
	
	/**
	 * Retrieve an array of flattened cookie names. This means when a cookie is an
	 * array you wont get the name of the cookie in the result but instead all
	 * child keys appended to the name (like foo[0],foo[1][0], ...).
	 *
	 * @return     array An indexed array of cookie names.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getFlatCookieNames()
	{
		return AgaviArrayPathDefinition::getFlatKeyNames($this->cookies);
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
	 * @return     array A list of HTTP headers (keys in original PHP format).
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
		$name = str_replace('-', '_', strtoupper($name));
		if(isset($this->headers[$name]) || array_key_exists($name, $this->headers)) {
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
		$name = str_replace('-', '_', strtoupper($name));
		return (isset($this->headers[$name]) || array_key_exists($name, $this->headers));
	}
	
	/**
	 * Checks if there is a value of a header is empty or not set.
	 *
	 * @param      string The header name.
	 *
	 * @return     bool The result.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
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
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function setHeader($name, $value)
	{
		$this->headers[str_replace('-', '_', strtoupper($name))] = $value;
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
		$name = str_replace('-', '_', strtoupper($name));
		if(isset($this->headers[$name]) || array_key_exists($name, $this->headers)) {
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
	 * Retrieve an array of file information.
	 *
	 * @param      string A file name.
	 * @param      mixed  A default return value.
	 *
	 * @return     mixed An AgaviUploadedFile object with file information, or an
	 *                   array if the field name has child elements, or null (or
	 *                   the supplied default return value) no such file exists.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function &getFile($name, $default = null)
	{
		if((isset($this->files[$name]) || array_key_exists($name, $this->files))) {
			$retval =& $this->files[$name];
		} else {
			try {
				$retval =& AgaviArrayPathDefinition::getValue($name, $this->files);
			} catch(InvalidArgumentException $e) {
				$retval = $default;
			}
		}
		if(is_array($retval) || $retval instanceof AgaviUploadedFile) {
			return $retval;
		}
		return $default;
	}

	/**
	 * Retrieve an array of files.
	 *
	 * @param      bool Whether or not to include names of nested elements.
	 *                  Defaults to true.
	 *
	 * @return     array An associative array of files.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function &getFiles()
	{
		return $this->files;
	}

	/**
	 * Indicates whether or not a file exists.
	 *
	 * @param      string A file name.
	 *
	 * @return     bool true, if the file exists, otherwise false.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function hasFile($name)
	{
		if((isset($this->files[$name]) || array_key_exists($name, $this->files))) {
			$val = $this->files[$name];
		} else {
			try {
				$val = AgaviArrayPathDefinition::getValue($name, $this->files);
			} catch(InvalidArgumentException $e) {
				return false;
			}
		}
		return (is_array($val) || $val instanceof AgaviUploadedFile);
	}

	/**
	 * Indicates whether or not any files exist.
	 *
	 * @return     bool true, if any files exist, otherwise false.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.11.0
	 */
	public function hasFiles()
	{
		return count($this->files) > 0;
	}

	/**
	 * Checks if a file is empty, i.e. not set or set, but not actually uploaded.
	 *
	 * @param      string The file name.
	 *
	 * @return     bool The result.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function isFileValueEmpty($name)
	{
		$file = $this->getFile($name);
		if(!($file instanceof AgaviUploadedFile)) {
			return true;
		}
		return ($file->getError() == UPLOAD_ERR_NO_FILE);
	}

	/**
	 * Removes file information for given file.
	 *
	 * @param      string A file name
	 *
	 * @return     mixed The old AgaviUploadedFile instance or array of elements.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function &removeFile($name)
	{
		if(isset($this->files[$name]) || array_key_exists($name, $this->files)) {
			$retval =& $this->files[$name];
			unset($this->files[$name]);
			return $retval;
		}
		try {
			return AgaviArrayPathDefinition::unsetValue($name, $this->files);
		} catch(InvalidArgumentException $e) {
		}
	}

	/**
	 * Set a file.
	 *
	 * If a file with the name already exists the value will be overridden.
	 *
	 * @param      string            A file name.
	 * @param      AgaviUploadedFile An AgaviUploadedFile object.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      0.11.0
	 */
	public function setFile($name, AgaviUploadedFile $file)
	{
		$this->files[$name] = $file;
	}

	/**
	 * Set an array of files.
	 *
	 * @param      array An assoc array of names and AgaviUploadedFile objects.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function setFiles(array $files)
	{
		$this->files = array_merge($this->files, $files);
	}

	/**
	 * Clear all files.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function clearFiles()
	{
		$this->files = array();
	}

	/**
	 * Retrieve an array of file names.
	 *
	 * @return     array An indexed array of file names.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getFileNames()
	{
		return array_keys($this->files);
	}
	
	/**
	 * Retrieve an array of flattened file names. This means when a file is an
	 * array you wont get the name of the file in the result but instead all child
	 * keys appended to the name (like foo[0],foo[1][0], ...).
	 *
	 * @return     array An indexed array of file names.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getFlatFileNames()
	{
		return AgaviArrayPathDefinition::getFlatKeyNames($this->files);
	}
	
	/**
	 * Corrects the order of $_FILES for arrays of files.
	 * The cleaned up array of AgaviUploadedFile objects is put into $this->files.
	 *
	 * @param      array Array of indices used during recursion, initially empty.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	protected function fixFilesArray(&$input = array(), $index = array())
	{
		$fromIndex = $index;
		if(count($fromIndex) > 0) {
			$first = array_shift($fromIndex);
			array_unshift($fromIndex, $first, 'error');
		} else {
			// first call
			$input = $this->files;
			$this->files = array();
		}
		$sub = AgaviArrayPathDefinition::getValue($fromIndex, $input);
		$theIndices = array();
		foreach(array('name', 'type', 'size', 'tmp_name', 'error', 'is_uploaded_file') as $name) {
			$theIndex = $fromIndex;
			$first = array_shift($theIndex);
			array_shift($theIndex);
			array_unshift($theIndex, $first, $name);
			$theIndices[$name] = $theIndex;
		}
		if(is_array($sub)) {
			foreach($sub as $key => $value) {
				$toIndex = array_merge($index, array($key));
				if(is_array($value)) {
					$this->fixFilesArray($input, $toIndex);
				} else {
					$data = new $this->uploadedFileClass();
					foreach($theIndices as $name => $theIndex) {
						$data[$name] = AgaviArrayPathDefinition::getValue(array_merge($theIndex, array($key)), $input, true /* for is_uploaded_file */);
					}
					AgaviArrayPathDefinition::setValue($toIndex, $this->files, $data);
				}
			}
		} else {
			$data = new $this->uploadedFileClass();
			foreach($theIndices as $name => $theIndex) {
				$data[$name] = AgaviArrayPathDefinition::getValue($theIndex, $input, true /* for is_uploaded_file */);
			}
			AgaviArrayPathDefinition::setValue($index, $this->files, $data);
		}
	}
	
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
		$this->registerSource(self::SOURCE_COOKIES, $this->cookies);
		$this->registerSource(self::SOURCE_FILES, $this->files);
		$this->registerSource(self::SOURCE_HEADERS, $this->headers);
		
		// call the parent ctor which handles the actual loading of the data
		parent::__construct($data);
		
		// now fix the files array if necessary
		if($this->files) {
			foreach(new RecursiveIteratorIterator(new RecursiveArrayIterator($this->files), RecursiveIteratorIterator::LEAVES_ONLY) as $leaf) {
				if(!($leaf instanceof AgaviUploadedFile)) {
					$this->fixFilesArray();
				}
				break;
			}
		}
	}
	
	/**
	 * Merge in Cookies from another request data holder.
	 *
	 * @param      AgaviRequestDataHolder The other request data holder.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function mergeCookies(AgaviRequestDataHolder $other)
	{
		if($other instanceof AgaviICookiesRequestDataHolder) {
			$this->setCookies($other->getCookies());
		}
	}
	
	/**
	 * Merge in Files from another request data holder.
	 *
	 * @param      AgaviRequestDataHolder The other request data holder.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function mergeFiles(AgaviRequestDataHolder $other)
	{
		if($other instanceof AgaviIFilesRequestDataHolder) {
			$this->setFiles($other->getFiles());
		}
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
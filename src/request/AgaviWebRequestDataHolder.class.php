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
 * AgaviWebRequestDataHolder provides methods for retrieving client request 
 * information parameters.
 *
 * @package    agavi
 * @subpackage request
 *
 * @author     Dominik del Bondio <ddb@bitxtender.com>
 * @author     Agavi Project <info@agavi.org>
 * @copyright  (c) Authors
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
	 * @var        bool Indicates whether or not PUT was used to upload a file.
	 */
	protected $isHttpPutFile = false;

	/**
	 * @var        array An (proper) array of files uploaded during the request.
	 */
	protected $files = array();
	
	/**
	 * @var        array An array of field names of uploaded files, recursive.
	 */
	protected $fileFieldNames = array();

	/**
	 * @var        array An array of cookies set in the request.
	 */
	protected $cookies = array();
	
	/**
	 * @var        array An array of headers sent with the request.
	 */
	protected $headers = array();
	

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
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function hasCookie($name)
	{
		if(isset($this->cookies[$name])) {
			return true;
		}
		$parts = AgaviArrayPathDefinition::getPartsFromPath($name);
		return AgaviArrayPathDefinition::hasValue($parts['parts'], $this->cookies);
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
	 * @author     Veikko Makinen <mail@veikkomakinen.com>
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function & getCookie($name, $default=null)
	{
		if(isset($this->cookies[$name])) {
			return $this->cookies[$name];
		}
		$parts = AgaviArrayPathDefinition::getPartsFromPath($name);
		return AgaviArrayPathDefinition::getValueFromArray($parts['parts'], $this->cookies, $default);
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
	public function setCookies($cookies)
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
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function & removeCookie($name)
	{
		$retval = null;
		if(isset($this->cookies[$name])) {
			$retval =& $this->cookies[$name];
			unset($this->cookies[$name]);
		}

		return $retval;
	}

	/**
	 * Retrieve all cookies.
	 *
	 * @return     array The cookies.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function & getCookies()
	{
		return $this->cookies;
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
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function & getHeaders()
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
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function & getHeader($name, $default = null)
	{
		$name = str_replace('-', '_', strtoupper($name));
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
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function hasHeader($name)
	{
		return isset($this->headers[str_replace('-', '_', strtoupper($name))]);
	}
	
	/**
	 * Set a header.
	 *
	 * The header name is normalized before storing it.
	 *
	 * @param      string A header name.
	 * @param      mixed  A header value.
	 *
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
	public function setHeaders($headers)
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
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function & removeHeader($name)
	{
		$retval = null;
		$name = str_replace('-', '_', strtoupper($name));
		if(isset($this->headers[$name])) {
			$retval =& $this->headers[$name];
			unset($this->headers[$name]);
		}
		return $retval;
	}
	
	/**
	 * Retrieve an array of file information.
	 *
	 * @param      string A file name.
	 * @param      mixed A default value.
	 *
	 * @return     array An associative array of file information, if the file
	 *                   exists, otherwise null.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function & getFile($name, $default = null)
	{
		$parts = AgaviArrayPathDefinition::getPartsFromPath($name);
		$retval = AgaviArrayPathDefinition::getValueFromArray($parts['parts'], $this->files);
		// check if it's a file (i.e. array with name, tmp name, size etc is there)
		if(is_array($retval)) {
			return $retval;
		}
		return $default;
	}

	/**
	 * Retrieve a file error.
	 *
	 * @param      string A file name.
	 *
	 * @return     int One of the following error codes:
	 *                 - <b>UPLOAD_ERR_OK</b>        (no error)
	 *                 - <b>UPLOAD_ERR_INI_SIZE</b>  (the uploaded file exceeds
	 *                                               upload_max_filesize
	 *                                               directive in php.ini)
	 *                 - <b>UPLOAD_ERR_FORM_SIZE</b> (the uploaded file exceeds
	 *                                               MAX_FILE_SIZE directive
	 *                                               specified in the HTML form)
	 *                 - <b>UPLOAD_ERR_PARTIAL</b>   (the uploaded file was only
	 *                                               partially uploaded)
	 *                 - <b>UPLOAD_ERR_NO_FILE</b>   (no file was uploaded)
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getFileError($name)
	{
		$parts = AgaviArrayPathDefinition::getPartsFromPath($name);
		$retval = AgaviArrayPathDefinition::getValueFromArray(array_merge($parts['parts'], array('error')), $this->files, UPLOAD_ERR_NO_FILE);
		// this check must be performed. there could be a situation where the requested path was not complete, i.e. there are children left, and one of the children has the same name as the appended part fragment above
		if(!is_array($retval)) {
			return $retval;
		}
		return null;
	}

	/**
	 * Retrieve a file name.
	 *
	 * @param      string A file name.
	 *
	 * @return     string A file name, if the file exists, otherwise null.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getFileName($name)
	{
		$parts = AgaviArrayPathDefinition::getPartsFromPath($name);
		$retval = AgaviArrayPathDefinition::getValueFromArray(array_merge($parts['parts'], array('name')), $this->files, null);
		// this check must be performed. there could be a situation where the requested path was not complete, i.e. there are children left, and one of the children has the same name as the appended part fragment above
		if(!is_array($retval)) {
			return $retval;
		}
		return null;
	}

	/**
	 * Retrieve an array of file field names.
	 *
	 * @param      bool Whether or not to include names of nested elements.
	 *                  Defaults to true.
	 *
	 * @return     array An indexed array of file field names.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getFileFieldNames($deep = true)
	{
		if($deep) {
			return $this->fileFieldNames;
		} else {
			return array_keys($this->files);
		}
	}

	/**
	 * Retrieve an array of files.
	 *
	 * @param      bool Whether or not to include names of nested elements.
	 *                  Defaults to true.
	 *
	 * @return     array An associative array of files.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function & getFiles($deep = true)
	{
		if($deep) {
			$retval = array();
			foreach($this->fileFieldNames as $name) {
				$retval[$name] = $this->getFile($name);
			}
			return $retval;
		} else {
			return $this->files;
		}
	}

	/**
	 * Retrieve a file path.
	 *
	 * @param      string A file name.
	 *
	 * @return     string A file path, if the file exists, otherwise null.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getFilePath($name)
	{
		$parts = AgaviArrayPathDefinition::getPartsFromPath($name);
		$retval = AgaviArrayPathDefinition::getValueFromArray(array_merge($parts['parts'], array('tmp_name')), $this->files, UPLOAD_ERR_NO_FILE);
		// this check must be performed. there could be a situation where the requested path was not complete, i.e. there are children left, and one of the children has the same name as the appended part fragment above
		if(!is_array($retval)) {
			return $retval;
		}
		return null;
	}

	/**
	 * Retrieve a file size.
	 *
	 * @param      string A file name.
	 *
	 * @return     int A file size, if the file exists, otherwise null.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getFileSize($name)
	{
		$parts = AgaviArrayPathDefinition::getPartsFromPath($name);
		$retval = AgaviArrayPathDefinition::getValueFromArray(array_merge($parts['parts'], array('size')), $this->files, UPLOAD_ERR_NO_FILE);
		// this check must be performed. there could be a situation where the requested path was not complete, i.e. there are children left, and one of the children has the same name as the appended part fragment above
		if(!is_array($retval)) {
			return $retval;
		}
		return null;
	}

	/**
	 * Retrieve a file type.
	 *
	 * This may not be accurate. This is the mime-type sent by the browser
	 * during the upload.
	 *
	 * @param      string A file name.
	 *
	 * @return     string A file type, if the file exists, otherwise null.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getFileType($name)
	{
		$parts = AgaviArrayPathDefinition::getPartsFromPath($name);
		$retval = AgaviArrayPathDefinition::getValueFromArray(array_merge($parts['parts'], array('type')), $this->files, UPLOAD_ERR_NO_FILE);
		// this check must be performed. there could be a situation where the requested path was not complete, i.e. there are children left, and one of the children has the same name as the appended part fragment above
		if(!is_array($retval)) {
			return $retval;
		}
		return null;
	}

	/**
	 * Indicates whether or not a file exists.
	 *
	 * @param      string A file name.
	 *
	 * @return     bool true, if the file exists, otherwise false.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function hasFile($name)
	{
		$parts = AgaviArrayPathDefinition::getPartsFromPath($name);
		// this check is correct, we must make sure the file, and not a "subkey" of it, is requested
		$retval = AgaviArrayPathDefinition::hasValue(array_merge($parts['parts'], array('error')), $this->files);
		// this check must be performed. there could be a situation where the requested path was not complete, i.e. there are children left, and one of the children has the same name as the appended part fragment above
		if(!is_array($retval)) {
			return true;
		}
		return false;
	}

	/**
	 * Indicates whether or not a file error exists.
	 *
	 * @param      string A file name.
	 *
	 * @return     bool true, if the file error exists, otherwise false.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function hasFileError($name)
	{
		$parts = AgaviArrayPathDefinition::getPartsFromPath($name);
		$retval = AgaviArrayPathDefinition::getValueFromArray(array_merge($parts['parts'], array('error')), $this->files);
		// this check must be performed. there could be a situation where the requested path was not complete, i.e. there are children left, and one of the children has the same name as the appended part fragment above
		if($retval !== null && !is_array($retval)) {
			return $retval !== UPLOAD_ERR_OK;
		}
		return false;
	}

	/**
	 * Indicates whether or not any file errors occured.
	 *
	 * @return     bool true, if any file errors occured, otherwise false.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function hasFileErrors()
	{
		foreach($this->fileFieldNames as $name) {
			if($this->hasFileError($name)) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Indicates whether or not any files exist.
	 *
	 * @return     bool true, if any files exist, otherwise false.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.11.0
	 */
	public function hasFiles()
	{
		return count($this->files) > 0;
	}

	/**
	 * Removes file information for given file.
	 *
	 * @param      string A file name
	 *
	 * @return     array The old information array, if it was set.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function & removeFile($name)
	{
		$parts = AgaviArrayPathDefinition::getPartsFromPath($name);
		return AgaviArrayPathDefinition::unsetValue($parts['parts'], $this->files);
	}

	/**
	 * Set a file.
	 *
	 * If a file with the name already exists the value will be overridden.
	 *
	 * @param      string A file name.
	 * @param      mixed  A file information array.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function setFile($name, $value)
	{
		$this->files[$name] = $value;
	}

	/**
	 * Set an array of files.
	 *
	 * @param      array An associative array of files and their values.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function setFiles($files)
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
	 * Corrects the order of $_FILES for arrays of files.
	 * The cleaned up array is put into $this->files.
	 *
	 * @param      array Array of indices used during recursion, initially empty.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
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
		$sub = AgaviArrayPathDefinition::getValueFromArray($fromIndex, $input);
		$theIndices = array();
		foreach(array('name', 'type', 'size', 'tmp_name', 'error') as $name) {
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
					$this->fixFilesArray($toIndex);
				} else {
					foreach($theIndices as $name => $theIndex) {
						$data[$name] = AgaviArrayPathDefinition::getValueFromArray(array_merge($theIndex, array($key)), $input);
					}
					AgaviArrayPathDefinition::setValueFromArray($toIndex, $this->files, $data);
					$this->fileFieldNames[] = $toIndex[0] . '[' . join('][', array_slice($toIndex, 1)) . ']';
				}
			}
		} else {
			foreach($theIndices as $name => $theIndex) {
				$data[$name] = AgaviArrayPathDefinition::getValueFromArray($theIndex, $input);
			}
			AgaviArrayPathDefinition::setValueFromArray($index, $this->files, $data);
			$this->fileFieldNames[] = $index[0];
		}
	}
	
	/**
	 * Constructor
	 *
	 * @param      array An associative array of request data source names and
	 *                   data arrays.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function __construct(array $data)
	{
		$this->registerSource(self::SOURCE_COOKIES, $this->cookies);
		$this->registerSource(self::SOURCE_FILES, $this->files);
		$this->registerSource(self::SOURCE_HEADERS, $this->headers);
		
		// call the parent ctor which handles the actual loading of the data
		parent::__construct($data);
		
		// now fix the files array
		$this->fixFilesArray();
	}
	
	/**
	 * Merge in Cookies from another request data holder.
	 *
	 * @param      AgaviRequestDataHolder The other request data holder.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
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
	 * @author     David Zuelke <dz@bitxtender.com>
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
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function mergeHeaders(AgaviRequestDataHolder $other)
	{
		if($other instanceof AgaviIHeadersRequestDataHolder) {
			$this->setHeaders($other->getHeaders());
		}
	}
	
	/**
	 * Move an uploaded file.
	 *
	 * @param      string A file name.
	 * @param      string An absolute filesystem path to where you would like
	 *                    the file moved. This includes the new filename, too,
	 *                    since uploaded files are stored with random names.
	 * @param      int    The octal mode to use for the new file.
	 * @param      bool   Indicates that we should make the directory before
	 *                    moving the file.
	 * @param      int    The octal mode to use when creating the directory.
	 *
	 * @return     bool true, if the file was moved, false if it doesn't exist.
	 *
	 * @throws     AgaviFileException If a major error occurs while attempting
	 *                                to move the file.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function moveFile($name, $dest, $fileMode = 0666, $create = true, $dirMode = 0777)
	{
		if($this->hasFile($name) && !$this->hasFileError($name) && $this->getFileSize($name) > 0) {
			// get our directory path from the destination filename
			$directory = dirname($dest);
			if(!is_readable($directory)) {
				$fmode = 0777;
				if($create && !@mkdir($directory, $dirMode, true)) {
					// failed to create the directory
					$error = 'Failed to create file upload directory "%s"';
					$error = sprintf($error, $directory);
					throw new AgaviFileException($error);
				}
				
				// chmod the directory since it doesn't seem to work on
				// recursive paths
				if(!@chmod($directory, $dirMode)) {
					// couldn't chmod target dir
					$error = 'Failed to chmod file upload directory "%s" to mode %o';
					$error = sprintf($error, $directory, $dirMode);
					throw new AgaviFileException($error);
				}
			} elseif(!is_dir($directory)) {
				// the directory path exists but it's not a directory
				$error = 'File upload path "%s" exists, but is not a directory';
				$error = sprintf($error, $directory);
				throw new AgaviFileException($error);
			} elseif(!is_writable($directory)) {
				// the directory isn't writable
				$error = 'File upload path "%s" is not writable';
				$error = sprintf($error, $directory);
				throw new AgaviFileException($error);
			}
			
			$from = $this->getFile($name);
			if(isset($from['HTTP_PUT'])) {
				$moved = @rename($from['tmp_name'], $dest);
			} else {
				$moved = @move_uploaded_file($from['tmp_name'], $dest);
			}
			
			if($moved) {
				// chmod our file
				if(!@chmod($dest, $fileMode)) {
					throw new AgaviFileException('Failed to chmod uploaded file after moving');
				}
			} else {
				// moving the file failed
				throw new AgaviFileException('Failed to move uploaded file');
			}
			
			return true;
		}
		return false;
	}
}

?>
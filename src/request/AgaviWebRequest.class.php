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
 * AgaviWebRequest provides additional support for web-only client requests 
 * such as cookie and file manipulation.
 *
 * @package    agavi
 * @subpackage request
 *
 * @author     Sean Kerr <skerr@mojavi.org>
 * @author     Veikko Makinen <mail@veikkomakinen.com>
 * @copyright  (c) Authors
 * @since      0.9.0
 *
 * @version    $Id$
 */
class AgaviWebRequest extends AgaviRequest
{
	/**
	 * Retrieve the scheme part of a request URL, typically the protocol.
	 * Example: "http".
	 *
	 * @return     string The request URL scheme.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getUrlScheme()
	{
		return 'http' . (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on' ? 's' : '');
	}
	
	/**
	 * Retrieve the request URL authority, typically host and port.
	 * Example: "foo.example.com:8080".
	 *
	 * @param      bool Whether or not ports 80 (for HTTP) and 433 (for HTTPS)
	 *                  should be included in the return string.
	 *
	 * @return     string The request URL authority.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getUrlAuthority($forcePort = false)
	{
		return
			preg_replace('/\:' . preg_quote($_SERVER['SERVER_PORT']) . '$/', '', $_SERVER['SERVER_NAME']) .
			
			($forcePort == true
				? (':' . $_SERVER['SERVER_PORT'])
				: ($this->getUrlScheme() == 'https'
					? ($_SERVER['SERVER_PORT'] != 443
						? ':' . $_SERVER['SERVER_PORT']
						: '')
					: ($_SERVER['SERVER_PORT'] != 80
						? ':' . $_SERVER['SERVER_PORT']
						: '')
				)
			);
	}
	
	/**
	 * Retrieve the relative part of the request URL, i.e. path and query.
	 * Example: "/foo/bar/baz?id=4815162342".
	 *
	 * @return     string The relative URL of the curent request.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getRelativeUrl()
	{
		if(isset($_SERVER['HTTP_X_REWRITE_URL']) && strpos($_SERVER['SERVER_SOFTWARE'], 'Mircosoft-IIS') !== false) {
			// Microsoft IIS with ISAPI_Rewrite
			return $_SERVER['HTTP_X_REWRITE_URL'];
		} elseif(isset($_SERVER['ORIG_PATH_INFO']) && strpos($_SERVER['SERVER_SOFTWARE'], 'Mircosoft-IIS') !== false) {
			// Microsoft IIS
			return $_SERVER['ORIG_PATH_INFO'];
		} else {
			// Apache
			return $_SERVER['REQUEST_URI'];
		}
	}
	
	/**
	 * Retrieve the path part of the URL.
	 * Example: "/foo/bar/baz".
	 *
	 * @return     string The path part of the URL.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getUrlPath()
	{
		$ru = $this->getRelativeUrl();
		$pos = strpos($ru, '?');
		if($pos !== false) {
			return substr($ru, 0, $pos);
		} else {
			return $ru;
		}
	}
	
	/**
	 * Retrieve the query part of the URL.
	 * Example: "id=4815162342".
	 *
	 * @return     string The query part of the URL, or an empty string.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getUrlQuery()
	{
		$ru = $this->getRelativeUrl();
		$pos = strpos($ru, '?');
		if($pos !== false) {
			return substr($ru, $pos + 1);
		} else {
			return '';
		}
	}
	
	/**
	 * Retrieve the full request URL, including protocol, server name, port (if
	 * necessary), and request URI.
	 * Example: "http://foo.example.com:8080/foo/bar/baz?id=4815162342".
	 *
	 * @return     string The URL of the curent request.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getUrl()
	{
		$query = $this->getUrlQuery();
		return 
			$this->getUrlScheme() . '://' . 
			$this->getUrlAuthority() . 
			$this->getRelativeUrl();
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
		return isset($_COOKIE[$name]);
	}

	/**
	 * Retrieve a value stored into a cookie.
	 *
	 * @param      string A cookie name.
	 * @param      mixed A default value.
	 *
	 * @return     mixed The value from the cookie, if such a cookie exists,
	 *                   otherwise null.
	 *
	 * @author     Veikko Makinen <mail@veikkomakinen.com>
	 * @since      0.10.0
	 */
	public function getCookie($name, $default=null)
	{
		$retval = $default;
		
		if(isset($_COOKIE[$name])) {
			$retval = $_COOKIE[$name];
		}
		
		return $retval;
	}

	/**
	 * Retrieve an array of file information.
	 *
	 * @param      string A file name
	 *
	 * @return     array An associative array of file information, if the file
	 *                   exists, otherwise null.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function getFile($name)
	{
		if(isset($_FILES[$name])) {
			return $_FILES[$name];
		}
		
		return null;
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
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function getFileError($name)
	{
		if(isset($_FILES[$name])) {
			return $_FILES[$name]['error'];
		}
		
		return UPLOAD_ERR_NO_FILE;
	}

	/**
	 * Retrieve a file name.
	 *
	 * @param      string A file name.
	 *
	 * @return     string A file name, if the file exists, otherwise null.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function getFileName($name)
	{
		if(isset($_FILES[$name])) {
			return $_FILES[$name]['name'];
		}
		
		return null;
	}

	/**
	 * Retrieve an array of file names.
	 *
	 * @return     array An indexed array of file names.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function getFileNames()
	{
		return array_keys($_FILES);
	}

	/**
	 * Retrieve an array of files.
	 *
	 * @return     array An associative array of files.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function getFiles()
	{
		return $_FILES;
	}

	/**
	 * Retrieve a file path.
	 *
	 * @param      string A file name.
	 *
	 * @return     string A file path, if the file exists, otherwise null.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function getFilePath($name)
	{
		if(isset($_FILES[$name])) {
			return $_FILES[$name]['tmp_name'];
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
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function getFileSize($name)
	{
		if(isset($_FILES[$name])) {
			return $_FILES[$name]['size'];
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
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function getFileType($name)
	{
		if(isset($_FILES[$name])) {
			return $_FILES[$name]['type'];
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
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function hasFile($name)
	{
		return isset($_FILES[$name]);
	}

	/**
	 * Indicates whether or not a file error exists.
	 *
	 * @param      string A file name.
	 *
	 * @return     bool true, if the file error exists, otherwise false.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function hasFileError($name)
	{
		if(isset($_FILES[$name])) {
			return ($_FILES[$name]['error'] != UPLOAD_ERR_OK);
		}
		
		return false;
	}

	/**
	 * Indicates whether or not any file errors occured.
	 *
	 * @return     bool true, if any file errors occured, otherwise false.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function hasFileErrors()
	{
		foreach($_FILES as &$file) {
			if($file['error'] != UPLOAD_ERR_OK) {
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
	 * @since      0.9.0
	 */
	public function hasFiles()
	{
		return (count($_FILES) > 0);
	}

	/**
	 * Initialize this Request.
	 *
	 * @param      AgaviContext An AgaviContext instance.
	 * @param      array        An associative array of initialization parameters.
	 *
	 * @throws     <b>AgaviInitializationException</b> If an error occurs while
	 *                                                 initializing this Request.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     Veikko Makinen <mail@veikkomakinen.com>
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.9.0
	 */
	public function initialize(AgaviContext $context, $parameters = array())
	{
		parent::initialize($context, $parameters);
		
		$methods = array('GET' => 'read', 'POST' => 'write');
		if(isset($parameters['method_names'])) {
			$methods = array_merge($methods, (array) $parameters['method_names']);
		}
		
		if(isset($_SERVER['REQUEST_METHOD'])) {
			switch($_SERVER['REQUEST_METHOD']) {
				case 'GET':
					$this->setMethod($methods['GET']);
					break;
				case 'POST':
					$this->setMethod($methods['POST']);
					break;
				default:
					$this->setMethod($methods['GET']);
			}
		} else {
			// set the default method
			$this->setMethod($methods['GET']);
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
	 * @return     bool true, if the file was moved, otherwise false.
	 *
	 * @throws     AgaviFileException If a major error occurs while attempting
	 *                                to move the file.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function moveFile($name, $file, $fileMode = 0666, $create = true, $dirMode = 0777)
	{
		if(isset($_FILES[$name]) && $_FILES[$name]['error'] == UPLOAD_ERR_OK && $_FILES[$name]['size'] > 0) {
			// get our directory path from the destination filename
			$directory = dirname($file);
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
				@chmod($directory, $dirMode);
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

			if(@move_uploaded_file($_FILES[$name]['tmp_name'], $file)) {
				// chmod our file
				@chmod($file, $fileMode);
				
				return true;
			}
		}
		return false;
	}
}

?>
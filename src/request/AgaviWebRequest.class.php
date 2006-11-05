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
	 * @var        string The current URL scheme.
	 */
	protected $urlScheme = '';

	/**
	 * @var        string The current URL authority.
	 */
	protected $urlHost = '';

	/**
	 * @var        string The current URL authority.
	 */
	protected $urlPort = 0;

	/**
	 * @var        string The current URL path.
	 */
	protected $urlPath = '';

	/**
	 * @var        string The current URL query.
	 */
	protected $urlQuery = '';

	/**
	 * @var        string The current request URL (path and query).
	 */
	protected $requestUri = '';

	/**
	 * @var        string The current URL.
	 */
	protected $url = '';

	/**
	 * @var        bool Indicates whether or not PUT was used to upload a file.
	 */
	protected $isHttpPutFile = false;

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
		return $this->urlScheme;
	}
	
	/**
	 * Retrieve the hostname part of a request URL.
	 *
	 * @return     string The request URL hostname.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getUrlHost()
	{
		return $this->urlHost;
	}
	
	/**
	 * Retrieve the hostname part of a request URL.
	 *
	 * @return     string The request URL hostname.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getUrlPort()
	{
		return $this->urlPort;
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
		$port = $this->getUrlPort();
		$scheme = $this->getUrlScheme();
		return $this->getUrlHost() . ($forcePort || ($scheme == 'https' && $port != 443) || ($scheme == 'http' && $port != 80) ? ':' . $port : '');
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
	public function getRequestUri()
	{
		return $this->requestUri;
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
		return $this->urlPath;
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
		return $this->urlQuery;
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
		return 
			$this->getUrlScheme() . '://' . 
			$this->getUrlAuthority() . 
			$this->getRequestUri();
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
		foreach($_FILES as $file) {
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
	public function initialize(AgaviContext $context, array $parameters = array())
	{
		parent::initialize($context, $parameters);
		
		$methods = array('GET' => 'read', 'POST' => 'write', 'PUT' => 'create', 'DELETE' => 'remove');
		if(isset($parameters['method_names'])) {
			$methods = array_merge($methods, (array) $parameters['method_names']);
		}
		
		switch(isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET') {
			case 'POST':
				$this->setMethod($methods['POST']);
				break;
			case 'PUT':
				$this->setMethod($methods['PUT']);
				break;
			case 'DELETE':
				$this->setMethod($methods['DELETE']);
				break;
			default:
				$this->setMethod($methods['GET']);
		}
		
		if($this->getMethod() == $methods['PUT']) {
			// PUT. We now gotta set a flag for that and populate $_FILES manually
			$this->isHttpPutFile = true;
			
			$putfile = tmpfile();
			
			stream_copy_to_stream(fopen("php://input", "rb"), $putFile);
			
			// for temp file name and size
			$putFileInfo = array(
				'stat' => fstat($putFile),
				'meta_data' => stream_get_meta_data($putFile)
			);
			
			$putFileName = isset($parameters['PUT_file_name']) ? $parameters['PUT_file_name'] : 'put_file';
			
			$_FILES = array(
				$putFileName => array(
					'name' => $putFileName,
					'type' => 'application/octet-stream',
					'size' => $putFileInfo['stat']['size'],
					'tmp_name' => $putFileInfo['meta_data']['uri'],
					'error' => UPLOAD_ERR_OK
				)
			);
		}
		
		$this->urlScheme = 'http' . (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on' ? 's' : '');

		if(isset($_SERVER['SERVER_PORT'])) {
			$this->urlPort = intval($_SERVER['SERVER_PORT']);
		}

		if(isset($_SERVER['SERVER_NAME'])) {
			$port = $this->getUrlPort();
			if(preg_match_all('/\:/', preg_quote($_SERVER['SERVER_NAME']), $m) > 1) {
				$this->urlHost = preg_replace('/\]\:' . preg_quote($port) . '$/', '', $_SERVER['SERVER_NAME']);
			} else {
				$this->urlHost = preg_replace('/\:' . preg_quote($port) . '$/', '', $_SERVER['SERVER_NAME']);
			}
		}

		if(isset($_SERVER['HTTP_X_REWRITE_URL'])) {
			// Microsoft IIS with ISAPI_Rewrite
			$this->requestUri = $_SERVER['HTTP_X_REWRITE_URL'];
		} elseif(!isset($_SERVER['REQUEST_URI']) && isset($_SERVER['SERVER_SOFTWARE']) && strpos($_SERVER['SERVER_SOFTWARE'], 'Microsoft-IIS') !== false) {
			// Microsoft IIS with PHP in CGI mode
			$this->requestUri = $_SERVER['ORIG_PATH_INFO'] . (isset($_SERVER['QUERY_STRING']) && strlen($_SERVER['QUERY_STRING']) > 0 ? '?' . $_SERVER['QUERY_STRING'] : '');
		} elseif(isset($_SERVER['REQUEST_URI'])) {
			$this->requestUri = $_SERVER['REQUEST_URI'];
		}

		// Microsoft IIS with PHP in CGI mode
		if(!isset($_SERVER['QUERY_STRING'])) {
			$_SERVER['QUERY_STRING'] = '';
		}
		if(!isset($_SERVER['REQUEST_URI'])) {
			$_SERVER['REQUEST_URI'] = $this->getRequestUri();
		}

		$parts = array_merge(array('path' => '', 'query' => ''), parse_url($this->getRequestUri()));
		$this->urlPath = $parts['path'];
		$this->urlQuery = $parts['query'];
		unset($parts);
		
		// merge GET parameters
		$this->setParameters($_GET);
		// merge POST parameters
		$this->setParameters($_POST);
	}
	
	/**
	 * Wrapper method for either move_uplaoded_file or HTTP PUT input handling.
	 *
	 * @param      string The name of the input file.
	 * @param      string The name of the destination file.
	 *
	 * @return     bool Whether or not the operation was successful.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	protected function moveUploadedFile($source, $destination)
	{
		if($this->isHttpPutFile) {
			return @rename($source, $destination);
		} else {
			return @move_uploaded_file($source, $destination);
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

			if($this->moveUploadedFile($_FILES[$name]['tmp_name'], $file)) {
				// chmod our file
				@chmod($file, $fileMode);
				
				return true;
			}
		}
		return false;
	}
}

?>
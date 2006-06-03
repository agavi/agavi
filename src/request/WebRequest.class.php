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

	// +-----------------------------------------------------------------------+
	// | PROTECTED VARIABLES                                                   |
	// +-----------------------------------------------------------------------+

	protected
		$cookieConfig = null;

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

		if (isset($_COOKIE[$name])) {
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
	public function getFile ($name)
	{

		if (isset($_FILES[$name]))
		{

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
	public function getFileError ($name)
	{

		if (isset($_FILES[$name]))
		{

			return $_FILES[$name]['error'];

		}

		return $retval;

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
	public function getFileName ($name)
	{

		if (isset($_FILES[$name]))
		{

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
	public function getFileNames ()
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
	public function getFiles ()
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
	public function getFilePath ($name)
	{

		if (isset($_FILES[$name]))
		{

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
	public function getFileSize ($name)
	{

		if (isset($_FILES[$name]))
		{

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
	public function getFileType ($name)
	{

		if (isset($_FILES[$name]))
		{

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
	public function hasFile ($name)
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
	public function hasFileError ($name)
	{

		if (isset($_FILES[$name]))
		{

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
	public function hasFileErrors ()
	{

		foreach ($_FILES as &$file)
		{

			if ($file['error'] != UPLOAD_ERR_OK)
			{

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
	public function hasFiles ()
	{

		return (count($_FILES) > 0);

	}

	/**
	 * Initialize this Request.
	 *
	 * @param      AgaviContext A Context instance.
	 * @param      array        An associative array of initialization parameters.
	 *
	 * @return     bool true, if initialization completes successfully,
	 *                  otherwise false.
	 *
	 * @throws     <b>AgaviInitializationException</b> If an error occurs while
	 *                                                 initializing this Request.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     Veikko Makinen <mail@veikkomakinen.com>
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.9.0
	 */
	public function initialize ($context, $parameters = null)
	{
		parent::initialize($context, $parameters);
		
		$getMethod = isset($parameters['GET_method_name']) ? $parameters['GET_method_name'] : 'read';
		if(isset($_SERVER['REQUEST_METHOD'])) {
			switch($_SERVER['REQUEST_METHOD']) {
				case 'GET':
				    $this->setMethod($getMethod);
				    break;
				case 'POST':
				    $this->setMethod(isset($parameters['POST_method_name']) ? $parameters['POST_method_name'] : 'write');
				    break;
				default:
				    $this->setMethod($getMethod);
			}
		} else {
			// set the default method
			$this->setMethod($getMethod);
		}
		// load parameters from GET/PATH_INFO/POST
		$this->loadParameters();
	}

	/**
	 * Loads GET, PATH_INFO and POST data into the parameter list.
	 *
	 * @return     void
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.9.0
	 */
	private function loadParameters()
	{
		// merge GET parameters
		$this->setParametersByRef($_GET);

		// merge POST parameters
		$this->setParametersByRef($_POST);
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
	public function moveFile ($name, $file, $fileMode = 0666, $create = true,
						      $dirMode = 0777)
	{

		if (isset($_FILES[$name]) && $_FILES[$name]['error'] == UPLOAD_ERR_OK &&
			$_FILES[$name]['size'] > 0)
		{

			// get our directory path from the destination filename
			$directory = dirname($file);

			if (!is_readable($directory))
			{

				$fmode = 0777;

				if ($create && !@mkdir($directory, $dirMode, true))
				{

				    // failed to create the directory
				    $error = 'Failed to create file upload directory "%s"';
				    $error = sprintf($error, $directory);

				    throw new AgaviFileException($error);

				}

				// chmod the directory since it doesn't seem to work on
				// recursive paths
				@chmod($directory, $dirMode);

			} else if (!is_dir($directory))
			{

				// the directory path exists but it's not a directory
				$error = 'File upload path "%s" exists, but is not a directory';
				$error = sprintf($error, $directory);

				throw new AgaviFileException($error);

			} else if (!is_writable($directory))
			{

				// the directory isn't writable
				$error = 'File upload path "%s" is not writable';
				$error = sprintf($error, $directory);

				throw new AgaviFileException($error);

			}

			if (@move_uploaded_file($_FILES[$name]['tmp_name'], $file))
			{

				// chmod our file
				@chmod($file, $fileMode);

				return true;

			}

		}

		return false;

	}

	/**
	 * Execute the shutdown procedure.
	 *
	 * @return     void
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function shutdown ()
	{

		// nothing to do here

	}

}

?>
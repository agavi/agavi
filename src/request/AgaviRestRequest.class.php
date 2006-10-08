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
 * AgaviRestRequest is an implementation for handling RESTful requests.
 *
 * @package    agavi
 * @subpackage request
 *
 * @author     David Zuelke <dz@bitxtender.com>
 * @copyright  (c) Authors
 * @since      0.11.0
 *
 * @version    $Id: AgaviWebRequest.class.php 1095 2006-10-07 15:53:10Z david $
 */
class AgaviRestRequest extends AgaviWebRequest
{
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
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
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
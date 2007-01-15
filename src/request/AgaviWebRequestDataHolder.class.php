
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
class AgaviWebRequestDataHolder extends AgaviRequestDataHolder
{
	const COOKIE = 'cookie';
	const FILE = 'file';

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
	 * Retrieve an array of file information.
	 *
	 * @param      string A file name
	 *
	 * @return     array An associative array of file information, if the file
	 *                   exists, otherwise null.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function & getFile($name)
	{
		$parts = AgaviArrayPathDefinition::getPartsFromPath($name);
		$retval = AgaviArrayPathDefinition::getValueFromArray($parts['parts'], $this->files);
		// check if it's a file (i.e. array with name, tmp name, size etc is there)
		if(is_array($retval)) {
			return $retval;
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
	 * Corrects the order of $_FILES for arrays of files.
	 * The cleaned up array is put into $this->files.
	 *
	 * @param      array Array of indices used during recursion, initially empty.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	protected function fixFilesArray($index = array())
	{
		$fromIndex = $index;
		if(count($fromIndex) > 0) {
			$first = array_shift($fromIndex);
			array_unshift($fromIndex, $first, 'error');
		}
		$sub = AgaviArrayPathDefinition::getValueFromArray($fromIndex, $_FILES);
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
						$data[$name] = AgaviArrayPathDefinition::getValueFromArray(array_merge($theIndex, array($key)), $_FILES);
					}
					AgaviArrayPathDefinition::setValueFromArray($toIndex, $this->files, $data);
					$this->fileFieldNames[] = $toIndex[0] . '[' . join('][', array_slice($toIndex, 1)) . ']';
				}
			}
		} else {
			foreach($theIndices as $name => $theIndex) {
				$data[$name] = AgaviArrayPathDefinition::getValueFromArray($theIndex, $_FILES);
			}
			AgaviArrayPathDefinition::setValueFromArray($index, $this->files, $data);
			$this->fileFieldNames[] = $index[0];
		}
	}
	
	/**
	 * Initialize this WebRequestDataHolder.
	 *
	 * @param      AgaviRequest An AgaviRequest instance.
	 * @param      array        An associative array of request parameters.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function initialize(AgaviRequest $request, array $parameters = array())
	{
		parent::initialize($request, $parameters);
		
		// TODO: maybe the request should store this ?
		$methods = array('GET' => 'read', 'POST' => 'write', 'PUT' => 'create', 'DELETE' => 'remove');
/*		if(isset($parameters['method_names'])) {
			$methods = array_merge($methods, (array) $parameters['method_names']);
		}
*/

		if($request->getMethod() == $methods['PUT']) {
			// PUT. We now gotta set a flag for that and populate $_FILES manually
			$this->isHttpPutFile = true;
			
			$putFile = tmpfile();
			
			stream_copy_to_stream(fopen("php://input", "rb"), $putFile);
			
			// for temp file name and size
			$putFileInfo = array(
				'stat' => fstat($putFile),
				'meta_data' => stream_get_meta_data($putFile)
			);
			
			$putFileName = $request->getParameter('PUT_file_name', 'put_file');
			
			$this->files = array(
				$putFileName => array(
					'name' => $putFileName,
					'type' => 'application/octet-stream',
					'size' => $putFileInfo['stat']['size'],
					'tmp_name' => $putFileInfo['meta_data']['uri'],
					'error' => UPLOAD_ERR_OK
				)
			);
		} else {
			$this->fixFilesArray();
		}

		// store the cookies so we wont change the global array when changing a 
		// cookie
		$this->cookies = $_COOKIE;
		
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
	 * @return     bool true, if the file was moved, false if it doesn't exist.
	 *
	 * @throws     AgaviFileException If a major error occurs while attempting
	 *                                to move the file.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function moveFile($name, $file, $fileMode = 0666, $create = true, $dirMode = 0777)
	{
		if($this->hasFile($name) && !$this->hasFileError($name) && $this->getFileSize($name) > 0) {
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

			if($this->moveUploadedFile($this->getFilePath($name), $file)) {
				// chmod our file
				if(!@chmod($file, $fileMode)) {
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
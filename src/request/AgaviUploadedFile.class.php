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
 * AgaviUploadedFile is a container with information for files that were
 * uploaded or submitted with the request.
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
class AgaviUploadedFile extends ArrayObject
{
	/**
	 * @var        array An array to map get* method name fragments to indices.
	 */
	protected static $indexMap = array(
		'Name' => 'name',
		'Type' => 'type',
		'Size' => 'size',
		'TmpName' => 'tmp_name',
		'Error' => 'error',
		'IsUploadedFile' => 'is_uploaded_file',
	);
	
	/**
	 * Constructor.
	 *
	 * @param      $flags int Flags, overridden to be ArrayObject::ARRAY_AS_PROPS.
	 *
	 * @see        ArrayObject::__construct()
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function __construct($array = array(), $flags = ArrayObject::ARRAY_AS_PROPS, $iteratorClass = 'ArrayIterator')
	{
		$defaults = array(
			'name' => null,
			'type' => null,
			'size' => 0,
			'tmp_name' => null,
			'error' => UPLOAD_ERR_NO_FILE,
			'is_uploaded_file' => true,
			'moved' => false,
		);
		parent::__construct(array_merge($defaults, $array), $flags, $iteratorClass);
	}
	
	/**
	 * Destructor. Removes the tempfile.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function __destruct()
	{
		// must use array syntax here, ArrayObject property access does not work in destructors
		if(!$this['moved'] && !$this['is_uploaded_file']) {
			@unlink($this['tmp_name']);
		}
	}
	
	/**
	 * Overload to handle getName() etc calls.
	 *
	 * @param      string The name of the method.
	 * @param      array  The method arguments.
	 *
	 * @return     string A value.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function __call($name, array $arguments)
	{
		if(substr($name, 0, 3) == 'get') {
			return $this[self::$indexMap[substr($name, 3)]];
		}
	}
	
	/**
	 * Check whether or not this file has an error.
	 *
	 * This only returns PHP's own information, not validator's.
	 *
	 * @return     bool True in case of UPLOAD_ERR_OK, false otherwise.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function hasError()
	{
		return $this->error !== UPLOAD_ERR_OK;
	}
	
	/**
	 * Whether or not this file is movable.
	 *
	 * @return     bool True if this file has not been moved yet, otherwise false.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function isMovable()
	{
		return !$this->moved;
	}
	
	/**
	 * Retrieve the contents of the uploaded file.
	 *
	 * @return     string The file contents.
	 *
	 * @throws     AgaviException If the file has errors or has been moved.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.2
	 */
	public function getContents()
	{
		if($this->hasError() || !$this->isMovable()) {
			throw new AgaviException('Cannot get contents of erroneous or moved file.');
		}
		
		return file_get_contents($this->tmp_name);
	}
	
	/**
	 * Retrieve a stream handle of the uploaded file.
	 *
	 * @param      string The fopen mode, defaults to 'rb'.
	 *
	 * @return     resource The stream.
	 *
	 * @throws     AgaviException If the file has errors or has been moved.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.2
	 */
	public function getStream($mode = 'rb')
	{
		if($this->hasError() || !$this->isMovable()) {
			throw new AgaviException('Cannot get contents of erroneous or moved file.');
		}
		
		return fopen($this->tmp_name, $mode);
	}
	
	/**
	 * Move the uploaded file.
	 *
	 * @param      string The destination filename.
	 * @param      int    The mode of the destination file, defaults to 0664.
	 * @param      bool   Whether or not subdirs should be created if necessary.
	 * @param      int    The mode to use when creating subdirs, defaults to 0775.
	 *
	 * @return     bool True, if the operation was successful, false otherwise.
	 *
	 * @throws     AgaviFileException If chmod or mkdir calls failed.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function move($dest, $fileMode = 0664, $create = true, $dirMode = 0775)
	{
		if($this->hasError()) {
			return false;
		} elseif(!$this->isMovable()) {
			return false;
		}
		
		// get our directory path from the destination filename
		$directory = dirname($dest);
		if(!is_readable($directory)) {
			if($create && !AgaviToolkit::mkdir($directory, $dirMode, true)) {
				// failed to create the directory
				$error = 'Failed to create file upload directory "%s"';
				$error = sprintf($error, $directory);
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
		
		if($this->is_uploaded_file) {
			$moved = @move_uploaded_file($this->tmp_name, $dest);
		} else {
			if(is_writable($dest)) {
				unlink($dest);
			}
			$moved = @rename($this->tmp_name, $dest);
		}
		
		if($moved) {
			$this->moved = true;
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
}

?>
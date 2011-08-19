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
class AgaviUploadedFile implements ArrayAccess
{
	/**
	 * @var        string The name of the file.
	 */
	protected $name;
	
	/**
	 * @var        string The type of the file.
	 */
	protected $type;
	
	/**
	 * @var        int The size of the file, in bytes.
	 */
	protected $size;
	
	/**
	 * @var        string A local path name to the file, if persisted to disk.
	 */
	protected $tmpName;
	
	/**
	 * @var        int An UPLOAD_ERR_* error code for the file upload.
	 */
	protected $error;
	
	/**
	 * @var        bool Whether or not this was a file uploaded via an HTML form.
	 */
	protected $isUploadedFile;
	
	/**
	 * @var        bool Whether or not this file has been moved already.
	 */
	protected $isMoved;
	
	/**
	 * @var        string A string of the raw binary contents of the file.
	 */
	protected $contents;
	
	/**
	 * @var        array An array to map get* method name fragments to indices.
	 */
	protected static $indexMap = array(
		'name' => 'name',
		'type' => 'type',
		'size' => 'size',
		'tmp_name' => 'tmpName',
		'error' => 'error',
		'is_uploaded_file' => 'isUploadedFile',
		'is_moved' => 'isMoved',
		'contents' => 'contents',
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
	public function __construct(array $array)
	{
		$defaults = array(
			'name' => null,
			'type' => null,
			'size' => 0,
			'tmp_name' => null,
			'error' => UPLOAD_ERR_NO_FILE,
			'is_uploaded_file' => true,
			'contents' => null,
		);
		$array = array_merge($defaults, $array, array('is_moved' => false)); // make sure it's marked not moved by default
		
		// we either need a tmp_name or contents
		if(
			(isset($array['tmp_name']) && isset($array['contents'])) ||
			(!isset($array['tmp_name']) && !isset($array['contents']))
		) {
			throw new InvalidArgumentException('Value for either key "tmp_name" or "contents" (but not both) must be supplied.');
		}
		
		// fill local props
		foreach(self::$indexMap as $index => $property) {
			if(isset($array[$index])) {
				$this->$property = $array[$index];
			}
		}
	}
	
	/**
	 * Destructor. Removes the tempfile.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function __destruct()
	{
		if(!$this->getIsMoved() && !$this->getIsUploadedFile() && $this->hasTmpName()) {
			@unlink($this->getTmpName());
		}
	}
	
	/**
	 * Property access overload.
	 *
	 * @param      string The key to fetch.
	 *
	 * @return     mixed The value of the key or null.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.1.0
	 */
	public function __get($key)
	{
		if($this->__isset($key)) {
			$method = 'get' . $key;
			return $this->$method();
		}
	}
	
	/**
	 * Property isset overload.
	 *
	 * @param      string The key to check.
	 *
	 * @return     bool Whether the given key exists.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.1.0
	 */
	public function __isset($key)
	{
		trigger_error('Property access in AgaviUploadedFile is deprecated and will be removed in Agavi 1.2. Please use getter methods or array access instead.', E_USER_DEPRECATED);
		return in_array($key, array('name', 'type', 'size', 'tmpName', 'error', 'isUploadedFile')) && isset($this->$key);
	}
	
	/**
	 * Array access: existence check.
	 *
	 * @param      string The key to check.
	 *
	 * @return     bool Whether or not the key exists.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.1.0
	 */
	public function offsetExists($key)
	{
		if(in_array($key, array('name', 'type', 'size', 'tmp_name', 'error', 'is_uploaded_file'))) {
			$property = self::$indexMap[$key];
			return isset($this->$property);
		}
		return false;
	}
	
	/**
	 * Array access: fetch value.
	 *
	 * @param      string The key of the value to fetch.
	 *
	 * @return     mixed The key for the given value.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.1.0
	 */
	public function offsetGet($key)
	{
		if($this->offsetExists($key)) {
			$method = 'get' . self::$indexMap[$key];
			return $this->$method();
		}
	}
	
	/**
	 * Array access: set value.
	 *
	 * @param      string The key to set.
	 * @param      string The value to set.
	 *
	 * @throws     BadMethodCallException AgaviUploadedFile objects are immutable.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.1.0
	 */
	public function offsetSet($key, $value)
	{
		throw new BadMethodCallException('AgaviUploadedFile objects are immutable.');
	}
	
	/**
	 * Array access: unset value.
	 *
	 * @param      string The key to unset.
	 *
	 * @throws     BadMethodCallException AgaviUploadedFile objects are immutable.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.1.0
	 */
	public function offsetUnset($key)
	{
		throw new BadMethodCallException('AgaviUploadedFile objects are immutable.');
	}
	
	/**
	 * Get the name of the file as submitted by the client.
	 *
	 * @return     string The file name as submitted by the client.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.1.0
	 */
	public function getName()
	{
		return $this->name;
	}
	
	/**
	 * Get the type of the file as submitted by the client.
	 *
	 * @return     string The file type as submitted by the client.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.1.0
	 */
	public function getType()
	{
		return $this->type;
	}
	
	/**
	 * Get the size of the file.
	 *
	 * @return     int The length of the file in bytes.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.1.0
	 */
	public function getSize()
	{
		return $this->size;
	}
	
	/**
	 * Get the temporary filename of this file.
	 *
	 * @return     string The temporary filename for this file.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.1.0
	 */
	public function getTmpName()
	{
		if(!$this->hasTmpName()) {
			// have faith in the ctor :)
			$this->tmpName = tempnam(AgaviConfig::get('core.cache_dir'), 'AgaviUploadedFile_');
			if(!is_writable($this->tmpName)) {
				$error = 'Temporary file path "%s" is not writable';
				$error = sprintf($error, $directory);
				throw new AgaviFileException($error);
			}
			file_put_contents($this->tmpName, $this->contents);
		}
		
		return $this->tmpName;
	}
	
	/**
	 * Check if this uploaded file has a temporary filename.
	 * If a file has no temp name, then it means that this object was constructed
	 * internally using the file contents rather than by PHP's upload handler.
	 * On calling getTmpName(), the contents will be flushed to disk so access
	 * using file methods is possible.
	 *
	 * @return     bool Whether or not the file contents are on disk yet.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.1.0
	 */
	public function hasTmpName()
	{
		return isset($this->tmpName);
	}
	
	/**
	 * Get the error code for this uploaded file.
	 *
	 * @return     int One of PHP's UPLOAD_ERR_* constants.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.1.0
	 */
	public function getError()
	{
		return $this->error;
	}
	
	/**
	 * Check if this file is a multipart/form-data upload handled by PHP itself,
	 * or another type of file submission handled by Agavi.
	 *
	 * @return     bool Whether or not this file was uploaded through a web form.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.1.0
	 */
	public function getIsUploadedFile()
	{
		return $this->isUploadedFile;
	}
	
	/**
	 * Check if this file has been moved from it's temporary location.
	 *
	 * @return     bool Whether or not the temporary file has been moved yet.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.1.0
	 */
	public function getIsMoved()
	{
		return $this->isMoved;
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
		return $this->getError() !== UPLOAD_ERR_OK;
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
		return !$this->getIsMoved();
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
		// for a file where we wrote the contents to a temp file in getTmpName(), we can always return contents
		if($this->hasError() || (!$this->isMovable() && !$this->hasBufferedContents())) {
			throw new AgaviException('Cannot get contents of erroneous or moved file.');
		}
		
		// we intentionally don't store the result of file_get_contents() here to keep memory usage low
		return $this->hasBufferedContents() ? $this->contents : file_get_contents($this->getTmpName());
	}
	
	/**
	 * Check if this uploaded file object already has the file contents buffered.
	 * If this method returns false, the getContents() method will still attempt
	 * to read the file from the temporary location on disk.
	 *
	 * @return     bool Whether or not the file contents are on disk yet.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.1.0
	 */
	public function hasBufferedContents()
	{
		return isset($this->contents);
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
		
		return fopen($this->getTmpName(), $mode);
	}
	
	/**
	 * Return the MIME type of this file using the fileinfo extension.
	 *
	 * @param      bool Whether to return the charset of the file as well.
	 *
	 * @return     string The MIME type of the file.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.1.0
	 */
	public function getMimeType($charset = false)
	{
		$finfo = new finfo($charset ? FILEINFO_MIME : FILEINFO_MIME_TYPE);
		// don't use finfo_file() to avoid unnecessary hits to disk in case it's not an uploaded file
		return $finfo->buffer($this->getContents());
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
		
		if($this->getIsUploadedFile()) {
			$moved = @move_uploaded_file($this->getTmpName(), $dest);
		} else {
			if(is_writable($dest)) {
				unlink($dest);
			}
			$moved = @rename($this->getTmpName(), $dest);
		}
		
		if($moved) {
			$this->isMoved = true;
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
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
 * AgaviUploadedFile is a container with information for files that were
 * uploaded or submitted with the request.
 *
 * @package    agavi
 * @subpackage request
 *
 * @author     David Zuelke <dz@bitxtender.com>
 * @copyright  (c) Authors
 * @since      0.11.0
 *
 * @version    $Id$
 */
final class AgaviUploadedFile extends ArrayObject
{
	protected static $indexMap = array(
		'Name' => 'name',
		'Type' => 'type',
		'TmpName' => 'tmp_name',
		'Error' => 'error',
		'IsUploadedFile' => 'is_uploaded_file',
	);
	
	public function __construct($array = array(), $flags = ArrayObject::ARRAY_AS_PROPS, $iteratorClass = 'ArrayIterator')
	{
		$defaults = array(
			'name' => null,
			'type' => null,
			'size' => 0,
			'tmp_name' => null,
			'error' => UPLOAD_ERR_NO_FILE,
			'is_uploaded_file' => true
		);
		parent::__construct(array_merge($defaults, $array), $flags, $iteratorClass);
	}
	
	public function __call($name, array $arguments)
	{
		if(substr($name, 0, 3) == 'get') {
			return $this[self::$indexMap[substr($name, 3)]];
		}
	}
	
	public function hasError()
	{
		return $this->error !== UPLOAD_ERR_OK;
	}
	
	public function move($dest, $fileMode = 0666, $create = true, $dirMode = 0777)
	{
		if(!$this->hasError() && $this->size > 0) {
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
			
			if($this->is_uploaded_file) {
				$moved = @move_uploaded_file($this->tmp_name, $dest);
			} else {
				$moved = @rename($this->tmp_name, $dest);
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
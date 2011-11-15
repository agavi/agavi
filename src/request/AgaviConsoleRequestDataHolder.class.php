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
 * AgaviConsoleRequestDataHolder provides methods for retrieving client request
 * information parameters.
 *
 * @package    agavi
 * @subpackage request
 *
 * @author     David Zülke <david.zuelke@bitextender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      1.0.0
 *
 * @version    $Id$
 */
class AgaviConsoleRequestDataHolder extends AgaviRequestDataHolder implements AgaviIFilesRequestDataHolder
{
	/**
	 * @constant   Constant for source name of files.
	 */
	const SOURCE_FILES = 'files';
	
	/**
	 * @var        array An array of files uploaded during the request.
	 */
	protected $files = array();

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
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.0
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
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.0
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
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.0
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
	 * @since      1.0.0
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
	 * @since      1.0.0
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
	 * @since      1.0.0
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
	 * @since      1.0.0
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
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.0
	 */
	public function setFiles(array $files)
	{
		$this->files = array_merge($this->files, $files);
	}

	/**
	 * Clear all files.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.0
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
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.0
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
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.0
	 */
	public function getFlatFileNames()
	{
		return AgaviArrayPathDefinition::getFlatKeyNames($this->files);
	}
	
	/**
	 * Constructor
	 *
	 * @param      array An associative array of request data source names and
	 *                   data arrays.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.0
	 */
	public function __construct(array $data = array())
	{
		$this->registerSource(self::SOURCE_FILES, $this->files);
		
		// call the parent ctor which handles the actual loading of the data
		parent::__construct($data);
	}
	
	/**
	 * Merge in Files from another request data holder.
	 *
	 * @param      AgaviRequestDataHolder The other request data holder.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.0
	 */
	public function mergeFiles(AgaviRequestDataHolder $other)
	{
		if($other instanceof AgaviIFilesRequestDataHolder) {
			$this->setFiles($other->getFiles());
		}
	}
}

?>
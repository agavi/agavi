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

require_once(dirname(__FILE__) . '/AgaviTask.php');

/**
 * Determines whether a file is available on the filesystem.
 *
 * @package    agavi
 * @subpackage build
 *
 * @author     Noah Fontes <noah.fontes@bitextender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      1.0.0
 *
 * @version    $Id$
 */
class AgaviAvailableTask extends AgaviTask
{
	const TYPE_ANY = 1;
	const TYPE_FILE = 2;
	const TYPE_DIRECTORY = 3;
	
	protected $property = null;
	protected $file = null;
	protected $value = true;
	protected $type = self::TYPE_ANY;
	
	/**
	 * Sets the property that this task will modify.
	 *
	 * @param      string The name of the property.
	 */
	public function setProperty($property)
	{
		$this->property = $property;
	}
	
	/**
	 * Sets the file to find.
	 *
	 * @param      PhingFile The file.
	 */
	public function setFile(PhingFile $file)
	{
		$this->file = $file;
	}
	
	/**
	 * Sets the value to which the property will be set if the condition is
	 * successfully evaluated.
	 *
	 * @param      string The value.
	 */
	public function setValue($value)
	{
		$this->value = $value;
	}
	
	/**
	 * Sets the type of the file.
	 *
	 * @param      string One of <code>file</code> or <code>directory</code>.
	 */
	public function setType($type)
	{
		switch($type) {
		case 'any':
			$this->type = self::TYPE_ANY;
			break;
		case 'file':
			$this->type = self::TYPE_FILE;
			break;
		case 'directory':
			$this->type = self::TYPE_DIRECTORY;
			break;
		default:
			throw new BuildException('The type attribute must be one of {any, file, directory}');
		}
	}
	
	/**
	 * Executes this task.
	 */
	public function main()
	{
		if($this->property === null) {
			throw new BuildException('The property attribute must be specified');
		}
		if($this->file === null) {
			throw new BuildException('The file attribute must be specified');
		}
		
		if($this->evaluate()) {
			if($this->value !== null) {
				$this->project->setUserProperty($this->property, $this->value);
			}
		} else {
			/* Unset. */
			$this->project->setUserProperty($this->property, null);
		}
	}
	
	/**
	 * Determines whether the file successfully meets the specified criteria.
	 */
	protected function evaluate()
	{
		switch($this->type) {
		case self::TYPE_ANY:
			return $this->file->exists();
		case self::TYPE_FILE:
			return $this->file->isFile();
		case self::TYPE_DIRECTORY:
			return $this->file->isDirectory();
		}
	}
}

?>
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
 * Validates that a given directory is the base directory for a project.
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
class AgaviCheckprojectTask extends AgaviTask
{
	protected $property = null;
	protected $path = null;
	protected $value = true;
	
	/**
	 * Sets the property that this task will modify.
	 *
	 * @param      string The property to modify.
	 */
	public function setProperty($property)
	{
		$this->property = $property;
	}
	
	/**
	 * Sets the path to use to validate the project.
	 *
	 * @param      string The path to use.
	 */
	public function setPath(PhingFile $path)
	{
		$this->path = $path;
	}
	
	/**
	 * Sets the value that the property will contain if the project is
	 * valid.
	 *
	 * @param      string The value to which the property will be set.
	 */
	public function setValue($value)
	{
		$this->value = $value;
	}

	/**
	 * Executes this target.
	 */
	public function main()
	{
		if($this->property === null) {
			throw new BuildException('The property attribute must be specified');
		}
		if($this->path === null) {
			throw new BuildException('The path attribute must be specified');
		}
		
		$check = new AgaviProjectFilesystemCheck();
		$check->setAppDirectory($this->project->getProperty('project.directory.app'));
		$check->setPubDirectory($this->project->getProperty('project.directory.pub'));
		
		$check->setPath($this->path->getAbsolutePath());
		if($check->check()) {
			$this->project->setUserProperty($this->property, $this->value);
		} else {
			$this->project->setUserProperty($this->property, null);
		}
	}
}

?>
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
 * Locates a module directory given a project and a module name.
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
class AgaviLocatemoduleTask extends AgaviTask
{
	protected $property = null;
	protected $path = null;
	protected $ignoreIfSet = false;
	
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
	 * Sets the path to use to locate the module.
	 *
	 * @param      string The path to use.
	 */
	public function setPath(PhingFile $path)
	{
		$this->path = $path;
	}
	
	/**
	 * Sets whether to ignore this check if the property is already set.
	 *
	 * @param      bool Whether to bypass the check if the property is set.
	 */
	public function setIgnoreIfSet($ignoreIfSet)
	{
		$this->ignoreIfSet = StringHelper::booleanValue($ignoreIfSet);
	}
	
	/**
	 * Executes the task.
	 */
	public function main()
	{
		if($this->property === null) {
			throw new BuildException('The property attribute must be specified');
		}
		if($this->path === null) {
			throw new BuildException('The path attribute must be specified');
		}
		
		if($this->ignoreIfSet && $this->project->getProperty($this->property) !== null) {
			return;
		}
		
		if(!$this->path->exists()) {
			throw new BuildException('The path ' . $this->path->getAbsolutePath() . ' does not exist');
		}
		$this->path = $this->path->getAbsoluteFile();
		if(!$this->path->isDirectory()) {
			$this->path = $this->path->getParentFile();
		}
		
		/* Check if the current directory is a project directory. */
		$check = new AgaviModuleFilesystemCheck();
		$check->setConfigDirectory($this->project->getProperty('module.directory.config'));
		
		$check->setPath($this->path->getAbsolutePath());
		if($check->check()) {
			/* The current path is the project directory. */
			$this->log('Module base directory: ' . $this->path);
			$this->project->setUserProperty($this->property, $this->path->getName());
			return;
		}
		
		/* Check if "actions", "views", "templates", or "config" are in the current path. */
		if(preg_match(sprintf('#^(.+?)/(?:%s|%s|%s|%s)(?:/|$)#', $this->project->getProperty('module.directory.actions'), $this->project->getProperty('module.directory.views'), $this->project->getProperty('module.directory.templates'), $this->project->getProperty('module.directory.config')), $this->path->getPath(), $matches)) {
			$directory = new PhingFile($matches[1]);
			$check->setPath($directory->getAbsolutePath());
			if($check->check()) {
				$this->log('Module base directory: ' . $directory);
				$this->project->setUserProperty($this->property, $directory->getName());
				return;
			}
		}
		
		/* Last chance: recurse upward and check for a project directory. */
		$directory = $this->path;
		while(($directory = $directory->getParentFile()) !== null) {
			$check->setPath($directory->getAbsolutePath());
			if($check->check()) {
				$this->log('Module base directory: ' . $directory);
				$this->project->setUserProperty($this->property, $directory->getName());
				return;
			}
		}
	}
}

?>
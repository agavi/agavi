<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2005-2008 the Agavi Project.                                |
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
 * Locates the base project directory given a directory within it.
 *
 * @package    agavi
 * @subpackage build
 *
 * @author     Noah Fontes <impl@cynigram.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      1.0.0
 *
 * @version    $Id$
 */
class AgaviLocateprojectTask extends Task
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
	 * Sets the path to use to locate the project.
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
	 * Determines whether a given directory is a valid Agavi project base
	 * directory.
	 *
	 * @param      PhingFile The directory to check.
	 *
	 * @return     bool True if the directory is valid, false otherwise.
	 */
	protected function checkProjectDirectory(PhingFile $directory)
	{
		$list = $directory->listDir();
		if(in_array('app', $list) && in_array('pub', $list))
		{
			$config = new PhingFile($directory, 'app/config.php');
			if($config->exists())
			{
				return true;
			}
		}
		return false;
	}
	
	/**
	 * Executes the task.
	 */
	public function main()
	{
		if($this->property === null)
		{
			throw new BuildException('The property attribute must be specified');
		}
		if($this->path === null)
		{
			throw new BuildException('The path attribute must be specified');
		}
		
		if($this->ignoreIfSet && $this->project->getProperty($this->property) !== null)
		{
			return;
		}
		
		if(!$this->path->exists())
		{
			throw new BuildException('The path ' . $this->path->getAbsolutePath() . ' does not exist');
		}
		$this->path = $this->path->getAbsoluteFile();
		if(!$this->path->isDirectory())
		{
			$this->path = $this->path->getParentFile();
		}
		
		if($this->checkProjectDirectory($this->path))
		{
			/* The current path is the project directory. */
			$this->log('Project base directory: ' . $this->path->getPath());
			$this->project->setUserProperty($this->property, $this->path);
		}
		
		if(preg_match('#^(.+?)/(?:app|pub)#', $this->path->getPath(), $matches))
		{
			$directory = new PhingFile($matches[1]);
			if($this->checkProjectDirectory($directory))
			{
				$this->log('Project base directory: ' . $this->path->getPath());
				$this->project->setUserProperty($this->property, $directory);
			}
		}
	}
}
?>
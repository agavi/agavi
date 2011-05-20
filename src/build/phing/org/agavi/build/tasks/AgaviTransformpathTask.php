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
 * Transforms a relative path into an absolute path given a base directory.
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
class AgaviTransformpathTask extends AgaviTask
{
	protected $property = null;
	protected $base = null;
	protected $path = null;
	
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
	 * Sets the path to transform.
	 *
	 * @param      string The path to transform.
	 */
	public function setPath($path)
	{
		/* This must be created here to prevent the directory from
		 * becoming automatically converted to an absolute path. */
		$this->path = new PhingFile($path);
	}
	
	/**
	 * Sets the relative base path for the directory.
	 *
	 * @param      PhingFile The directory's base path.
	 */
	public function setBase(PhingFile $base)
	{
		$this->base = $base;
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
		if(!$this->path->isAbsolute()) {
			if($this->base === null) {
				$this->path = new PhingFile($this->project->getProperty('project.basedir'), $this->path->getPath());
			} else {
				if($this->base->isFile()) {
					throw new BuildException('Cannot use base directory ' . $this->base->getAbsolutePath() . ' because a file exists with the same name');
				}
				$this->path = new PhingFile($this->base, $this->path->getPath());
			}
		}
		$this->project->setUserProperty($this->property, $this->path);
	}
}

?>
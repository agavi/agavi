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
 * Executes a target in the buildfile.
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
class AgaviExecutetargetTask extends AgaviTask
{
	protected $name = null;
	protected $exceptionsFatal = true;
	
	/**
	 * Sets the name of the target to call.
	 *
	 * @param     string The name of the target.
	 */
	public function setName($name)
	{
		$this->name = $name;
	}
	
	/**
	 * Sets whether exceptions are fatal for targets called by this task.
	 *
	 * @param      bool Whether exceptions should be considered fatal.
	 */
	public function setExceptionsFatal($exceptionsFatal)
	{
		$this->exceptionsFatal = StringHelper::booleanValue($exceptionsFatal);
	}
	
	/**
	 * Executes this target.
	 */
	public function main()
	{
		if($this->name === null) {
			throw new BuildException('The name attribute must be specified');
		}
		
		/* Words cannot describe how ridiculously fucking stupid this is. Phing
		 * seems to resolve properties only once, ever, so in order to run a
		 * target multiple times with different properties we'll have to create
		 * a new project, parse the build file all over again, copy everything
		 * over from the current project, execute the new target, and then copy
		 * everything back. Fuck. */
		$project = new Project();
		
		try {
			foreach($this->project->getBuildListeners() as $listener) {
				$project->addBuildListener($listener);
			}
			$project->setInputHandler($this->project->getInputHandler());
			
			$this->project->copyUserProperties($project);
			$this->project->copyInheritedProperties($project);
			foreach($this->project->getProperties() as $name => $property) {
				if($project->getProperty($name) === null) {
					$project->setNewProperty($name, $property);
				}
			}
			
			$project->init();
			ProjectConfigurator::configureProject($project, new PhingFile($this->project->getProperty('phing.file')));
			
			Phing::setCurrentProject($project);
			
			$project->executeTarget($this->name);
		}
		catch(BuildException $be) {
			if($this->exceptionsFatal) {
				throw $be;
			} else {
				$this->log('Ignoring build exception: ' . $be->getMessage(), Project::MSG_WARN);
				$this->log('Continuing build', Project::MSG_INFO);
			}
		}
		
		Phing::setCurrentProject($this->project);
		
		$project->copyUserProperties($this->project);
		$project->copyInheritedProperties($this->project);
		foreach($project->getProperties() as $name => $property) {
			if($this->project->getProperty($name) === null) {
				$this->project->setNewProperty($name, $property);
			}
		}
		
		/* Fuck. */
		unset($project);
	}
}

?>
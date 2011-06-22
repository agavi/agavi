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
 * Proxies a target from an external build file.
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
class AgaviProxyTarget extends Target
{
	/**
	 * @var        Target The proxied target.
	 */
	protected $target;
	
	/**
	 * Sets the proxied target.
	 *
	 * @param      Target The target to proxy.
	 *
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 * @since      1.0.0
	 */
	public function setTarget(Target $target)
	{
		$this->target = $target;
	}
	
	/**
	 * Gets the proxied target.
	 *
	 * @return     Target The proxied target.
	 *
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 * @since      1.0.0
	 */
	public function getTarget()
	{
		return $this->target;
	}
	
	/**
	 * Proxies task adding.
	 *
	 * @param      Task The task that is being added.
	 *
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 * @since      1.0.0
	 */
	public function addTask(Task $task)
	{
		if($this->target === null) {
			throw new BuildException('Tasks can not be added to a proxy target without a concrete target');
		}
		$task->setOwningTarget($this->target);
		$this->target->addTask($task);
	}
	
	/**
	 * Proxies datatype adding.
	 *
	 * @param      DataType The datatype that is being added.
	 *
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 * @since      1.0.0
	 */
	public function addDataType($type)
	{
		if($this->target === null) {
			throw new BuildException('Datatypes can not be added to a proxy target without a concrete target');
		}
		$this->target->addDataType($type);
	}
	
	/**
	 * Proxies if-conditional adding.
	 *
	 * @param      string The condition.
	 *
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 * @since      1.0.0
	 */
	public function setIf($property)
	{
		if($this->target === null) {
			throw new BuildException('Tasks can not be added to a proxy target without a concrete target');
		}
		$this->target->setIf($property);
	}
	
	/**
	 * Proxies unless-conditional adding.
	 *
	 * @param      string The condition.
	 *
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 * @since      1.0.0
	 */
	public function setUnless($property)
	{
		if($this->target === null) {
			throw new BuildException('Tasks can not be added to a proxy target without a concrete target');
		}
		$this->target->setUnless($property);
	}
	
	/**
	 * Executes this target.
	 *
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 * @since      1.0.0
	 */
	public function main()
	{
		$thisProject = $this->getProject();
		$project = $this->target->getProject();
		
		Phing::setCurrentProject($project);
		chdir($project->getBasedir()->getAbsolutePath());
		
		/* Assign properties for consistency. */
		$thisProject->copyUserProperties($project);
		$thisProject->copyInheritedProperties($project);
		foreach($thisProject->getProperties() as $name => $property) {
			if(!AgaviProxyProject::isPropertyProtected($name) && $project->getProperty($name) === null) {
				$project->setNewProperty($name, $property);
			}
		}
		
		/* Execute the proxied target. */
		$project->executeTarget($this->target->getName());
		
		Phing::setCurrentProject($thisProject);
		chdir($thisProject->getBasedir()->getAbsolutePath());
		
		$project->copyUserProperties($thisProject);
		$project->copyInheritedProperties($thisProject);
		foreach($project->getProperties() as $name => $property) {
			if(!AgaviProxyProject::isPropertyProtected($name) && $thisProject->getProperty($name) === null) {
				$thisProject->setNewProperty($name, $property);
			}
		}
	}
}

?>
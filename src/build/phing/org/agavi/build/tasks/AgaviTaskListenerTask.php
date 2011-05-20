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

require_once(dirname(__FILE__) . '/AgaviListenerTask.php');

/**
 * Defines a new listener on tasks for this build environment.
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
class AgaviTaskListenerTask extends AgaviListenerTask
{
	public function main()
	{
		if($this->object === null) {
			throw new BuildException('The object attribute must be specified');
		}
		
		$objectType = $this->object->getReferencedObject($this->project);
		if(!$objectType instanceof AgaviObjectType) {
			throw new BuildException('The object attribute must be a reference to an Agavi object type');
		}
		
		$object = $objectType->getInstance();
		if(!$object instanceof AgaviIPhingTaskListener) {
			throw new BuildException(sprintf('Cannot add task listener: Object is of type %s which does not implement %s',
				get_class($object), 'AgaviIPhingTaskListener'));
		}
		
		$dispatcher = AgaviPhingEventDispatcherManager::get($this->project);
		$dispatcher->addTaskListener($object);
	}
}

?>
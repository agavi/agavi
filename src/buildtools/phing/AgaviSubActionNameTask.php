<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2003-2007 the Agavi Project.                                |
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
 * @package    agavi
 * @subpackage buildtools
 *
 * @author     David Zülke <dz@bitxtender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.10.0
 *
 * @version    $Id$
 */
class AgaviSubActionNameTask extends Task
{
	protected $property = 'action';
	protected $outputPropertyPrefix = '';

	public function setProperty($property) {
		$this->property = $property;
	}

	public function setOutputpropertyprefix($prefix) {
		$this->outputPropertyPrefix = $prefix;
	}

	public function main()
	{
		$action = $this->project->getProperty($this->property);
		$actionPath = str_replace('.', '/', str_replace('\\', '/', $action));
		$actionName = str_replace('/', '_', $actionPath);
		$actionDir = '';
		$actionFile = $actionPath;
		if(($lastSlash = strrpos($actionPath, '/')) !== false)
		{
			$actionDir = substr($actionPath, 0, $lastSlash);
			$actionFile = substr($actionPath, $lastSlash + 1);
		}
		$this->project->setProperty($this->outputPropertyPrefix.'actionName', $actionName);
		$this->project->setProperty($this->outputPropertyPrefix.'actionPath', $actionPath);
		$this->project->setProperty($this->outputPropertyPrefix.'actionDir', $actionDir);
		$this->project->setProperty($this->outputPropertyPrefix.'actionFile', $actionFile);
	}
}
?>
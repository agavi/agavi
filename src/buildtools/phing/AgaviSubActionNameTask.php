<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2003-2006 the Agavi Project.                                |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE   |
// | file that was distributed with this source code. You can also view the    |
// | LICENSE file online at http://www.agavi.org/LICENSE.txt                   |
// |   vi: set noexpandtab:                                                    |
// |   Local Variables:                                                        |
// |   indent-tabs-mode: t                                                     |
// |   End:                                                                    |
// +---------------------------------------------------------------------------+

class AgaviSubActionNameTask extends Task
{
	public function main()
	{
		$action = $this->project->getProperty('action');
		$actionPath = str_replace('.', '/', str_replace('\\', '/', $action));
		$actionName = str_replace('/', '_', $actionPath);
		$actionDir = '';
		$actionFile = $actionPath;
		if(($lastSlash = strrpos($actionPath, '/')) !== false)
		{
			$actionDir = substr($actionPath, 0, $lastSlash);
			$actionFile = substr($actionPath, $lastSlash + 1);
		}
		$this->project->setProperty('actionName', $actionName);
		$this->project->setProperty('actionPath', $actionPath);
		$this->project->setProperty('actionDir', $actionDir);
		$this->project->setProperty('actionFile', $actionFile);
	}
}
?>
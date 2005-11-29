<?php
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

<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2005-2009 the Agavi Project.                                |
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
 * Lists all actions in an Agavi module.
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
class AgaviListactionsTask extends AgaviTask
{
	protected $property = null;
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
	 * Sets the path to the project directory from which this task will read.
	 *
	 * @param      PhingFile Path to the project directory.
	 */
	public function setPath(PhingFile $path)
	{
		$this->path = $path;
	}
	
	/**
	 * Executes this task.
	 */
	public function main()
	{
		if($this->property === null) {
			throw new BuildException('The property attribute must be specified');
		}
		if($this->path === null) {
			throw new BuildException('The path attribute must be specified');
		}
		
		$check = new AgaviModuleFilesystemCheck();
		$check->setConfigDirectory($this->project->getProperty('module.config.directory'));
		
		$check->setPath($this->path->getAbsolutePath());
		if(!$check->check()) {
			throw new BuildException('The path attribute must be a valid module base directory');
		}
		
		/* We don't know whether the module is configured or not here, so load the
		 * values we want properly. */
		$this->tryLoadAgavi();
		$this->tryBootstrapAgavi();
		
		require_once(AgaviConfigCache::checkConfig(
			sprintf('%s/%s/module.xml',
				$this->path->getAbsolutePath(),
				(string)$this->project->getProperty('module.config.directory')
			)
		));
		
		$actionPath = AgaviToolkit::expandVariables(
			AgaviToolkit::expandDirectives(AgaviConfig::get(
				sprintf('modules.%s.agavi.action.path', strtolower($this->path->getName())),
				'%core.module_dir%/${moduleName}/actions/${actionName}Action.class.php'
			)),
			array(
				'moduleName' => $this->path->getName()
			)
		);
		$pattern = '#^' . AgaviToolkit::expandVariables(
			/* Blaaaaaaaaauuuuuughhhhhhh... */
			str_replace('\\$\\{actionName\\}', '${actionName}', preg_quote($actionPath, '#')),
			array('actionName' => '(?P<action_name>.*?)')
		) . '$#';
		
		$actions = array();
		$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->path->getAbsolutePath()));
		for(; $iterator->valid(); $iterator->next()) {
			$rdi = $iterator->getInnerIterator();
			if($rdi->isDot() || !$rdi->isFile()) {
				continue;
			}
			
			$file = $rdi->getPathname();
			if(preg_match($pattern, $file, $matches)) {
				$actions[] = str_replace(DIRECTORY_SEPARATOR, '.', $matches['action_name']);
			}
		}
		
		$list = new AgaviArraytostringTransform();
		$list->setInput($actions);
		$list->setDelimiter(' ');
		
		$this->project->setUserProperty($this->property, $list->transform());
	}
}

?>
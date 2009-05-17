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
 * Configures an Agavi module by reading the module's configuration file.
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
class AgaviConfiguremoduleTask extends AgaviTask
{
	protected $name;
	protected $prefix = 'module';
	
	/**
	 * Sets the module name.
	 *
	 * @param      string The module name.
	 */
	public function setName($name)
	{
		$this->name = $name;
	}
	
	/**
	 * Sets the property prefix.
	 *
	 * @param      string The prefix.
	 */
	public function setPrefix($prefix)
	{
		$this->prefix = $prefix;
	}
	
	/**
	 * Executes the task.
	 */
	public function main()
	{
		if($this->name === null) {
			throw new BuildException('The name attribute must be specified');
		}
		
		$this->tryLoadAgavi();
		$this->tryBootstrapAgavi();
		
		/* Oookay. This is interesting. */
		$moduleName = $this->name;
		require_once(AgaviConfigCache::checkConfig(
			sprintf('%s/%s/%s/%s/module.xml',
				(string)$this->project->getProperty('project.directory'),
				(string)$this->project->getProperty('project.directory.app.modules'),
				$this->name,
				(string)$this->project->getProperty('module.config.directory')
			)
		));
		
		/* Set up us the values.
		 *
		 * XXX: With regards to the defaults:
		 *
		 * You might expect to use the <property>.default properties defined in
		 * build.xml. But this is not so; consider that someone might have decided
		 * to upgrade their project properties but still have some legacy modules
		 * lying around. We need to use the actual Agavi defaults to ensure
		 * consistency.
		 *
		 * If you change this, you're fucking asking for it. */
		$values = array();
		$lowerModuleName = strtolower($moduleName);
		
		$values['action.path'] = AgaviConfig::get(
			sprintf('modules.%s.agavi.action.path', $lowerModuleName),
			'%core.module_dir%/${moduleName}/actions/${actionName}Action.class.php'
		);
		$values['action.path'] = AgaviToolkit::expandVariables(
			$values['action.path'],
			array('moduleName' => $moduleName)
		);
		
		$values['cache.path'] = AgaviConfig::get(
			sprintf('modules.%s.agavi.cache.path', $lowerModuleName),
			'%core.module_dir%/${moduleName}/cache/${actionName}.xml'
		);
		$values['cache.path'] = AgaviToolkit::expandVariables(
			$values['cache.path'],
			array('moduleName' => $moduleName)
		);
		
		$values['templates.directory'] = AgaviConfig::get(
			sprintf('modules.%s.agavi.template.directory', $lowerModuleName),
			'%core.module_dir%/${module}/templates'
		);
		$values['templates.directory'] = AgaviToolkit::expandVariables(
			$values['templates.directory'],
			array('module' => $moduleName)
		);
		
		$values['validate.path'] = AgaviConfig::get(
			sprintf('modules.%s.agavi.validate.path', $lowerModuleName),
			'%core.module_dir%/${moduleName}/validate/${actionName}.xml'
		);
		$values['validate.path'] = AgaviToolkit::expandVariables(
			$values['validate.path'],
			array('moduleName' => $moduleName)
		);
		
		$values['view.path'] = AgaviConfig::get(
			sprintf('modules.%s.agavi.view.path', $lowerModuleName),
			'%core.module_dir%/${moduleName}/views/${viewName}View.class.php'
		);
		$values['view.path'] = AgaviToolkit::expandVariables(
			$values['view.path'],
			array('moduleName' => $moduleName)
		);
		
		$values['view.name'] = AgaviConfig::get(
			sprintf('modules.%s.agavi.view.name', $lowerModuleName),
			'${actionName}${viewName}'
		);
		
		/* Main screen turn on. */
		foreach($values as $name => $value) {
			$this->project->setUserProperty(sprintf('%s.%s', $this->prefix, $name), $value);
		}
	}
}

?>
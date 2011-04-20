<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2005-2010 the Agavi Project.                                |
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
 * Base task for all Agavi tasks.
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
abstract class AgaviTask extends Task
{
	protected $quiet = false;
	protected static $bootstrapped = false;
	protected static $agaviBootstrapped = false;
	
	/**
	 * Initializes the task by bootstrapping Agavi build components.
	 */
	public function init()
	{
		if(!class_exists('AgaviBuild')) {
			require_once(__DIR__ . '/../../../../../agavi/build.php');
			AgaviBuild::bootstrap();
		}
	}
	
	/**
	 * Sets whether log messages for this task will be suppressed.
	 *
	 * @param      bool Whether to suppressing log messages for this task.
	 */
	public function setQuiet($quiet)
	{
		$this->quiet = StringHelper::booleanValue($quiet);
	}
	
	/**
	 * Logs an event.
	 *
	 * @param      string The message to log.
	 * @param      int The priority of the message.
	 */
	public function log($message, $level = Project::MSG_INFO)
	{
		if($this->quiet === false) {
			parent::log($message, $level);
		}
	}
	
	/**
	 * Utility method to load Agavi classes.
	 */
	protected function tryLoadAgavi()
	{
		if(!class_exists('Agavi')) {
			$sourceDirectory = (string)$this->project->getProperty('agavi.directory.src');
			require_once($sourceDirectory . '/agavi.php');
		}
	}
	
	/**
	 * Utility method to bootstrap Agavi.
	 */
	protected function tryBootstrapAgavi()
	{
		if(!self::$agaviBootstrapped) {
			/* Something might fuck up. We always use the template that you can
			 * actually read. */
			AgaviConfig::set('exception.default_template',
				sprintf('%s/templates/plaintext.php', (string)$this->project->getProperty('agavi.directory.src.exception')),
				$overwrite = true,
				$readonly = true
			);
			
			/* To further prevent fucking up, we force it into debug mode. */
			AgaviConfig::set('core.debug', true, $overwrite = true, $readonly = true);
			
			require_once(
				sprintf('%s/%s/config.php',
					(string)$this->project->getProperty('project.directory'),
					(string)$this->project->getProperty('project.directory.app')
				)
			);
			Agavi::bootstrap($this->project->getProperty('project.build.environment'));
			self::$agaviBootstrapped = true;
		}
	}
}

?>
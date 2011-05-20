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
	
	/**
	 * Initializes the task by bootstrapping Agavi build components.
	 */
	public function init()
	{
		if(!class_exists('AgaviBuild')) {
			require_once(dirname(__FILE__) . '/../../../../../agavi/build.php');
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
}

?>
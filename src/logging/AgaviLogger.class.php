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

/**
 * AgaviLogger provides an easy way to manage multiple log destinations and 
 * write to them all simultaneously.
 *
 * @package    agavi
 * @subpackage logging
 *
 * @author     Sean Kerr <skerr@mojavi.org>
 * @copyright  (c) Authors
 * @since      0.9.0
 *
 * @version    $Id$
 */
class AgaviLogger
{
	/**
	 * Fatal level.
	 *
	 * @since      0.9.0
	 */
	const FATAL = 1;

	/**
	 * Error level.
	 *
	 * @since      0.9.0
	 */
	const ERROR = 2;

	/**
	 * Warning level.
	 *
	 * @since      0.9.0
	 */
	const WARN = 4;

	/**
	 * Information level.
	 *
	 * @since      0.9.0
	 */
	const INFO = 8;

	/**
	 * Debug level.
	 *
	 * @since      0.9.0
	 */
	const DEBUG = 16;

	/**
	 * All levels. (2^32-1)
	 *
	 * @since      0.11.0
	 */
	const ALL = 4294967295;

	/**
	 * @var        array An array of AgaviLoggerAppenders.
	 */
	protected $appenders = array();

	/**
	 * @var        int Logging level.
	 */
	protected $level = AgaviLogger::WARN;

	/**
	 * Log a message.
	 *
	 * @param      AgaviLoggerMessage A Message instance.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function log(AgaviLoggerMessage $message)
	{
		// get message level
		$msgLevel = $message->getLevel();

		if($this->level & $msgLevel) {
			foreach($this->appenders as $appender) {
				$appender->write($message);
			}
		}
	}

	/**
	 * Set an appender.
	 *
	 * If an appender with the name already exists, an exception will be thrown.
	 *
	 * @param      string              An appender name.
	 * @param      AgaviLoggerAppender An Appender instance.
	 *
	 * @throws     <b>AgaviLoggingException</b> If an appender with the name 
	 *                                          already exists.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function setAppender($name, AgaviLoggerAppender $appender)
	{
		if(!isset($this->appenders[$name])) {
			$this->appenders[$name] = $appender;
			return;
		}

		// appender already exists
		$error = 'An appender with the name "%s" is already registered';
		$error = sprintf($error, $name);
		throw new AgaviLoggingException($error);
	}

	/**
	 * Set the level.
	 *
	 * @param      int A log level.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function setLevel($level)
	{
		$this->level = $level;
	}

	/**
	 * Execute the shutdown procedure.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function shutdown()
	{
		// loop through our appenders and shut them all down
		foreach($this->appenders as $appender) {
			$appender->shutdown();
		}
	}
}

?>
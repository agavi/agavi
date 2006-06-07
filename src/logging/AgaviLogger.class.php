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
 * AgaviLogger provides an easy way to manage multiple log destinations and write
 * to them all simultaneously.
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
	 * Debug level.
	 *
	 * @since      0.9.0
	 */
	const DEBUG = 1000;

	/**
	 * Error level.
	 *
	 * @since      0.9.0
	 */
	const ERROR = 4000;

	/**
	 * Information level.
	 *
	 * @since      0.9.0
	 */
	const INFO = 2000;

	/**
	 * Warning level.
	 *
	 * @since      0.9.0
	 */
	const WARN = 3000;

	/**
	 * Fatal level.
	 *
	 * @since      0.9.0
	 */
	const FATAL = 5000;

	private
		$appenders    = array(),
		$priority     = null;

	/**
	 * Constructor.
	 * 
	 * 
	 * @author     Bob Zoller <bob@agavi.org>
	 * @since      0.10.0
	 */
	public function __construct()
	{
		$this->priority = self::WARN;
	}

	/**
	 * Log a message.
	 *
	 * @param      AgaviMessage A Message instance.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function log (AgaviMessage $message)
	{
		// get message priority
		$msgPriority = $message->getPriority();

		if ($msgPriority >= $this->priority || $this->priority < 1)
		{
			foreach ($this->appenders as $appender)
			{
				$appender->write($message);
			}
		}
	}

	/**
	 * Set an appender.
	 *
	 * If an appender with the name already exists, an exception will be thrown.
	 *
	 * @param      string        An appender name.
	 * @param      AgaviAppender An Appender instance.
	 *
	 * @throws     <b>AgaviLoggingException</b> If an appender with the name already
	 *                                          exists.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function setAppender ($name, $appender)
	{
		if (!isset($this->appenders[$name]))
		{
			$this->appenders[$name] = $appender;
			return;
		}

		// appender already exists
		$error = 'An appender with the name "%s" is already registered';
		$error = sprintf($error, $name);

		throw new AgaviLoggingException($error);
	}

	/**
	 * Set the priority level.
	 *
	 * @param      int A priority level.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function setPriority ($priority)
	{
		$this->priority = $priority;
	}

	/**
	 * Execute the shutdown procedure.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function shutdown ()
	{
		// loop through our appenders and shut them all down
		foreach ($this->appenders as $appender)
		{
			$appender->shutdown();
		}
	}

}

?>
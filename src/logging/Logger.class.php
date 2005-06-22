<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2003-2005  Sean Kerr.                                       |
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
 * Logger provides an easy way to manage multiple log destinations and write
 * to them all simultaneously.
 *
 * @package    agavi
 * @subpackage logging
 *
 * @author    Sean Kerr (skerr@mojavi.org)
 * @copyright (c) Sean Kerr, {@link http://www.mojavi.org}
 * @since     0.9.0
 * @version   $Id$
 */
class Logger extends AgaviObject
{

	// +-----------------------------------------------------------------------+
	// | CONSTANTS                                                             |
	// +-----------------------------------------------------------------------+

	/**
	 * Debug level.
	 *
	 * @since 0.9.0
	 */
	const DEBUG = 1000;

	/**
	 * Error level.
	 *
	 * @since 0.9.0
	 */
	const ERROR = 4000;

	/**
	 * Information level.
	 *
	 * @since 0.9.0
	 */
	const INFO = 2000;

	/**
	 * Warning level.
	 *
	 * @since 0.9.0
	 */
	const WARN = 3000;

	/**
	 * Fatal level.
	 *
	 * @since 0.9.0
	 */
	const FATAL = 5000;

	// +-----------------------------------------------------------------------+
	// | PRIVATE VARIABLES                                                     |
	// +-----------------------------------------------------------------------+

	private
		$appenders    = array(),
		$exitPriority = null,
		$priority     = null;

	public function __construct()
	{
		$this->exitPriority = self::ERROR;
		$this->priority = self::WARN;
	}

	// +-----------------------------------------------------------------------+
	// | METHODS                                                               |
	// +-----------------------------------------------------------------------+

	/**
	 * Retrieve an appender.
	 *
	 * @param string An appender name.
	 *
	 * @return Appender An Appender, if an appender with the name exists,
	 *                  otherwise null.
	 *
	 * @author Sean Kerr (skerr@mojavi.org)
	 * @since  0.9.0
	 */
	public function getAppender ($name)
	{

		$retval = null;

		return $retval;

	}

	// -------------------------------------------------------------------------

	/**
	 * Retrieve the exit priority level.
	 *
	 * This is the priority level required in order to immediately exit the
	 * request.
	 *
	 * @return int The exit priority level.
	 *
	 * @author Sean Kerr (skerr@mojavi.org)
	 * @since  0.9.0
	 */
	public function getExitPriority ()
	{

		return $this->exitPriority;

	}

	// -------------------------------------------------------------------------

	/**
	 * Retrieve the priority level.
	 *
	 * This is the priority level required before a message will be written
	 * to the log.
	 *
	 * @return int The priority level.
	 *
	 * @author Sean Kerr (skerr@mojavi.org)
	 * @since  0.9.0
	 */
	public function getPriority ()
	{

		return $this->priority;

	}

	// -------------------------------------------------------------------------

	/**
	 * Log a message.
	 *
	 * @param Message A Message instance.
	 *
	 * @return void
	 *
	 * @author Sean Kerr (skerr@mojavi.org)
	 * @since  0.9.0
	 */
	public function log ($message)
	{

		// get message priority
		$msgPriority = $message->getAttribute('p');

		if ($msgPriority >= $this->priority || $this->priority < 1)
		{

			// loop through our appenders and grab their layouts
			// then format the message and write it to the appender
			foreach ($this->appenders as $appender)
			{

				$appender->write($message);

			}

		}

		// to exit or not to exit, that is the question
		if ($this->exitPriority > 0 && $msgPriority >= $this->exitPriority)
		{

			Controller::getInstance()->shutdown();

			exit;

		}

	}

	// -------------------------------------------------------------------------

	/**
	 * Remove an appender.
	 *
	 * This does not shutdown the appender. The shutdown method must be called
	 * manually.
	 *
	 * @param string An appender name.
	 *
	 * @return Appender An Appender, if one with the name exists, otherwise
	 *                  null.
	 *
	 * @author Sean Kerr (skerr@mojavi.org)
	 * @since  0.9.0
	 */
	public function removeAppender ($name)
	{

		if (isset($this->appenders[$name]))
		{

			$retval = $this->appenders[$name];

			unset($this->appenders[$name]);

			return $retval;

		}

		return null;

	}

	// -------------------------------------------------------------------------

	/**
	 * Set an appender.
	 *
	 * If an appender with the name already exists, an exception will be thrown.
	 *
	 * @param string   An appender name.
	 * @param Appender An Appender instance.
	 *
	 * @return void
	 *
	 * @throws <b>LoggingException</b> If an appender with the name already
	 *                                 exists.
	 *
	 * @author Sean Kerr (skerr@mojavi.org)
	 * @since  0.9.0
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

		throw new LoggingException($error);

	}

	// -------------------------------------------------------------------------

	/**
	 * Set the exit priority level.
	 *
	 * @param int An exit priority level.
	 *
	 * @return void
	 *
	 * @author Sean Kerr (skerr@mojavi.org)
	 * @since  0.9.0
	 */
	public function setExitPriority ($priority)
	{

		$this->exitPriority = $priority;

	}

	// -------------------------------------------------------------------------

	/**
	 * Set the priority level.
	 *
	 * @param int A priority level.
	 *
	 * @return void
	 *
	 * @author Sean Kerr (skerr@mojavi.org)
	 * @since  0.9.0
	 */
	public function setPriority ($priority)
	{

		$this->priority = $priority;

	}

	// -------------------------------------------------------------------------

	/**
	 * Execute the shutdown procedure.
	 *
	 * @return void
	 *
	 * @author Sean Kerr (skerr@mojavi.org)
	 * @since  0.9.0
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

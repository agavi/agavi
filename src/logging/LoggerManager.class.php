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
 * LoggerManager provides accessibility and management of all loggers.
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
class LoggerManager
{

	private
		$loggers = array(),
		$context = null;

	/**
	 * Retrieve the current application context.
	 *
	 * @return     Context The current Context instance.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public final function getContext()
	{
		return $this->context;
	}

	/**
	 * Initialize this LoggingManager.
	 *
	 * @return     bool true, if initialization completes successfully,
	 *                  otherwise false.
	 *
	 * @throws     <b>InitializationException</b> If an error occurs while
	 *                                            initializing a LoggingManager.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function initialize($context)
	{
		$this->context = $context;
		
		// load database configuration
		require_once(ConfigCache::checkConfig('config/logging.ini'));
	}
	
	/**
	 * Retrieve a logger.
	 *
	 * @param      string A logger name.
	 *
	 * @return     Logger A Logger, if a logger with the name exists, otherwise
	 *                    null.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function getLogger ($name = 'default')
	{
		if (isset($this->loggers[$name]))
		{
			return $this->loggers[$name];
		}
		return null;
	}

	/**
	 * Retrieve a list of logger names.
	 *
	 * @return     array An indexed array of logger names.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function getLoggerNames ()
	{
		return array_keys($this->loggers);
	}

	/**
	 * Indicates that a logger exists.
	 *
	 * @param      string A logger name.
	 *
	 * @return     bool true, if the logger exists, otherwise false.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function hasLogger ($name)
	{
		return isset($this->loggers[$name]);
	}

	/**
	 * Remove a logger.
	 *
	 * @param      string A logger name.
	 *
	 * @return     Logger A Logger, if the logger has been removed, else null.
	 *
	 * @throws     <b>LoggingException</b> If the logger name is default, which
	 *                                     cannot be removed.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function & removeLogger ($name)
	{

		$retval = null;

		if (isset($this->loggers[$name]))
		{
			if ($name != 'default')
			{
				$retval = $this->loggers[$name];
				unset($this->loggers[$name]);
			} else
			{
				// cannot remove the default logger
				$error = 'Cannot remove the default logger';
				throw new LoggingException($error);
			}
		}

		return $retval;
	}

	/**
	 * Set a new logger instance.
	 *
	 * If a logger with the name already exists, an exception will be thrown.
	 *
	 * @param      string A logger name.
	 * @param      Logger A Logger instance.
	 *
	 * @return     void
	 *
	 * @throws     <b>LoggingException</b> If a logger with the name already 
	 *                                     exists.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function setLogger ($name, $logger)
	{
		if (!isset($this->loggers[$name]))
		{
			$this->loggers[$name] = $logger;
			return;
		}

		// logger already exists
		$error = 'A logger with the name "%s" is already registered';
		$error = sprintf($error, $name);

		throw new LoggingException($error);
	}

	/**
	 * Log a Message.
	 * 
	 * @param      Message The Message to log.
	 * @param      string Optional logger to log to.
	 * 
	 * @return     void
	 * @throws     LoggingException if the logger was not found.
	 * 
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @author     Bob Zoller <bob@agavi.org>
	 * @since      0.10.0
	 */
	public function log(Message $message, $logger = null)
	{
		if (is_null($logger)) {
			foreach ($this->loggers as $logger) {
				$logger->log($message);
			}
		} else if (!is_null($logger = self::getLogger($logger))) {
			$logger->log($message);
		} else {
			throw new LoggingException("{$logger} Logger is not configured.");
		}
	}

	/**
	 * Execute the shutdown procedure.
	 *
	 * @return     void
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function shutdown ()
	{
		// loop through our loggers and shut them all down
		foreach ($this->loggers as $name => $logger)
		{
			$logger->shutdown();
			unset($this->loggers[$name]);
		}
	}

}

?>
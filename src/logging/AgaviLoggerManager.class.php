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
 * AgaviLoggerManager provides accessibility and management of all loggers.
 *
 * @package    agavi
 * @subpackage logging
 *
 * @author     Sean Kerr <skerr@mojavi.org>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.9.0
 *
 * @version    $Id$
 */
class AgaviLoggerManager
{
	/**
	 * @var        array An array of AgaviLoggers.
	 */
	protected $loggers = array();

	/**
	 * @var        AgaviContext An AgaviContext instance.
	 */
	protected $context = null;

	/**
	 * @var        string The name of the default logger.
	 * @since      0.11.0
	 */
	protected $defaultLoggerName = 'default';
	
	/**
	 * @var        string The name of the default LoggerMessage class to use.
	 */
	protected $defaultMessageClass = 'AgaviLoggerMessage';

	/**
	 * Retrieve the current application context.
	 *
	 * @return     AgaviContext The current AgaviContext instance.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public final function getContext()
	{
		return $this->context;
	}

	/**
	 * Initialize this AgaviLoggingManager.
	 *
	 * @param      AgaviContext An AgaviContext instance.
	 * @param      array        An array of initialization parameters.
	 *
	 * @throws     <b>AgaviInitializationException</b> If an error occurs while
	 *                                                 initializing this instance.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function initialize(AgaviContext $context, array $parameters = array())
	{
		$this->context = $context;
		
		if(isset($parameters['default_message_class'])) {
			$this->defaultMessageClass = $parameters['default_message_class'];
		}
		
		// load logging configuration
		require(AgaviConfigCache::checkConfig(AgaviConfig::get('core.config_dir') . '/logging.xml', $context->getName()));
	}

	/**
	 * Retrieve a logger.
	 *
	 * @param      string A logger name.
	 *
	 * @return     AgaviLogger A Logger, if a logger with the name exists,
	 *                         otherwise null.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function getLogger($name = null)
	{
		if($name === null) {
			$name = $this->defaultLoggerName;
		}
		if(isset($this->loggers[$name])) {
			return $this->loggers[$name];
		}
		return null;
	}

	/**
	 * Retrieve a list of logger names.
	 *
	 * @return     array An indexed array of logger names.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function getLoggerNames()
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
	 * @author     David Zülke <dz@bitxtender.com>
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function hasLogger($name)
	{
		return isset($this->loggers[$name]);
	}

	/**
	 * Remove a logger.
	 *
	 * @param      string A logger name.
	 *
	 * @return     AgaviLogger A Logger, if the logger has been removed, else 
	 *                         null.
	 *
	 * @throws     <b>AgaviLoggingException</b> If the logger name is default,
	 *                                           which cannot be removed.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function removeLogger($name)
	{
		$retval = null;
		if(isset($this->loggers[$name])) {
			if($name != $this->defaultLoggerName) {
				$retval = $this->loggers[$name];
				unset($this->loggers[$name]);
			} else {
				// cannot remove the default logger
				$error = 'Cannot remove the default logger';
				throw new AgaviLoggingException($error);
			}
		}
		return $retval;
	}

	/**
	 * Set a new logger instance.
	 *
	 * If a logger with the name already exists, an exception will be thrown.
	 *
	 * @param      string       A logger name.
	 * @param      AgaviILogger A Logger instance.
	 *
	 * @throws     <b>AgaviLoggingException</b> If a logger with the name already
	 *                                          exists.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function setLogger($name, AgaviILogger $logger)
	{
		if(!isset($this->loggers[$name])) {
			$this->loggers[$name] = $logger;
			return;
		}

		// logger already exists
		$error = 'A logger with the name "%s" is already registered';
		$error = sprintf($error, $name);
		throw new AgaviLoggingException($error);
	}

	/**
	 * Returns the name of the default logger.
	 *
	 * @return     string The name of the default logger.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getDefaultLoggerName()
	{
		return $this->defaultLoggerName;
	}

	/**
	 * Returns the name of the default message class.
	 *
	 * @return     string The name of the default message class.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getDefaultMessageClass()
	{
		return $this->defaultMessageClass;
	}

	/**
	 * Sets the default logger.
	 *
	 * @param      string      The name of the the default logger.
	 *
	 * @throws     <b>AgaviLoggingException</b> if the logger was not found.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function setDefaultLoggerName($name)
	{
		if(!isset($this->loggers[$name])) {
			throw new AgaviLoggingException('A logger with the name ' . $name . ' does not exist');
		}

		$this->defaultLoggerName = $name;
	}

	/**
	 * Log a Message.
	 *
	 * @param      mixed A message to log - either an AgaviLoggerMessage instance,
	 *                   or a message string.
	 * @param      mixed Optional logger to log to (instance or name), or an int
	 *                   with the severity of the message.
	 *
	 * @throws     AgaviLoggingException if the logger was not found.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @author     Bob Zoller <bob@agavi.org>
	 * @since      0.10.0
	 */
	public function log($message, $loggerOrSeverity = null)
	{
		if(!($message instanceof AgaviLoggerMessage)) {
			$message = new $this->defaultMessageClass($message);
		}
		
		// the loggers to log to
		$loggers = array();
		
		if($loggerOrSeverity === null) {
			// no logger/severity given - log to all loggers
			$loggers = $this->loggers;
		} elseif($loggerOrSeverity instanceof AgaviILogger) {
			// we're given a logger instance, use that
			$loggers[] = $loggerOrSeverity;
		} elseif(is_int($loggerOrSeverity)) {
			// we're given a message level, set that and log to all loggers
			$message->setLevel($loggerOrSeverity);
			$loggers = $this->loggers;
		} elseif(($logger = $this->getLogger($loggerOrSeverity)) !== null) {
			// there is a logger of that name
			$loggers[] = $logger;
		} else {
			// nothing found? bark!
			throw new AgaviLoggingException(sprintf('Logger "%s" has not been configured.', $loggerOrSeverity));
		}
		
		// and log the stuff
		foreach($loggers as $logger) {
			$logger->log($message);
		}
	}

	/**
	 * Do any necessary startup work after initialization.
	 *
	 * This method is not called directly after initialize().
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function startup()
	{
	}

	/**
	 * Execute the shutdown procedure.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function shutdown()
	{
		$appenders = array();
		// loop through our loggers and shut them all down
		foreach($this->loggers as $name => $logger) {
			$appenders = $appenders + $logger->getAppenders();
			$logger->shutdown();
			unset($this->loggers[$name]);
		}
		// loop through our appenders and shut them all down
		foreach($appenders as $appender) {
			$appender->shutdown();
		}
	}
}

?>
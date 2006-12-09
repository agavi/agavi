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
 * AgaviLoggerManager provides accessibility and management of all loggers.
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
	 * Retrieve the current application context.
	 *
	 * @return     AgaviContext The current AgaviContext instance.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
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
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function initialize(AgaviContext $context, array $parameters = array())
	{
		$this->context = $context;

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
	 * @author     David Zuelke <dz@bitxtender.com>
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
	 * @author     David Zuelke <dz@bitxtender.com>
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
	 * @author     David Zuelke <dz@bitxtender.com>
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
	 * @author     David Zuelke <dz@bitxtender.com>
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
	 * @param      string      A logger name.
	 * @param      AgaviLogger A Logger instance.
	 *
	 * @throws     <b>AgaviLoggingException</b> If a logger with the name already
	 *                                          exists.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function setLogger($name, AgaviLogger $logger)
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
	 * Sets the default logger.
	 *
	 * @param      string      The name of the the default logger.
	 *
	 * @throws     <b>AgaviLoggingException<b> if the logger was not found.
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
	 * @param      AgaviLoggerMessage The Message to log.
	 * @param      string Optional logger to log to.
	 *
	 * @throws     AgaviLoggingException if the logger was not found.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @author     Bob Zoller <bob@agavi.org>
	 * @since      0.10.0
	 */
	public function log(AgaviLoggerMessage $message, $logger = null)
	{
		if($logger === null) {
			foreach($this->loggers as $logger) {
				$logger->log($message);
			}
		} elseif(($logger = $this->getLogger($logger)) !== null) {
			$logger->log($message);
		} else {
			throw new AgaviLoggingException('Logger "' . $logger . '" has not been configured.');
		}
	}

	/**
	 * Execute the shutdown procedure.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function shutdown()
	{
		// loop through our loggers and shut them all down
		foreach($this->loggers as $name => $logger) {
			$logger->shutdown();
			unset($this->loggers[$name]);
		}
	}
}

?>
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

require_once('phing/listener/DefaultLogger.php');

/**
 * Default logger for Agavi Phing build events.
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
class AgaviBuildLogger extends DefaultLogger
{
	/**
	 * @var        DefaultLogger The inner logger to which this logger will write.
	 */
	protected $logger = null;
	
	/**
	 * Creates a new build logger.
	 *
	 * @param      DefaultLogger The logger to which this logger should write.
	 *
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 * @since      1.0.0
	 */
	public function __construct(DefaultLogger $logger)
	{
		$this->logger = $logger;
	}
	
	/**
	 * Sets the output level for the logging.
	 *
	 * @param      int The logging level.
	 *
	 * @see        DefaultLogger::setMessageOutputLevel()
	 *
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 * @since      1.0.0
	 */
	public function setMessageOutputLevel($level)
	{
		parent::setMessageOutputLevel($level);
		$this->logger->setMessageOutputLevel($level);
	}
	
	/**
	 * Sets the output stream for the logging.
	 *
	 * @param      OutputStream The output stream.
	 *
	 * @see        DefaultLogger::setOutputStream()
	 *
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 * @since      1.0.0
	 */
	public function setOutputStream(OutputStream $output)
	{
		parent::setOutputStream($output);
		$this->logger->setOutputStream($output);
	}
	
	/**
	 * Sets the error stream for the logging.
	 *
	 * @param      OutputStream The error stream.
	 *
	 * @see        DefaultLogger::setOutputStream()
	 *
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 * @since      1.0.0
	 */
	public function setErrorStream(OutputStream $error)
	{
		parent::setErrorStream($error);
		$this->logger->setErrorStream($error);
	}
	
	/**
	 * Logs the start of a build.
	 *
	 * This logger does not output anything when a build is started.
	 *
	 * @param      BuildEvent An event containing the data to be logged.
	 *
	 * @see        DefaultLogger::buildStarted()
	 *
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 * @since      1.0.0
	 */
	public function buildStarted(BuildEvent $event)
	{
	}
	
	/**
	 * Logs the end of a build.
	 *
	 * @param      BuildEvent An event containing the data to be logged.
	 *
	 * @see        DefaultLogger::buildFinished()
	 *
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 * @since      1.0.0
	 */
	public function buildFinished(BuildEvent $event)
	{
		$exception = $event->getException();
		if($exception !== null) {
			$this->printMessage(str_pad('[error] ', DefaultLogger::LEFT_COLUMN_SIZE, ' ', STR_PAD_LEFT) . $exception->getMessage(), $this->out, $event->getPriority());
		}
	}
	
	/**
	 * Logs the start of a target by proxying the event to this logger's inner
	 * logger.
	 *
	 * @param      BuildEvent An event containing the data to be logged.
	 *
	 * @see        DefaultLogger::targetStarted()
	 *
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 * @since      1.0.0
	 */
	public function targetStarted(BuildEvent $event)
	{
		$this->logger->targetStarted($event);
	}
	
	/**
	 * Logs the end of a target by proxying the event to this logger's inner
	 * logger.
	 *
	 * @param      BuildEvent An event containing the data to be logged.
	 *
	 * @see        DefaultLogger::targetFinished()
	 *
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 * @since      1.0.0
	 */
	public function targetFinished(BuildEvent $event)
	{
		$this->logger->targetFinished($event);
	}
	
	/**
	 * Logs the start of a task by proxying the event to this logger's inner
	 * logger.
	 *
	 * @param      BuildEvent An event containing the data to be logged.
	 *
	 * @see        DefaultLogger::taskStarted()
	 *
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 * @since      1.0.0
	 */
	public function taskStarted(BuildEvent $event)
	{
		$this->logger->taskStarted($event);
	}
	
	/**
	 * Logs the end of a task by proxying the event to this logger's inner logger.
	 *
	 * @param      BuildEvent An event containing the data to be logged.
	 *
	 * @see        DefaultLogger::taskFinished()
	 *
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 * @since      1.0.0
	 */
	public function taskFinished(BuildEvent $event)
	{
		$this->logger->taskFinished($event);
	}
	
	/**
	 * Logs each instance of a recorded message by proxying the event to this
	 * logger's inner logger.
	 *
	 * @param      BuildEvent An event containing the data to be logged.
	 *
	 * @see        DefaultLogger::messageLogged()
	 *
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 * @since      1.0.0
	 */
	public function messageLogged(BuildEvent $event)
	{
		$this->logger->messageLogged($event);
	}
}

?>
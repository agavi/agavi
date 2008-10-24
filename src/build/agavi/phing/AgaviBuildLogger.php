<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2005-2008 the Agavi Project.                                |
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
	 * The inner logger to which this logger will write.
	 *
	 * @var        DefaultLogger
	 */
	protected $logger = null;
	
	/**
	 * Creates a new build logger.
	 *
	 * @param      DefaultLogger The logger to which this logger should write.
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
	 */
	public function setErrorStream(OutputStream $error)
	{
		parent::setErrorStream($error);
		$this->logger->setErrorStream($error);
	}
	
	public function buildStarted(BuildEvent $event)
	{
		$this->logger->buildStarted($event);
	}
	
	public function buildFinished(BuildEvent $event)
	{
		$exception = $event->getException();
		if($exception !== null) {
			$this->printMessage(str_pad('[error] ', DefaultLogger::LEFT_COLUMN_SIZE, ' ', STR_PAD_LEFT) . $exception->getMessage(), $this->out, $event->getPriority());
		}
	}
	
	public function targetStarted(BuildEvent $event)
	{
		$this->logger->targetStarted($event);
	}
	
	public function targetFinished(BuildEvent $event)
	{
		$this->logger->targetFinished($event);
	}
	
	public function taskStarted(BuildEvent $event)
	{
		$this->logger->taskStarted($event);
	}
	
	public function taskFinished(BuildEvent $event)
	{
		$this->logger->taskFinished($event);
	}
	
	public function messageLogged(BuildEvent $event)
	{
		$this->logger->messageLogged($event);
	}
}

?>
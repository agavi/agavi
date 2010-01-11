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
 * AgaviLoggerMessage, by default, holds a message and a priority level.
 * It is intended to be passed to a AgaviLogger.
 *
 * @package    agavi
 * @subpackage logging
 *
 * @author     Bob Zoller <bob@agavi.org>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.10.0
 *
 * @version    $Id$
 */
class AgaviLoggerMessage extends AgaviParameterHolder
{
	/**
	 * Constructor.
	 *
	 * @param      string optional message
	 * @param      int    optional priority level
	 *
	 * @author     Bob Zoller <bob@agavi.org>
	 * @since      0.10.0
	 */
	public function __construct($message = null, $level = AgaviLogger::INFO)
	{
		$this->setParameter('m', $message);
		$this->setParameter('l', $level);
	}

	/**
	 * toString method.
	 *
	 * @return     string The message.
	 *
	 * @author     Bob Zoller <bob@agavi.org>
	 * @since      0.10.0
	 */
	public function __toString()
	{
		return(is_array($this->getParameter('m')) ? implode("\n", $this->getParameter('m')) : (string) $this->getParameter('m'));
	}

	/**
	 * Set the message.
	 *
	 * @param      string The message to set.
	 *
	 * @return     AgaviLoggerMessage
	 *
	 * @author     Bob Zoller <bob@agavi.org>
	 * @since      0.10.0
	 */
	public function setMessage($message)
	{
		$this->setParameter('m', $message);
		return $this;
	}

	/**
	 * Append to the message.
	 *
	 * @param      string Message to append.
	 *
	 * @return     AgaviLoggerMessage
	 *
	 * @author     Bob Zoller <bob@agavi.org>
	 * @since      0.10.0
	 */
	public function appendMessage($message)
	{
		$this->appendParameter('m', $message);
		return $this;
	}

	/**
	 * Set the priority level.
	 *
	 * @param      int The priority level.
	 *
	 * @return     AgaviLoggerMessage
	 *
	 * @author     Bob Zoller <bob@agavi.org>
	 * @since      0.10.0
	 */
	public function setLevel($level)
	{
		$this->setParameter('l', $level);
		return $this;
	}

	/**
	 * Get the priority level.
	 *
	 * @return     int The priority level.
	 *
	 * @author     Bob Zoller <bob@agavi.org>
	 * @since      0.10.0
	 */
	public function getLevel()
	{
		return $this->getParameter('l');
	}

	/**
	 * Get the message.
	 *
	 * @return     mixed The message.
	 *
	 * @author     Bob Zoller <bob@agavi.org>
	 * @since      0.10.0
	 */
	public function getMessage()
	{
		return $this->getParameter('m');
	}
}

?>
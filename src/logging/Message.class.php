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
 *
 *
 * @package    agavi
 * @subpackage logging
 *
 * @author    Sean Kerr (skerr@mojavi.org)
 * @copyright (c) Sean Kerr, {@link http://www.mojavi.org}
 * @since     0.9.0
 * @version   $Id$
 */
class Message extends ParameterHolder
{

	public function __construct($message = null, $priority = Logger::INFO)
	{
		$this->setParameter('m', $message);
		$this->setParameter('p', $priority);
	}

	public function log()
	{
		LoggerManager::log($this);
	}
	
	public function __toString()
	{
		return(is_array($this->getParameter('m')) ? implode("\n", $this->getParameter('a')) : (string) $this->getParameter('m'));
	}

	public function setMessage($message)
	{
		$this->setParameter('m', $message);
	}

	public function appendMessage($message)
	{
		$this->appendParameter('m', $message);
	}
	
	public function setPriority($priority)
	{
		$this->setParameter('p', $priority);
	}

	public function getPriority()
	{
		return $this->getParameter('p');
	}

	public function getMessage()
	{
		return $this->getParameter('m');
	}

}

?>

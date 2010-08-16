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
 * AgaviScribeLoggerAppender sends AgaviLoggerMessages to a Scribe server or an
 * interface using the Scribe Thrift protocol (Facebook Scribe, Cloudera Flume).
 *
 * Configuration parameters:
 *  'default_category'       - Default scribe category for messages ("default"),
 *                             can be overriden in a message using parameter
 *                             "scribe_category".
 *  'socket_host'            - Hostname of scribe server (default "localhost")
 *  'socket_port'            - Port of scribe server (default 1463)
 *  'socket_persist'         - Whether to use persistent conns (default false)
 *  'transport_strict_read'  - Strict protocol reads (default false)
 *  'transport_strict_write' - Strict protocol writes (default true)
 *
 * @package    agavi
 * @subpackage logging
 *
 * @author     David Zülke <david.zuelke@bitextender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      1.0.4
 *
 * @version    $Id$
 */
class AgaviScribeLoggerAppender extends AgaviLoggerAppender
{
	/**
	 * @var        scribeClient The scribeClient instance to write to.
	 */
	protected $scribeClient = null;
	
	/**
	 * @var        TTransport The Thrift transport instance to use.
	 */
	protected $transport = null;

	/**
	 * Retrieve the scribeClient instance to write to.
	 *
	 * @return     scribeClient The scribeClient instance to write to.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.4
	 */
	protected function getScribeClient()
	{
		if(!$this->scribeClient) {
			$socket = new TSocket($this->getParameter('socket_host', 'localhost'), $this->getParameter('socket_port', 1463), $this->getParameter('socket_persist', false));
			$this->transport = new TFramedTransport($socket);
			$protocol = new TBinaryProtocol($this->transport, $this->getParameter('transport_strict_read', false), $this->getParameter('transport_strict_write', true));
			$this->scribeClient = new scribeClient($protocol, $protocol);
			$this->transport->open();
		}
		
		return $this->scribeClient;
	}

	/**
	 * Execute the shutdown procedure.
	 *
	 * Tells the Scribe client to flush and send a shutdown command.
	 * Underlying sockets will be auto-closed at the end of the script.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.4
	 */
	public function shutdown()
	{
		if($this->scribeClient) {
			$this->transport->close();
		}
	}

	/**
	 * Write log data to this appender.
	 *
	 * @param      AgaviLoggerMessage Log data to be written.
	 *
	 * @throws     <b>AgaviLoggingException</b> if no Layout is set or the stream
	 *                                          cannot be written.
	 *
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.4
	 */
	public function write(AgaviLoggerMessage $message)
	{
		if(($layout = $this->getLayout()) === null) {
			throw new AgaviLoggingException('No Layout set');
		}
		
		$this->getScribeClient()->Log(array(new LogEntry(array(
			'category' => $message->getParameter('scribe_category', $this->getParameter('default_category', 'default')),
			'message' => (string)$this->getLayout()->format($message),
		))));
	}
}

?>
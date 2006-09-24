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
 * AgaviFileAppender appends AgaviMessages to a given file.
 *
 * @package    agavi
 * @subpackage logging
 *
 * @author     Bob Zoller <bob@agavi.org>
 * @copyright  (c) Authors
 * @since      0.10.0
 *
 * @version    $Id$
 */
class AgaviFileAppender extends AgaviAppender
{
	/**
	 * @var        The resource of the file this appender is writing to.
	 */
	protected $handle = null;

	/**
	 * @var        The name of the file this appender is writing to.
	 */
	protected $filename = '';

	/**
	 * Initialize the object.
	 *
	 * @param      AgaviContext An AgaviContext instance.
	 * @param      array        An associative array of initialization parameters.
	 *
	 * @author     Bob Zoller <bob@agavi.org>
	 * @since      0.10.0
	 */
	function initialize(AgaviContext $context, array $parameters = array())
	{
		parent::initialize($context, $parameters);

		if(isset($parameters['file'])) {
			$this->filename = $parameters['file'];
		}
	}

	/**
	 * Retrieve the file handle for this FileAppender.
	 *
	 * @throws     <b>AgaviLoggingException</b> if file cannot be opened for
	 *                                          appending.
	 *
	 * @return     integer
	 *
	 * @author     Bob Zoller <bob@agavi.org>
	 * @since      0.10.0
	 */
	protected function getHandle()
	{
		if(is_null($this->handle)) {
			if(!$this->handle = fopen($this->filename, 'a')) {
				throw new AgaviLoggingException("Cannot open file (" . $this->filename . ")");
			}
		}
		return $this->handle;
	}

	/**
	 * Execute the shutdown procedure.
	 *
	 * If open, close the filehandle.
	 *
	 * @author     Bob Zoller <bob@agavi.org>
	 * @since      0.10.0
	 */
	public function shutdown()
	{
		if(!is_null($this->handle)) {
			fclose($this->handle);
		}
	}

	/**
	 * Write a Message to the file.
	 *
	 * @param      mixed Message
	 *
	 * @throws     <b>AgaviLoggingException</b> if no Layout is set or the file
	 *                                          cannot be written.
	 *
	 *
	 * @author     Bob Zoller <bob@agavi.org>
	 * @since      0.10.0
	 */
	public function write($message)
	{
		if(($layout = $this->getLayout()) === null) {
			throw new AgaviLoggingException('No Layout set');
		}

		$str = sprintf("%s\n", $this->getLayout()->format($message));
		if(fwrite($this->getHandle(), $str) === FALSE) {
			throw new AgaviLoggingException("Cannot write to file ( " . $this->filename . ")");
		}
	}
}

?>
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
 * AgaviConsoleResponse handles command line responses.
 *
 * @package    agavi
 * @subpackage response
 *
 * @author     David Zülke <david.zuelke@bitextender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      1.0.0
 *
 * @version    $Id$
 */
class AgaviConsoleResponse extends AgaviResponse
{
	/**
	 * @var        string The content to send back with this response.
	 */
	protected $content = '';
	
	/**
	 * @var        int The shell exit code.
	 */
	protected $exitCode = 0;
	
	/**
	 * Import response metadata (nothing in this case) from another response.
	 *
	 * @param      AgaviResponse The other response to import information from.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.0
	 */
	public function merge(AgaviResponse $otherResponse)
	{
		parent::merge($otherResponse);
	}
	
	/**
	 * Redirect externally. Not implemented here.
	 *
	 * @param      mixed Where to redirect.
	 *
	 * @throws     BadMethodCallException
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.0
	 */
	public function setRedirect($to)
	{
		throw new BadMethodCallException('Redirects are not implemented for Console.');
	}
	
	/**
	 * Get info about the set redirect. Not implemented here.
	 *
	 * @return     array An assoc array of redirect info, or null if none set.
	 *
	 * @throws     BadMethodCallException
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.0
	 */
	public function getRedirect()
	{
		throw new BadMethodCallException('Redirects are not implemented for Console.');
	}

	/**
	 * Check if a redirect is set. Not implemented here.
	 *
	 * @return     bool true, if a redirect is set, otherwise false
	 *
	 * @throws     BadMethodCallException
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.0
	 */
	public function hasRedirect()
	{
		throw new BadMethodCallException('Redirects are not implemented for Console.');
	}

	/**
	 * Clear any set redirect information. Not implemented here.
	 *
	 * @throws     BadMethodCallException
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.0
	 */
	public function clearRedirect()
	{
		throw new BadMethodCallException('Redirects are not implemented for Console.');
	}
	
	/**
	 * Set the shell exit code of this response.
	 *
	 * @param      int The exit code.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.0
	 */
	public function setExitCode($exitCode)
	{
		$this->exitCode = (int)$exitCode;
	}
	
	/**
	 * Get the shell exit code of this response.
	 *
	 * @return     int The exit code.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.0
	 */
	public function getExitCode()
	{
		return $this->exitCode;
	}
	
	/**
	 * Determine whether the content in the response may be modified by appending
	 * or prepending data using string operations. Typically false for streams, 
	 * and for responses like XMLRPC where the content is an array.
	 *
	 * @return     bool If the content can be treated as / changed like a string.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.0
	 */
	public function isContentMutable()
	{
		return !is_resource($this->content);
	}
	
	/**
	 * Send all response data to the client.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.0
	 */
	public function send(AgaviOutputType $outputType = null)
	{
		$this->sendContent();
		
		register_shutdown_function(array($this, 'sendExit'));
	}
	
	/**
	 * Clear all response data.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.0
	 */
	public function clear()
	{
		$this->clearContent();
		$this->setExitCode(0);
	}
	
	/**
	 * Send the content for this response
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.0
	 */
	protected function sendContent()
	{
		$isContentMutable = $this->isContentMutable();
		
		parent::sendContent();
		
		if($isContentMutable && $this->getParameter('append_eol', true)) {
			echo PHP_EOL;
		}
	}
	
	/**
	 * Call exit() and submit the exit code.
	 * This is called by PHP during script shutdown.
	 * It gets registered as a shutdown function in AgaviConsoleResponse::send().
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.0
	 */
	public function sendExit()
	{
		exit($this->exitCode);
	}
}

?>
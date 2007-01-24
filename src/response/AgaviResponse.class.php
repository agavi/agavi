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
 * AgaviResponse handles the output and other stuff sent back to the client.
 *
 * @package    agavi
 * @subpackage response
 *
 * @author     David Zuelke <dz@bitxtender.com>
 * @copyright  (c) Authors
 * @since      0.11.0
 *
 * @version    $Id$
 */
abstract class AgaviResponse extends AgaviParameterHolder
{
	/**
	 * @var        AgaviContext An AgaviContext instance.
	 */
	protected $context = null;
	
	/**
	 * @var        bool Indicates whether or not modifications are allowed.
	 */
	protected $locked = false;
	
	/**
	 * @var        mixed The content to send back to the client.
	 */
	protected $content = null;
	
	/**
	 * Retrieve the AgaviContext instance this Response object belongs to.
	 *
	 * @return     AgaviContext An AgaviContext instance.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getContext()
	{
		return $this->context;
	}
	
	/**
	 * Initialize this Response.
	 *
	 * @param      AgaviContext An AgaviContext instance.
	 * @param      array        An array of initialization parameters.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function initialize(AgaviContext $context, array $parameters = array())
	{
		$this->context = $context;
		$this->setParameters($parameters);
	}
	
	/**
	 * Retrieve the content set for this Response
	 *
	 * @return     mixed The content set in this Response.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getContent()
	{
		return $this->content;
	}
	
	/**
	 * Set the content for this Response.
	 *
	 * @param      mixed The content to be sent in this Response.
	 *
	 * @return     bool Whether or not the operation was successful.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function setContent($content)
	{
		$this->content = $content;
		return true;
	}
	
	/**
	 * Prepend content to the existing content for this Response.
	 *
	 * @param      mixed The content to be prepended to this Response.
	 *
	 * @return     bool Whether or not the operation was successful.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function prependContent($content)
	{
		return $this->setContent($content . $this->getContent());
	}
	
	/**
	 * Append content to the existing content for this Response.
	 *
	 * @param      mixed The content to be appended to this Response.
	 *
	 * @return     bool Whether or not the operation was successful.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function appendContent($content)
	{
		return $this->setContent($this->getContent() . $content);
	}
	
	/**
	 * Clear the content for this Response
	 *
	 * @return     bool Whether or not the operation was successful.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function clearContent()
	{
		$this->content = '';
		return true;
	}
	
	/**
	 * Redirect externally.
	 *
	 * @param      mixed Where to redirect.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	abstract public function setRedirect($to);

	/**
	 * Import response metadata from another response.
	 *
	 * @param      AgaviResponse The other response to import information from.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	abstract public function merge(AgaviResponse $otherResponse);
	
	/**
	 * Clear all data for this Response.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	abstract public function clear();
	
	/**
	 * Send all response data to the client.
	 *
	 * @param      AgaviOutputType An optional Output Type object with information
	 *                             the response can use to send additional data.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	abstract public function send(AgaviOutputType $outputType = null);
	
	/**
	 * Send the content for this response
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	protected function sendContent()
	{
		echo $this->content;
	}
}

?>
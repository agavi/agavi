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
	 * @var        mixed The content to send back to the client.
	 */
	protected $content = null;
	
	/**
	 * Pre-serialization callback.
	 *
	 * Will set the name of the context and exclude the instance from serializing.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function __sleep()
	{
		$this->contextName = $this->context->getName();
		if(is_resource($this->content)) {
			$this->contentStreamMeta = stream_get_meta_data($this->content);
		}
		$arr = get_object_vars($this);
		unset($arr['context']);
		if(isset($this->contentStreamMeta)) {
			unset($arr['content']);
		}
		return array_keys($arr);
	}
	
	/**
	 * Post-unserialization callback.
	 *
	 * Will restore the context based on the names set by __sleep.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function __wakeup()
	{
		$this->context = AgaviContext::getInstance($this->contextName);
		unset($this->contextName);
		if(isset($this->contentStreamMeta)) {
			// contrary to what the documentation says, stream_get_meta_data() will not return a list of filters attached to the stream, so we cannot restore these, unfortunately.
			$this->content = fopen($this->contentStreamMeta['uri'], $this->contentStreamMeta['mode']);
			unset($this->contentStreamMeta);
		}
	}
	
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
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function setContent($content)
	{
		$this->content = $content;
	}
	
	/**
	 * Prepend content to the existing content for this Response.
	 *
	 * @param      mixed The content to be prepended to this Response.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function prependContent($content)
	{
		$this->setContent($content . $this->getContent());
	}
	
	/**
	 * Append content to the existing content for this Response.
	 *
	 * @param      mixed The content to be appended to this Response.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function appendContent($content)
	{
		$this->setContent($this->getContent() . $content);
	}
	
	/**
	 * Clear the content for this Response
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function clearContent()
	{
		$this->content = null;
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
		if(is_resource($this->content)) {
			fpassthru($this->content);
			fclose($this->content);
		} else {
			echo $this->content;
		}
	}
}

?>
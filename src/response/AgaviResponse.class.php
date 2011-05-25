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
 * AgaviResponse handles the output and other stuff sent back to the client.
 *
 * @package    agavi
 * @subpackage response
 *
 * @author     David Zülke <dz@bitxtender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.11.0
 *
 * @version    $Id$
 */
abstract class AgaviResponse extends AgaviAttributeHolder
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
	 * @var        AgaviOutputType The output type of this response.
	 */
	protected $outputType = null;
	
	/**
	 * Pre-serialization callback.
	 *
	 * Will set the name of the context and exclude the instance from serializing.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function __sleep()
	{
		$vars = get_object_vars($this);
		$also = array();
		
		$this->contextName = $this->context->getName();
		unset($vars['context']);
		$also[] = 'contextName';
		
		if($this->outputType) {
			$this->outputTypeName = $this->outputType->getName();
			unset($vars['outputType']);
			$also[] = 'outputTypeName';
		}
		
		if(is_resource($this->content)) {
			$this->contentStreamMeta = stream_get_meta_data($this->content);
			unset($vars['content']);
			$also[] = 'contentStreamMeta';
		}
		
		return array_merge(array_keys($vars), $also);
	}
	
	/**
	 * Post-unserialization callback.
	 *
	 * Will restore the context based on the names set by __sleep.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function __wakeup()
	{
		$this->context = AgaviContext::getInstance($this->contextName);
		unset($this->contextName);
		
		if(isset($this->outputTypeName)) {
			$this->outputType = $this->context->getController()->getOutputType($this->outputTypeName);
			unset($this->outputTypeName);
		}
		
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
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public final function getContext()
	{
		return $this->context;
	}
	
	/**
	 * Initialize this Response.
	 *
	 * @param      AgaviContext An AgaviContext instance.
	 * @param      array        An array of initialization parameters.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function initialize(AgaviContext $context, array $parameters = array())
	{
		$this->context = $context;
		$this->setParameters($parameters);
	}
	
	/**
	 * Get the Output Type to use with this response.
	 *
	 * @return     AgaviOutputType The Output Type instance associated with.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.1
	 */
	public function getOutputType()
	{
		return $this->outputType;
	}
	
	/**
	 * Set the Output Type to use with this response.
	 *
	 * @param      AgaviOutputType The Output Type instance to associate with.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.1
	 */
	public function setOutputType(AgaviOutputType $outputType)
	{
		$this->outputType = $outputType;
	}
	
	/**
	 * Clear the Output Type to use with this response.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.1
	 */
	public function clearOutputType()
	{
		$this->outputType = null;
	}
	
	/**
	 * Retrieve the content set for this Response.
	 *
	 * @return     mixed The content set in this Response.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getContent()
	{
		return $this->content;
	}
	
	/**
	 * Check whether or not some content is set.
	 *
	 * @return     bool If any content is set, false otherwise.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      0.11.6
	 */
	public function hasContent()
	{
		return $this->content !== null;
	}
	
	/**
	 * Retrieve the size (in bytes) of the content set for this Response.
	 *
	 * @return     int The content size in bytes.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getContentSize()
	{
		if(is_resource($this->content)) {
			if(($stat = fstat($this->content)) !== false) {
				return $stat['size'];
			} else {
				return false;
			}
		} else {
			return strlen($this->content);
		}
	}
	
	/**
	 * Set the content for this Response.
	 *
	 * @param      mixed The content to be sent in this Response.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
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
	 * @author     David Zülke <dz@bitxtender.com>
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
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function appendContent($content)
	{
		$this->setContent($this->getContent() . $content);
	}
	
	/**
	 * Clear the content for this Response
	 *
	 * @author     David Zülke <dz@bitxtender.com>
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
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	abstract public function setRedirect($to);

	/**
	 * Get info about the set redirect.
	 *
	 * @return     array An assoc array of redirect info, or null if none set.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	abstract public function getRedirect();

	/**
	 * Check if a redirect is set.
	 *
	 * @return     bool true, if a redirect is set, otherwise false
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	abstract public function hasRedirect();

	/**
	 * Clear any set redirect information.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	abstract public function clearRedirect();

	/**
	 * Import response metadata from another response.
	 *
	 * @param      AgaviResponse The other response to import information from.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function merge(AgaviResponse $otherResponse)
	{
		foreach($otherResponse->getAttributeNamespaces() as $namespace) {
			foreach($otherResponse->getAttributes($namespace) as $name => $value) {
				if(!$this->hasAttribute($name, $namespace)) {
					$this->setAttribute($name, $value, $namespace);
				} elseif(is_array($value)) {
					$thisAttribute =& $this->getAttribute($name, $namespace);
					if(is_array($thisAttribute)) {
						$thisAttribute = array_merge($value, $thisAttribute);
					}
				}
			}
		}
	}
	
	/**
	 * Clear all data for this Response.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	abstract public function clear();
	
	/**
	 * Send all response data to the client.
	 *
	 * @param      AgaviOutputType An optional Output Type object with information
	 *                             the response can use to send additional data.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	abstract public function send(AgaviOutputType $outputType = null);
	
	/**
	 * Determine whether the content in the response may be modified by appending
	 * or prepending data using string operations. Typically false for streams, 
	 * and for responses like XMLRPC where the content is an array.
	 *
	 * @return     bool If the content can be treated as / changed like a string.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function isContentMutable()
	{
		return !$this->hasRedirect() && !is_resource($this->content);
	}
	
	/**
	 * Send the content for this response
	 *
	 * @author     David Zülke <dz@bitxtender.com>
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
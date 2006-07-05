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
abstract class AgaviResponse
{
	/**
	 * @var        AgaviContext An AgaviContext instance.
	 */
	protected $context = null;
	
	/**
	 * @var        bool Indicates new content has been set since the last output.
	 */
	protected $dirty = false;
	
	/**
	 * @var        bool Indicates whether or not modifications are allowed.
	 */
	protected $locked = false;
	
	/**
	 * @var        string The content to send back to the client.
	 */
	protected $content = '';
	
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
	public function initialize(AgaviContext $context, $parameters = array())
	{
		$this->context = $context;
	}
	
	/**
	 * Export the contents of this response.
	 *
	 * @return     array An array of data.
	 *
	 * @author     David Zuelke <du@bitxtender.com>
	 * @since      0.11.0
	 */
	public function export()
	{
		return array('content' => $this->getContent(), 'locked' => $this->isLocked());
	}
	
	/**
	 * Export the information data (e.g. HTTP Headers, Cookies) for this response.
	 *
	 * @return     array An array of data.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function exportInfo()
	{
		return array('locked' => $this->isLocked());
	}
	
	/**
	 * Import data for this response.
	 *
	 * @param      array An array of data.
	 *
	 * @return     bool Whether or not the operation was successful.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function import($data)
	{
		$retval = true;
		if(isset($data['content'])) {
			$retval = $this->setContent($data['content']);
		}
		if(isset($data['locked']) && $data['locked']) {
			$this->lock();
		}
		return $retval;
	}
	
	/**
	 * Merge in data for this response.
	 *
	 * @param      array An array of data.
	 *
	 * @return     bool Whether or not the operation was successful.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function merge($data)
	{
		// do not lock the response even if $data has locked=true!
		
		if(isset($data['content'])) {
			return $this->appendContent($data['content']);
		}
		return true;
	}
	
	/**
	 * Append data to this response.
	 *
	 * @param      array An array of data.
	 *
	 * @return     bool Whether or not the operation was successful.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function append($data)
	{
		// do not lock the response even if $data has locked=true!
		
		if(isset($data['content'])) {
			return $this->appendContent($data['content']);
		}
		return true;
	}
	
	/**
	 * Indicates whether or not the Response is "dirty", i.e. if new content has
	 * been set since the last sending of data.
	 *
	 * @return     bool
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function isDirty()
	{
		return $this->dirty;
	}
	
	/**
	 * Check if this Response is locked, i.e. whether or not new content and other
	 * output information can be set.
	 *
	 * @return     bool
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function isLocked()
	{
		return $this->locked;
	}
	
	/**
	 * Lock this Response so that it does not accept any modifications.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function lock()
	{
		$this->locked = true;
	}
	
	/**
	 * Retrieve the content set for this Response
	 *
	 * @return     string The content set in this Response.
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
	 * @param      string The content to be sent in this Response.
	 *
	 * @return     bool Whether or not the operation was successful.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function setContent($content)
	{
		if(!$this->locked && $content != $this->content) {
			$this->content = $content;
			$this->dirty = true;
			return true;
		}
		return false;
	}
	
	/**
	 * Prepend content to the existing content for this Response.
	 *
	 * @param      string The content to be prepended to this Response.
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
	 * @param      string The content to be appended to this Response.
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
		$empty = '';
		if(!$this->locked && $this->content != $empty) {
			$this->content = $empty;
			$this->dirty = true;
			return true;
		}
		return false;
	}
	
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
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	abstract public function send();
	
	/**
	 * Send the content for this response
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function sendContent()
	{
		echo $this->content;
	}
}

?>
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
	 * @var        AgaviContext A Context instance.
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
	 * @var        string The currently set Output Type.
	 */
	protected $outputType = null;
	
	/**
	 * @var        array An array of registered Output Types.
	 */
	protected $outputTypes = array();
	
	/**
	 * Retrieve the Context instance this Response object belongs to.
	 *
	 * @return     AgaviContext A Context instance.
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
	 * @param      AgaviContext A Context instance.
	 * @param      array        An array of initialization parameters.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function initialize(AgaviContext $context, $parameters = array())
	{
		$this->context = $context;
		
		$cfg = AgaviConfig::get('core.config_dir') . '/output_types.xml';
		require_once(AgaviConfigCache::checkConfig($cfg, $context->getName()));
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
		return $this->dirty();
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
	 * Set the content for this Response
	 *
	 * @param      string The content to be sent in this Response.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function setContent($content)
	{
		if(!$this->locked && $content != $this->content) {
			$this->content = $content;
			$this->dirty = true;
		}
	}
	
	/**
	 * Clear the content for this Response
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
		}
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
	
	/**
	 * Sets an output type for this response.
	 *
	 * @param      string The output type name.
	 *
	 * @throws     <b>AgaviException</b> If the given output type doesnt exist.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function setOutputType($outputType)
	{
		if(isset($this->outputTypes[$outputType])) {
			$this->outputType = $outputType;
			return;
		} else {
			throw new AgaviException('Output Type "' . $outputType . '" has not been configured.');
		}
	}
	
	/**
	 * Retrieves the output type name set for this response.
	 *
	 * @return     string The name of the output type.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getOutputType()
	{
		return $this->outputType;
	}
	
	/**
	 * Retrieve configuration details about an output type.
	 *
	 * @param      string The output type name.
	 *
	 * @return     array An associative array of output type settings and params.
	 *
	 * @throws     <b>AgaviException</b> If the given output type doesnt exist.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getOutputTypeInfo($outputType = null)
	{
		if($outputType === null) {
			$outputType = $this->outputType;
		}
		if(isset($this->outputTypes[$outputType])) {
			return $this->outputTypes[$outputType];
		} else {
			throw new AgaviException('Output Type "' . $outputType . '" has not been configured.');
		}
	}
}

?>
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
 * A template layer wraps information necessary to render a template.
 *
 * @package    agavi
 * @subpackage view
 *
 * @author     David Zuelke <dz@bitxtender.com>
 * @author     Agavi Project <info@agavi.org>
 * @copyright  (c) Authors
 * @since      0.11.0
 *
 * @version    $Id$
 */
class AgaviTemplateLayer
{
	/**
	 * @var        AgaviContext The current Context.
	 */
	protected $context = null;
	
	/**
	 * @var        AgaviRenderer The Renderer instance to be used for this layer.
	 */
	protected $renderer = null;
	
	/**
	 * @var        string The name of the template.
	 */
	protected $template = null;
	
	/**
	 * @var        string The directory of the template.
	 */
	protected $templateDir = null;
	
	/**
	 * @var        array An associative array of execution containers for slots.
	 */
	protected $slots = array();
	
	/**
	 * Initialize the layer.
	 *
	 * @param      AgaviContext The current Context instance.
	 * @param      array        An array of initialization parameters.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function initialize(AgaviContext $context, array $parameters = array())
	{
		$this->context = $context;
	}
	
	/**
	 * Set a renderer instance to use for this layer.
	 *
	 * @param      AgaviRenderer A renderer instance.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function setRenderer(AgaviRenderer $renderer)
	{
		$this->renderer = $renderer;
	}
	
	/**
	 * Get the renderer instance used for this layer.
	 *
	 * @return     AgaviRenderer A renderer instance.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getRenderer()
	{
		return $this->renderer;
	}
	
	/**
	 * Set a slot that is rendered along with and available inside this layer.
	 *
	 * @param      string                  The name of the slot.
	 * @param      AgaviExecutionContainer The slot's execution container.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function setSlot($name, AgaviExecutionContainer $c)
	{
		$this->slots[$name] = $c;
	}
	
	/**
	 * Get the execution container for a slot.
	 *
	 * @param      string The name of the slot.
	 *
	 * @return     AgaviExecutionContainer The slot's execution container.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getSlot($name)
	{
		if(isset($this->slots[$name])) {
			return $this->slots[$name];
		}
	}
	
	/**
	 * Get all slots.
	 *
	 * @return     array An associative array of slot names and exec containers.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getSlots()
	{
		return $this->slots;
	}
	
	/**
	 * Check whether or not a slot has been set.
	 *
	 * @param      string The name of the slot.
	 *
	 * @return     bool True if the slot exists, false otherwise.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function hasSlot($name)
	{
		return isset($this->slots[$name]);
	}
	
	/**
	 * Check if any slots have been set.
	 *
	 * @return     bool true if any slots are defined, falseotherwise.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function hasSlots()
	{
		return (count($this->slots) > 0);
	}
	
	/**
	 * Remove a slot.
	 *
	 * @param      string The name of the slot.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function removeSlot($name)
	{
		if(isset($this->slots[$name])) {
			unset($this->slots[$name]);
		}
	}
	
	/**
	 * Set a template.
	 *
	 * @param      string The template name.
	 * @param      bool   Whether the template name is to be taken literally.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function setTemplate($template, $literal = false)
	{
		if($template === null) {
			$this->template = null;
			return;
		}
		if(AgaviToolkit::isPathAbsolute($template)) {
			$this->templateDir = dirname($template);
			$this->template  = array(basename($template), $literal);
		} else {
			$this->template = array($template, $literal);
		}
	}
	
	/**
	 * Set the template directory.
	 *
	 * @param      string The directory name.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function setTemplateDir($dir)
	{
		$this->templateDir = $dir;
	}
	
	/**
	 * Get the template name.
	 *
	 * @return     string The name of the template.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getTemplate()
	{
		return $this->template;
	}
	
	/**
	 * Get the template directory.
	 *
	 * @return     string The directory name.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getTemplateDir()
	{
		return $this->templateDir;
	}
	
	/**
	 * Get the full name to the template resource.
	 *
	 * @return     string A resource identifier.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getResourceName()
	{
		return $this->getTemplate() . DIRECTORY_SEPARATOR . $this->getTemplateDir();
	}
}

?>
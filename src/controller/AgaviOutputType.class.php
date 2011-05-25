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
 * This class holds information about an Output Type.
 * 
 * @package    agavi
 * @subpackage controller
 * 
 * @author     David Zülke <dz@bitxtender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.11.0
 *
 * @version    $Id$
 */
class AgaviOutputType extends AgaviParameterHolder
{
	/**
	 * @var        AgaviContext The context instance.
	 */
	protected $context = null;
	
	/**
	 * @var        string The name of the Output Type.
	 */
	protected $name = '';
	
	/**
	 * @var        array An array of Renderers (settings and instances).
	 */
	protected $renderers = array();
	
	/**
	 * @var        string The name of the default Renderer, if set.
	 */
	protected $defaultRenderer = null;
	
	/**
	 * @var        array An array of configured layouts.
	 */
	protected $layouts = array();
	
	/**
	 * @var        string The name of the default layout, if set.
	 */
	protected $defaultLayout = null;
	
	/**
	 * @var        string The name of the exception template for this output type.
	 */
	protected $exceptionTemplate = null;
	
	/**
	 * Initialize the Output Type.
	 *
	 * @param      AgaviContext The current Context instance.
	 * @param      array        An array of initialization parameters.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function initialize(AgaviContext $context, array $parameters, $name, array $renderers, $defaultRenderer, array $layouts, $defaultLayout, $exceptionTemplate = null)
	{
		$this->context = $context;
		
		$this->parameters = $parameters;
		
		$this->name = $name;
		
		$this->renderers = $renderers;
		
		$this->defaultRenderer = $defaultRenderer;
		
		$this->layouts = $layouts;
		
		$this->defaultLayout = $defaultLayout;
		
		$this->exceptionTemplate = $exceptionTemplate;
	}
	
	/**
	 * Get the name of the Output Type.
	 *
	 * @return     string The name of the Output Type.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getName()
	{
		return $this->name;
	}
	
	/**
	 * @see        AgaviOutputType::getName()
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public final function __toString()
	{
		return $this->getName();
	}
	
	/**
	 * Checks whether or not any renderers are defined for this Output Type.
	 *
	 * @return     bool True, if renderers are defined, false otherwise.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function hasRenderers()
	{
		return (count($this->renderers) > 0);
	}
	
	/**
	 * Get a renderer instance.
	 *
	 * @param      string The optional name of the Renderer to fetch.
	 *
	 * @return     AgaviRenderer A Renderer instance or null if none defined.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getRenderer($name = null)
	{
		if(count($this->renderers) == 0) {
			return null;
		} elseif($name === null) {
			$name = $this->defaultRenderer;
		}
		if(isset($this->renderers[$name])) {
			if($this->renderers[$name]['instance'] === null) {
				$renderer = new $this->renderers[$name]['class']();
				$renderer->initialize($this->context, $this->renderers[$name]['parameters']);
				if(isset($this->renderers[$name]['extension'])) {
					$renderer->setExtension($this->renderers[$name]['extension']);
				}
				if($renderer instanceof AgaviIReusableRenderer) {
					$this->renderers[$name]['instance'] = $renderer;
				}
				return $renderer;
			} else {
				return $this->renderers[$name]['instance'];
			}
		} else {
			throw new AgaviException('Unknown renderer "' . $name . '"');
		}
	}
	
	/**
	 * Get the name of the default layout.
	 *
	 * @return     string The name of the default layout, or null if none defined.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getDefaultLayoutName()
	{
		return $this->defaultLayout;
	}
	
	/**
	 * Get a layout.
	 *
	 * @param      The optional name of the layout to fetch.
	 *
	 * @return     array An array of layout information.
	 *
	 * @throws     AgaviException If the layout doesn't exist.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getLayout($name = null)
	{
		if($name === null) {
			$name = $this->defaultLayout;
		}
		
		if(isset($this->layouts[$name])) {
			return $this->layouts[$name];
		} else {
			throw new AgaviException('Unknown layout "' . $name . '"');
		}
	}
	
	/**
	 * Get the exception template filename for this renderer.
	 *
	 * @return     string A path to the exception template, or null if undefined.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getExceptionTemplate()
	{
		return $this->exceptionTemplate;
	}
}

?>
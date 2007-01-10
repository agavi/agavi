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
 * This class holds information about an Output Type.
 * 
 * @package    agavi
 * @subpackage controller
 * 
 * @author     Agavi Project <info@agavi.org>
 * @author     David Zuelke <dz@bitxtender.com>
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
	 * @var        string The name of the exception template for this output type.
	 */
	protected $exceptionTemplate = null;
	
	/**
	 * Initialize the Output Type.
	 *
	 * @param      AgaviContext The current Context instance.
	 * @param      array        An array of initialization parameters.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function initialize(AgaviContext $context, array $parameters, $name, array $renderers, $defaultRenderer, $exceptionTemplate = null)
	{
		$this->context = $context;
		
		$this->parameters = $parameters;
		
		$this->name = $name;
		
		$this->renderers = $renderers;
		
		$this->defaultRenderer = $defaultRenderer;
		
		$this->exceptionTemplate = $exceptionTemplate;
	}
	
	/**
	 * Get the name of the Output Type.
	 *
	 * @return     string The name of the Output Type.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getName()
	{
		return $this->name;
	}
	
	/**
	 * @see        AgaviOutputType::getName()
	 */
	final public function __toString()
	{
		return $this->getName();
	}
	
	/**
	 * Checks whether or not any renderers are defined for this Output Type.
	 *
	 * @return     bool True, if renderers are defined, false otherwise.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
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
	 * @author     David Zuelke <dz@bitxtender.com>
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
			$r =& $this->renderers[$name];
			$ri =& $r['instance'];
			if($ri === null) {
				$renderer = new $r['class']();
				$renderer->initialize($this->context, $r['parameters']);
				if(isset($r['extension'])) {
					$renderer->setExtension($r['extension']);
				}
				if($renderer instanceof AgaviIReusableRenderer) {
					$ri =& $renderer;
				}
				return $renderer;
			} else {
				return $ri;
			}
		} else {
			throw new AgaviException('Unknown renderer "' . $name . '"');
		}
		return $this->renderers[$name];
	}
	
	/**
	 * Get the exception template filename for this renderer.
	 *
	 * @return     string A path to the exception template, or null if undefined.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getExceptionTemplate()
	{
		return $this->exceptionTemplate;
	}
}

?>
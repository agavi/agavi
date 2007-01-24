<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2003-2006 the Agavi Project.                                |
// | Based on the Mojavi3 MVC Framework, Copyright (c) 2003-2005 Sean Kerr.    |
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
 * A view represents the presentation layer of an action. Output can be
 * customized by supplying attributes, which a template can manipulate and
 * display.
 *
 * @package    agavi
 * @subpackage view
 *
 * @author     Sean Kerr <skerr@mojavi.org>
 * @author     Agavi Project <info@agavi.org>
 * @copyright  (c) Authors
 * @since      0.9.0
 *
 * @version    $Id$
 */
abstract class AgaviView
{
	/**
	 * @since      0.9.0
	 */
	const NONE = null;

	/**
	 * Render the presentation to the client.
	 *
	 * @since      0.9.0
	 */
	const RENDER_CLIENT = 2;

	/**
	 * Do not render the presentation.
	 *
	 * @since      0.9.0
	 */
	const RENDER_NONE = 1;

	/**
	 * Render the presentation to a variable.
	 *
	 * @since      0.9.0
	 */
	const RENDER_VAR = 4;

	/**
	 * @var        AgaviExecutionContainer This view's execution container.
	 */
	protected $container = null;
	
	/**
	 * @var        AgaviContext The AgaviContext instance this View belongs to.
	 */
	protected $context = null;
	
	/**
	 * @var        array An array of defined layers.
	 */
	protected $layers = array();
	
	/**
	 * Execute any presentation logic and set template attributes.
	 *
	 * @param      AgaviRequestDataHolder The action's request data holder.
	 *
	 * @return     AgaviExecutionContainer An array of forwarding information in
	 *                                     case a forward should occur, or null.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.9.0
	 */
	abstract function execute(AgaviRequestDataHolder $r);

	/**
	 * Retrieve the current application context.
	 *
	 * @return     AgaviContext The current AgaviContext instance.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public final function getContext()
	{
		return $this->context;
	}

	/**
	 * Retrieve the execution container for this action.
	 *
	 * @return     AgaviExecutionContainer This action's execution container.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public final function getContainer()
	{
		return $this->container;
	}

	/**
	 * Retrieve the Response instance for this View.
	 *
	 * @return     AgaviResponse The Response instance.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public final function getResponse()
	{
		return $this->container->getResponse();
	}

	/**
	 * Initialize this view.
	 *
	 * @param      AgaviResponse The Response for this Action/View.
	 * @param      array         The attributes for this View.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function initialize(AgaviExecutionContainer $container)
	{
		$this->container = $container;
		
		$this->context = $container->getContext();
		
		$this->response = $container->getResponse();
	}

	public function createLayer($class, $name, $renderer = null)
	{
		$layer = new $class();
		if(!is_subclass_of($layer, 'AgaviTemplateLayer')) {
			throw new AgaviViewException('Class "$class" is not a subclass of AgaviTemplateLayer');
		}
		$layer->initialize($this->context, array('name' => $name, 'module' => $this->container->getViewModuleName(), 'template' => $this->container->getViewName(), 'output_type' => $this->container->getOutputType()->getName()));
		$layer->setRenderer($this->container->getOutputType()->getRenderer($renderer));
		return $layer;
	}
	
	public function appendLayer(AgaviTemplateLayer $layer, AgaviTemplateLayer $otherLayer = null)
	{
		if($otherLayer !== null && in_array($otherLayer, $this->layers, true)) {
			throw new AgaviViewException('Layer "' . $otherLayer->getName() . '" not in list');
		}
		
		if($pos = array_search($layer, $this->layers, true) !== false) {
			// given layer is already in the list, so we remove it first
			array_splice($this->layers, $pos, 1);
		}
		
		if($otherLayer === null) {
			$dest = count($this->layers);
		} else {
			$dest = array_search($otherLayer, $this->layers, true) + 1;
		}
		array_splice($this->layers, $dest, 0, array($layer));
		
		return $layer;
	}
	
	public function prependLayer(AgaviTemplateLayer $layer, AgaviTemplateLayer $otherLayer = null)
	{
		if($otherLayer !== null && in_array($otherLayer, $this->layers, true)) {
			throw new AgaviViewException('Layer "' . $otherLayer->getName() . '" not in list');
		}
		
		if($pos = array_search($layer, $this->layers, true) !== false) {
			// given layer is already in the list, so we remove it first
			array_splice($this->layers, $pos, 1);
		}
		
		if($otherLayer === null) {
			$dest = 0;
		} else {
			$dest = array_search($otherLayer, $this->layers, true);
		}
		array_splice($this->layers, $dest, 0, array($layer));
		
		return $layer;
	}
	
	public function removeLayer(AgaviTemplateLayer $layer)
	{
		if(($pos = array_search($layer, $this->layers, true)) === false) {
			throw new AgaviViewException('Layer "' . $otherLayer->getName() . '" not in list');
		}
		array_splice($this->layers, $pos, 1);
	}
	
	public function clearLayers()
	{
		$this->layers = array();
	}
	
	public function getLayer($name)
	{
		foreach($this->layers as $layer) {
			if($name == $layer->getName()) {
				return $layer;
			}
		}
	}
	
	public function getLayers()
	{
		return $this->layers;
	}
	
	public function loadLayout($layoutName = null)
	{
		$layout = $this->container->getOutputType()->getLayout($layoutName);
		
		$this->clearLayers();
		
		foreach($layout['layers'] as $name => $layer) {
			$l = $this->createLayer($layer['class'], $name, $layer['renderer']);
			$l->setParameters($layer['parameters']);
			foreach($layer['slots'] as $slotName => $slot) {
				$l->setSlot($slotName, $this->container->createExecutionContainer($slot['module'], $slot['action'], new AgaviRequestDataHolder(array('parameters' => $slot['parameters'])), $slot['output_type']));
			}
			$this->appendLayer($l);
		}
	}
	
	/**
	 * @see        AgaviAttributeHolder::setAttributesByRef()
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.9.0
	 */
	public function clearAttributes()
	{
		$this->container->clearAttributes();
	}

	/**
	 * @see        AgaviAttributeHolder::setAttributesByRef()
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.9.0
	 */
	public function & getAttribute($name, $default = null)
	{
		return $this->container->getAttribute($name, null, $default);
	}

	/**
	 * @see        AgaviAttributeHolder::setAttributesByRef()
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.9.0
	 */
	public function getAttributeNames()
	{
		return $this->container->getAttributeNames();
	}

	/**
	 * @see        AgaviAttributeHolder::setAttributesByRef()
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function & getAttributes()
	{
		return $this->container->getAttributes();
	}

	/**
	 * @see        AgaviAttributeHolder::setAttributesByRef()
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.9.0
	 */
	public function hasAttribute($name)
	{
		return $this->container->hasAttribute($name);
	}

	/**
	 * @see        AgaviAttributeHolder::setAttributesByRef()
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.9.0
	 */
	public function & removeAttribute($name)
	{
		return $this->container->removeAttribute($name);
	}
	
	/**
	 * @see        AgaviAttributeHolder::setAttributesByRef()
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.9.0
	 */
	public function setAttribute($name, $value)
	{
		$this->container->setAttribute($name, $value);
	}

	/**
	 * @see        AgaviAttributeHolder::setAttributesByRef()
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.10.0
	 */
	public function appendAttribute($name, $value)
	{
		$this->container->appendAttribute($name, $value);
	}

	/**
	 * @see        AgaviAttributeHolder::setAttributesByRef()
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.9.0
	 */
	public function setAttributeByRef($name, &$value)
	{
		$this->container->setAttributeByRef($name, $value);
	}

	/**
	 * @see        AgaviAttributeHolder::setAttributesByRef()
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.10.0
	 */
	public function appendAttributeByRef($name, &$value)
	{
		$this->container->appendAttributeByRef($name, $value);
	}

	/**
	 * @see        AgaviAttributeHolder::setAttributesByRef()
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.9.0
	 */
	public function setAttributes(array $attributes)
	{
		$this->container->setAttributes($attributes);
	}

	/**
	 * @see        AgaviAttributeHolder::setAttributesByRef()
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.9.0
	 */
	public function setAttributesByRef(array &$attributes)
	{
		$this->container->setAttributesByRef($attributes);
	}
}

?>
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
	 * @return     AgaviParameterHolder An array of forwarding information in case
	 *                                  a forward should occur, otherwise null.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	abstract function execute(AgaviParameterHolder $parameters);

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

	public function addLayer($name, $template, $renderer = null)
	{
		$layer = new AgaviTemplateLayer();
		$layer->initialize($this->context);
		$layer->setTemplate($template);
		$layer->setTemplateDir(AgaviConfig::get('core.module_dir') . DIRECTORY_SEPARATOR . $this->container->getModuleName() . DIRECTORY_SEPARATOR . 'templates');
		$layer->setRenderer($this->container->getOutputType()->getRenderer($renderer));
		$this->layers[$name] = $layer;
		return $layer;
	}

	public function getLayer($name)
	{
		return (isset($this->Layers[$name]) ? $this->layers[$name] : null);
	}
	
	public function getLayers()
	{
		return $this->layers;
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
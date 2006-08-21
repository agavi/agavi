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
abstract class AgaviView extends AgaviAttributeHolder
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
	 * @var        AgaviContext The AgaviContext instance this View belongs to.
	 */
	protected $context = null;
	
	/**
	 * @var        AgaviResponse The Response object for this Action/View.
	 */
	protected $response = null;
	
	/**
	 * @var        bool Whether or not this View is configured to use a decorator.
	 */
	protected $decorator = false;
	
	/**
	 * @var        string The Decorator template directory.
	 */
	protected $decoratorDirectory = null;
	
	/**
	 * @var        array An array containing decorator filename and "literal" flag
	 */
	protected $decoratorTemplate = null;
	
	/**
	 * @var        string The directory of the template.
	 */
	protected $directory = null;
	
	/**
	 * @var        array The slots to be used in the Decorator.
	 */
	protected $slots = array();
	
	/**
	 * @var        array An array containing template file name and "literal" flag
	 */
	protected $template = null;
	
	/**
	 * Execute any presentation logic and set template attributes.
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
	 * Retrieve the Response instance for this View.
	 *
	 * @return     AgaviResponse The Response instance.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public final function getResponse()
	{
		return $this->response;
	}

	/**
	 * Retrieve this views decorator template directory.
	 *
	 * @return     string An absolute filesystem path to this views decorator
	 *                    template directory.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function getDecoratorDirectory()
	{
		return $this->decoratorDirectory;
	}

	/**
	 * Retrieve this views decorator template.
	 *
	 * @return     string A template filename, if a template has been set,
	 *                    otherwise null.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function getDecoratorTemplate()
	{
		return $this->decoratorTemplate;
	}

	/**
	 * Retrieve this views template directory.
	 *
	 * @return     string An absolute filesystem path to this views template
	 *                    directory.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function getDirectory()
	{
		return $this->directory;
	}

	/**
	 * Retrieve an array of specified slots for the decorator template.
	 *
	 * @return     array An associative array of decorator slots.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function getSlots()
	{
		return $this->slots;
	}

	/**
	 * Retrieve this views template.
	 *
	 * @return     string A template filename, if a template has been set,
	 *                    otherwise null.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function getTemplate()
	{
		return $this->template;
	}

	/**
	 * Initialize this view.
	 *
	 * @param      AgaviResponse The Response for this Action/View.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function initialize(AgaviResponse $response, $attributes = array())
	{
		$this->context = $response->getContext();
		
		$this->response = $response;

		// set the currently executing module's template directory as the
		// default template directory
		$this->directory = $this->decoratorDirectory = AgaviConfig::get('core.module_dir') . '/' . $this->context->getController()->getActionStack()->getLastEntry()->getViewModuleName() . '/templates';
		
		$this->setAttributes($attributes);
	}

	/**
	 * Indicates that this view is a decorating view.
	 *
	 * @return     bool true, if this view is a decorating view, otherwise 
	 *                  false.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function isDecorator()
	{
		return $this->decorator;
	}

	/**
	 * Set the decorator template directory for this view.
	 *
	 * @param      string An absolute filesystem path to a template directory.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function setDecoratorDirectory($directory)
	{
		$this->decoratorDirectory = $directory;
	}

	/**
	 * Set the decorator template for this view.
	 *
	 * If the template path is relative, it will be based on the currently
	 * executing module's template sub-directory.
	 *
	 * @param      string An absolute or relative filesystem path to a template.
	 * @param      bool   If set to true, the template name will be forced, i.e.
	 *                    no extension defined by the Renderer or in the Output
	 *                    Type configuration will be appended.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function setDecoratorTemplate($template, $literal = false)
	{
		if(AgaviToolkit::isPathAbsolute($template)) {
			$this->decoratorDirectory = dirname($template);
			$this->decoratorTemplate  = array(basename($template), $literal);
		} else {
			$this->decoratorTemplate = array($template, $literal);
		}

		// set decorator status
		$this->decorator = true;
	}

	/**
	 * Clears out a previously assigned decorator template and directory
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.10.0
	 */
	public function clearDecorator()
	{
		$this->decoratorDirectory = null;
		$this->decoratorTemplate  = null;
		$this->decorator = false;
	}

	/**
	 * Set the template directory for this view.
	 *
	 * @param      string An absolute filesystem path to a template directory.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function setDirectory($directory)
	{
		$this->directory = $directory;
	}

	/**
	 * Set the module and action to be executed in place of a particular
	 * template attribute.
	 *
	 * If a slot with the name already exists, it will be overridden.
	 *
	 * @param      string A template attribute name.
	 * @param      string A module name.
	 * @param      string An action name.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function setSlot($attributeName, $moduleName, $actionName, $additionalParams = array())
	{
		$this->slots[$attributeName] = array(
			'module_name' => $moduleName,
			'action_name' => $actionName,
			'additional_params' => $additionalParams,
		);
	}

	/**
	 * Set an array of slots
	 *
	 * @see        AgaviView::setSlot()
	 * @param      array An array of slots
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.10.0
	 */
	public function setSlots($slots)
	{
		$this->slots = $slots;
	}

	/**
	 * Empties the slots array, clearing all previously registered slots
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.10.0
	 */
	public function clearSlots()
	{
		$this->slots = array();
	}

	/**
	 * Set the template for this view.
	 *
	 * If the template path is relative, it will be based on the currently
	 * executing module's template sub-directory.
	 *
	 * @param      string An absolute or relative filesystem path to a template.
	 * @param      bool   If set to true, the template name will be forced, i.e.
	 *                    no extension defined by the Renderer or in the Output
	 *                    Type configuration will be appended.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function setTemplate($template = null, $literal = false)
	{
		if($template === null) {
			$this->template = null;
			return;
		}
		if(AgaviToolkit::isPathAbsolute($template)) {
			$this->directory = dirname($template);
			$this->template  = array(basename($template), $literal);
		} else {
			$this->template = array($template, $literal);
		}
	}
}

?>
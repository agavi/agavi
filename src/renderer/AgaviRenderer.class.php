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
 * A renderer produces the output as defined by a View
 *
 * @package    agavi
 * @subpackage renderer
 *
 * @author     David Zuelke <dz@bitxtender.com>
 * @author     Agavi Project <info@agavi.org>
 * @copyright  (c) Authors
 * @since      0.11.0
 *
 * @version    $Id$
 */
abstract class AgaviRenderer
{
	/**
	 * A string with the default template file extension, including the dot.
	 */
	protected $extension = '';
	
	/**
	 * An array containing the output of slots and, if a decorator is used, the
	 * output of the content view.
	 */
	protected $output = array();
	
	/**
	 * The View instance that belongs to this Renderer instance.
	 */
	protected $viewInstance = null;
	
	/**
	 * Get the template file extension
	 *
	 * @return     string The extension, including a leading dot.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getExtension()
	{
		return $this->extension;
	}
	
	/**
	 * Set the template file extension
	 *
	 * @param      string The extension, including a leading dot.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function setExtension($extension)
	{
		$this->extension = $extension;
	}

	/**
	 * Retrieve the View instance that belongs to this Renderer instance.
	 *
	 * @return     AgaviView An AgaviView instance
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getView()
	{
		return $this->viewInstance;
	}
	
	/**
	 * Set the View instance that belongs to this Renderer instance.
	 *
	 * @param      AgaviView An AgaviView instance
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function setView(AgaviView $viewInstance)
	{
		$this->viewInstance = $viewInstance;
	}
	
	/**
	 * Loop through all template slots and fill them in with the results of
	 * presentation data.
	 *
	 * @param      string A chunk of decorator content.
	 *
	 * @return     string A decorated template.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function & decorate(&$content)
	{
		$view = $this->getView();
		
		// alias controller
		$controller = $view->getContext()->getController();

		// get original render mode
		$renderMode = $controller->getRenderMode();

		// set render mode to var
		$controller->setRenderMode(AgaviView::RENDER_VAR);

		// grab the action stack
		$actionStack = $controller->getActionStack();

		// loop through our slots, and replace them one-by-one in the
		// decorator template
		$slots = $view->getSlots();

		foreach ($slots as $name => $slot) {
			// grab this next forward's action stack index
			$index = $actionStack->getSize();

			// forward to the first slot action
			$controller->forward($slot['module_name'], $slot['action_name']);

			// grab the action entry from this forward
			$actionEntry = $actionStack->getEntry($index);

			// set the presentation data as a template attribute
			$this->output[$name] =& $actionEntry->getPresentation();
		}

		// put render mode back
		$controller->setRenderMode($renderMode);

		// set the decorator content as an attribute
		$this->output['content'] =& $content;

		// return a null value to satisfy the requirement
		$retval = null;

		return $retval;
	}
	
	/**
	 * Retrieve the template engine associated with this view.
	 *
	 * Note: This will return null for PHPView instances.
	 *
	 * @return     mixed A template engine instance.
	 */
	abstract function & getEngine();

	/**
	 * Execute a basic pre-render check to verify all required variables exist
	 * and that the template is readable.
	 *
	 * @return     void
	 *
	 * @throws     <b>AgaviRenderException</b> If the pre-render check fails.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function preRenderCheck()
	{
		$view = $this->getView();
		
		$oti = $view->getContext()->getController()->getOutputTypeInfo();
		if(isset($oti['ignore_decorators']) && $oti['ignore_decorators']) {
			$view->clearDecorator();
		}
		if(isset($oti['ignore_slots']) && $oti['ignore_slots']) {
			$view->clearSlots();
		}
		
		if($view->getTemplate() == null) {
			// a template has not been set
			$error = 'A template has not been set';

			throw new AgaviRenderException($error);
		}

		$template = $view->getDirectory() . '/' . $view->getTemplate() . $this->getExtension();

		if(!is_readable($template)) {
			// the template isn't readable
			$error = 'The template "%s" does not exist or is unreadable';
			$error = sprintf($error, $template);

			throw new AgaviRenderException($error);
		}

		// check to see if this is a decorator template
		if($view->isDecorator()) {
			
			$template = $view->getDecoratorDirectory() . '/' . $view->getDecoratorTemplate() . $this->getExtension();

			if(!is_readable($template)) {
				// the decorator template isn't readable
				$error = 'The decorator template "%s" does not exist or is ' .
						 'unreadable';
				$error = sprintf($error, $template);

				throw new AgaviRenderException($error);
			}
		}
	}

	/**
	 * Render the presentation.
	 *
	 * When the controller render mode is View::RENDER_CLIENT, this method will
	 * render the presentation directly to the client and null will be returned.
	 *
	 * @return     string A string representing the rendered presentation, if
	 *                    the controller render mode is View::RENDER_VAR,
	 *                    otherwise null.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	abstract function & render();

}
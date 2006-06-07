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
class AgaviPhpRenderer extends AgaviRenderer
{
	protected $extension = '.php';
	
	/**
	 * Loop through all template slots and fill them in with the results of
	 * presentation data.
	 *
	 * @param      string A chunk of decorator content.
	 *
	 * @return     string A decorated template.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.11.0
	 */
	public function decorate(&$content)
	{
		// call our parent decorate() method
		parent::decorate($content);

		$view = $this->getView();
		
		// alias the attributes array so it's directly accessible to the
		// template
		$attribs =& $view->getAttributes();
		
		$template =& array_merge($attribs, $this->output);

		// render the decorator template and return the result
		$decoratorTemplate = $view->getDecoratorDirectory() . '/' . $view->getDecoratorTemplate() . $this->getExtension();

		ob_start();

		require($decoratorTemplate);

		$retval = ob_get_contents();

		ob_end_clean();

		return $retval;
	}

	/**
	 * Retrieve the template engine associated with this view.
	 *
	 * Note: This will return null because PHP itself has no engine reference.
	 *
	 * @return     null
	 */
	public function & getEngine()
	{
		$retval = null;

		return $retval;
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
	 * @since      0.11.0
	 */
	public function & render()
	{
		$retval = null;

		$view = $this->getView();

		// get the render mode
		$mode = $view->getContext()->getController()->getRenderMode();

		// alias the attributes array so it's directly accessible to the
		// template
		$template =& $view->getAttributes();

		if($mode == AgaviView::RENDER_CLIENT && !$view->isDecorator())
		{
			// render directly to the client
			require($view->getDirectory() . '/' . $view->getTemplate() . $this->getExtension());
			
		} else if($mode != AgaviView::RENDER_NONE) {
			// render to variable
			ob_start();

			require($view->getDirectory() . '/' . $view->getTemplate() . $this->getExtension());

			$retval = ob_get_contents();

			ob_end_clean();

			// now render our decorator template, if one exists
			if($view->isDecorator()) {
				$retval =& $this->decorate($retval);
			}

			if($mode == AgaviView::RENDER_CLIENT) {
				echo $retval;

				$retval = null;
			}
		}

		return $retval;
	}
}
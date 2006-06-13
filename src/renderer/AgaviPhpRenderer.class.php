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
		
		// DO NOT USE VARIABLES IN HERE, THEY MIGHT INTERFERE WITH TEMPLATE VARS
		
		if($this->extractVars) {
			extract(array_merge($this->view->getAttributes(), $this->output), EXTR_REFS | EXTR_PREFIX_INVALID, '_');
		} else {
			${$this->varName} =& array_merge($this->view->getAttributes(), $this->output);
		}
		
		// render the decorator template and return the result
		ob_start();
		
		require($this->view->getDecoratorDirectory() . '/' . $this->view->getDecoratorTemplate() . $this->getExtension());
		
		$retval = ob_get_contents();
		ob_end_clean();
		
		return $retval;
	}

	/**
	 * Retrieve the template engine associated with this view.
	 *
	 * Note: This will return null because PHP itself has no engine reference.
	 */
	public function getEngine()
	{
	}

	/**
	 * Render the presentation.
	 *
	 * When the controller render mode is View::RENDER_CLIENT, this method will
	 * render the presentation directly to the Response and null will be returned.
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
		// DO NOT USE VARIABLES IN HERE, THEY MIGHT INTERFERE WITH TEMPLATE VARS
		
		if($this->extractVars) {
			extract($this->view->getAttributes(), EXTR_REFS | EXTR_PREFIX_INVALID, '_');
		} else {
			${$this->varName} =& $this->view->getAttributes();
		}

		if($this->context->getController()->getRenderMode() == AgaviView::RENDER_CLIENT && !$this->view->isDecorator()) {
			// render directly to the client via Response
			ob_start();
			
			require($this->view->getDirectory() . '/' . $this->view->getTemplate() . $this->getExtension());
			
			$this->context->getResponse()->setContent(ob_get_contents());
			ob_end_clean();
			
		} elseif($this->view->getContext()->getController()->getRenderMode() != AgaviView::RENDER_NONE) {
			// render to variable
			ob_start();

			require($this->view->getDirectory() . '/' . $this->view->getTemplate() . $this->getExtension());

			$retval = ob_get_contents();
			ob_end_clean();

			// now render our decorator template, if one exists
			if($this->view->isDecorator()) {
				$retval =& $this->decorate($retval);
			}

			if($this->context->getController()->getRenderMode() == AgaviView::RENDER_CLIENT) {
				$this->context->getResponse()->setContent($retval);

				$retval = null;
			}
		} else {
			$retval = null;
		}

		return $retval;
	}
}
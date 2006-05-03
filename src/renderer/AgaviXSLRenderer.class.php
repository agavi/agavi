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
abstract class AgaviXSLRenderer
{
	/**
	 * This will return null for XSLView instances
	 *
	 * @param      $context.
	 *
	 * @return     null.
	 *
	 * @author     Wes Hays <weshays@gbdev.com>
	 * @since      0.10.0
	 */
	public function &decorate(&$content)
	{
		return null;
	}

	/**
	 * Retrieve the template engine associated with this view.
	 *
	 * @return     XSLTProcessor A template engine instance used for this class.
	 *
	 * @author     Wes Hays <weshays@gbdev.com>
	 * @since      0.10.0
	 */
	public function &getEngine()
	{
		return $this->xslProc;
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
	 * @author     Wes Hays <weshays@gbdev.com>
	 * @since      0.10.0
	 */
	public function &render()
	{
		$retVal = null;

		// execute pre-render check
		$this->preRenderCheck();

		// get the render mode
		$mode = $this->getContext()->getController()->getRenderMode();

		$this->xslProc->importStyleSheet(DOMDocument::load($this->getDecoratorDirectory() . '/' .$this->getTemplate()));

		$xhtml = $this->xslProc->transformToXML($this->domDoc);

		if($mode == AgaviView::RENDER_CLIENT) {
			echo $xhtml;
		} else if($mode == AgaviView::RENDER_VAR) {
			$retVal = $xhtml;
		}

		return $retVal;
	}

	// -------------------------------------------------------------------------
}
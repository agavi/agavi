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
	private $xslProc      = null;  // XSLTProcessor.
	private $domDoc       = null;  // DomDocument.
	private $rootNode     = null;  // The root node of the DomDocument.
	private $rootNodeRS   = null;  // The copy of the initilization of the DomDocument incase a restart is needed.
	private $rootNodeName = null;  // The name of the root node incase it is needed.

	/**
	 * Initialize this Renderer.
	 *
	 * @param      AgaviContext The current application context.
	 * @param      array        An associative array of initialization parameters.
	 *
	 * @return     bool true, if initialization completes successfully,
	 *                  otherwise false.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function initialize($context, $parameters = array())
	{
		$retval = parent::initialize();
		
		$this->xslProc = new XSLTProcessor();

		// initialize this object
		if(!$this->setDomDocument(new DOMDocument(isset($parameters['version']) ? $parameters['version'] : '1.0', isset($parameters['encoding']) ? $parameters['encoding'] : 'utf-8'), isset($parameters['root_node_name']) ? $parameters['root_node_name'] : 'rootnode')) {
			 return false;
		}

		return $retval && true;
	}

	/**
	 * Sets the DOMDocument to be used.
	 * The default value is DOMDocument('1.0', 'iso-8859-1').
	 *
	 * @param      DOMDocument $domDocument The DOMDocument to use.
	 * @param      string $rootNode (Optional) The name of the root node to use.  
	 *                    If not specified then the root node will have a name
	 *                    of "rootnode".
	 *
	 * @return     True on success, otherwise false.
	 *
	 * @author     Wes Hays <weshays@gbdev.com>
	 * @since      0.10.0
	 */
	public function setDomDocument($domDocument, $rootNode = 'rootnode')
	{
		// Make sure that $domDocument is indeed a DomDocument.
		if(($domDocument instanceof DOMDocument) && is_string($rootNode))
		{
			$this->domDoc       = $domDocument;
			$this->rootNodeName = $rootNode;
			$this->rootNode     = $this->domDoc->appendChild(new DOMElement($this->rootNodeName));
			$this->domDocRS     = $this->domDoc->cloneNode(true);

			return true;
		}

		return false;
	}

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
	public function getEngine()
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
		
		$view = $this->getView();
		$engine = $this->getEngine();

		// get the render mode
		$mode = $this->getContext()->getController()->getRenderMode();

		$engine->importStyleSheet(DOMDocument::load($view->getDecoratorDirectory() . '/' . $view->getTemplate()));

		$xhtml = $engine->transformToXML($this->domDoc);

		if($mode == AgaviView::RENDER_CLIENT) {
			echo $xhtml;
		} else if($mode == AgaviView::RENDER_VAR) {
			$retVal = $xhtml;
		}

		return $retVal;
	}

	// -------------------------------------------------------------------------
}
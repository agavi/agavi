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
 * @author     Noah Fontes <agavi@cynigram.com>
 * @author     Wes Hays <weshays@gbdev.com>
 * @author     Agavi Project <info@agavi.org>
 * @copyright  (c) Authors
 * @since      0.11.0
 *
 * @version    $Id$
 */
class AgaviXslRenderer extends AgaviRenderer implements AgaviIReusableRenderer
{
	/**
	 * @var	       XSLTProcessor Processor for loading XSL templates.
	 */
	protected $xsl = null;
	
	/**
	 * @var        DOMDocument The document containing the content.
	 */
	protected $xml = null;
	
	/**
	 * @var        XMLNode The root XML node.
	 */
	protected $xmlRoot = null;
	
	/**
	 * @var        XMLNode The attributes XML node.
	 */
	protected $xmlAttributes = null;
	
	/**
	 * @var        XMLNode The slots XML node.
	 */
	protected $xmlSlots = null;
	
	/**
	 * @var        string A string with the default template file extension,
	 *                    including the dot.
	 */
	protected $defaultExtension = '.xsl';
	
	/**
	 * Initialize this Renderer.
	 *
	 * @param      AgaviContext The context to use.
	 * @param      array An associative array of initialization parameters.
	 *
	 * @author     Noah Fontes <agavi@cynigram.com>
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function initialize(AgaviContext $context, array $parameters = array())
	{
		parent::initialize($context, $parameters);
		
		if(!($this->xml =
			new DOMDocument(
				isset($parameters['version']) ? $parameters['version'] : '1.0',
				isset($parameters['encoding']) ? $parameters['encoding'] : 'utf-8'
			)
		)) {
			throw new AgaviInitializationException('Could not create DOM Document');
		}
				
		if($this->extractVars) {
			throw new AgaviInitializationException('The XSL renderer cannot support the extraction of variables for templating');
		}
		
		$this->xmlRoot = $this->xml->appendChild(new DOMElement('data'));
		
		$this->varName = AgaviInflector::singularize($this->varName);
		$this->xmlAttributes = $this->xmlRoot->appendChild(new DOMElement(AgaviInflector::pluralize($this->varName)));
		
		$this->slotsVarName = AgaviInflector::singularize($this->slotsVarName);
		$this->xmlSlots = $this->xmlRoot->appendChild(new DOMElement(AgaviInflector::pluralize($this->slotsVarName)));
	}
	
	/**
	 * Appends data recursively to the XML document.
	 *
	 * @param      DOMNode The element to append to.
	 * @param      array The attributes to add to the XML document.
	 *
	 * @author     Noah Fontes <agavi@cynigram.com>
	 * @since      0.11.0
	 */
	protected function setXml(DOMNode $element, $tag, &$data)
	{
		$luie = libxml_use_internal_errors(true);
		
		foreach($data as $name => $value) {
			$newElement = $element->appendChild(new DOMElement($tag));
			$newElement->setAttribute('name', $name);
			
			if($value instanceof DOMNode) {
				for($node = $value->firstChild;
					$node;
					$node = $node->nextSibling
				) {
					$newElement->appendChild(
						$this->xml->importNode(
							$node, true
						)
					);
				}
			} elseif(is_array($value) || is_object($value)) {
				$newElementAttributes = $newElement->appendChild(new DOMElement($tag));
				$this->setXml($newElementAttributes, $value);
			} elseif($import = DOMDocument::load($value)) {
				$newElement->appendChild($this->xml->importNode($import));
			} else {
				$newElement->appendChild(new DOMCDATASection((string)$value));
			}
		}
		
		libxml_clear_errors();
		libxml_use_internal_errors($luie);
	}
	
	/**
	 * Retrieve the template engine associated with this view.
	 *
	 * @return     XSLTProcessor A template engine instance used for this class.
	 *
	 * @author     Noah Fontes <agavi@cynigram.com>
	 * @author     Wes Hays <weshays@gbdev.com>
	 * @since      0.10.0
	 */
	public function getEngine()
	{
		if($this->xsl === null) {
			$this->xsl = new XSLTProcessor();
		}
		
		return $this->xsl;
	}
	
	/**
	 * Reset the engine for re-use.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	protected function reset()
	{		
		$this->xsl = null;
	}
	
	/**
	 * Render the presentation to the Response.
	 *
	 * @author     Noah Fontes <agavi@cynigram.com>
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @author     Wes Hays <weshays@gbdev.com>
	 * @since      0.11.0
	 */
	public function render(AgaviTemplateLayer $layer, array &$attributes = array(), array &$slots = array(), array &$moreAssigns = array())
	{
		$luie = libxml_use_internal_errors(true);
		libxml_clear_errors();
		
		$template = new DOMDocument();
		$template->load($layer->getResourceStreamIdentifier());
		if(libxml_get_last_error() !== false) {
			$errors = array();
			foreach(libxml_get_errors() as $error) {
				$errors[] = sprintf('Line %d: %s', $error->line, $error->message);
			}
			
			libxml_clear_errors();
			libxml_use_internal_errors($luie);
			
			$error = sprintf("Template could not be parsed by DOM due to the following error%s:\n\n%s", count($errors) > 1 ? 's' : '', implode("\n", $errors));
			throw new AgaviRenderException($error);
		}
		
		// Try to parse the stylesheet
		$engine = $this->getEngine();
		$engine->importStyleSheet($template);
		
		if(libxml_get_last_error() !== false) {
			$errors = array();
			foreach(libxml_get_errors() as $error) {
				$errors[] = sprintf('Line %d: %s', $error->line, $error->message);
			}
			
			libxml_clear_errors();
			libxml_use_internal_errors($luie);
			
			$error = sprintf("Template could not be imported as an XSL template due to the following error%s:\n\n%s", count($errors) > 1 ? 's' : '', implode("\n", $errors));
			throw new AgaviRenderException($error);
		}
		
		libxml_use_internal_errors($luie);
		
		$this->setXml($this->xmlAttributes, $this->varName, $attributes);
		$this->setXml($this->xmlSlots, $this->slotsVarName, $slots);
		return $engine->transformToXML($this->xml);
	}
}

?>
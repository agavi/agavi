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
 * @author     Noah Fontes <impl@cynigram.com>
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
	protected $xslEngine            = null;
	
	/**
	 * @var        DOMDocument The document containing the content.
	 */
	protected $xmlEngine            = null;
	
	/**
	 * @var        XMLNode The root XML node.
	 */
	protected $xmlEngineRoot        = null;
	
	/**
	 * @var        XMLNode The attributes XML node.
	 */
	protected $xmlEngineAttributes  = null;
	
	/**
	 * @var        XMLNode The slots XML node.
	 */
	protected $xmlEngineSlots       = null;
	
	/**
	 * @var        array Errors that may have accumulated while parsing
	 *                   an XML file.
	 */
	protected $errors               = array();
	
	/**
	 * @var        string A string with the default template file extension,
	 *                    including the dot.
	 */
	protected $extension            = '.xsl';
	
	/**
	 * @var        string The plural form of the template variable name.
	 */
	protected $pluralVarName        = '';
	
	/**
	 * @var        string The singular form of the template variable name.
	 */
	protected $singularVarName      = '';
	
	/**
	 * @var        string The plural form of the slots variable name.
	 */
	protected $pluralSlotsVarName   = '';
	
	/**
	 * @var        string The singular form of the slots variable name.
	 */
	protected $singularSlotsVarName = '';
	
	/**
	 * Initialize this Renderer.
	 *
	 * @param      AgaviContext The context to use.
	 * @param      array An associative array of initialization parameters.
	 *
	 * @author     Noah Fontes <impl@cynigram.com>
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function initialize(AgaviContext $context, array $parameters = array())
	{
		parent::initialize($context, $parameters);

		$this->xslEngine = new XSLTProcessor();

		if(!($this->xmlEngine =
			new DOMDocument(
				isset($parameters['version']) ? $parameters['version'] : '1.0',
				isset($parameters['encoding']) ? $parameters['encoding'] : 'utf-8'
			)
		)) {
			throw new AgaviInitializationException('Could not create DOM Document');
		}
		
		$this->xmlEngineRoot = $this->xmlEngine->appendChild(new DOMElement('data'));
		
		if($this->extractVars || $this->extractSlots) {
			throw new AgaviInitializationException('The XSL renderer cannot support the extraction of variables for templating');
		}
		
		$this->pluralVarName = $this->varName;
		$this->singularVarName = AgaviInflector::singularize($this->varName);
		if($this->singularVarName === $this->pluralVarName) {
			// Oh dear, we got them backwards!
			$this->singularVarName = $this->varName;
			$this->pluralVarName = AgaviInflector::pluralize($this->varName);
		}
		
		$this->xmlEngineAttributes = $this->xmlEngineRoot->appendChild(new DOMElement($this->pluralVarName));
		
		if($this->slotsVarName !== $this->varName) {
			$this->pluralSlotsVarName = $this->slotsVarName;
			$this->singularSlotsVarName = AgaviInflector::singularize($this->slotsVarName);
			if($this->singularSlotsVarName === $this->pluralSlotsVarName) {
				// Backwards again!
				$this->singularSlotsVarName = $this->slotsVarName;
				$this->pluralSlotsVarName = AgaviInflector::pluralize($this->slotsVarName);
			}
			
			$this->xmlEngineSlots = $this->xmlEngineRoot->appendChild(new DOMElement($this->pluralSlotsVarName));
		}
		
	}
	
	/**
	 * Catches errors triggered by XSL and XML classes.
	 *
	 * @param      int The error's level.
	 * @param      string The error's message.
	 *
	 * @author     Noah Fontes <impl@cynigram.com>
	 * @since      0.11.0
	 */
	public function xmlErrorHandler($errno, $errstr)
	{
		$this->errors[] = $errstr;
	}
	
	/**
	 * Loop through all template slots and fill them in with the results of
	 * presentation data.
	 *
	 * @param      string A chunk of decorator content.
	 * @param      bool True to load the view's attributes into the XML
	 *                  template, or false to load only the slot output. 
	 *
	 * @return     string A decorated template.
	 *
	 * @author     Noah Fontes <impl@cynigram.com>
	 * @author     Wes Hays <weshays@gbdev.com>
	 * @since      0.10.0
	 */
	public function decorate(&$content, $setAttributes = true)
	{
		parent::decorate($content);
		
		if($setAttributes === true) {
			$this->setXml($this->xmlEngineAttributes, $this->singularVarName, $this->view->getAttributes());
		}
		
		if($this->xmlEngineSlots !== null) {
			$this->setXml($this->xmlEngineSlots, $this->singularSlotsVarName, $this->output);
		}
		else {
			$this->setXml($this->xmlEngineAttributes, $this->singularVarName, $this->output);
		}
		
		$template = $this->view->getDecoratorDirectory() . '/' . $this->buildTemplateName($this->view->getDecoratorTemplate());
		
		// Try to load the document
		$this->errors = array();
		set_error_handler(array($this, 'xmlErrorHandler'));
		$document = new DOMDocument();
		$document->load($template);
		restore_error_handler();
		
		if(count($this->errors)) {
			$error = 'The template "%s" could not be loaded by DOM<ul><li>%s</li></ul>';
			$error = sprintf($error, $template, implode('</li><li>', $this->errors));
			
			throw new AgaviRenderException($error);
		}
		
		// Try to parse the stylesheet
		$this->errors = array();
		set_error_handler(array($this, 'xmlErrorHandler'));
		$this->xslEngine->importStyleSheet($document);
		restore_error_handler();
		
		if(count($this->errors)) {
			$error = 'The template "%s" contained invalid XSLT rules<ul><li>%s</li></ul>';
			$error = sprintf($error, $template, implode('</li><li>', $this->errors));
			
			throw new AgaviRenderException($error);
		}
		
		$output = $this->xslEngine->transformToDoc($this->xmlEngine);
		
		$output->version = $this->xmlEngine->version;
		$output->encoding = $this->xmlEngine->encoding;
		
		return $output->saveXML();
	}
	
	/**
	 * Appends data recursively to the XML document.
	 *
	 * @param      DOMNode The element to append to.
	 * @param      array The attributes to add to the XML document.
	 *
	 * @author     Noah Fontes <impl@cynigram.com>
	 * @since      0.11.0
	 */
	protected function setXml(DOMNode $element, $tag, $data)
	{
		foreach($data as $name => $value) {
			$newElement = $element->appendChild(new DOMElement($tag));
			$newElement->setAttribute('name', $name);
			
			if($value instanceof DOMNode) {
				for($node = $value->firstChild;
					$node;
					$node = $node->nextSibling
				) {
					$newElement->appendChild(
						$this->xmlEngine->importNode(
							$node, true
						)
					);
				}
			} elseif(is_array($value) || is_object($value)) {
				$newElementAttributes = $newElement->appendChild(new DOMElement($tag));
				$this->setXml($newElementAttributes, $value);
			} else {
				$newElement->appendChild(new DOMCDATASection((string)$value));				
			}
		}
	}

	/**
	 * Retrieve the template engine associated with this view.
	 *
	 * @return     XSLTProcessor A template engine instance used for this class.
	 *
	 * @author     Noah Fontes <impl@cynigram.com>
	 * @author     Wes Hays <weshays@gbdev.com>
	 * @since      0.10.0
	 */
	public function getEngine()
	{
		return $this->xslEngine;
	}
	
	/**
	 * Reset the engine for re-use
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	protected function reset()
	{
	}
	
	/**
	 * Render the presentation to the Response.
	 *
	 * @author     Noah Fontes <impl@cynigram.com>
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @author     Wes Hays <weshays@gbdev.com>
	 * @since      0.11.0
	 */
	public function render()
	{
		$template = $this->view->getDirectory() . '/' . $this->buildTemplateName($this->view->getTemplate());
		
		// Try to load the document
		$this->errors = array();
		set_error_handler(array($this, 'xmlErrorHandler'));
		$document = new DOMDocument();
		$document->load($template);
		restore_error_handler();
		
		if(count($this->errors)) {
			$error = 'The template "%s" could not be loaded by DOM<ul><li>%s</li></ul>';
			$error = sprintf($error, $template, implode('</li><li>', $this->errors));
			
			throw new AgaviRenderException($error);
		}
		
		// Try to parse the stylesheet
		$this->errors = array();
		set_error_handler(array($this, 'xmlErrorHandler'));
		$this->xslEngine->importStyleSheet($document);
		restore_error_handler();
		
		if(count($this->errors)) {
			$error = 'The template "%s" contained invalid XSLT rules<ul><li>%s</li></ul>';
			$error = sprintf($error, $template, implode('</li><li>', $this->errors));
			
			throw new AgaviRenderException($error);
		}
		
		$this->setXml($this->xmlEngineAttributes, $this->singularVarName, $this->view->getAttributes());
		$output = $this->xslEngine->transformToDoc($this->xmlEngine);
		
		if($this->view->isDecorator()) {
			$display = '';
			for($node = $output->firstChild;
				$node;
				$node = $node->nextSibling
			) {
				$display .= $output->saveXML($node);
			}
			
			$this->response->setContent($this->decorate($display, false));
		} else {
			$output->version = $this->xmlEngine->version;
			$output->encoding = $this->xmlEngine->encoding;
				
			$this->response->setContent($output->saveXML());
		}
	}
}

?>
<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2005-2011 the Agavi Project.                                |
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
 * AgaviXmlConfigSchematronProcessor transforms DOM documents according to
 * ISO Schematron validation and transformation rules into a document
 * containing successful reports and failed assertions.
 *
 * @package    agavi
 * @subpackage config
 *
 * @author     Noah Fontes <noah.fontes@bitextender.com>
 * @author     David Zülke <david.zuelke@bitextender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      1.0.0
 *
 * @version    $Id$
 */
class AgaviXmlConfigSchematronProcessor extends AgaviParameterHolder
{
	/**
	 * @var        array A cache of processor instances.
	 */
	protected static $processors = array();
	
	/**
	 * @var        array The list of Schematron implementation paths to process.
	 */
	protected static $defaultChain = array(
		'%core.agavi_dir%/config/schematron/iso_dsdl_include.xsl',
		'%core.agavi_dir%/config/schematron/iso_abstract_expand.xsl',
		'%core.agavi_dir%/config/schematron/iso_svrl_for_xslt1.xsl'
	);
	
	/**
	 * @var        DOMNode The node the processor will work on.
	 */
	protected $node = null;
	
	/**
	 * Creates a new processor for transforming documents into a Schematron
	 * report.
	 *
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 * @since      1.0.0
	 */
	public function __construct(array $chain = null)
	{
		if($chain === null) {
			$chain = static::$defaultChain;
		}
		
		if(!$chain) {
			throw new AgaviException('Schematron processor chain must contain at least one path name.');
		}
		
		$this->chain = array_map(array('AgaviToolkit', 'expandDirectives'), $chain);
	}
	
	/**
	 * Get an array of all processors.
	 *
	 * @return     array An array of AgaviXmlConfigXsltProcessor instances.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.1.0
	 */
	public function getProcessors()
	{
		$retval = array();
		foreach($this->chain as $path) {
			$retval[] = static::getProcessor($path);
		}
		return $retval;
	}
	
	/**
	 * Get a processor instance for the given XSLT path.
	 *
	 * @param      string The file path to the XSL template.
	 *
	 * @return     AgaviXmlConfigXsltProcessor The processor instance.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.1.0
	 */
	protected static function getProcessor($path)
	{
		if(!isset(self::$processors[$path])) {
			$processorImpl = new AgaviXmlConfigDomDocument();
			$processorImpl->load($path);
			$processor = new AgaviXmlConfigXsltProcessor();
			$processor->importStylesheet($processorImpl);
			self::$processors[$path] = $processor;
		}
		
		return self::$processors[$path];
	}
	
	/**
	 * Sets the node that this processor will transform and validate.
	 *
	 * @param      DOMNode The node to use.
	 *
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 * @since      1.0.0
	 */
	public function setNode(DOMNode $node)
	{
		$this->node = $node;
	}
	
	/**
	 * Prepare the given processor for use.
	 * Sets all parameters from this processor class.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.1.0
	 */
	protected function prepareProcessor($processor)
	{
		$processor->setParameter('', $this->getParameters());
	}
	
	/**
	 * Cleanup the given processor after use.
	 * Removes all parameters from this processor class.
	 * Cannot be done in AgaviXmlConfigSchematronProcessor::prepareProcessor(),
	 * which is why this must be called in transform().
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.1.0
	 */
	protected function cleanupProcessor($processor)
	{
		foreach(array_keys($this->getParameters()) as $parameter) {
			$processor->removeParameter('', $parameter);
		}
	}
	
	/**
	 * Validates the node against a given Schematron validation file.
	 *
	 * @param      DOMDocument The validator to use.
	 *
	 * @return     AgaviXmlConfigDomDocument The transformed validation document.
	 *
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 * @since      1.0.0
	 */
	public function transform(DOMDocument $schema)
	{
		// do we even have a document?
		if($this->node === null) {
			throw new AgaviParseException('Schema validation failed because no document could be parsed');
		}
		
		// is it an ISO Schematron file?
		if(!$schema->documentElement || $schema->documentElement->namespaceURI != AgaviXmlConfigParser::NAMESPACE_SCHEMATRON_ISO) {
			throw new AgaviParseException(sprintf('Schema file "%s" is invalid', $schema->documentURI));
		}
		
		// transform the .sch file to a validation stylesheet using the Schematron implementation
		$validatorImpl = $schema;
		$first = true;
		foreach($this->getProcessors() as $processor) {
			if($first) {
				// set some vars for the schema
				$this->prepareProcessor($processor);
			}
			try {
				$validatorImpl = $processor->transformToDoc($validatorImpl);
			} catch(Exception $e) {
				if($first) {
					$this->cleanupProcessor($processor);
				}
				throw new AgaviParseException(sprintf('Could not transform schema file "%s": %s', $schema->documentURI, $e->getMessage()), 0, $e);
			}
			if($first) {
				$this->cleanupProcessor($processor);
				$first = false;
			}
		}
		
		// it transformed fine. but did we get a proper stylesheet instance at all? wrong namespaces can lead to empty docs that only have an XML prolog
		if(!$validatorImpl->documentElement || $validatorImpl->documentElement->namespaceURI != AgaviXmlConfigParser::NAMESPACE_XSL_1999) {
			throw new AgaviParseException(sprintf('Processing using schema file "%s" resulted in an invalid stylesheet', $schema->documentURI));
		}
		
		// all fine so far. let us import the stylesheet
		try {
			$validator = new AgaviXmlConfigXsltProcessor();
			$validator->importStylesheet($validatorImpl);
		} catch(Exception $e) {
			throw new AgaviParseException(sprintf('Could not process the schema file "%s": %s', $schema->documentURI, $e->getMessage()), 0, $e);
		}
		
		// run the validation by transforming our document using the generated validation stylesheet
		try {
			$result = $validator->transformToDoc($this->node);
		} catch(Exception $e) {
			throw new AgaviParseException(sprintf('Could not validate the document against the schema file "%s": %s', $schema->documentURI, $e->getMessage()), 0, $e);
		}
		
		return $result;
	}
}

?>
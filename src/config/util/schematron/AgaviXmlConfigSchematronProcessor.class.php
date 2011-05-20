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
	 * @var        array A list of processor instances.
	 */
	protected static $processors = null;
	
	/**
	 * @var        int The number of processors.
	 */
	protected static $processorCount = 0;
	
	/**
	 * @var        array The list of schematron implementation parts to process.
	 */
	protected static $chain = array(
		'iso_dsdl_include.xsl',
		'iso_abstract_expand.xsl',
		'iso_svrl_for_xslt1.xsl'
	);
	
	/**
	 * @var        DOMNode The node the processor will work on.
	 */
	protected $node = null;
	
	/**
	 * Creates a new processor for transforming documents into a schematron
	 * report.
	 *
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 * @since      1.0.0
	 */
	public function __construct()
	{
	}
	
	/**
	 * Generates the processing chain.
	 *
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 * @since      1.0.0
	 */
	protected static function createProcessors()
	{
		self::$processors = array();
		self::$processorCount = 0;
		
		foreach(self::$chain as $file) {
			$processorImpl = new AgaviXmlConfigDomDocument();
			$processorImpl->load(AgaviConfig::get('core.agavi_dir') . '/config/schematron/' . $file);
			$processor = new AgaviXmlConfigXsltProcessor();
			$processor->importStylesheet($processorImpl);
			self::$processors[] = $processor;
			self::$processorCount++;
		}
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
		if(self::$processors === null) {
			self::createProcessors();
		}
		
		$this->node = $node;
	}
	
	/**
	 * Validates the node against a given schematron validation file.
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
		
		// transform the .sch file to a validation stylesheet using the schematron implementation
		try {
			$initialProcessor = self::$processors[0];
			$initialProcessor->setParameter('', $this->getParameters());
			
			// ...and do the actual transformations
			$validatorImpl = $initialProcessor->transformToDoc($schema);
			for($i = 1; $i < self::$processorCount; $i ++) {
				$validatorImpl = self::$processors[$i]->transformToDoc($validatorImpl);
			}
			
			// for some reason we can't clone XSLTProcessor instances, so we have to
			// go back and remove all the parameters :(
			foreach(array_keys($this->getParameters()) as $parameter) {
				$initialProcessor->removeParameter('', $parameter);
			}
		} catch(Exception $e) {
			throw new AgaviParseException(sprintf('Could not transform schema file "%s": %s', $schema->documentURI, $e->getMessage()));
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
			throw new AgaviParseException(sprintf('Could not process the schema file "%s": %s', $schema->documentURI, $e->getMessage()));
		}
		
		// run the validation by transforming our document using the generated validation stylesheet
		try {
			$result = $validator->transformToDoc($this->node);
		} catch(Exception $e) {
			throw new AgaviParseException(sprintf('Could not validate the document against the schema file "%s": %s', $schema->documentURI, $e->getMessage()));
		}
		
		return $result;
	}
}

?>
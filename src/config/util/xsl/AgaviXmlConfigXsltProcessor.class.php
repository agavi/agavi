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
 * Extended XSLTProcessor class that throws exceptions on errors.
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
class AgaviXmlConfigXsltProcessor extends XSLTProcessor
{
	/**
	 * Import a stylesheet.
	 *
	 * @param      DOMDocument The stylesheet to import.
	 *
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 * @since      1.0.0
	 */
	public function importStylesheet($stylesheet)
	{
		$luie = libxml_use_internal_errors(true);
		libxml_clear_errors();
		
		parent::importStylesheet($stylesheet);
		
		// libxml_get_last_error() returns false if importStylesheet failed, libxml_get_errors() works nontheless. zomfg libxml.
		// also, if we catch the errors here and throw an exception, we don't need an @ further down at transformToDoc().
		if(libxml_get_last_error() !== false || count(libxml_get_errors())) {
			$errors = array();
			foreach(libxml_get_errors() as $error) {
				$errors[] = sprintf('[%s #%d] Line %d: %s', $error->level == LIBXML_ERR_WARNING ? 'Warning' : ($error->level == LIBXML_ERR_ERROR ? 'Error' : 'Fatal'), $error->code, $error->line, $error->message);
			}
			libxml_clear_errors();
			libxml_use_internal_errors($luie);
			throw new Exception(
				sprintf(
					'Error%s occurred while importing the stylesheet "%s": ' . "\n\n%s",
					count($errors) > 1 ? 's' : '', 
					$stylesheet->documentURI,
					implode("\n", $errors)
				)
			);
		}
		
		libxml_use_internal_errors($luie);
	}
	
	/**
	 * Transform a node with a stylesheet.
	 *
	 * @param      DOMNode The node to transform.
	 *
	 * @return     AgaviXmlConfigDomDocument The resulting DOMDocument.
	 *
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.0
	 */
	public function transformToDoc($doc)
	{
		$luie = libxml_use_internal_errors(true);
		libxml_clear_errors();
		
		$result = parent::transformToDoc($doc);
		
		// check if result is false, too, as that means the transformation failed for reasons like infinite template recursion
		if($result === false || libxml_get_last_error() !== false || count(libxml_get_errors())) {
			$errors = array();
			foreach(libxml_get_errors() as $error) {
				$errors[] = sprintf('[%s #%d] Line %d: %s', $error->level == LIBXML_ERR_WARNING ? 'Warning' : ($error->level == LIBXML_ERR_ERROR ? 'Error' : 'Fatal'), $error->code, $error->line, $error->message);
			}
			libxml_clear_errors();
			libxml_use_internal_errors($luie);
			throw new Exception(
				sprintf(
					'Error%s occurred while transforming the document using an XSL stylesheet: ' . "\n\n%s", 
					count($errors) > 1 ? 's' : '', 
					implode("\n", $errors)
				)
			);
		}
		
		libxml_use_internal_errors($luie);
		
		// turn this into an Agavi DOMDocument rather than a regular one
		$document = new AgaviXmlConfigDomDocument();
		$document->loadXML($result->saveXML());
		
		// save the URI just in case
		$document->documentURI = $result->documentURI;
		
		unset($result);
		
		return $document;
	}
}

?>
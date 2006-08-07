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

require_once(AgaviConfig::get('core.agavi_dir') . '/util/AgaviInflector.class.php');

/**
 * AgaviXmlConfigHandler allows you to retrieve the contents of a xml config
 * file as structured object tree
 *
 * @package    agavi
 * @subpackage config
 *
 * @author     Dominik del Bondio <ddb@bitxtender.com>
 * @copyright  (c) Authors
 * @since      0.11.0
 *
 * @version    $Id$
 */

class AgaviXmlConfigParser extends AgaviConfigParser
{

	/**
	 * @var        array An array of parsing errors
	 */
	protected $errors = array();

	/**
	 * @var        DomXPath A DomXPath instance used to parse this document.
	 */
	protected $xpath = null;
	
	/**
	 * @var        string The encoding of the file that's being parsed here.
	 */
	protected $encoding = 'utf-8';
	
	/**
	 * @var        string The name of the config file we're parsing.
	 */
	protected $config = '';

	/**
	 * The error handler to catch DOM errors so they can be thrown as exceptions.
	 *
	 * @param      int    The error level.
	 * @param      string The error string.
	 * @param      string The file where the error occured.
	 * @param      int    The line where the error occured.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function errorHandler($errno, $errstr, $errfile, $errline)
	{
		$this->errors[] = $errstr;
	}

	/**
	 * @see        AgaviConfigParser::parse()
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function parse($config, $validationFile = null)
	{
		if(!is_readable($config)) {
			$error = 'Configuration file "' . $config . '" does not exist or is unreadable';
			throw new AgaviUnreadableException($error);
		}
		
		$this->config = $config;

		// suppress errors from dom, ppl should use a proper xml editor to validate their files atm ...
		set_error_handler(array($this, 'errorHandler'));
		$doc = DOMDocument::load($config);
		restore_error_handler();
		if(!($doc instanceof DOMDocument)) {
			$error = 'Configuration file "' . $config . '" could not be parsed, error' . (count($this->errors) > 1 ? 's' : '') . ' reported by DOM: ' . "\n\n" . implode("\n", $this->errors);
			throw new AgaviParseException($error);
		}
		$this->encoding = strtolower($doc->encoding);
		$this->xpath = new DomXPath($doc);
		if($validationFile) {
			// TODO: check for file existance
			$this->errors = array();
			set_error_handler(array($this, 'errorHandler'));
			if(!$doc->schemaValidate($validationFile)) {
				restore_error_handler();
				$error = 'XSD Validation of configuration file "' . $config . '" failed, error' . (count($this->errors) > 1 ? 's' : '') . ' reported by DOM: ' . "\n\n" . implode("\n", $this->errors);
				throw new AgaviParseException($error);
			} else {
				restore_error_handler();
			}
		}

		$rootRes = new AgaviConfigValueHolder();

		$this->parseNodes(array($doc->documentElement), $rootRes);

		return $rootRes;
	}

	/**
	 * Iterates thru a list of nodes and stores to each node in the <b>XmlValueHolder</b>
	 *
	 * @param      mixed An array or an object that can be iterated over
	 * @param      AgaviXmlValueHolder The storage for the info from the nodes
	 * @param      bool Whether this list is the singular form of the parent node
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	protected function parseNodes($nodes, AgaviConfigValueHolder $parentVh, $isSingular = false)
	{
		foreach($nodes as $node) {
			if($node->nodeType == XML_ELEMENT_NODE) {
				$vh = new AgaviConfigValueHolder();
				$vh->setName($this->convertEncoding($node->nodeName));
				if($isSingular) {
					$parentVh->appendChildren($vh);
				} else {
					$parentVh->addChildren($this->convertEncoding($node->tagName), $vh);
				}

				foreach($node->attributes as $attribute) {
					$vh->setAttribute($this->convertEncoding($attribute->name), $this->convertEncoding($attribute->value));
				}

				// there are no child nodes so we set the node text contents as the value for the valueholder
				if($this->xpath->query('*', $node)->length == 0) {
					$vh->setValue($this->convertEncoding($node->nodeValue));
				}

				$tagName = $node->tagName;
				$tagNameStart = '';
				if(($lastUScore = strrpos($tagName, '_')) !== false) {
					$lastUScore++;
					$tagNameStart = substr($tagName, 0, $lastUScore);
					$tagName = substr($tagName, $lastUScore);
				}

				$singularNodeName = $tagNameStart . AgaviInflector::singularize($tagName);
				$singularNodes = $this->xpath->query($singularNodeName, $node);
				// there is at least one child with the singularized version of this tag name so we take them
				// to create an indexed array in the parent valueholder
				if($singularNodes->length > 0) {
					$this->parseNodes($singularNodes, $vh, true);
				} else {
					if($node->hasChildNodes()) {
						$this->parseNodes($node->childNodes, $vh);
					}
				}
			}
		}
	}
	
	/**
	 * Handle encoding for a value, i.e. translate from UTF-8 if necessary.
	 *
	 * @param      string A UTF-8 string value from the DomDocument.
	 *
	 * @return     string A value in the correct encoding of the parsed document.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	protected function convertEncoding($value)
	{
		if($this->encoding == 'utf-8') {
			return $value;
		} elseif($this->encoding == 'iso-8859-1') {
			return utf8_decode($value);
		} elseif(function_exists('iconv')) {
			return iconv('UTF-8', $this->encoding, $value);
		} else {
			throw new AgaviParseException('No iconv module available, configuration file "' . $this->config . '" with input encoding "' . $this->encoding . '" cannot be parsed.');
		}
	}
}
?>
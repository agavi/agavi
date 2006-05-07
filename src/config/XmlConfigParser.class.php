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

require_once(AgaviConfig::get('core.agavi_dir') . '/util/Inflector.class.php');

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
	protected $errors = array();
	public function errorHandler($errno, $errstr, $errfile, $errline)
	{
		$this->errors[] = $errstr;
	}

	protected $xpath = null;

	/**
	 * @see        AgaviConfigParser::parse()
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function parse($config)
	{
		if (!is_readable($config)) {
			$error = 'Configuration file "' . $config . '" does not exist or is not readable';
			throw new AgaviUnreadableException($error);
		}

		// suppress errors from dom, ppl should use a proper xml editor to validate their files atm ...
		set_error_handler(array($this, 'errorHandler'));
		$doc = DOMDocument::load($config);
		restore_error_handler();
		if(!($doc instanceof DOMDocument)) {
			$error = 'Configuration file "' . $config . '" contains errors (' . implode("<br />\r\n", $this->errors) . ')';
			throw new AgaviParseException($error);
		}
		$this->xpath = new DomXPath($doc);

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
	 * @return     void
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	protected function parseNodes($nodes, AgaviConfigValueHolder $parentVh, $isSingular = false)
	{
		foreach ($nodes as $node) {
			if ($node->nodeType == XML_ELEMENT_NODE) {
				$vh = new AgaviConfigValueHolder();
				$vh->setName($node->nodeName);
				if($isSingular) {
					$parentVh->appendChildren($vh);
				} else {
					$parentVh->addChildren($node->tagName, $vh);
				}

				foreach ($node->attributes as $attribute) {
					$vh->setAttribute($attribute->name, $attribute->value);
				}

				// there are no child nodes so we set the node text contents as the value for the valueholder
				if ($this->xpath->query('*', $node)->length == 0) {
					$vh->setValue($node->nodeValue);
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
				if ($singularNodes->length > 0) {
					$this->parseNodes($singularNodes, $vh, true);
				} else {
					if ($node->hasChildNodes()) {
						$this->parseNodes($node->childNodes, $vh);
					}
				}
			}
		}
	}
}
?>
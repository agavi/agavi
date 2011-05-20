<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2005-2011 the Agavi Project.                                |
// | Based on the Mojavi3 MVC Framework, Copyright (c) 2003-2005 Sean Kerr.    |
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
 * AgaviTestSuitesConfigHandler reads the testsuites configuration files to determine 
 * the available suites and their tests.
 *
 * @package    agavi
 * @subpackage config
 *
 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      1.0.0
 *
 * @version    $Id$
 */
class AgaviTestSuitesConfigHandler extends AgaviXmlConfigHandler
{
	const XML_NAMESPACE = 'http://agavi.org/agavi/config/parts/testing/suites/1.0';
	
	/**
	 * Execute this configuration handler.
	 *
	 * @param      AgaviXmlConfigDomDocument The document to parse.
	 *
	 * @return     string Data to be written to a cache file.
	 *
	 * @throws     <b>AgaviParseException</b> If a requested configuration file is
	 *                                        improperly formatted.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      0.9.0
	 */
	public function execute(AgaviXmlConfigDomDocument $document)
	{
		// set up our default namespace
		$document->setDefaultNamespace(self::XML_NAMESPACE, 'suite');
		
		// remember the config file path
		$config = $document->documentURI;
		
		$data = array();
		// loop over <configuration> elements
		foreach($document->getConfigurationElements() as $configuration) {
			foreach($configuration->get('suites') as $current) {
				$suite =  array('class' => $current->getAttribute('class', 'AgaviTestSuite'));
				$suite['testfiles'] = array();
				foreach($current->get('testfiles') as $file) {
					$suite['testfiles'][] = $file->textContent;
				}
				$data[$current->getAttribute('name')] = $suite;
			}
		}
		$code = 'return '.var_export($data, true);
		return $this->generate($code, $config);
	}
}

?>
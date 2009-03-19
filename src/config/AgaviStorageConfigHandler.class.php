<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2005-2009 the Agavi Project.                                |
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
 * AgaviStorageConfigHandler allows you to setup storage connections in a
 * configuration file that will be created for you automatically upon first
 * request.
 *
 * @package    agavi
 * @subpackage config
 *
 * @author     David Zülke <david.zuelke@bitextender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      1.1.0
 *
 * @version    $Id$
 */
class AgaviStorageConfigHandler extends AgaviXmlConfigHandler
{
	const XML_NAMESPACE = 'http://agavi.org/agavi/config/parts/storages/1.1';
	
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
	 * @since      1.1.0
	 */
	public function execute(AgaviXmlConfigDomDocument $document)
	{
		// set up our default namespace
		$document->setDefaultNamespace(self::XML_NAMESPACE, 'storages');
		
		$storages = array();
		$default = null;
		foreach($document->getConfigurationElements() as $configuration) {
			if(!$configuration->has('storages')) {
				continue;
			}
			
			$storagesElement = $configuration->getChild('storages');
			
			// make sure we have a default storage exists
			if(!$storagesElement->hasAttribute('default') && $default === null) {
				// missing default storage
				$error = 'Configuration file "%s" must specify a default storage configuration';
				$error = sprintf($error, $document->documentURI);

				throw new AgaviParseException($error);
			}
			$default = $storagesElement->getAttribute('default');

			// let's do our fancy work
			foreach($configuration->get('storages') as $storage) {
				$name = $storage->getAttribute('name');

				if(!isset($storages[$name])) {
					$storages[$name] = array('parameters' => array());

					if(!$storage->hasAttribute('class')) {
						$error = 'Configuration file "%s" specifies storage "%s" with missing class key';
						$error = sprintf($error, $document->documentURI, $name);

						throw new AgaviParseException($error);
					}
				}

				$storages[$name]['class'] = $storage->hasAttribute('class') ? $storage->getAttribute('class') : $storages[$name]['class'];

				$storages[$name]['parameters'] = $storage->getAgaviParameters($storages[$name]['parameters']);
			}
		}

		$data = array();

		foreach($storages as $name => $db) {
			// append new data
			$data[] = sprintf('$storage = new %s();', $db['class']);
			$data[] = sprintf('$this->storages[%s] = $storage;', var_export($name, true));
			$data[] = sprintf('$storage->initialize($this, %s);', var_export($db['parameters'], true));
		}

		if(!isset($storages[$default])) {
			$error = 'Configuration file "%s" specifies undefined default storage "%s".';
			$error = sprintf($error, $document->documentURI, $default);
			throw new AgaviConfigurationException($error);
		}

		$data[] = sprintf("\$this->defaultStorageName = %s;", var_export($default, true));

		return $this->generate($data, $document->documentURI);
	}
}

?>
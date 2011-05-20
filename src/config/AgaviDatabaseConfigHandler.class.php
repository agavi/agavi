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
 * AgaviDatabaseConfigHandler allows you to setup database connections in a
 * configuration file that will be created for you automatically upon first
 * request.
 *
 * @package    agavi
 * @subpackage config
 *
 * @author     Sean Kerr <skerr@mojavi.org>
 * @author     Dominik del Bondio <ddb@bitxtender.com>
 * @author     Noah Fontes <noah.fontes@bitextender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.9.0
 *
 * @version    $Id$
 */
class AgaviDatabaseConfigHandler extends AgaviXmlConfigHandler
{
	const XML_NAMESPACE = 'http://agavi.org/agavi/config/parts/databases/1.0';
	
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
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 * @since      0.9.0
	 */
	public function execute(AgaviXmlConfigDomDocument $document)
	{
		// set up our default namespace
		$document->setDefaultNamespace(self::XML_NAMESPACE, 'databases');
		
		$databases = array();
		$default = null;
		foreach($document->getConfigurationElements() as $configuration) {
			if(!$configuration->has('databases')) {
				continue;
			}
			
			$databasesElement = $configuration->getChild('databases');
			
			// make sure we have a default database exists
			if(!$databasesElement->hasAttribute('default') && $default === null) {
				// missing default database
				$error = 'Configuration file "%s" must specify a default database configuration';
				$error = sprintf($error, $document->documentURI);

				throw new AgaviParseException($error);
			}
			$default = $databasesElement->getAttribute('default');

			// let's do our fancy work
			foreach($configuration->get('databases') as $database) {
				$name = $database->getAttribute('name');

				if(!isset($databases[$name])) {
					$databases[$name] = array('parameters' => array());

					if(!$database->hasAttribute('class')) {
						$error = 'Configuration file "%s" specifies database "%s" with missing class key';
						$error = sprintf($error, $document->documentURI, $name);

						throw new AgaviParseException($error);
					}
				}

				$databases[$name]['class'] = $database->hasAttribute('class') ? $database->getAttribute('class') : $databases[$name]['class'];

				$databases[$name]['parameters'] = $database->getAgaviParameters($databases[$name]['parameters']);
			}
		}

		if(!$databases) {
			// we have no connections
			$error = 'Configuration file "%s" does not contain any database connections.';
			$error = sprintf($error, $document->documentURI);
			throw new AgaviConfigurationException($error);
		}

		$data = array();

		foreach($databases as $name => $db) {
			// append new data
			$data[] = sprintf('$database = new %s();', $db['class']);
			$data[] = sprintf('$this->databases[%s] = $database;', var_export($name, true));
			$data[] = sprintf('$database->initialize($this, %s);', var_export($db['parameters'], true));
		}

		if(!isset($databases[$default])) {
			$error = 'Configuration file "%s" specifies undefined default database "%s".';
			$error = sprintf($error, $document->documentURI, $default);
			throw new AgaviConfigurationException($error);
		}

		$data[] = sprintf('$this->defaultDatabaseName = %s;', var_export($default, true));

		return $this->generate($data, $document->documentURI);
	}
}

?>
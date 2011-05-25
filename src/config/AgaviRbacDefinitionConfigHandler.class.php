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
 * RbacDefinitionConfigHandler handles RBAC role and permission definition files
 *
 * @package    agavi
 * @subpackage config
 *
 * @author     David Zülke <dz@bitxtender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.11.0
 *
 * @version    $Id$
 */
class AgaviRbacDefinitionConfigHandler extends AgaviXmlConfigHandler
{
	const XML_NAMESPACE = 'http://agavi.org/agavi/config/parts/rbac_definitions/1.0';
	
	/**
	 * Execute this configuration handler.
	 *
	 * @param      AgaviXmlConfigDomDocument The document to parse.
	 *
	 * @return     string Data to be written to a cache file.
	 *
	 * @throws     <b>AgaviUnreadableException</b> If a requested configuration
	 *                                             file does not exist or is not
	 *                                             readable.
	 * @throws     <b>AgaviParseException</b> If a requested configuration file is
	 *                                        improperly formatted.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function execute(AgaviXmlConfigDomDocument $document)
	{
		// set up our default namespace
		$document->setDefaultNamespace(self::XML_NAMESPACE, 'rbac_definitions');
		
		$data = array();

		foreach($document->getConfigurationElements() as $cfg) {
			if(!$cfg->has('roles')) {
				continue;
			}
			
			$this->parseRoles($cfg->get('roles'), null, $data);
		}

		$code = "return " . var_export($data, true) . ";";
		
		return $this->generate($code, $document->documentURI);
	}
	
	/**
	 * Parse a 'roles' node.
	 *
	 * @param      mixed  The "roles" node (element or node list)
	 * @param      string The name of the parent role, or null.
	 * @param      array  A reference to the output data array.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	protected function parseRoles($roles, $parent, &$data)
	{
		foreach($roles as $role) {
			$name = $role->getAttribute('name');
			$entry = array();
			$entry['parent'] = $parent;
			$entry['permissions'] = array();
			if($role->has('permissions')) {
				foreach($role->get('permissions') as $permission) {
					$entry['permissions'][] = $permission->getValue();
				}
			}
			if($role->has('roles')) {
				$this->parseRoles($role->get('roles'), $name, $data);
			}
			$data[$name] = $entry;
		}
	}
}

?>
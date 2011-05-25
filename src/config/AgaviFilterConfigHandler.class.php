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
 * AgaviFilterConfigHandler allows you to register filters with the system.
 *
 * @package    agavi
 * @subpackage config
 *
 * @author     David Zülke <dz@bitxtender.com>
 * @author     Sean Kerr <skerr@mojavi.org>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.9.0
 *
 * @version    $Id$
 */
class AgaviFilterConfigHandler extends AgaviXmlConfigHandler
{
	const XML_NAMESPACE = 'http://agavi.org/agavi/config/parts/filters/1.0';
	
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
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function execute(AgaviXmlConfigDomDocument $document)
	{
		// set up our default namespace
		$document->setDefaultNamespace(self::XML_NAMESPACE, 'filters');
		
		$config = $document->documentURI;
		
		$filters = array();
		
		foreach($document->getConfigurationElements() as $cfg) {
			if($cfg->has('filters')) {
				foreach($cfg->get('filters') as $filter) {
					$name = $filter->getAttribute('name', AgaviToolkit::uniqid());
					
					if(!isset($filters[$name])) {
						$filters[$name] = array('params' => array(), 'enabled' => AgaviToolkit::literalize($filter->getAttribute('enabled', true)));
					} else {
						$filters[$name]['enabled'] = AgaviToolkit::literalize($filter->getAttribute('enabled', $filters[$name]['enabled']));
					}
					
					if($filter->hasAttribute('class')) {
						$filters[$name]['class'] = $filter->getAttribute('class');
					}
					
					$filters[$name]['params'] = $filter->getAgaviParameters($filters[$name]['params']);
				}
			}
		}
		
		$data = array();

		foreach($filters as $name => $filter) {
			if(!isset($filter['class'])) {
				throw new AgaviConfigurationException('No class name specified for filter "' . $name . '" in ' . $config);
			}
			if($filter['enabled']) {
				$rc = new ReflectionClass($filter['class']);
				$if = 'AgaviI' . ucfirst(strtolower(substr(basename($config), 0, strpos(basename($config), '_filters')))) . 'Filter';
				if(!$rc->implementsInterface($if)) {
					throw new AgaviFactoryException('Filter "' . $name . '" does not implement interface "' . $if . '"');
				}
				$data[] = '$filter = new ' . $filter['class'] . '();';
				$data[] = '$filter->initialize($this->context, ' . var_export($filter['params'], true) . ');';
				$data[] = '$filters[] = $filter;';
			}
		}

		return $this->generate($data, $config);
	}
}

?>
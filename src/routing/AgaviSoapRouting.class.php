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
 * AgaviSoapRouting handles the routing for SOAP web service requests.
 *
 * @package    agavi
 * @subpackage routing
 *
 * @author     David Zülke <dz@bitxtender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.11.0
 *
 * @version    $Id$
 */
class AgaviSoapRouting extends AgaviWebserviceRouting
{
	/**
	 * Initialize the routing instance.
	 *
	 * @param      AgaviContext A Context instance.
	 * @param      array        An array of initialization parameters.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function initialize(AgaviContext $context, array $parameters = array())
	{
		// must always be on
		// don't do this after parent::initialize() as AgaviWebserviceRouting::initialize() checks the value already
		$parameters['enabled'] = true;
		
		parent::initialize($context, $parameters);
	}
	
	/**
	 * Returns the local filesystem path to the WSDL file built from routing.xml.
	 *
	 * @return     string A fully qualified filesystem path.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getWsdlPath()
	{
		$path = $this->getParameter('wsdl', AgaviConfig::get('core.agavi_dir') . '/routing/soap/wsdl.xml');
		
		return AgaviConfigCache::checkConfig($path, $this->context->getName());
	}
}

?>
<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2003-2007 the Agavi Project.                                |
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
 * AgaviXmlrpcepiphpRequest is an implementation for handling XMLRPC Web
 * Services using the XMLRPC-EPI extension for PHP.
 *
 * @package    agavi
 * @subpackage request
 *
 * @author     David Zülke <dz@bitxtender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 * @since      0.11.0
 *
 * @version    $Id$
 */
class AgaviXmlrpcepiphpRequest extends AgaviWebserviceRequest
{
	/**
	 * Initialize this Request.
	 *
	 * @param      AgaviContext An AgaviContext instance.
	 * @param      array        An associative array of initialization parameters.
	 *
	 * @throws     <b>AgaviInitializationException</b> If an error occurs while
	 *                                                 initializing this Request.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function initialize(AgaviContext $context, array $parameters = array())
	{
		parent::initialize($context, $parameters);
		
		$decoded = xmlrpc_decode_request($this->input, $this->calledMethod, isset($parameters['encoding']) ? $parameters['encoding'] : 'utf-8');
		
		if(count($decoded) == 1 && is_int($key = array_pop(array_keys($decoded))) && is_array($decoded[$key])) {
			$decoded = $decoded[$key];
		}
		
		$this->setParameters($decoded);
		
		$split = explode(':', $this->calledMethod);
		if(count($split) == 2) {
			$this->setParameter($this->getModuleAccessor(), $split[0]);
			$this->setParameter($this->getActionAccessor(), $split[1]);
		} else {
			$this->setParameter($this->getActionAccessor(), $this->calledMethod);
		}
	}
}

?>
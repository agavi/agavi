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
 * AgaviXmlrpcepiphpRequest is an implementation for handling XMLRPC Web
 * Services using the XMLRPC-EPI extension for PHP.
 *
 * @package    agavi
 * @subpackage request
 *
 * @author     David Zülke <dz@bitxtender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
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
		
		$decoded = (array) xmlrpc_decode_request($this->input, $this->invokedMethod, isset($parameters['encoding']) ? $parameters['encoding'] : 'utf-8');
		
		$akeys = array_keys($decoded);
		if(count($decoded) == 1 && is_int($key = array_pop($akeys)) && is_array($decoded[$key])) {
			$decoded = $decoded[$key];
		}
		
		$rdhc = $this->getParameter('request_data_holder_class');
		$rd = new $rdhc(array(
			constant("$rdhc::SOURCE_PARAMETERS") => (array)$decoded,
		));
		
		if($this->getParameter('use_module_action_parameters')) {
			$split = explode(':', $this->invokedMethod);
			if(count($split) == 2) {
				$rd->setParameter($this->getParameter('module_accessor'), $split[0]);
				$rd->setParameter($this->getParameter('action_accessor'), $split[1]);
			} else {
				$rd->setParameter($this->getParameter('action_accessor'), $this->invokedMethod);
			}
		}
		
		$this->setRequestData($rd);
	}
}

?>
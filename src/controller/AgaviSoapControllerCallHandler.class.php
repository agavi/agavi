<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2003-2007 the Agavi Project.                                |
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
 * AgaviSoapControllerCallHandler has the __call overload for the PHP SOAP ext.
 *
 * @package    agavi
 * @subpackage controller
 *
 * @author     David Zülke <dz@bitxtender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.11.0
 *
 * @version    $Id$
 */
class AgaviSoapControllerCallHandler
{
	/**
	 * @var        AgaviContext The context.
	 */
	protected $context;
	
	/**
	 * Constructor. Expects the SoapController instance to use as the first arg.
	 *
	 * @param      AgaviSoapController The SOAP controller to call doDispatch on.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function __construct(AgaviContext $context)
	{
		$this->context = $context;
	}
	
	/**
	 * Call overload run by PHP's SoapServer while attempting to execute the 
	 * method called in the SOAP request.
	 *
	 * @param      string The name of the SOAP method called.
	 * @param      array  An array of parameters from the method call.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function __call($name, $arguments)
	{
		$ct = $this->context->getController();
		$rq = $this->context->getRequest();
		
		// set the name of the method that was called
		// the request will also update the routing input
		$rq->setInvokedMethod($name);
		
		// then we grab the SoapClient with the WSDL (yes, SoapClient!)
		// and grab a list of functions. in SoapClient, that list contains the method signatures, including the parameter names. SoapServer's __getFunctions() doesn't...
		$functions = $ct->getSoapClient()->__getFunctions();
		foreach($functions as $function) {
			// now we try to match the called method against the function signatures
			if(preg_match('/^(?:\S+|list\([^\)]*\))\s' . preg_quote($name) . '\(([^\)]*)\)$/', $function, $matches)) {
				// we found something, so we can extract all method argument names
				preg_match_all('/\$([\w]+)/', $matches[1], $params);
				for($i = 0; $i < count($params[1]); $i++) {
					// and replace the numeric keys from our method call args with the actual parameter names as defined in the WSDL
					$arguments[$params[1][$i]] = $arguments[$i];
					unset($arguments[$i]);
				}
				break;
			}
		}
		// all that was done because PHP's SOAP extension doesn't allow us to get information about the request. In SOAP, remote methods are always defined using named parameters, but that naming gets lost as PHP calls the respective function on the server directly, and PHP doesn't have named arguments. So all we know is the values that were given for the first, second, and so on parameter. But in Agavi, we want to access parameters by their names. We made it. With an ugly hack. Thank you, Zend.
		
		// finally, we can populate the request with the final data and call the _real_ dispatch() method on the "normal" controller. We hand it the arguments we got in the SOAP request. Everyone's happy.
		$rd = $rq->getRequestData();
		
		$rd->setParameters($arguments);
		
		// call doDispatch on the controller
		$response = $ct->doDispatch();
		
		// return the content. that's an array, or a float, or whatever, and PHP's SOAP extension will handle the response envelope creation, sending etc for us
		return $response->getContent();
	}
}

?>
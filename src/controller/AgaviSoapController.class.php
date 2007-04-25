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
 * AgaviSoapController handles SOAP requests.
 *
 * @package    agavi
 * @subpackage controller
 *
 * @author     David Zülke <dz@bitxtender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.9.0
 *
 * @version    $Id$
 */
class AgaviSoapController extends AgaviController
{
	/**
	 * @param      AgaviRequestDataHolder Additional request data for later use.
	 */
	protected $dispatchArguments = null;
	
	/**
	 * Dispatch a request
	 *
	 * @param      AgaviRequestDataHolder A RequestDataHolder with additional
	 *                                    request arguments.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function dispatch(AgaviRequestDataHolder $arguments = null)
	{
		// Remember The Milk... err... the arguments given.
		$this->dispatchArguments = $arguments;
		
		// get the name of the class to use for the server, defaults to PHP's own "SoapServer"
		$soapServerClass = $this->getParameter('soap_server_class', 'SoapServer');
		
		// user-supplied "wsdl" and "options" parameters
		$wsdl = $this->getParameter('wsdl');
		$options = (array) $this->getParameter('options', array());
		
		// create a server
		$soapServer = new $soapServerClass($wsdl, $options);
		
		// give it a class that handles method calls
		// that class uses __call
		// the class ctor gets the context as the first argument
		$soapServer->setClass($this->getParameter('soap_handler_class', 'AgaviSoapControllerCallHandler'), $this->context);
		
		// please don't send a response automatically, we need to return it inside the __call overload so PHP's SOAP extension creates a SOAP response envelope with the data
		$this->setParameter('send_response', false);
		
		// handle the request. the aforementioned __call will be run next
		// we use the input from the request as the argument, it contains the SOAP request
		$soapServer->handle($this->context->getRequest()->getInput());
	}
	
	/**
	 * A method that is called in the __call overload by the SOAP call handler.
	 *
	 * All it does is call parent::dispatch() to prevent an infinite loop.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function doDispatch()
	{
		try {
			return parent::dispatch($this->dispatchArguments);
		} catch(SoapFault $f) {
			return $f;
		}
	}
}

?>
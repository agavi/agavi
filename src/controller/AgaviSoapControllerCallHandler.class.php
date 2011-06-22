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
			if(preg_match('/^(?:\S+|list\([^\)]*\))\s' . preg_quote($name, '/') . '\(([^\)]*)\)$/', $function, $matches)) {
				// we found something, so we can extract all method argument names
				preg_match_all('/\$([\w]+)/', $matches[1], $params);
				for($i = 0; $i < count($params[1]); $i++) {
					// and replace the numeric keys from our method call args with the actual parameter names as defined in the WSDL
					$arguments[$params[1][$i]] = $arguments[$i];
					unset($arguments[$i]);
				}
				// and while we're at it, please get us the name of the return value as well, we need it in document/literal wrapped WSDL styles
				$returnType = '';
				if(preg_match('/^(\w+) /', $function, $matches)) {
					$returnType = $matches[1];
				}
				break;
			}
		}
		// all that was done because PHP's SOAP extension doesn't allow us to get information about the request. In SOAP, remote methods are always defined using named parameters, but that naming gets lost as PHP calls the respective function on the server directly, and PHP doesn't have named arguments. So all we know is the values that were given for the first, second, and so on parameter. But in Agavi, we want to access parameters by their names. We made it. With an ugly hack. Thank you, Zend.
		
		// for document/literal wrapped style services, unpack the complex type passed in by php, see http://bugs.php.net/bug.php?id=30302 - PHP produces an stdClass object with named members.
		if($ct->getParameter('force_document_literal_wrapped_marshalling', false)) {
			$unpackedArguments = array();
			foreach($arguments as $argument) {
				foreach($argument as $name => $value) {
					$unpackedArguments[$name] = $value;
				}
			}
			$arguments = $unpackedArguments;
		}
		
		// finally, we can populate the request with the final data and call the _real_ dispatch() method on the "normal" controller. We hand it the arguments we got in the SOAP request. Everyone's happy.
		$rd = $rq->getRequestData();
		
		$rd->setParameters($arguments);
		
		// call doDispatch on the controller
		$response = $ct->doDispatch();
		$responseContent = $response->getContent();
		
		// repack the document/literal wrapped content if required
		if($ct->getParameter('force_document_literal_wrapped_marshalling', false)) {
			// the return type is a complex type with a single element, but what's the name of that element?
			// struct methodNameResponse {
			//   typeName returnValueName;
			// }
			// it may also be empty, depending on the definition (if the request/response has a void input/output):
			// struct deleteEverything {
			// }
			
			// do not wrap soap faults
			if(!($responseContent instanceof SoapFault)) {
				$originalResponseContent = $responseContent;
				$wrapperFound = false;
				foreach($ct->getSoapClient()->__getTypes() as $type) {
					if($originalResponseContent !== null && preg_match('/^struct ' . preg_quote($returnType, '/') . ' \{\s*(.+)\s*\}$/s', $type, $matches)) {
						// next: extract all the return value part names (usually just one)
						$returnPartCount = preg_match_all('/^\s*(?P<type>\w+) (?P<name>\w+);$/m', $matches[1], $returnParts);
						
						// we convert the response content to an array if it's exactly one return part
						// so the code further down works without additional checks
						// a check like !is_array() would be wrong as the return value might be an array itself already (e.g. for a list of objects)
						if($returnPartCount == 1) {
							$originalResponseContent = array($originalResponseContent);
						}
						
						$responseContent = new stdClass();
						
						// it *should* be an array with return parts as keys, but doesn't have to be (first because PHP allows this, and second because we do this a couple of lines above)
						// so we need to iterate by hand and check for named key first, numeric offset second
						for($i = 0; $i < $returnPartCount; $i++) {
							$returnPartName = $returnParts['name'][$i];
							
							if(array_key_exists($returnPartName, $originalResponseContent)) {
								$returnPartValue = $originalResponseContent[$returnPartName];
							} elseif(array_key_exists($i, $originalResponseContent)) {
								$returnPartValue = $originalResponseContent[$i];
							} else {
								// nothing found
								// that means the response was invalid or something... we should bail out here, so $wrapperFound won't be true and the next type is tried
								continue 2;
							}
							
							$responseContent->$returnPartName = $returnPartValue;
						}
						
						// we set $wrapperFound only now
						$wrapperFound = true;
						break;
					} elseif($originalResponseContent === null && preg_match('/^struct ' . preg_quote($returnType, '/') . ' \{\s*\}$/s', $type, $matches)) {
						$wrapperFound = true;
						$responseContent = new stdClass();
						break;
					}
				}
				if(!$wrapperFound) {
					$responseContent = new SoapFault('Server', 'Failed to marshal document/literal wrapped response: no suitable type found.');
				}
			}
		}
		
		// return the content. that's an array, or a float, or whatever, and PHP's SOAP extension will handle the response envelope creation, sending etc for us
		return $responseContent;
	}
}

?>
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
 * AgaviFlowTestCase is the base class for all flow tests and provides
 * the necessary assertions
 * 
 * 
 * @package    agavi
 * @subpackage testing
 *
 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
 * @copyright  The Agavi Project
 *
 * @since      1.0.0
 *
 * @version    $Id$
 */
abstract class AgaviFlowTestCase extends AgaviPhpUnitTestCase implements AgaviIFlowTestCase
{
	/**
	 * @var        string the name of the context to use, null for default context
	 */
	protected $contextName = null;
	
	/**
	 * @var        string the fake routing input
	 */
	protected $input;
	
	/**
	 * @var        AgaviResponse the response after the dispatch call
	 */
	protected $response;
	
	/**
	 * Constructs a test case with the given name.
	 *
	 * @param        string $name
	 * @param        array  $data
	 * @param        string $dataName
	 */
	public function __construct($name = NULL, array $data = array(), $dataName = '')
	{
		parent::__construct($name, $data, $dataName);
		$this->setRunTestInSeparateProcess(true);
	}
	
	/**
	 * Return the context defined for this test (or the default one).
	 *
	 * @return     AgaviContext The context instance defined for this test.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.7
	 */
	public function getContext()
	{
		return AgaviContext::getInstance($this->contextName);
	}
	
	/**
	 * dispatch the request
	 *
	 * @author       Felix Gilcher <felix.gilcher@bitextender.com>
	 * @since        1.0.0 
	 */
	public function dispatch($parameters = array())
	{
		$_SERVER['REQUEST_URI'] = $this->getDispatchScriptName() . $this->getRoutingInput();
		$_SERVER['SCRIPT_NAME'] = $this->getDispatchScriptName();
		
		$context = $this->getContext();
		$this->setRequestData($parameters);
		$context->getRequest()->setMethod($this->getRequestMethod());
		
		$controller = $context->getController();
		$controller->setParameter('send_response', false);
		
		$this->response = $controller->dispatch();
	}
	
	protected function setRequestData($data)
	{
		$rd = $this->getContext()->getRequest()->getRequestData();
		if (is_array($data)) {
			$rd->setParameters($data);
		} elseif ($data instanceof AgaviRequestDataHolder) {
			$rd->merge($data);
		}
	}
	
	/**
	 * retrieve the name of the dispatcher script
	 * 
	 * @return       string the dispatcher scriptname set by an annotation, '/index.php' by default
	 * 
	 * @author       Felix Gilcher <felix.gilcher@bitextender.com>
	 * @since        1.0.1
	 */
	protected function getDispatchScriptName()
	{
		$scriptName = null;
		
		$annotations = $this->getAnnotations();
		
		if(!empty($annotations['method']['agaviDispatchScriptName'])) {
			$scriptName = $annotations['method']['agaviDispatchScriptName'][0];
		} elseif(!empty($annotations['class']['agaviDispatchScriptName'])) {
			$scriptName = $annotations['class']['agaviDispatchScriptName'][0];
		} else {
			$scriptName = '/index.php';
		}
		
		return $scriptName;
	}
	
	/**
	 * retrieve the request method for the dispatch call
	 * 
	 * @return       string the name of the request method, 'Read' by default
	 * 
	 * @author       Felix Gilcher <felix.gilcher@bitextender.com>
	 * @since        1.0.1
	 */
	protected function getRequestMethod()
	{
		$method = null;
		
		$annotations = $this->getAnnotations();
		
		if(!empty($annotations['method']['agaviRequestMethod'])) {
			$method = $annotations['method']['agaviRequestMethod'][0];
		} elseif(!empty($annotations['class']['agaviRequestMethod'])) {
			$method = $annotations['class']['agaviRequestMethod'][0];
		} else {
			$method = 'Read';
		}
		
		return $method;
	}
	
	/**
	 * retrieve the routing input for the dispatch call
	 * 
	 * @return       string the name of the request method, 'Read' by default
	 * 
	 * @author       Felix Gilcher <felix.gilcher@bitextender.com>
	 * @since        1.0.1
	 */
	protected function getRoutingInput()
	{
		$input = null;
		
		$annotations = $this->getAnnotations();
		
		if(!empty($annotations['method']['agaviRoutingInput'])) {
			$input = $annotations['method']['agaviRoutingInput'][0];
		} elseif(!empty($annotations['class']['agaviRoutingInput'])) {
			$input = $annotations['class']['agaviRoutingInput'][0];
		} elseif(!empty($this->input)) {
			$input = $this->input;
		} else {
			$input = '';
		}
		
		return $input;
	}
	
	/**
	 * assert that the response has a given tag
	 * 
	 * @see the documentation of PHPUnit's assertTag()
	 * 
	 * @param        array the matcher describing the tag
	 * @param        string an optional message
	 * 
	 * @author       Felix Gilcher <felix.gilcher@bitextender.com>
	 * @since        1.0.0
	 */
	public function assertResponseHasTag($matcher, $message = '', $isHtml = true)
	{
		$this->assertTag($matcher, $this->response->getContent(), $message, $isHtml);
	}
	
	
	/**
	 * assert that the response does not have a given tag
	 * 
	 * @see the documentation of PHPUnit's assertTag()
	 * 
	 * @author       Felix Gilcher <felix.gilcher@bitextender.com>
	 * @since        1.0.0
	 */
	public function assertResponseHasNotTag($matcher, $message = '', $isHtml = true)
	{
		$this->assertNotTag($matcher, $this->response->getContent(), $message, $isHtml);
	}
}

?>
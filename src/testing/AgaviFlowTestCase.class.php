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
	 * @var        string the name of the action to use
	 */
	protected $actionName;
	
	/**
	 * @var        string the name of the module the action resides in
	 */
	protected $moduleName;
	
	/**
	 * @var        AgaviResponse the response after the dispatch call
	 */
	protected $response;
	
	/**
	 * Constructs a test case with the given name.
	 *
	 * @param  string $name
	 * @param  array  $data
	 * @param  string $dataName
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
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 * @since      1.0.0 
	 */
	public function dispatch($arguments = null, $outputType = null, $requestMethod = null)
	{
		$_SERVER['REQUEST_URI'] = '/index.php' . $this->input;
		$_SERVER['SCRIPT_NAME'] = '/index.php';
		
		$context = AgaviContext::getInstance();
		
		$controller = $context->getController();
		$controller->setParameter('send_response', false);
		
		if(!($arguments instanceof AgaviRequestDataHolder)) {
			$arguments = $this->createRequestDataHolder(array(AgaviRequestDataHolder::SOURCE_PARAMETERS => $arguments));
		}
		
		$this->response = $controller->dispatch(null, $controller->createExecutionContainer($this->moduleName, $this->actionName, $arguments, $outputType, $requestMethod));
	}
	
	/**
	 * assert that the response has a given tag
	 * 
	 * @see the documentation of PHPUnit's assertTag()
	 * 
	 * @param      array the matcher describing the tag
	 * @param      string an optional message
	 * 
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 * @since      1.0.0
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
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 * @since      1.0.0
	 */
	public function assertResponseHasNotTag($matcher, $message = '', $isHtml = true)
	{
		$this->assertNotTag($matcher, $this->response->getContent(), $message, $isHtml);
	}

	/**
	 * create a requestDataHolder with the given arguments and type
	 * 
	 * arguments need to be passed in the way {@see AgaviRequestDataHolder} accepts them
	 * 
	 * array(AgaviRequestDataHolder::SOURCE_PARAMETERS => array('foo' => 'bar'))
	 * 
	 * if no type is passed, the default for the configured request class will be used
	 * 
	 * @param      array   a two-dimensional array with the arguments
	 * @param      string  the subclass of AgaviRequestDataHolder to create
	 * 
	 * @return     AgaviRequestDataHolder
	 * 
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 * @since      1.0.0
	 */
	protected function createRequestDataHolder(array $arguments = array(), $type = null)
	{
		if(null === $type) {
			$type = AgaviContext::getInstance()->getRequest()->getParameter('request_data_holder_class', 'AgaviRequestDataHolder');
		}
		
		$class = new $type($arguments);
		return $class;
	}
}

?>
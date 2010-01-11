<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2005-2010 the Agavi Project.                                |
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
 * AgaviContainerTestCase is the base class for all tests that target a specific
 * container execution and provides the necessary assertions
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
 * @version    $Id: AgaviFlowTestCase.class.php 3843 2009-02-16 14:12:47Z felix $
 */
abstract class AgaviContainerTestCase extends AgaviFragmentTestCase
{
	/**
	 * @var        string the name of the action to use
	 */
	protected $acionName;
	
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
	 * dispatch the request
	 *
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 * @since      1.0.0 
	 */
	public function execute($arguments = null, $outputType = null, $requestMethod = null)
	{
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
}

?>
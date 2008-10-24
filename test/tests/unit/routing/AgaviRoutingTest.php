<?php

class AgaviRoutingTest extends AgaviPhpUnitTestCase
{
	protected $routing;
	protected $parameters = array('enabled' => true);
	
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
	
	public function setUp()
	{
		$this->routing = new AgaviTestingRouting();
		$this->routing->initialize(AgaviContext::getInstance(null), $this->parameters);
		$this->routing->startup();
	}
	
	public function testExecuteDisabled()
	{
		$this->routing->setParameter('enabled', false);
		$container = $this->routing->execute();
		$this->assertEquals(null, $container->getActionName());
		$this->assertEquals(null, $container->getModuleName());
	}
	
	public function testExecuteEmptyInput()
	{
		$this->routing->setInput('');
		$container = $this->routing->execute();
		$this->assertEquals(AgaviConfig::get('actions.error_404_action'), $container->getActionName());
		$this->assertEquals(AgaviConfig::get('actions.error_404_module'), $container->getModuleName());
		$this->assertEquals(array(), AgaviContext::getInstance(null)->getRequest()->getAttribute('matched_routes', 'org.agavi.routing'));
	}
	
	public function testExecuteSimpleInput()
	{
		$this->routing->setInput('/');
		$container = $this->routing->execute();
		$this->assertEquals(AgaviConfig::get('actions.default_action'), $container->getActionName());
		$this->assertEquals(AgaviConfig::get('actions.default_module'), $container->getModuleName());
		$this->assertEquals(array('index'), AgaviContext::getInstance(null)->getRequest()->getAttribute('matched_routes', 'org.agavi.routing'));
	}
	
	public function testExecuteUserAuthenticated()
	{
		$ctx = AgaviContext::getInstance(null);
		$ctx->getUser()->setAuthenticated(true);
		$this->routing->setInput('/');
		$container = $this->routing->execute();
		$this->assertEquals('LoggedIn', $container->getActionName());
		$this->assertEquals('Auth', $container->getModuleName());
		$this->assertEquals(array('user_logged_in'), $ctx->getRequest()->getAttribute('matched_routes', 'org.agavi.routing'));
		$ctx->getUser()->setAuthenticated(false);
	}
	
	public function testExecuteServer()
	{	
		$_SERVER['routing_test'] = 'foo';
		$ctx = AgaviContext::getInstance(null);
		$this->routing->setInput('/');
		$this->routing->setRoutingSource('_SERVER', $_SERVER);
		$container = $this->routing->execute();
		$this->assertEquals('Matched', $container->getActionName());
		$this->assertEquals('Server', $container->getModuleName());
		$this->assertEquals(array('server'), $ctx->getRequest()->getAttribute('matched_routes', 'org.agavi.routing'));
	}
	
	public function testExecuteRandomSource()
	{	
		$data = array();
		$data['bar'] = 'foo';
		$ctx = AgaviContext::getInstance(null);
		$this->routing->setInput('/');
		$this->routing->setRoutingSource('testingsource', $data);
		$container = $this->routing->execute();
		$this->assertEquals('Matched', $container->getActionName());
		$this->assertEquals('TestingSource', $container->getModuleName());
		$this->assertEquals(array('testingsource'), $ctx->getRequest()->getAttribute('matched_routes', 'org.agavi.routing'));
	}
	
	/*
    public function testExecuteNonexistantSource()
	{	
		$ctx = AgaviContext::getInstance(null);
		$this->routing->setInput('/');
		$container = $this->routing->execute();
		$this->assertEquals('Matched', $container->getActionName());
		$this->assertEquals('TestingSource', $container->getModuleName());
		$this->assertEquals(array('testingsource'), $ctx->getRequest()->getAttribute('matched_routes', 'org.agavi.routing'));
	}*/

	public function testMatchWithParam()
	{
		$ctx = AgaviContext::getInstance(null);
		$this->routing->setInput('/withparam/5');
		$container = $this->routing->execute();
		$this->assertEquals(array('with_param'), AgaviContext::getInstance(null)->getRequest()->getAttribute('matched_routes', 'org.agavi.routing'));
		$this->assertEquals(5, $ctx->getRequest()->getRequestData()->getParameter('number'));
		$this->assertEquals('MatchedParam', $container->getActionName());
		$this->assertEquals('TestWithParam', $container->getModuleName());
	}
	
	public function testMatchWithMultipleParams()
	{
		$ctx = AgaviContext::getInstance(null);
		$this->routing->setInput('/withmultipleparams/5/foo');
		$container = $this->routing->execute();
		$this->assertEquals(array('with_two_params'), AgaviContext::getInstance(null)->getRequest()->getAttribute('matched_routes', 'org.agavi.routing'));
		$this->assertEquals(5, $ctx->getRequest()->getRequestData()->getParameter('number'));
		$this->assertEquals('foo', $ctx->getRequest()->getRequestData()->getParameter('string'));
		$this->assertEquals('MatchedMultipleParams', $container->getActionName());
		$this->assertEquals('TestWithParam', $container->getModuleName());
	}
	
	public function testOnNotMatched()
	{
		$this->routing->setInput('/callbacks/on_not_matched/callback_stopper');
		try {
			$container = $this->routing->execute();
		} catch (AgaviException $e) {
			$this->assertEquals('Not Matched', $e->getMessage());
		}
	}
	
	public function testNonMatchingCallback()
	{
		$this->routing->setInput('/callbacks/nonmatching_callback');
		$container = $this->routing->execute();
		$this->assertEquals(array('callbacks'), AgaviContext::getInstance(null)->getRequest()->getAttribute('matched_routes', 'org.agavi.routing'));
		$this->assertEquals(AgaviConfig::get('actions.error_404_module'), $container->getModuleName());
		$this->assertEquals(AgaviConfig::get('actions.error_404_action'), $container->getActionName());
	}
}


?>
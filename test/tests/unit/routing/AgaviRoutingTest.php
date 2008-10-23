<?php

class AgaviRoutingTest extends AgaviPhpUnitTestCase
{
	protected $routing;
	protected $parameters = array('enabled' => true);
	
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
}


?>
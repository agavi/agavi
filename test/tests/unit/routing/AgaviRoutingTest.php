<?php

class AgaviRoutingTest extends AgaviPhpUnitTestCase
{
	protected $routing;
	protected $parameters = array();
	
	public function setUp()
	{
		$this->routing = new AgaviTestingRouting();
		$this->routing->initialize(AgaviContext::getInstance(null), $this->parameters);
	}
	
	public function testExecuteDisabled()
	{
		$this->routing->setParameter('enabled', false);
		$container = $this->routing->execute();
		$this->assertEquals(null, $container->getActionName());
		$this->assertEquals(null, $container->getModuleName());
	}
}


?>
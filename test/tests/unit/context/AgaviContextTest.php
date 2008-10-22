<?php

class AgaviContextTest extends AgaviPhpUnitTestCase
{
	public function testGetInstance()
	{
		$instance = AgaviContext::getInstance('foo');
		$this->assertNotNull($instance);
		$this->markTestIncomplete('Should test that the instance is an AgaviContext');
	}
	
	public function testSameInstanceForSameProfile()
	{
		$instance1 = AgaviContext::getInstance('foo');
		$instance2 = AgaviContext::getInstance('foo');
		$this->assertSame($instance1, $instance2);
	}
	
	public function testDifferentInstanceForDifferentProfile()
	{
		$instance1 = AgaviContext::getInstance('foo');
		$instance2 = AgaviContext::getInstance('bar');
		$this->assertNotSame($instance1, $instance2);
	}	
}
?>
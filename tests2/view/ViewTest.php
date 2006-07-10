<?php

class SampleView extends AgaviView
{
	public function execute() {}
}

class ViewTest extends AgaviTestCase
{
	private 
		$_v = null, 
		$_r = null;

	public function setUp()
	{
		AgaviContext::getInstance()->initialize();
		$request = AgaviContext::getInstance()->getRequest();
		AgaviContext::getInstance()->getController()->dispatch(array($request->getModuleAccessor() => 'Test', $request->getActionAccessor() => 'Test'));
		$this->_r = new NoHeadersAgaviWebResponse();
		$this->_r->initialize(AgaviContext::getInstance());
		$this->_v = new SampleView();
		$this->_v->initialize($this->_r);
	}

	public function testInitialize()
	{
		$ctx = AgaviContext::getInstance();
		$v = $this->_v;

		$ctx_test = $v->getContext();
		$r_test = $v->getResponse();
		$this->assertReference($ctx, $ctx_test);
		$this->assertReference($this->_r, $r_test);

		$this->assertEquals($ctx->getController()->getModuleDirectory() . '/templates', $v->getDecoratorDirectory());
		$this->assertEquals($ctx->getController()->getModuleDirectory() . '/templates', $v->getDirectory());

		$this->assertNull($v->getDecoratorTemplate());
		$this->assertNull($v->getTemplate());

		$this->assertEquals(array(), $v->getSlots());

		$this->assertFalse($v->isDecorator());
	}


}
?>
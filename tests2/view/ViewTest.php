<?php

class SampleView extends AgaviView
{
	public function execute(AgaviParameterHolder $parameters) {}
}

class ViewTest extends AgaviTestCase
{
	private 
		$_v = null, 
		$_r = null;

	public function setUp()
	{
		$ctx = AgaviContext::getInstance('test');
		$ctx->initialize();
		$request = $ctx->getRequest();
		
		ob_start();
		$ctx->getController()->dispatch(array($request->getModuleAccessor() => 'Test', $request->getActionAccessor() => 'Test'));
		ob_end_clean();
		
		$this->_v = new SampleView();
		$this->_v->initialize($ct = $ctx->getController()->createExecutionContainer('Test', 'Test'));
		$this->_r = $ct->getResponse();
	}

	public function testInitialize()
	{
		$ctx = AgaviContext::getInstance('test');
		$v = $this->_v;

		$ctx_test = $v->getContext();
		$r_test = $v->getResponse();
		$this->assertReference($ctx, $ctx_test);
		$this->assertReference($this->_r, $r_test);

		$this->assertNull($v->getDecoratorTemplate());
		$this->assertNull($v->getTemplate());

		$this->assertEquals(array(), $v->getSlots());

		$this->assertFalse($v->isDecorator());
	}


}
?>
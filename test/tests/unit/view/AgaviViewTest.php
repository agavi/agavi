<?php

class SampleView extends AgaviView
{
	public function execute(AgaviRequestDataHolder $rd) {}
}

class ViewTest extends AgaviUnitTestCase
{
	private 
		$_v = null, 
		$_r = null;

	public function setUp()
	{
		$ctx = $this->getContext();
		$ctx->initialize();
		$request = $ctx->getRequest();

		$this->_v = new SampleView();
		$this->_v->initialize($ct = $ctx->getController()->createExecutionContainer('Test', 'Test'));
		$this->_r = $ct->getResponse();
	}

	public function testInitialize()
	{
		$ctx = $this->getContext();
		$v = $this->_v;

		$ctx_test = $v->getContext();
		$this->assertSame($ctx, $ctx_test);
	}


}
?>
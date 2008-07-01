<?php

class SampleView extends AgaviView
{
	public function execute(AgaviRequestDataHolder $rd) {}
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
		$ctx->getController()->dispatch(new AgaviRequestDataHolder(array(AgaviRequestDataHolder::SOURCE_PARAMETERS => array($request->getParameter('module_accessor') => 'Test', $request->getParameter('action_accessor') => 'Test'))));
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
		$this->assertReference($ctx, $ctx_test);
	}


}
?>
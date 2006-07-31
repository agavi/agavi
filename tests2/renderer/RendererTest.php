<?php

class TRTestSampleRenderer extends AgaviRenderer
{
	public function getEngine()
	{
	}

	public function render()
	{
	}
}

class TRTestSampleView extends AgaviView
{
	public function initialize(AgaviResponse $response)
	{
		$this->context = $response->getContext();
		
		$this->response = $response;
	}

	public function execute(AgaviParameterHolder $parameters)
	{
	}
}

class TestRenderer extends AgaviTestCase
{
	protected $_r = null, $_v = null;

	public function setUp()
	{
		$rs = new AgaviWebResponse();
		$rs->initialize(AgaviContext::getInstance('test'));
		$this->_v = new TRTestSampleView();
		$this->_v->initialize($rs);
		$this->_r = new TRTestSampleRenderer();
		$this->_r->initialize(AgaviContext::getInstance('test'));
	}

	public function testGetContext()
	{
		$this->assertReference(AgaviContext::getInstance('test'), $this->_r->getContext());
	}

	public function testSetGetExtension()
	{
		$r = $this->_r;
		$this->assertSame('', $r->getExtension());
		$r->setExtension('sampleExt');
		$this->assertSame('sampleExt', $r->getExtension());
	}

	public function testSetGetView()
	{
		$r = $this->_r;
		$this->assertNull($r->getView());
		$r->setView($this->_v);
		$this->assertReference($this->_v, $this->_r->getView());
		
		$v = new TRTestSampleView();
		$r->setView($v);
		$this->assertReference($v, $this->_r->getView());
	}

	public function testGetResponse()
	{
		$this->assertNull($this->_r->getResponse());
	}
}
?>
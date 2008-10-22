<?php

class SampleResponse extends AgaviResponse
{
	public function clear()
	{
	}

	public function send(AgaviOutputType $ot = null)
	{
	}
	
	public function setRedirect($to)
	{
	}
	
	public function getRedirect()
	{
	}
	
	public function hasRedirect()
	{
	}
	
	public function clearRedirect()
	{
	}
	
	public function merge(AgaviResponse $other)
	{
	}
}

class ResponseTest extends AgaviTestCase
{
	private $_r = null;

	public function setUp()
	{
		$this->_r = new SampleResponse();
		$this->_r->initialize(AgaviContext::getInstance('test'));
	}

	public function testGetContext()
	{
		$ctx = AgaviContext::getInstance('test');
		$ctx_test = $this->_r->getContext();
		$this->assertReference($ctx, $ctx_test);
	}

	public function testSetGetContent()
	{
		$r = $this->_r;
		$this->assertEquals('', $r->getContent());
		$r->setContent('test1');
		$this->assertEquals('test1', $r->getContent());
	}

	public function testPrependContent()
	{
		$r = $this->_r;

		$r->setContent('content a');
		$r->prependContent('content b');
		$this->assertEquals('content b' . 'content a', $r->getContent());
	}

	public function testAppendContent()
	{
		$r = $this->_r;

		$r->setContent('content a');
		$r->appendContent('content b');
		$this->assertEquals('content a' . 'content b', $r->getContent());
	}
}

?>
<?php

class TRTestSampleRenderer extends AgaviRenderer
{
	public function render(AgaviTemplateLayer $layer, array &$attributes = array(), array &$slots = array(), array &$moreAssigns = array())
	{
	}
}

class RendererTest extends AgaviTestCase
{
	protected $_r = null, $_v = null;

	public function setUp()
	{
		$this->_r = new TRTestSampleRenderer();
		$this->_r->initialize(AgaviContext::getInstance('test'));
	}

	public function testGetContext()
	{
		$this->assertReference(AgaviContext::getInstance('test'), $this->_r->getContext());
	}
}
?>
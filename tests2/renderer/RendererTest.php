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
		$c1 = AgaviContext::getInstance('test');
		$c2 = $this->_r->getContext();
		$this->assertReference($c1, $c2);
	}
}
?>
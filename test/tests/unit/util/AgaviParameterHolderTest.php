<?php

class AgaviParameterHolderTest extends AgaviUnitTestCase
{

	public function testHasParameterArrayKeys()
	{
		$p = new AgaviParameterHolder(array('foo' => array('bar' => 'baz')));

		$this->assertTrue($p->hasParameter('foo[bar]'), 'Failed asserting that the parameter holder has the parameter foo[bar].'); 
		$this->assertEquals('baz', $p->getParameter('foo[bar]'), 'Failed asserting that the parameter foo[bar] has the value baz.');
		$this->assertEquals(array('bar' => 'baz'), $p->getParameter('foo'), 'Failed asserting that the parameter foo has the value array("baz" => "bar").');
		$this->assertTrue($p->hasParameter('foo'), 'Failed asserting that the parameter holder has the parameter foo.');
	}
	
	public function testRemoveInvalidKeyCausesNoNotice()
	{
		$ph = new AgaviParameterHolder();
		$zomg =& $ph->removeParameter('[]foo[]');
		$this->assertNull($zomg);
	}
}

?>
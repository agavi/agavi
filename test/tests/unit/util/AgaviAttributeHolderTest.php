<?php

class AgaviAttributeHolderTestAttributeHolder extends AgaviAttributeHolder
{
}

class AgaviAttributeHolderTest extends AgaviUnitTestCase
{
	public function testRemoveReturnsByReference()
	{
		$one = 'two';
		$omg = array('foo' => 'bar', 'bar' => 'baz');
		$foo =& $omg['foo'];
		
		$ph = new AgaviAttributeHolderTestAttributeHolder();
		
		$ph->setAttributeByRef('one', $one);
		$two =& $ph->removeAttribute('one');
		$two = 'six';
		$this->assertEquals('six', $one);
		
		$ph->setAttributeByRef('omg', $omg);
		$omgfoo =& $ph->removeAttribute('omg[foo]');
		$omgfoo = 'baz';
		$this->assertEquals('baz', $foo);
	}
}

?>
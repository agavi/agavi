<?php

class SampleAttributeHolder extends AgaviAttributeHolder {}

class AttributeHolderTest extends AgaviTestCase
{
	private $_ah = null;

	public function setUp()
	{
		$this->_ah = new SampleAttributeHolder();
	}

	public function testClearAttributes()
	{
		$ah = $this->_ah;
		$ah->setAttribute('blah', 'blahval');
		$ah->setAttribute('blah2', 'blah2val');
		$ah->clearAttributes();
		$this->assertEquals(array(), $ah->getAttributes());
		$this->assertEquals(null, $ah->getAttributeNames());
	}

	public function testGetAttribute()
	{
		$ah = $this->_ah;
		$ah->setAttribute('blah', 'blahval');
		$this->assertEquals('blahval', $ah->getAttribute('blah'));
		$this->assertNull($ah->getAttribute('bunk'));

		$ah->setAttribute('blah', 'otherblah', 'some/other/namespace');
		$this->assertEquals('otherblah', $ah->getAttribute('blah', 'some/other/namespace'));
		$this->assertNull($ah->getAttribute('bunk', 'some/other/namespace'));

		$this->assertEquals('blahval', $ah->getAttribute('blah'));
	}

	public function testGetAttributeNames()
	{
		$ah = $this->_ah;
		$ah->setAttribute('blah', 'blahval');
		$ah->setAttribute('blah2', 'blah2val');
		$this->assertEquals(array('blah', 'blah2'), $ah->getAttributeNames());

		$ah->setAttribute('blah', 'otherblah', 'some/other/namespace');
		$this->assertEquals(array('blah'), $ah->getAttributeNames('some/other/namespace'));

		$this->assertEquals(array('blah', 'blah2'), $ah->getAttributeNames());

		$this->assertNull($ah->getAttributeNames('/bunk/namespace'));
	}

	public function testGetAttributeNamespace()
	{
		$ah = $this->_ah;
		$ah->setAttribute('blah', 'blahval');
		$ah->setAttribute('blah2', 'blah2val');
		$this->assertEquals(array('blah'=>'blahval', 'blah2'=>'blah2val'), $ah->getAttributeNamespace());

		$ah->setAttribute('blah', 'otherblah', 'some/other/namespace');
		$this->assertEquals(array('blah'=>'otherblah'), $ah->getAttributeNamespace('some/other/namespace'));

		$this->assertEquals(array('blah'=>'blahval', 'blah2'=>'blah2val'), $ah->getAttributeNamespace());

		$this->assertNull($ah->getAttributeNamespace('/bunk/namespace'));
	}

	public function testGetAttributeNamespaces()
	{
		$ah = $this->_ah;
		$this->assertEquals(array(), $ah->getAttributeNamespaces());
		$ah->setAttribute('blah', 'blahval');
		$this->assertEquals(array($ah->getDefaultNamespace()), $ah->getAttributeNamespaces());
		$ah->setAttribute('blah', 'blahval', 'some/other/namespace');
		$this->assertEquals(array($ah->getDefaultNamespace(), 'some/other/namespace'), $ah->getAttributeNamespaces());
	}

	public function testgetAttributes()
	{
		$ah = $this->_ah;
		$ah->setAttribute('blah', 'blahval');
		$ah->setAttribute('blah2', 'blah2val');
		$this->assertEquals(array('blah'=>'blahval', 'blah2'=>'blah2val'), $ah->getAttributes());

		$ah->setAttribute('blah', 'otherblah', 'some/other/namespace');
		$this->assertEquals(array('blah'=>'otherblah'), $ah->getAttributes('some/other/namespace'));

		$this->assertEquals(array('blah'=>'blahval', 'blah2'=>'blah2val'), $ah->getAttributes());

		$this->assertEquals(array(), $ah->getAttributes('/bunk/namespace'));
	}

	public function testHasAttribute()
	{
		$ah = $this->_ah;
		$this->assertFalse($ah->hasAttribute('blah'));
		$ah->setAttribute('blah', 'blahval');
		$this->assertTrue($ah->hasAttribute('blah'));
		$this->assertFalse($ah->hasAttribute('bunk'));

		$this->assertFalse($ah->hasAttribute('blah', 'some/other/namespace'));
		$ah->setAttribute('blah', 'otherblah', 'some/other/namespace');
		$this->assertTrue($ah->hasAttribute('blah', 'some/other/namespace'));
		$this->assertFalse($ah->hasAttribute('bunk', 'some/other/namespace'));
		$ah->removeAttribute('blah', 'some/other/namespace');

		$this->assertTrue($ah->hasAttribute('blah'));
	}

	public function testHasAttributeNamespace()
	{
		$ah = $this->_ah;
		$this->assertFalse($ah->hasAttributeNamespace($ah->getDefaultNamespace()));
		$ah->setAttribute('blah', 'blahval');
		$this->assertTrue($ah->hasAttributeNamespace($ah->getDefaultNamespace()));
		$this->assertFalse($ah->hasAttributeNamespace('some/other/namespace'));
		$ah->setAttribute('blah', 'blahval', 'some/other/namespace');
		$this->assertTrue($ah->hasAttributeNamespace('some/other/namespace'));
	}

	public function testRemoveAttribute()
	{
		$ah = $this->_ah;
		$this->assertNull($ah->removeAttribute('blah'));
		$ah->setAttribute('blah', 'blahval');
		$this->assertEquals('blahval', $ah->removeAttribute('blah'));
		$this->assertNull($ah->removeAttribute('blah'));
		$ah->setAttribute('blah', 'blahval');

		$this->assertNull($ah->removeAttribute('blah', 'some/other/namespace'));
		$ah->setAttribute('blah', 'otherblah', 'some/other/namespace');
		$this->assertEquals('otherblah', $ah->removeAttribute('blah', 'some/other/namespace'));
		$this->assertNull($ah->removeAttribute('blah', 'some/other/namespace'));

		$this->assertEquals('blahval', $ah->removeAttribute('blah'));
	}

	public function testRemoveAttributeNamespace()
	{
		$ah = $this->_ah;
		$ah->setAttribute('blah', 'blahval');
		$ah->removeAttributeNamespace($ah->getDefaultNamespace());
		$this->assertFalse($ah->hasAttributeNamespace($ah->getDefaultNamespace()));
	}

	public function testSetAttribute()
	{
		$ah = $this->_ah;
		$ah->setAttribute('blah', 'blahval');
		$this->assertEquals('blahval', $ah->getAttribute('blah'));

		$ah->setAttribute('blah', 'otherblah', 'some/other/namespace');
		$this->assertEquals('otherblah', $ah->getAttribute('blah', 'some/other/namespace'));

		$this->assertEquals('blahval', $ah->getAttribute('blah'));
	}

	public function testAppendAttribute()
	{
		$ah = $this->_ah;
		$ah->appendAttribute('blah', 'blahval');
		$this->assertEquals(array('blahval'), $ah->getAttribute('blah'));
		$ah->appendAttribute('blah', 'blahval2');
		$this->assertEquals(array('blahval','blahval2'), $ah->getAttribute('blah'));

		$ah->appendAttribute('blah', 'otherblah', 'some/other/namespace');
		$this->assertEquals(array('otherblah'), $ah->getAttribute('blah', 'some/other/namespace'));
		$ah->appendAttribute('blah', 'otherblah2', 'some/other/namespace');
		$this->assertEquals(array('otherblah', 'otherblah2'), $ah->getAttribute('blah', 'some/other/namespace'));

		$this->assertEquals(array('blahval','blahval2'), $ah->getAttribute('blah'));
	}

	public function testSetAttributeByRef()
	{
		$ah = $this->_ah;
		$myval = 'blahval';
		$ah->setAttributeByRef('blah', $myval);
		$this->assertReference($myval, $ah->getAttribute('blah'));

		$myval2 = 'otherblah';
		$ah->setAttributeByRef('blah', $myval2, 'some/other/namespace');
		$this->assertReference($myval2, $ah->getAttribute('blah', 'some/other/namespace'));

		$this->assertReference($myval, $ah->getAttribute('blah'));
	}

	public function testAppendAttributeByRef()
	{
		$ah = $this->_ah;
		$myval1 = 'jack';
		$myval2 = 'bill';
		$ah->appendAttributeByRef('blah', $myval1);
		$out = $ah->getAttribute('blah');
		$this->assertReference($myval1, $out[0]);
		$ah->appendAttributeByRef('blah', $myval2);
		$out = $ah->getAttribute('blah');
		$this->assertReference($myval1, $out[0]);
		$this->assertReference($myval2, $out[1]);

		$myval3 = 'jill';
		$myval4 = 'jane';
		$ah->appendAttributeByRef('blah', $myval3, 'some/other/namespace');
		$out = $ah->getAttribute('blah', 'some/other/namespace');
		$this->assertReference($myval3, $out[0]);
		$ah->appendAttributeByRef('blah', $myval4, 'some/other/namespace');
		$out = $ah->getAttribute('blah', 'some/other/namespace');
		$this->assertReference($myval3, $out[0]);
		$this->assertReference($myval4, $out[1]);

		$out = $ah->getAttribute('blah');
		$this->assertReference($myval1, $out[0]);
	}


	public function testSetAttributes()
	{
		$ah = $this->_ah;
		$ah->setAttributes(array('blah'=>'blahval'));
		$this->assertEquals('blahval', $ah->getAttribute('blah'));
		$ah->setAttributes(array('blah2'=>'blah2val'));
		$this->assertEquals('blahval', $ah->getAttribute('blah'));
		$this->assertEquals('blah2val', $ah->getAttribute('blah2'));

		$ah->setAttributes(array('blah'=>'otherblah'), 'some/other/namespace');
		$this->assertEquals('otherblah', $ah->getAttribute('blah', 'some/other/namespace'));
		$ah->setAttributes(array('blah2'=>'otherblah2'), 'some/other/namespace');
		$this->assertEquals('otherblah', $ah->getAttribute('blah', 'some/other/namespace'));
		$this->assertEquals('otherblah2', $ah->getAttribute('blah2', 'some/other/namespace'));

		$this->assertEquals('blahval', $ah->getAttribute('blah'));
		$this->assertEquals('blah2val', $ah->getAttribute('blah2'));
	}

	public function testSetAttributesByRef()
	{
		$ah = $this->_ah;
		$myval1 = 'blah';
		$myval2 = 'blah2';
		$ah->setAttributes(array('blah'=>&$myval1));
		$this->assertReference($myval1, $ah->getAttribute('blah'));
		$ah->setAttributes(array('blah2'=>&$myval2));
		$this->assertReference($myval1, $ah->getAttribute('blah'));
		$this->assertReference($myval2, $ah->getAttribute('blah2'));

		$myval3 = 'blah';
		$myval4 = 'blah2';
		$ah->setAttributes(array('blah'=>&$myval3), 'some/other/namespace');
		$this->assertReference($myval3, $ah->getAttribute('blah', 'some/other/namespace'));
		$ah->setAttributes(array('blah2'=>&$myval4), 'some/other/namespace');
		$this->assertReference($myval3, $ah->getAttribute('blah', 'some/other/namespace'));
		$this->assertReference($myval4, $ah->getAttribute('blah2', 'some/other/namespace'));

		$this->assertReference($myval1, $ah->getAttribute('blah'));
		$this->assertReference($myval2, $ah->getAttribute('blah2'));
	}

}

?>
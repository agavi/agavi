<?php

if(!class_exists('AgaviArrayPathDefinition')) {
	include(__DIR__ . '/../../../../src/util/AgaviArrayPathDefinition.class.php');
}

if(!class_exists('AgaviVirtualArrayPath')) {
	include(__DIR__ . '/../../../../src/util/AgaviVirtualArrayPath.class.php');
}

if(!class_exists('AgaviParameterHolder')) {
	include(__DIR__ . '/../../../../src/util/AgaviParameterHolder.class.php');
}

if(!class_exists('AgaviAttributeHolder')) {
	include(__DIR__ . '/../../../../src/util/AgaviAttributeHolder.class.php');
}

class MyAgaviAttributeHolder extends AgaviAttributeHolder {}

class AgaviAttributeHolderTest extends AgaviPhpUnitTestCase
{

	public function __construct($name = NULL, array $data = array(), $dataName = '')
	{
		parent::__construct($name, $data, $dataName);
		// $this->setRunTestInSeparateProcess(true);
	}
	
	public function testGetDefaultNamespace()
	{
		$p = new MyAgaviAttributeHolder(array('baz' => 'boo'));
		$this->assertEquals('org.agavi', $p->getDefaultNamespace());
	}

	public function testClearAttributes()
	{
		$data = array('baz' => 'boo');
		$p = new MyAgaviAttributeHolder();
		$p->setAttributes($data);
		$this->assertEquals($data, $p->getAttributes());
		$p->clearAttributes();
		$this->assertEquals(array(), $p->getAttributes());
	}

	public function testGetAndSetAttributes()
	{
		$data = array('baz' => 'boo');
		$p = new MyAgaviAttributeHolder();
		$p->setAttributes($data);
		$this->assertEquals($data, $p->getAttributes());
	}

	public function testGetAndSetAttributesWithNamespace()
	{
		$data = array('baz' => 'boo');
		$p = new MyAgaviAttributeHolder();
		$p->setAttributes($data, 'mynamespace');
		$this->assertEquals(array(), $p->getAttributes());
		$this->assertEquals($data, $p->getAttributes('mynamespace'));
	}

	public function testSetAttributesWithIntegerIndex()
	{
		$data = array(1 => 'boo');
		$p = new MyAgaviAttributeHolder();
		$p->setAttributes($data);
		$this->assertEquals(array(1 => 'boo'), $p->getAttributes());
	}

	public function testGetAttribute()
	{
		$data = array('baz' => 'boo');
		$p = new MyAgaviAttributeHolder();
		$p->setAttributes($data);
		$this->assertEquals('boo', $p->getAttribute('baz'));
	}

	public function testGetAttributeWithNamespace()
	{
		$data = array('baz' => 'boo');
		$p = new MyAgaviAttributeHolder();
		$p->setAttributes($data, 'mynamespace');
		$this->assertEquals('boo', $p->getAttribute('baz', 'mynamespace'));
		$this->assertEquals(NULL, $p->getAttribute('baz'));
	}

	public function testGetAttributeFromDifferentNamespaces()
	{
		$data = array('baz' => 'boo');
		$data2 = array('ben' => 'jerry');
		$p = new MyAgaviAttributeHolder();
		$p->setAttributes($data, 'mynamespace');
		$p->setAttributes($data2);
		$this->assertEquals('boo', $p->getAttribute('baz', 'mynamespace'));
		$this->assertEquals('jerry', $p->getAttribute('ben'));
	}

	public function testGetAttributeWithNamespaceAndDefault()
	{
		$data = array('baz' => 'boo');
		$p = new MyAgaviAttributeHolder();
		$p->setAttributes($data, 'mynamespace');
		$this->assertEquals('boo', $p->getAttribute('baz', 'mynamespace', 'beh'));
		$this->assertEquals('beh', $p->getAttribute('bla', 'mynamespace', 'beh'));
		$this->assertEquals('beh', $p->getAttribute('baz', 'anothernamespace', 'beh'));
	}

	public function testGetAttributeWithoutNamespaceAndDefault()
	{
		$data = array('baz' => 'boo');
		$p = new MyAgaviAttributeHolder();
		$p->setAttributes($data);
		$this->assertEquals('boo', $p->getAttribute('baz', null, 'beh'));
		$this->assertEquals('beh', $p->getAttribute('bla', null, 'beh'));
	}

	public function testGetAttributeWithIntegerIndex()
	{
		$data = array(2 => 'boo');
		$p = new MyAgaviAttributeHolder();
		$p->setAttributes($data);
		$this->assertEquals('boo', $p->getAttribute("2"));
	}

	public function testHasAttribute()
	{
		$data = array('baz' => 'boo');
		$p = new MyAgaviAttributeHolder();
		$p->setAttributes($data);
		$this->assertTrue($p->hasAttribute('baz'));
		$this->assertFalse($p->hasAttribute('boo'));
		$p->clearAttributes();
		$this->assertEquals(array(), $p->getAttributes());
	}

	public function testHasAttributeWithIntegerIndex()
	{
		$data = array(2 => 'boo');
		$p = new MyAgaviAttributeHolder();
		$p->setAttributes($data);
		//reindexing made my php
		$this->assertTrue($p->hasAttribute(2));
		$this->assertFalse($p->hasAttribute(0));
	}

	public function testHasAttributeWithNamespace()
	{
		$data = array('baz' => 'boo');
		$p = new MyAgaviAttributeHolder();
		$p->setAttributes($data, 'mynamespace');
		$this->assertTrue($p->hasAttribute('baz', 'mynamespace'));
		$this->assertFalse($p->hasAttribute('boo', 'mynamespace'));
		$this->assertFalse($p->hasAttribute('baz'));
	}

	public function testGetAttributeNames()
	{
		$data = array('baz' => 'boo', 'flip' => 'flop');
		$p = new MyAgaviAttributeHolder();
		$p->setAttributes($data);
		$this->assertEquals(array('baz', 'flip'), $p->getAttributeNames());
		$p->clearAttributes();
		$this->assertEquals(array(), $p->getAttributes());
	}

	public function testGetAttributeNamesWithNamespace()
	{
		$data = array('baz' => 'boo', 'flip' => 'flop');
		$p = new MyAgaviAttributeHolder();
		$p->setAttributes($data, 'mynamespace');
		$this->assertEquals(NULL, $p->getAttributeNames());
		$this->assertEquals(array('baz', 'flip'), $p->getAttributeNames('mynamespace'));
		$p->clearAttributes();
		$this->assertEquals(array(), $p->getAttributes());
	}

	public function testGetFlatAttributeNames()
	{
		$data = array('baz' => 'boo', 'flip' => 'flop');
		$p = new MyAgaviAttributeHolder();
		$p->setAttributes($data);
		$this->assertEquals(array('baz', 'flip'), $p->getFlatAttributeNames());
	}

	public function testGetFlatAttributeNamesWithNamespace()
	{
		$data = array('baz' => 'boo', 'flip' => 'flop');
		$p = new MyAgaviAttributeHolder();
		$p->setAttributes($data, 'mynamespace');
		$this->assertEquals(NULL, $p->getFlatAttributeNames());
		$this->assertEquals(array('baz', 'flip'), $p->getFlatAttributeNames('mynamespace'));
	}

	public function testGetAttributes()
	{
		$data = array('baz' => 'boo', 'flip' => 'flop');
		$p = new MyAgaviAttributeHolder();
		$p->setAttributes($data, 'mynamespace');
		$this->assertEquals($data, $p->getAttributes('mynamespace'));
	}

	public function testGetAttributesEmpty()
	{
		$data = array('baz' => 'boo', 'flip' => 'flop');
		$p = new MyAgaviAttributeHolder();
		$p->setAttributes($data, 'mynamespace');
		$this->assertEquals(array(), $p->getAttributes('namespace'));
	}

	public function testGetAttributeNamespace()
	{
		$data = array('baz' => 'boo', 'flip' => 'flop');
		$p = new MyAgaviAttributeHolder();
		$p->setAttributes($data, 'mynamespace');
		$this->assertEquals($data, $p->getAttributeNamespace('mynamespace'));
	}

	public function testGetAttributeNamespaceDefault()
	{
		$data = array('baz' => 'boo', 'flip' => 'flop');
		$p = new MyAgaviAttributeHolder();
		$p->setAttributes($data);
		$this->assertEquals($data, $p->getAttributeNamespace());
	}

	public function testGetAttributeNamespaceEmpty()
	{
		$data = array('baz' => 'boo', 'flip' => 'flop');
		$p = new MyAgaviAttributeHolder();
		$p->setAttributes($data, 'mynamespace');
		$this->assertEquals(NULL, $p->getAttributeNamespace('namespace'));
	}

	public function testGetAttributeNamespaces()
	{
		$p = new MyAgaviAttributeHolder();
		$p->setAttributes(array('flip' => 'flop'), 'one');
		$p->setAttributes(array('bus' => 'car'), 'one');
		$p->setAttributes(array('infi' => 'nity'), 'two');
		$this->assertEquals(array('one', 'two'), $p->getAttributeNamespaces());
	}

	public function testRemoveAttribute()
	{
		$data = array('baz' => 'boo');
		$p = new MyAgaviAttributeHolder();
		$p->setAttributes($data);
		$this->assertEquals('boo', $p->removeAttribute('baz'));
		$this->assertEquals(NULL, $p->removeAttribute('boo'));
		$p->clearAttributes();
		$this->assertEquals(array(), $p->getAttributes());
	}

	public function testRemoveAttributeWithIntegerIndex()
	{
		$data = array('2' => 'boo');
		$p = new MyAgaviAttributeHolder();
		$p->setAttributes($data);
		$this->assertEquals('boo', $p->removeAttribute(2));
	}

	public function testRemoveAttributeWithNamespace()
	{
		$data = array('baz' => 'boo');
		$p = new MyAgaviAttributeHolder();
		$p->setAttributes($data, 'mynamespace');
		$this->assertEquals('boo', $p->removeAttribute('baz', 'mynamespace'));
		$this->assertEquals(NULL, $p->removeAttribute('boo', 'mynamespace'));
		$this->assertEquals(NULL, $p->removeAttribute('baz'));
	}

	public function testSetAttribute()
	{
		$p = new MyAgaviAttributeHolder();
		$p->setAttribute('baz', 'boo');
		$this->assertTrue($p->hasAttribute('baz'));
		$this->assertEquals('boo', $p->getAttribute('baz'));
	}

	public function testSetAttributeWithIntegerIndex()
	{
		$p = new MyAgaviAttributeHolder();
		$p->setAttribute(1, 'boo');
		$this->assertTrue($p->hasAttribute('1'));
		$this->assertEquals('boo', $p->getAttribute(1));
	}

	public function testSetAttributeWithNamespace()
	{
		$p = new MyAgaviAttributeHolder();
		$p->setAttribute('baz', 'boo', 'namespace');
		$this->assertTrue($p->hasAttribute('baz', 'namespace'));
		$this->assertFalse($p->hasAttribute('baz'));
		$this->assertEquals('boo', $p->getAttribute('baz', 'namespace'));
	}

	public function testHasAttributeNamespace()
	{
		$data = array('baz' => 'boo', 'flip' => 'flop');
		$p = new MyAgaviAttributeHolder();
		$p->setAttributes($data, 'mynamespace');
		$this->assertTrue($p->hasAttributeNamespace('mynamespace'));
		$this->assertFalse($p->hasAttributeNamespace('namespace'));
	}

	public function testRemoveAttributeNamespace()
	{
		$data = array('baz' => 'boo', 'flip' => 'flop');
		$p = new MyAgaviAttributeHolder();
		$p->setAttributes($data, 'mynamespace');
		$this->assertEquals($data, $p->removeAttributeNamespace('mynamespace'));
		$this->assertFalse($p->hasAttributeNamespace('mynamespace'));
		$this->assertEquals(NULL, $p->getAttributeNamespace('mynamespace'));
		$this->assertEquals(NULL, $p->removeAttributeNamespace('mynamespace'));
	}

	public function testAppendAttribute()
	{
		$data = array('baz' => 'boo', 'flip' => 'flop');
		$p = new MyAgaviAttributeHolder();
		$p->setAttributes($data);
		$this->assertEquals($data, $p->getAttributes());
		$p->appendAttribute('flip', 'flap');
		$this->assertEquals(array('baz' => 'boo', 'flip' => array('flop', 'flap')), $p->getAttributes());
		$p->appendAttribute('flip', 'flap', 'none');
		$this->assertEquals(array('baz' => 'boo', 'flip' => array('flop', 'flap')), $p->getAttributes());
		$this->assertEquals(array('flip' => array('flap')), $p->getAttributes('none'));
	}

	public function testAppendAttributeWithNamespace()
	{
		$data = array('baz' => 'boo', 'flip' => 'flop');
		$p = new MyAgaviAttributeHolder();
		$p->setAttributes($data, 'mynamespace');
		$this->assertEquals($data, $p->getAttributes('mynamespace'));
		$p->appendAttribute('hakuna', 'matata', 'mynamespace');
		$this->assertEquals(array('baz' => 'boo', 'flip' => 'flop', 'hakuna' => array('matata')), $p->getAttributes('mynamespace'));
		$p->appendAttribute('hakuna', 'tie', 'mynamespace');
		$this->assertEquals(array('baz' => 'boo', 'flip' => 'flop', 'hakuna' => array('matata', 'tie')), $p->getAttributes('mynamespace'));
	}

	public function testSetAttributeByRef()
	{
		$p = new MyAgaviAttributeHolder();
		$baz = 'boo';
		$p->setAttributeByRef('baz', $baz);
		$baz = 'safi';
		$this->assertTrue($p->hasAttribute('baz'));
		$this->assertEquals('safi', $p->getAttribute('baz'));
	}

	public function testSetAttributeByRefWithIntegerIndex()
	{
		$p = new MyAgaviAttributeHolder();
		$baz = 'boo';
		$p->setAttributeByRef(1, $baz);
		$baz = 'safi';
		$this->assertTrue($p->hasAttribute(1));
		$this->assertEquals('safi', $p->getAttribute(1));
	}

	public function testSetAttributeByRefWithNamespace()
	{
		$p = new MyAgaviAttributeHolder();
		$p->setAttribute('baz', 'stg', 'namespace');
		$baz = 'boo';
		$p->setAttributeByRef('baz', $baz, 'namespace');
		$baz = 'safi';
		$this->assertTrue($p->hasAttribute('baz', 'namespace'));
		$this->assertFalse($p->hasAttribute('baz'));
		$this->assertEquals('safi', $p->getAttribute('baz', 'namespace'));
	}

	public function testAppendAttributeByRef()
	{
		$data = array('baz' => 'boo', 'flip' => 'flop');
		$p = new MyAgaviAttributeHolder();
		$p->setAttributes($data);
		$this->assertEquals($data, $p->getAttributes());
		$flap = 'flep';
		$p->appendAttributeByRef('flip', $flap);
		$flap = 'flap';
		$this->assertEquals(array('baz' => 'boo', 'flip' => array('flop', 'flap')), $p->getAttributes());
		$swah = 'mambo';
		$p->appendAttributeByRef('flip', $swah, 'none');
		$this->assertEquals(array('flip' => array('mambo')), $p->getAttributes('none'));
		$swah = 'vipi';
		$this->assertEquals(array('baz' => 'boo', 'flip' => array('flop', 'flap')), $p->getAttributes());
		$this->assertEquals(array('flip' => array('vipi')), $p->getAttributes('none'));
	}

	public function testSetAttributesByRef()
	{
		$p = new MyAgaviAttributeHolder();
		$baz = 'boo';
		$data = array('baz' => &$baz);
		$p->setAttributesByRef($data);
		$this->assertEquals('boo', $p->getAttribute('baz'));
		$baz = 'coo';
		$this->assertEquals('coo', $p->getAttribute('baz'));
		$p->clearAttributes();
		$this->assertEquals(array(), $p->getAttributes());
	}

	public function testSetAttributesByRefWithNamespace()
	{
		$baz = 'boo';
		$data = array('baz' => &$baz);
		$p = new MyAgaviAttributeHolder();
		$p->setAttributesByRef($data, 'mynamespace');
		$this->assertEquals('boo', $p->getAttribute('baz', 'mynamespace'));
		$baz = 'coo';
		$this->assertEquals('coo', $p->getAttribute('baz', 'mynamespace'));
	}

	public function testRemoveReturnsByReference()
	{
		$one = 'two';
		$omg = array('foo' => 'bar', 'bar' => 'baz');
		$foo =& $omg['foo'];
		
		$ph = new MyAgaviAttributeHolder();
		
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

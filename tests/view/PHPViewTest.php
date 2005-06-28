<?php

class SamplePHPView extends PHPView
{
	public function execute() {}
}

class PHPViewTest extends UnitTestCase
{
	private $_v = null;

	public function setUp()
	{
		$this->_v = new SamplePHPView();
	}

	public function testclearAttributes()
	{
		$this->_v->setAttribute('blah', 'blahval');
		$this->_v->setAttribute('blah2', 'blah2val');
		$this->_v->clearAttributes();
		$this->assertEqual(array(), $this->_v->getAttributeNames());
	}

	public function testdecorate()
	{
		$this->assertTrue(0,'Incomplete Test');
	}

	public function testgetAttribute()
	{
		$this->_v->setAttribute('blah', 'blahval');
		$this->assertEqual('blahval', $this->_v->getAttribute('blah'));
		$this->assertNull($this->_v->getAttribute('bunk'));
	}

	public function testgetAttributeNames()
	{
		$this->_v->setAttribute('blah', 'blahval');
		$this->_v->setAttribute('blah2', 'blah2val');
		$this->assertEqual(array('blah', 'blah2'), $this->_v->getAttributeNames());
	}

	public function testgetEngine()
	{
		$this->assertNull($this->_v->getEngine());
	}

	public function testremoveAttribute()
	{
		$this->assertNull($this->_v->removeAttribute('blah'));
		$this->_v->setAttribute('blah', 'blahval');
		$this->assertEqual('blahval', $this->_v->removeAttribute('blah'));
		$this->assertNull($this->_v->removeAttribute('blah'));
	}

	public function testrender()
	{
		$this->assertTrue(0,'Incomplete Test');
	}

	public function testsetAttribute()
	{
		$this->_v->setAttribute('blah', 'blahval');
		$this->assertEqual('blahval', $this->_v->getAttribute('blah'));
	}

	public function testappendAttribute()
	{
		$this->_v->appendAttribute('blah', 'blahval');
		$this->assertEqual(array('blahval'), $this->_v->getAttribute('blah'));
		$this->_v->appendAttribute('blah', 'blahval2');
		$this->assertEqual(array('blahval','blahval2'), $this->_v->getAttribute('blah'));
	}

	public function testsetAttributeByRef()
	{
		$myval = 'blahval';
		$this->_v->setAttributeByRef('blah', $myval);
		$this->assertReference($myval, $this->_v->getAttribute('blah'));
	}

	public function testappendAttributeByRef()
	{
		$myval1 = 'jack';
		$myval2 = 'bill';
		$this->_v->appendAttributeByRef('blah', $myval1);
		$out = $this->_v->getAttribute('blah');
		$this->assertReference($myval1, $out[0]);
		$this->_v->appendAttributeByRef('blah', $myval2);
		$out = $this->_v->getAttribute('blah');
		$this->assertReference($myval1, $out[0]);
		$this->assertReference($myval2, $out[1]);
	}


	public function testsetAttributes()
	{
		$this->_v->setAttributes(array('blah'=>'blahval'));
		$this->assertEqual('blahval', $this->_v->getAttribute('blah'));
		$this->_v->setAttributes(array('blah2'=>'blah2val'));
		$this->assertEqual('blahval', $this->_v->getAttribute('blah'));
		$this->assertEqual('blah2val', $this->_v->getAttribute('blah2'));
	}

	public function testsetAttributesByRef()
	{
		$myval1 = 'blah';
		$myval2 = 'blah2';
		$this->_v->setAttributes(array('blah'=>&$myval1));
		$this->assertReference($myval1, $this->_v->getAttribute('blah'));
		$this->_v->setAttributes(array('blah2'=>&$myval2));
		$this->assertReference($myval1, $this->_v->getAttribute('blah'));
		$this->assertReference($myval2, $this->_v->getAttribute('blah2'));
	}

	public function testhasAttribute()
	{
		$this->assertFalse($this->_v->hasAttribute('blah'));
		$this->_v->setAttribute('blah', 'blahval');
		$this->assertTrue($this->_v->hasAttribute('blah'));
		$this->assertFalse($this->_v->hasAttribute('bunk'));

	}

}
?>

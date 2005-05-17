<?php
require_once('core/AgaviObject.class.php');
require_once('util/ParameterHolder.class.php');
require_once('exception/AgaviException.class.php');
require_once('exception/FactoryException.class.php');
require_once('request/Request.class.php');

class SampleRequest extends Request
{
	public function initialize($context, $parameters = null) {}
	public function shutdown() {}
}

class RequestTest extends UnitTestCase
{
	private $_r = null;

	public function setUp()
	{
		$this->_r = new SampleRequest();
	}

	public function testclearAttributes()
	{
		$this->_r->setAttribute('blah', 'blahval');
		$this->_r->setAttribute('blah2', 'blah2val');
		$this->_r->clearAttributes();
		$this->assertEqual(array(), $this->_r->getAttributeNames());
	}

	public function testextractParameters()
	{
		$this->_r->setParameter('blah', 'blahval');
		$this->assertEqual(array('blah'=>'blahval'), $this->_r->extractParameters(array('blah')));
	}

	public function testgetAttribute()
	{
		$this->_r->setAttribute('blah', 'blahval');
		$this->assertEqual('blahval', $this->_r->getAttribute('blah'));
		$this->assertNull($this->_r->getAttribute('bunk'));
	}

	public function testgetAttributeNames()
	{
		$this->_r->setAttribute('blah', 'blahval');
		$this->_r->setAttribute('blah2', 'blah2val');
		$this->assertEqual(array('blah', 'blah2'), $this->_r->getAttributeNames());
	}

	public function getError()
	{
		$this->_r->setError('blah', 'blahval');
		$this->assertEqual('blahval', $this->_r->getError('blah'));
		$this->assertNull($this->_r->getError('bunk'));
	}

	public function getErrorNames()
	{
		$this->_r->setError('blah', 'blahval');
		$this->_r->setError('blah2', 'blah2val');
		$this->assertEqual(array('blah', 'blah2'), $this->_r->getErrorNames());
	}

	public function getErrors()
	{
		$this->_r->setError('blah', 'blahval');
		$this->_r->setError('blah2', 'blah2val');
		$this->assertEqual(array('blah'=>'blahval', 'blah2'=>'blah2val'), $this->_r->getErrors());
	}

	public function getMethod()
	{
		$this->assertNull($this->_r->getMethod());
		$this->_r->setMethod(Request::GET);
		$this->assertEqual(Request::GET, $this->_r->getMethod());
	}

	public function testhasAttribute()
	{
		$this->assertFalse($this->_r->hasAttribute('blah'));
		$this->_r->setAttribute('blah', 'blahval');
		$this->assertTrue($this->_r->hasAttribute('blah'));
		$this->assertFalse($this->_r->hasAttribute('bunk'));

	}

	public function testhasError()
	{
		$this->assertFalse($this->_r->hasError('blah'));
		$this->_r->setError('blah', 'blahval');
		$this->assertTrue($this->_r->hasError('blah'));
		$this->assertFalse($this->_r->hasError('bunk'));
	}

	public function testhasErrors()
	{
		$this->assertFalse($this->_r->hasErrors());
		$this->_r->setError('blah', 'blahval');
		$this->assertTrue($this->_r->hasErrors());
	}

	public function testnewInstance()
	{
		$this->assertIsA(Request::newInstance('SampleRequest'), 'SampleRequest');
		try {
			Request::newInstance('AgaviException');
			$this->fail('Expected FactoryException not thrown.');
		} catch (FactoryException $e) {
			$this->pass();
		}
	}

	public function testremoveAttribute()
	{
		$this->assertNull($this->_r->removeAttribute('blah'));
		$this->_r->setAttribute('blah', 'blahval');
		$this->assertEqual('blahval', $this->_r->removeAttribute('blah'));
		$this->assertNull($this->_r->removeAttribute('blah'));
	}

	public function testremoveError()
	{
		$this->assertNull($this->_r->removeError('blah'));
		$this->_r->setError('blah', 'blahval');
		$this->assertEqual('blahval', $this->_r->removeError('blah'));
		$this->assertNull($this->_r->removeError('blah'));
	}

	public function testsetAttribute()
	{
		$this->_r->setAttribute('blah', 'blahval');
		$this->assertEqual('blahval', $this->_r->getAttribute('blah'));
	}

	public function setAttributeByRef()
	{
		$myval = 'blahval';
		$this->_r->setAttributeByRef('blah', $myval);
		$this->assertReference($myval, $this->_r->getAttribute('blah'));
	}

	public function setAttributes()
	{
		$this->_r->setAttributes(array('blah'=>'blahval'));
		$this->assertEqual('blahval', $this->_r->getAttribute('blah'));
		$this->_r->setAttributes(array('blah2'=>'blah2val'));
		$this->assertEqual('blahval', $this->_r->getAttribute('blah'));
		$this->assertEqual('blah2val', $this->_r->getAttribute('blah2'));
	}

	public function setAttributesByRef()
	{
		$myval1 = 'blah';
		$myval2 = 'blah2';
		$this->_r->setAttributes(array('blah'=>$myval1));
		$this->assertReference($myval1, $this->_r->getAttribute('blah'));
		$this->_r->setAttributes(array('blah2'=>$myval2));
		$this->assertReference($myval1, $this->_r->getAttribute('blah'));
		$this->assertReference($myval2, $this->_r->getAttribute('blah2'));
	}

	public function testsetError()
	{
		$this->_r->setError('blah', 'blahval');
		$this->assertEqual('blahval', $this->_r->getError('blah'));
	}

	public function testsetErrors()
	{
		$this->_r->setErrors(array('blah'=>'blahval'));
		$this->assertEqual('blahval', $this->_r->getError('blah'));
		$this->_r->setErrors(array('blah2'=>'blah2val'));
		$this->assertEqual('blahval', $this->_r->getError('blah'));
		$this->assertEqual('blah2val', $this->_r->getError('blah2'));
	}

	public function testsetMethod()
	{
		try {
			$this->_r->setMethod(Request::GET);
			$this->pass();
		} catch (AgaviException $e) {
			$this->fail('Unexpected AgaviException thrown: ' . $e->getMessage());
		}
		try {
			$this->_r->setMethod(1012309213);
			$this->fail('Expected AgaviException not thrown');
		} catch (AgaviException $e) {
			$this->pass();
		}
	}

}
?>

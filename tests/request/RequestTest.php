<?php

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
		$p = array(
			'One' => '1',
			'Two' => 'Too',
			'Three' => '3eee',
			'Four' => 'foh');
		$this->_r->setParameters($p);
		$this->assertIdentical(array('One'=>'1'), $this->_r->extractParameters(array('One')));
		$this->assertIdentical(array('Two'=>'Too'), $this->_r->extractParameters(array('Two')));
		$this->assertIdentical(array('Four'=>'foh', 'Five' => null), $this->_r->extractParameters(array('Four','Five')));
		
		// what happens if we forget to contain the args within an array? ;) 
		// - Since it's casted to an array it snags only the first arg
		$this->assertIdentical(array('heh'=>null), $this->_r->extractParameters('heh'));
		$this->assertIdentical(array('heh'=>null), $this->_r->extractParameters('heh', 'hah'));
		$this->assertIdentical(array(), $this->_r->extractParameters(array()));
		$this->assertIdentical(array('One'=>'1'), $this->_r->extractParameters(array('One')));

		// Test that we're working with references
		$ref1	= $this->_r->extractParameters(array('One'));
		$ref2 = $this->_r->extractParameters(array('One'));
		$this->assertReference($ref1['One'], $ref2['One']);
		$this->assertIdentical($ref1['One'], $ref2['One']);
		
		$ref1['One'] = 'Wun';
		$this->assertReference($ref1['One'], $ref2['One']);
		$this->assertIdentical($ref1['One'], $ref2['One']);
		
		$this->_r->setParameter('One', 'AndOnly');
		$this->assertReference($ref1['One'], $ref2['One']);
		$this->assertIdentical($ref1['One'], $ref2['One']);
		
		$ref3 = $this->_r->extractParameters(array('One'));
		$this->assertIdentical($ref1['One'], $ref3['One']);
		$this->assertReference($ref1['One'], $ref3['One']);
		$this->assertIdentical($ref2['One'], $ref3['One']);
		$this->assertIdentical('AndOnly', $ref3['One']);
		$this->assertIdentical('AndOnly', $ref1['One']);
		
	}

	public function testgetAttribute()
	{
		$this->_r->setAttribute('blah', 'blahval');
		$this->assertEqual('blahval', $this->_r->getAttribute('blah'));
		$this->assertNull($this->_r->getAttribute('bunk'));

		$this->_r->setAttribute('blah', 'otherblah', 'some/other/namespace');
		$this->assertEqual('otherblah', $this->_r->getAttribute('blah', 'some/other/namespace'));
		$this->assertNull($this->_r->getAttribute('bunk', 'some/other/namespace'));

		$this->assertEqual('blahval', $this->_r->getAttribute('blah'));
	}

	public function testgetAttributeNames()
	{
		$this->_r->setAttribute('blah', 'blahval');
		$this->_r->setAttribute('blah2', 'blah2val');
		$this->assertEqual(array('blah', 'blah2'), $this->_r->getAttributeNames());

		$this->_r->setAttribute('blah', 'otherblah', 'some/other/namespace');
		$this->assertEqual(array('blah'), $this->_r->getAttributeNames('some/other/namespace'));

		$this->assertEqual(array('blah', 'blah2'), $this->_r->getAttributeNames());

		$this->assertNull($this->_r->getAttributeNames('/bunk/namespace'));
	}

	public function testgetAttributeNamespace()
	{
		$this->_r->setAttribute('blah', 'blahval');
		$this->_r->setAttribute('blah2', 'blah2val');
		$this->assertEqual(array('blah'=>'blahval', 'blah2'=>'blah2val'), $this->_r->getAttributeNamespace());

		$this->_r->setAttribute('blah', 'otherblah', 'some/other/namespace');
		$this->assertEqual(array('blah'=>'otherblah'), $this->_r->getAttributeNamespace('some/other/namespace'));

		$this->assertEqual(array('blah'=>'blahval', 'blah2'=>'blah2val'), $this->_r->getAttributeNamespace());

		$this->assertNull($this->_r->getAttributeNamespace('/bunk/namespace'));
	}

	public function testgetAttributeNamespaces()
	{
		$this->assertEqual(array(), $this->_r->getAttributeNamespaces());
		$this->_r->setAttribute('blah', 'blahval');
		$this->assertEqual(array(AG_REQUEST_NAMESPACE), $this->_r->getAttributeNamespaces());
		$this->_r->setAttribute('blah', 'blahval', 'some/other/namespace');
		$this->assertEqual(array(AG_REQUEST_NAMESPACE, 'some/other/namespace'), $this->_r->getAttributeNamespaces());
	}

	public function testgetAttributes()
	{
		$this->_r->setAttribute('blah', 'blahval');
		$this->_r->setAttribute('blah2', 'blah2val');
		$this->assertEqual(array('blah'=>'blahval', 'blah2'=>'blah2val'), $this->_r->getAttributes());

		$this->_r->setAttribute('blah', 'otherblah', 'some/other/namespace');
		$this->assertEqual(array('blah'=>'otherblah'), $this->_r->getAttributes('some/other/namespace'));

		$this->assertEqual(array('blah'=>'blahval', 'blah2'=>'blah2val'), $this->_r->getAttributes());

		$this->assertNull($this->_r->getAttributes('/bunk/namespace'));
	}

	public function testgetError()
	{
		$this->_r->setError('blah', 'blahval');
		$this->assertEqual('blahval', $this->_r->getError('blah'));
		$this->assertNull($this->_r->getError('bunk'));
	}

	public function testgetErrorNames()
	{
		$this->_r->setError('blah', 'blahval');
		$this->_r->setError('blah2', 'blah2val');
		$this->assertEqual(array('blah', 'blah2'), $this->_r->getErrorNames());
	}

	public function testgetErrors()
	{
		$this->_r->setError('blah', 'blahval');
		$this->_r->setError('blah2', 'blah2val');
		$this->assertEqual(array('blah'=>'blahval', 'blah2'=>'blah2val'), $this->_r->getErrors());
	}

	public function testgetMethod()
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

		$this->assertFalse($this->_r->hasAttribute('blah', 'some/other/namespace'));
		$this->_r->setAttribute('blah', 'otherblah', 'some/other/namespace');
		$this->assertTrue($this->_r->hasAttribute('blah', 'some/other/namespace'));
		$this->assertFalse($this->_r->hasAttribute('bunk', 'some/other/namespace'));
		$this->_r->removeAttribute('blah', 'some/other/namespace');

		$this->assertTrue($this->_r->hasAttribute('blah'));
	}

	public function testhasAttributeNamespace()
	{
		$this->assertFalse($this->_r->hasAttributeNamespace(AG_REQUEST_NAMESPACE));
		$this->_r->setAttribute('blah', 'blahval');
		$this->assertTrue($this->_r->hasAttributeNamespace(AG_REQUEST_NAMESPACE));
		$this->assertFalse($this->_r->hasAttributeNamespace('some/other/namespace'));
		$this->_r->setAttribute('blah', 'blahval', 'some/other/namespace');
		$this->assertTrue($this->_r->hasAttributeNamespace('some/other/namespace'));
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

	public function testremoveError()
	{
		$this->assertNull($this->_r->removeError('blah'));
		$this->_r->setError('blah', 'blahval');
		$this->assertEqual('blahval', $this->_r->removeError('blah'));
		$this->assertNull($this->_r->removeError('blah'));
	}

	public function testremoveAttribute()
	{
		$this->assertNull($this->_r->removeAttribute('blah'));
		$this->_r->setAttribute('blah', 'blahval');
		$this->assertEqual('blahval', $this->_r->removeAttribute('blah'));
		$this->assertNull($this->_r->removeAttribute('blah'));
		$this->_r->setAttribute('blah', 'blahval');

		$this->assertNull($this->_r->removeAttribute('blah', 'some/other/namespace'));
		$this->_r->setAttribute('blah', 'otherblah', 'some/other/namespace');
		$this->assertEqual('otherblah', $this->_r->removeAttribute('blah', 'some/other/namespace'));
		$this->assertNull($this->_r->removeAttribute('blah', 'some/other/namespace'));

		$this->assertEqual('blahval', $this->_r->removeAttribute('blah'));
	}

	public function testremoveAttributeNamespace()
	{
		$this->_r->setAttribute('blah', 'blahval');
		$this->_r->removeAttributeNamespace(AG_REQUEST_NAMESPACE);
		$this->assertFalse($this->_r->hasAttributeNamespace(AG_REQUEST_NAMESPACE));
	}

	public function testsetAttribute()
	{
		$this->_r->setAttribute('blah', 'blahval');
		$this->assertEqual('blahval', $this->_r->getAttribute('blah'));

		$this->_r->setAttribute('blah', 'otherblah', 'some/other/namespace');
		$this->assertEqual('otherblah', $this->_r->getAttribute('blah', 'some/other/namespace'));

		$this->assertEqual('blahval', $this->_r->getAttribute('blah'));
	}

	public function testappendAttribute()
	{
		$this->_r->appendAttribute('blah', 'blahval');
		$this->assertEqual(array('blahval'), $this->_r->getAttribute('blah'));
		$this->_r->appendAttribute('blah', 'blahval2');
		$this->assertEqual(array('blahval','blahval2'), $this->_r->getAttribute('blah'));

		$this->_r->appendAttribute('blah', 'otherblah', 'some/other/namespace');
		$this->assertEqual(array('otherblah'), $this->_r->getAttribute('blah', 'some/other/namespace'));
		$this->_r->appendAttribute('blah', 'otherblah2', 'some/other/namespace');
		$this->assertEqual(array('otherblah', 'otherblah2'), $this->_r->getAttribute('blah', 'some/other/namespace'));

		$this->assertEqual(array('blahval','blahval2'), $this->_r->getAttribute('blah'));
	}

	public function testsetAttributeByRef()
	{
		$myval = 'blahval';
		$this->_r->setAttributeByRef('blah', $myval);
		$this->assertReference($myval, $this->_r->getAttribute('blah'));

		$myval2 = 'otherblah';
		$this->_r->setAttributeByRef('blah', $myval2, 'some/other/namespace');
		$this->assertReference($myval2, $this->_r->getAttribute('blah', 'some/other/namespace'));

		$this->assertReference($myval, $this->_r->getAttribute('blah'));
	}

	public function testappendAttributeByRef()
	{
		$myval1 = 'jack';
		$myval2 = 'bill';
		$this->_r->appendAttributeByRef('blah', $myval1);
		$out = $this->_r->getAttribute('blah');
		$this->assertReference($myval1, $out[0]);
		$this->_r->appendAttributeByRef('blah', $myval2);
		$out = $this->_r->getAttribute('blah');
		$this->assertReference($myval1, $out[0]);
		$this->assertReference($myval2, $out[1]);

		$myval3 = 'jill';
		$myval4 = 'jane';
		$this->_r->appendAttributeByRef('blah', $myval3, 'some/other/namespace');
		$out = $this->_r->getAttribute('blah', 'some/other/namespace');
		$this->assertReference($myval3, $out[0]);
		$this->_r->appendAttributeByRef('blah', $myval4, 'some/other/namespace');
		$out = $this->_r->getAttribute('blah', 'some/other/namespace');
		$this->assertReference($myval3, $out[0]);
		$this->assertReference($myval4, $out[1]);

		$out = $this->_r->getAttribute('blah');
		$this->assertReference($myval1, $out[0]);
	}


	public function testsetAttributes()
	{
		$this->_r->setAttributes(array('blah'=>'blahval'));
		$this->assertEqual('blahval', $this->_r->getAttribute('blah'));
		$this->_r->setAttributes(array('blah2'=>'blah2val'));
		$this->assertEqual('blahval', $this->_r->getAttribute('blah'));
		$this->assertEqual('blah2val', $this->_r->getAttribute('blah2'));

		$this->_r->setAttributes(array('blah'=>'otherblah'), 'some/other/namespace');
		$this->assertEqual('otherblah', $this->_r->getAttribute('blah', 'some/other/namespace'));
		$this->_r->setAttributes(array('blah2'=>'otherblah2'), 'some/other/namespace');
		$this->assertEqual('otherblah', $this->_r->getAttribute('blah', 'some/other/namespace'));
		$this->assertEqual('otherblah2', $this->_r->getAttribute('blah2', 'some/other/namespace'));

		$this->assertEqual('blahval', $this->_r->getAttribute('blah'));
		$this->assertEqual('blah2val', $this->_r->getAttribute('blah2'));
	}

	public function testsetAttributesByRef()
	{
		$myval1 = 'blah';
		$myval2 = 'blah2';
		$this->_r->setAttributes(array('blah'=>&$myval1));
		$this->assertReference($myval1, $this->_r->getAttribute('blah'));
		$this->_r->setAttributes(array('blah2'=>&$myval2));
		$this->assertReference($myval1, $this->_r->getAttribute('blah'));
		$this->assertReference($myval2, $this->_r->getAttribute('blah2'));

		$myval3 = 'blah';
		$myval4 = 'blah2';
		$this->_r->setAttributes(array('blah'=>&$myval3), 'some/other/namespace');
		$this->assertReference($myval3, $this->_r->getAttribute('blah', 'some/other/namespace'));
		$this->_r->setAttributes(array('blah2'=>&$myval4), 'some/other/namespace');
		$this->assertReference($myval3, $this->_r->getAttribute('blah', 'some/other/namespace'));
		$this->assertReference($myval4, $this->_r->getAttribute('blah2', 'some/other/namespace'));

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
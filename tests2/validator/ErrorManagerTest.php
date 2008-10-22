<?php

class MyErrorManager extends AgaviErrorManager
{
	public function setValidatorArray($array) { $this->ValidatorArray = $array; }
	public function setInputArray($array) { $this->InputArray = $array; }
	public function setResult($result) { $this->Result = $result; }
	public function setMessage($result) { $this->ErrorMessage = $result; }
}

class ErrorManagerTest extends AgaviTestCase
{
	public function testclear()
	{
		$em = new MyErrorManager;
		$em->setValidatorArray(array(1));
		$em->setInputArray(array(2));
		$em->setResult(AgaviValidator::CRITICAL);
		$em->setMessage('foo bar');
		
		$em->clear();
		$this->assertEquals($em->getErrorArrayByValidator(), array());
		$this->assertEquals($em->getErrorArrayByInput(), array());
		$this->assertEquals($em->getResult(), AgaviValidator::SUCCESS);
		$this->assertEquals($em->getErrorMessage(), '');
	}
	
	public function testsubmitError()
	{
		$em = new MyErrorManager();
		$error = 'bar';
		$em->submitError('foo', $error, array('test', 'test2'), AgaviValidator::ERROR, '', true);
		
		$this->assertEquals($em->getErrorMessage(), '');
		$this->assertEquals($em->getResult(), AgaviValidator::ERROR);
		$this->assertEquals($em->getErrorArrayByValidator(), array('foo' => array('error' => &$error, 'fields' => array('test', 'test2'))));
		$this->assertEquals($em->getErrorArrayByInput(), array(
			'test' => array('message' => '', 'validators' => array('foo' => &$error)),
			'test2' => array('message' => '', 'validators' => array('foo' => &$error))
		));

		$error2 = 'foobar';
		$em->submitError('foo2', $error2, array('test2', 'test3'), AgaviValidator::CRITICAL);
		
		$this->assertEquals($em->getErrorMessage(), $error2);
		$this->assertEquals($em->getResult(), AgaviValidator::CRITICAL);
		$this->assertEquals($em->getErrorArrayByValidator(), array(
			'foo' => array('error' => &$error, 'fields' => array('test', 'test2')),
			'foo2' => array('error' => &$error2, 'fields' => array('test2', 'test3'))
		));
		$this->assertEquals($em->getErrorArrayByInput(), array(
			'test' => array('message' => '', 'validators' => array('foo' => &$error)),
			'test2' => array('message' => &$error2, 'validators' => array('foo' => &$error, 'foo2' => &$error2)),
			'test3' => array('message' => &$error2, 'validators' => array('foo2' => &$error2))
		));

		$error3 = 'foobarfoo';
		$em->submitError('foo3', $error3, array('test3', '/test4'), AgaviValidator::CRITICAL, '/test5');
		
		$this->assertEquals($em->getErrorMessage(), $error2);
		$this->assertEquals($em->getResult(), AgaviValidator::CRITICAL);
		$this->assertEquals($em->getErrorArrayByValidator(), array(
			'foo' => array('error' => &$error, 'fields' => array('test', 'test2')),
			'foo2' => array('error' => &$error2, 'fields' => array('test2', 'test3')),
			'/test5/foo3' => array('error' => &$error3, 'fields' => array('/test5/test3', '/test4')),
		));
		$this->assertEquals($em->getErrorArrayByInput(), array(
			'test' => array('message' => '', 'validators' => array('foo' => &$error)),
			'test2' => array('message' => &$error2, 'validators' => array('foo' => &$error, 'foo2' => &$error2)),
			'test3' => array('message' => &$error2, 'validators' => array('foo2' => &$error2)),
			'/test5/test3' => array('message' => &$error3, 'validators' => array('/test5/foo3' => &$error3)),
			'/test4' => array('message' => &$error3, 'validators' => array('/test5/foo3' => &$error3))
		));
	}
	
	public function testgetErrorArrayByValidator()
	{
		$em = new MyErrorManager;
		
		$this->assertEquals($em->getErrorArrayByValidator(), array());
		$em->setValidatorArray(array(1));
		$this->assertEquals($em->getErrorArrayByValidator(), array(1));
	}
	
	public function testgetErrorArrayByInput()
	{
		$em = new MyErrorManager;
		
		$this->assertEquals($em->getErrorArrayByInput(), array());
		$em->setInputArray(array(1));	
		$this->assertEquals($em->getErrorArrayByInput(), array(1));
	}
	
	public function testgetResult()
	{
		$em = new MyErrorManager;
		
		$this->assertEquals($em->getResult(), AgaviValidator::SUCCESS);
		$em->setResult(AgaviValidator::CRITICAL);
		$this->assertEquals($em->getResult(), AgaviValidator::CRITICAL);
	}
	
	public function testgetErrorMessage()
	{
		$em = new MyErrorManager;
		
		$this->assertEquals($em->getErrorMessage(), '');
		$em->setMessage('foobar');
		$this->assertEquals($em->getErrorMessage(), 'foobar');
	}
}
?>

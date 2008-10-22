<?php

class SampleValidator extends AgaviValidator
{
	public $bases = array();
	public $val_result = true;

	protected function validate() { return $this->val_result; }
	
	public function getBase() { return $this->CurBase->__toString(); }
	public function getParent() {return $this->ParentContainer; }
	public function getData2($parameter) { return $this->getData($parameter); }
	public function getData3() { return $this->getData(); }
	public function throwError2($index = 'error', $ignoreAsMessage = false, $affectedFields = null, $backupError = null)
	{
		$this->throwError($index, $ignoreAsMessage, $affectedFields, $backupError);
	}
	public function getAffectedFields2($fields) { $this->AffectedFieldNames = $fields; return $this->getAffectedFields(); }
	public function export2($value) { $this->export($value); }
	
	protected function validateInBase($base) { array_push($this->bases, $base); return parent::validateInBase($base); }
	public function validateInBase2($base) { return $this->validateInBase($base); }
}

class SampleValidator2 extends AgaviValidator
{
	public $base = '';
	public $val_result = 0;
	
	protected function validate() { return true; }
	protected function validateInBase($base) { $this->base = $base; return $this->val_result; }
}

class ValidatorTest extends AgaviTestCase
{
	private $_vm = null;
					
	public function setUp()
	{
		$this->_vm = AgaviContext::getInstance()->getValidationManager();
	}

	public function tearDown()
	{
		$this->_vm = null;
	}

	public function testconstruct()
	{
		$this->_vm->setParameter('base', '/test');
		$validator = new SampleValidator($this->_vm, array());
		$this->assertEquals($validator->getBase(), '/test');
		$this->assertEquals($validator->getParameter('depends'), array());
		$this->assertEquals($validator->getParameter('provides'), array());
		$this->assertEquals($validator->getParent(), $this->_vm);
	}
	
	public function testcontstructWithParameters()
	{
		$parameters = array(
			'depends'	=> 'test1,test2,test3',
			'provides'	=> 'foo,bar',
		);
		$validator = new SampleValidator($this->_vm, , array('test'), array(), $parameters);
		$this->assertEquals($validator->getParameter('depends'), array('test1', 'test2', 'test3'));
		$this->assertEquals($validator->getParameter('provides'), array('foo', 'bar'));
		$this->assertEquals($validator->getArgument(), 'test');
	}
	
	public function testgetData()
	{
		$this->_vm->setParameter('base', '/');
		$validator = new SampleValidator($this->_vm, array('param' => 'test/0'));
		$this->_vm->getRequest()->setParameter('test', array('foo'));
		$this->assertEquals($validator->getData2('param'), 'foo');
		$this->assertEquals($validator->getData3(), 'foo');
	}
	
	public function testthrowError()
	{
		$this->_vm->setParameter('base', '/foo');
		$error1 = 'error1';
		$error2 = 'error2';
		$parameters = array(
			'param' => 'bar',
			'affects' => 'foo,bar2',
			'error' => &$error1,
			'error2' => &$error2,
			'severity' => 'error',
			'name' => 'foobar'
		);
		$validator = new SampleValidator($this->_vm, $parameters);
		$m = $this->_vm->getErrorManager();
		$validator->throwError2();
		$this->assertEquals($m->getErrorArrayByValidator(), array('/foo/foobar' => array('error' => &$error1, 'fields' => array('/foo/foo', '/foo/bar2', '/foo/bar'))));
		$this->assertEquals($m->getErrorArrayByInput(), array(
			'/foo/bar' => array('message' => &$error1, 'validators' => array('/foo/foobar' => &$error1)),
			'/foo/bar2' => array('message' => &$error1, 'validators' => array('/foo/foobar' => &$error1)),
			'/foo/foo' => array('message' => &$error1, 'validators' => array('/foo/foobar' => &$error1))
		));
		$this->assertEquals($m->getResult(), AgaviValidator::ERROR);
		$this->assertEquals($m->getErrorMessage(), 'error1');
		
		$validator->throwError2('error2');
		$err = $m->getErrorArrayByValidator();
		$this->assertEquals($err['/foo/foobar']['error'], $error2);
		
		$validator->throwError2('error3');
		$err = $m->getErrorArrayByValidator();
		$this->assertEquals($err['/foo/foobar']['error'], $error1);
		
		$validator->setParameter('error', null);
		$validator->throwError2('error3', false, null, 'backupError');
		$err = $m->getErrorArrayByValidator();
		$this->assertEquals($err['/foo/foobar']['error'], 'backupError');
		
		$validator->throwError2('error2', false, array('test'));
		$err = $m->getErrorArrayByValidator();
		$this->assertEquals($err['/foo/foobar']['fields'], array('/foo/test'));
		
		$validator->throwError2('error2', false, array('/test'));
		$err = $m->getErrorArrayByValidator();
		$this->assertEquals($err['/foo/foobar']['fields'], array('/test'));
		
	}
	
	public function testgetAffectedFields()
	{
		$validator = new SampleValidator($this->_vm);
		$validator->setParameter('affects', 'foo,bar');
		$validator->setParameter('param', 'foobar');
		$validator->setParameter('testparam', 'test');
		
		$this->assertEquals($validator->getAffectedFields2(array()), array('foo', 'bar'));
		$this->assertEquals($validator->getAffectedFields2(array('param', 'testparam')), array('foo', 'bar', 'foobar', 'test'));
	}
	
	public function testexport()
	{
		$validator = new SampleValidator($this->_vm);
		$validator->setParameter('base', '/foo');
		$validator->setParameter('export', 'bar');
		$validator->export2('foobar');
		$ar = $this->_vm->getRequest()->getParameters();
		
		$this->assertEquals($ar['foo']['bar'], 'foobar');
	}
	
	public function testvalidateInBase()
	{
		$this->_vm->setParameter('base', '');
		$validator = new SampleValidator($this->_vm);
		$validator->setParameter('severity', 'error');
		$validator->bases = array();
		$validator->val_result = true;
		
		$ret = $validator->validateInBase2('foo/bar/foobar');
		$this->assertEquals($ret, AgaviValidator::SUCCESS);
		$this->assertEquals($validator->bases, array('foo/bar/foobar', 'bar/foobar', 'foobar', ''));
		
		$req = $this->_vm->getRequest();
		$req->setParameter('foo', array('foo' => array('test' => 1), 'bar' => array('test' => 2), 'foobar' => array('test' => 3)));
		
		$validator->bases = array();
		$ret = $validator->validateInBase2('foo/*/test');
		$this->assertEquals($ret, AgaviValidator::SUCCESS);
		$this->assertEquals($validator->bases, array('foo/*/test', '*/test', 'test', '', 'test', '', 'test', ''));
		
		$validator->bases = array();
		$validator->val_result = false;
		$ret = $validator->validateInBase2('foo/bar/foobar');
		$this->assertEquals($ret, AgaviValidator::ERROR);
		$this->assertEquals($validator->bases, array('foo/bar/foobar', 'bar/foobar', 'foobar', ''));
		
		$validator->setParameter('severity', 'critical');
		$validator->bases = array();
		$validator->val_result = false;
		$ret = $validator->validateInBase2('foo/*/test');
		$this->assertEquals($ret, AgaviValidator::CRITICAL);
		$this->assertEquals($validator->bases, array('foo/*/test', '*/test', 'test', ''));
	}
	
	public function testexecute()
	{
		$validator = new SampleValidator2($this->_vm);
		$validator->setParameter('base', '/foo/bar');
		$validator->base = '';
		$validator->val_result = AgaviValidator::SUCCESS;
		
		$this->assertEquals($validator->execute(), AgaviValidator::SUCCESS);
		$this->assertEquals($validator->base, '/foo/bar');
	}
	
	public function testmapErrorCode()
	{
		$this->assertEquals(AgaviValidator::mapErrorCode('success'), AgaviValidator::SUCCESS);
		$this->assertEquals(AgaviValidator::mapErrorCode('none'), AgaviValidator::NONE);
		$this->assertEquals(AgaviValidator::mapErrorCode('error'), AgaviValidator::ERROR);
		$this->assertEquals(AgaviValidator::mapErrorCode('critical'), AgaviValidator::CRITICAL);
		$this->assertEquals(AgaviValidator::mapErrorCode('sUcCEsS'), AgaviValidator::SUCCESS);
		
		try {
			AgaviValidator::mapErrorCode('foo');
			$this->fail();
		} catch(AgaviValidatorException $e) {
			$this->assertEquals($e->getMessage(), 'unknown error code: foo');
		}
	}
}

?>
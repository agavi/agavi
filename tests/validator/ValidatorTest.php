<?php
require_once dirname(__FILE__) . '/../mockContext.php';
require_once('validator/Validator.class.php');

class SampleValidator extends Validator
{
	public function execute(&$value, &$error) { return true; }
}

class ValidatorTest extends UnitTestCase
{
	private $_validator = null,
					$_contreoller = null,
					$_context = null;
					
	public function setUp()
	{
		$this->_controller = new MockController($this);
		$this->_controller->dispatch();
		$this->_context = $this->_controller->getContext();
		
		$this->_validator = new SampleValidator();
	}

	public function tearDown()
	{
		$this->_validator = null;
		$this->_controller = null;
		$this->_context->cleanSlate();
		$this->_context = null;
	}

	public function testinitialize()
	{
		$this->assertNull($this->_validator->getContext());
		$this->assertTrue($this->_validator->initialize($this->_context));
		$this->assertTrue($this->_validator->getContext() instanceof MockContext );
	}

	public function testinitializeWithParameters()
	{
		$this->assertTrue($this->_validator->initialize($this->_context, array('foo' => 'bar')));
		$this->assertIdentical('bar', $this->_validator->getParameter('foo'));
	}

	public function testgetContext()
	{
		$this->_validator->initialize($this->_context);
		$c = $this->_validator->getContext();
		$this->assertReference($this->_context, $c);
	}

	public function testExecute()
	{
		$test = 'test';
		$msg = 'error message';
		$this->assertTrue($this->_validator->execute($test, $msg));
	}
}

?>

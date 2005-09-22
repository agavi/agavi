<?php
require_once dirname(__FILE__) . '/../test_environment.php';

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
		$this->_context = Context::getInstance();
		$this->_controller = $this->_context->getController();
		
		$this->_validator = new SampleValidator();
	}

	public function tearDown()
	{
		$this->_validator = null;
		$this->_controller = null;
		$this->_context = null;
	}

	public function testinitialize()
	{
		$this->assertNull($this->_validator->getContext());
		$this->assertTrue($this->_validator->initialize($this->_context));
		$this->assertTrue($this->_validator->getContext() instanceof Context );
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

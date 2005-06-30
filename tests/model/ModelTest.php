<?php
require_once dirname(__FILE__) . '/../mockContext.php';

class ModelSampleModel extends Model {}

class SingletonModelSampleModel extends SingletonModel
{
	public $foo = null;
	public function setFoo($value)
	{
		$this->foo = $value;
	}
	public function getFoo()
	{
		return $this->foo;
	}
}

class TestModel extends UnitTestCase
{
	private $_model = null,
					$_controller = null,
					$_context = null;

	public function setUp()
	{
		$this->_controller = new MockController($this);
		$this->_controller->dispatch();
		$this->_context = $this->_controller->getContext();
		$this->_model = new ModelSampleModel();
	}


	public function tearDown()
	{
		$this->_controller = null;
		$this->_model = null;
		$this->_context->cleanSlate();
		$this->_context = null;
	}

	public function testinitialize()
	{
		$this->assertTrue($this->_model->initialize($this->_context));
	}

	public function testgetContext()
	{
		$this->_model->initialize($this->_context);
		$mc = $this->_model->getContext();
		$this->assertReference($this->_context, $mc);
	}

	public function testsingleton()
	{
		$firstSingleton = SingletonModelSampleModel::getInstance('SingletonModelSampleModel');
		$firstSingleton->setFoo('bar');
		$secondSingleton = SingletonModelSampleModel::getInstance('SingletonModelSampleModel');
		$this->assertEqual($firstSingleton->getFoo(), $secondSingleton->getFoo());
	}
}
?>

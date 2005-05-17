<?php
require_once('core/AgaviObject.class.php');
require_once('model/Model.class.php');

class ModelSampleModel extends Model {}

class ModelContext extends Context {}

class TestModel extends UnitTestCase
{
	private $_m = null;

	public function setUp()
	{
		$this->_m = new ModelSampleModel();
	}

	public function testinitialize()
	{
		$context = new ModelContext();
		$this->assertTrue($this->_m->initialize($context));
	}

	public function testgetContext()
	{
		$context = new ModelContext();
		$this->_m->initialize($context);
		$this->assertReference($context, $this->_m->getContext());
	}
}
?>

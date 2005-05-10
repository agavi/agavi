<?php
require_once('core/AgaviObject.class.php');
require_once('model/Model.class.php');

class SampleModel extends Model {}

class Context extends AgaviObject {}

class TestModel extends UnitTestCase
{
	private $_m = null;

	public function setUp()
	{
		$this->_m = new SampleModel();
	}

	public function testinitialize()
	{
		$context = new Context();
		$this->assertTrue($this->_m->initialize($context));
	}

	public function testgetContext()
	{
		$context = new Context();
		$this->_m->initialize($context);
		$this->assertReference($context, $this->_m->getContext());
	}
}
?>
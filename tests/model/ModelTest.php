<?php
require_once('core/AgaviObject.class.php');
require_once('model/Model.class.php');

class ModelSampleModel extends Model {}
class ModelTestSampleController extends Controller 
{
	public function dispatch() {}
}

class TestModel extends UnitTestCase
{
	private $_m = null,
					$_context = null;

	public function setUp()
	{
		$c = new ModelTestSampleController();
		$this->_context = Context::getInstance($c);
		$this->_m = new ModelSampleModel();
	}

	public function testinitialize()
	{
		$this->assertTrue($this->_m->initialize($this->_context));
	}

	public function testgetContext()
	{
		$this->_m->initialize($this->_context);
		$mc = $this->_m->getContext();
		$this->assertReference($this->_context, $mc);
	}
}
?>

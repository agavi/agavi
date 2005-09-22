<?php
require_once dirname(__FILE__) . '/../test_environment.php';

class SampleModel extends Model {}

class TestModel extends UnitTestCase
{

	public function testinitialize()
	{
		$context = Context::getInstance();
		$model = new SampleModel();
		$this->assertTrue($model->initialize($context));
	}

	public function testgetContext()
	{
		$context = Context::getInstance();
		$model = new SampleModel();
		$model->initialize($context);
		$mContext = $model->getContext();
		$this->assertReference($mContext, $context);
	}

}
?>

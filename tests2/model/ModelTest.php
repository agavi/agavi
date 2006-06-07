<?php

class SampleModel extends AgaviModel {}

class TestModel extends AgaviTestCase
{

	public function testinitialize()
	{
		$context = AgaviContext::getInstance();
		$model = new SampleModel();
		$this->assertTrue($model->initialize($context));
	}

	public function testgetContext()
	{
		$context = AgaviContext::getInstance();
		$model = new SampleModel();
		$model->initialize($context);
		$mContext = $model->getContext();
		$this->assertReference($mContext, $context);
	}

}
?>
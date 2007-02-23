<?php

class SampleModel extends AgaviModel {}

class ModelTest extends AgaviTestCase
{
	public function testInitialize()
	{
		$context = AgaviContext::getInstance('test');
		$model = new SampleModel();
		$model->initialize($context);
	}

	public function testGetContext()
	{
		$context = AgaviContext::getInstance('test');
		$model = new SampleModel();
		$model->initialize($context);
		$mContext = $model->getContext();
		$this->assertReference($mContext, $context);
	}

}
?>
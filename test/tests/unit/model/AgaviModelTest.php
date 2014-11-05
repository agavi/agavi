<?php

class SampleModel extends AgaviModel {}

class AgaviModelTest extends AgaviUnitTestCase
{
	public function testGetContext()
	{
		$context = $this->getContext();
		$model = new SampleModel();
		$model->initialize($context);
		$mContext = $model->getContext();
		$this->assertSame($mContext, $context);
	}

}
?>
<?php

class SampleLayout extends AgaviLoggerLayout
{
	public function format(AgaviLoggerMessage $message)
	{
	}
}

class LayoutTest extends AgaviTestCase
{
	public function testGetSetLayout()
	{
		$layout = new SampleLayout;
		$this->assertNull($layout->getLayout());
		$layout->setLayout('something');
		$this->assertEquals('something', $layout->getLayout());
	}
}

?>
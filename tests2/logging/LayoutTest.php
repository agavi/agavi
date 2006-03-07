<?php

class SampleLayout extends AgaviLayout
{
	public function & format($message){}
}

class LayoutTest extends AgaviTestCase
{
	public function testgetsetLayout()
	{
		$layout = new SampleLayout;
		$this->assertNull($layout->getLayout());
		$layout->setLayout('something');
		$this->assertEquals('something', $layout->getLayout());
	}
}

?>
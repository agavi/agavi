<?php
require_once dirname(__FILE__) . '/../test_environment.php';

class SampleLayout extends Layout
{
	public function & format($message){}
}

class LayoutTest extends UnitTestCase
{
	public function testgetsetLayout()
	{
		$layout = new SampleLayout;
		$this->assertNull($layout->getLayout());
		$layout->setLayout('something');
		$this->assertEqual('something', $layout->getLayout());
	}
}

?>
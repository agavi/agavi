<?php

class SampleAppender extends AgaviAppender
{
	public function initialize($params) {}
	public function shutdown() {}
	public function write($message) {}
}

class AppenderTest extends AgaviTestCase
{

	public function testgetsetLayout()
	{
		$a = new SampleAppender();
		$this->assertNull($a->getLayout());
		$this->assertReference($a, $a->setLayout('bill'));
		$this->assertEquals('bill', $a->getLayout());
	}

}

?>
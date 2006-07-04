<?php

class SampleAppender extends AgaviAppender
{
	public function initialize($params = array()) {}
	public function shutdown() {}
	public function write($message) {}
}

class AppenderTest extends AgaviTestCase
{

	public function testGetSetLayout()
	{
		$a = new SampleAppender();
		$this->assertNull($a->getLayout());
		$this->assertReference($a, $a->setLayout('bill'));
		$this->assertEquals('bill', $a->getLayout());
	}

}

?>
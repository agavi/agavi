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
		$a_test = $a->setLayout('bill');
		$this->assertReference($a, $a_test);
		$this->assertEquals('bill', $a->getLayout());
	}

}

?>
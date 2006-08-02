<?php

class Sample2Layout extends AgaviLayout
{
	public function format($message){}
}

class SampleAppender extends AgaviAppender
{
	public function initialize(AgaviContext $context, $params = array()) {}
	public function shutdown() {}
	public function write($message) {}
}

class AppenderTest extends AgaviTestCase
{
	public function testGetSetLayout()
	{
		$a = new SampleAppender();
		$this->assertNull($a->getLayout());
		$l = new Sample2Layout();
		$a_test = $a->setLayout($l);
		$this->assertReference($a, $a_test);
		$this->assertEquals($l, $a->getLayout());
	}

}

?>
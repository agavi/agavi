<?php
require_once dirname(__FILE__) . '/../test_environment.php';

class SampleAppender extends Appender
{
	public function initialize($params) {}
	public function shutdown() {}
	public function write($message) {}
}

class AppenderTest extends UnitTestCase
{

	public function testgetsetLayout()
	{
		$a = new SampleAppender();
		$this->assertNull($a->getLayout());
		$this->assertReference($a, $a->setLayout('bill'));
		$this->assertEqual('bill', $a->getLayout());
	}

}

?>
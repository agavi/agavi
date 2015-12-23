<?php

class Sample2Layout extends AgaviLoggerLayout
{
	public function format(AgaviLoggerMessage $message)
	{
	}
}

class SampleAppender extends AgaviLoggerAppender
{
	public function initialize(AgaviContext $context, array $params = array()) {}
	public function shutdown() {}
	public function write(AgaviLoggerMessage $message) {}
}

class AgaviLoggerAppenderTest extends AgaviUnitTestCase
{
	public function testGetSetLayout()
	{
		$a = new SampleAppender();
		$this->assertNull($a->getLayout());
		$l = new Sample2Layout();
		$a_test = $a->setLayout($l);
		$this->assertSame($a, $a_test);
		$this->assertEquals($l, $a->getLayout());
	}

}

?>
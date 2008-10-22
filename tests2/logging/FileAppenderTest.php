<?php

class FileAppenderTest extends AgaviTestCase
{
	private $_file, $_fa, $_context;

	public function setUp()
	{
		$this->_context = AgaviContext::getInstance('test');
		$this->_file = tempnam('', 'FOO');
		unlink($this->_file);
		$this->_fa = new AgaviFileLoggerAppender();
		$this->_fa->initialize($this->_context, array('file'=>$this->_file));
		$this->_fa->setLayout(new AgaviPassthruLoggerLayout());
	}

	public function tearDown()
	{
		@unlink($this->_file);
	}

	public function testinitialize()
	{
		$this->assertFalse(file_exists($this->_file));
		$this->_fa->write(new AgaviLoggerMessage('my message'));
		$this->assertTrue(file_exists($this->_file));
		$this->_fa->shutdown();
	}

	public function testwrite()
	{
		$this->_fa->write(new AgaviLoggerMessage('my message'));
		$this->assertRegexp('/my message/', file_get_contents($this->_file));
		$this->_fa->shutdown();
	}

	/*
	public function testshutdown()
	{
		// how do you test if the file is still open? - flock() and then attempt to remove it (??)
	}
	*/

}

?>
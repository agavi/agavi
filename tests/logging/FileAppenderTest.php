<?php
require_once dirname(__FILE__) . '/../test_environment.php';

class FileAppenderTest extends UnitTestCase
{
	private $_file, $_fa;

	public function setUp()
	{
		$this->_file = tempnam('/tmp', 'FOO');
		unlink($this->_file);
		$this->_fa = new FileAppender();
		$this->_fa->initialize(array('file'=>$this->_file));
		$this->_fa->setLayout(new PassthruLayout());
	}
	
	public function tearDown()
	{
		@unlink($this->_file);
	}

	public function testinitialize()
	{
		$this->assertFalse(file_exists($this->_file));
		$this->_fa->write(new Message('my message'));
		$this->assertTrue(file_exists($this->_file));
		$this->_fa->shutdown();
	}

	public function testwrite()
	{
		$this->_fa->write(new Message('my message'));
		$this->assertTrue(preg_match('/my message/', file_get_contents($this->_file)));
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

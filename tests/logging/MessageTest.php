<?php
require_once dirname(__FILE__) . '/../test_environment.php';

class MessageTest extends UnitTestCase
{
	public function testconstructor()
	{
		$message = new Message();
		$this->assertNull($message->getMessage());
		$this->assertEqual(Logger::INFO, $message->getPriority());
		$message = new Message('test');
		$this->assertEqual('test', $message->getMessage());
		$this->assertEqual(Logger::INFO, $message->getPriority());
		$message = new Message('test', Logger::DEBUG);
		$this->assertEqual('test', $message->getMessage());
		$this->assertEqual(Logger::DEBUG, $message->getPriority());
	}

	public function testgetsetappendMessage()
	{
		$message = new Message();
		$message->setMessage('my message');
		$this->assertEqual('my message', $message->getMessage());
		$message->setMessage('my message 2');
		$this->assertEqual('my message 2', $message->getMessage());
		$message->appendMessage('my message 3');
		$this->assertEqual(array('my message 2', 'my message 3'), $message->getMessage());
	}

	public function test__toString()
	{
		$message = new Message('test message', Logger::INFO);
		$this->assertEqual('test message', $message->__toString());
		$message->appendMessage('another line');
		$this->assertEqual("test message\nanother line", $message->__toString());
	}

	public function testgetsetPriority()
	{
		$message = new Message;
		$message->setPriority(Logger::DEBUG);
		$this->assertEqual(Logger::DEBUG, $message->getPriority());
		$message->setPriority(Logger::INFO);
		$this->assertEqual(Logger::INFO, $message->getPriority());
	}

}

?>

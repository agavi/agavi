<?php

class MessageTest extends AgaviTestCase
{
	public function testconstructor()
	{
		$message = new AgaviMessage();
		$this->assertNull($message->getMessage());
		$this->assertEquals(AgaviLogger::INFO, $message->getLevel());
		$message = new AgaviMessage('test');
		$this->assertEquals('test', $message->getMessage());
		$this->assertEquals(AgaviLogger::INFO, $message->getLevel());
		$message = new AgaviMessage('test', AgaviLogger::DEBUG);
		$this->assertEquals('test', $message->getMessage());
		$this->assertEquals(AgaviLogger::DEBUG, $message->getLevel());
	}

	public function testgetsetappendMessage()
	{
		$message = new AgaviMessage();
		$message->setMessage('my message');
		$this->assertEquals('my message', $message->getMessage());
		$message->setMessage('my message 2');
		$this->assertEquals('my message 2', $message->getMessage());
		$message->appendMessage('my message 3');
		$this->assertEquals(array('my message 2', 'my message 3'), $message->getMessage());
	}

	public function test__toString()
	{
		$message = new AgaviMessage('test message', AgaviLogger::INFO);
		$this->assertEquals('test message', $message->__toString());
		$message->appendMessage('another line');
		$this->assertEquals("test message\nanother line", $message->__toString());
	}

	public function testgetsetLevel()
	{
		$message = new AgaviMessage;
		$message->setLevel(AgaviLogger::DEBUG);
		$this->assertEquals(AgaviLogger::DEBUG, $message->getLevel());
		$message->setLevel(AgaviLogger::INFO);
		$this->assertEquals(AgaviLogger::INFO, $message->getLevel());
	}

}

?>
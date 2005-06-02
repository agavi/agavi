<?php

require_once dirname(__FILE__) . '/../mockContext.php';

class ContextTest extends UnitTestCase 
{
	private $_controller;

	public function setup()
	{
		$this->_controller = new MockController($this);
		$this->_controller->dispatch();
	}
	

	public function testgetInstance()
	{
		//$c = Context::getInstance($this->_controller);
	}
}


?>

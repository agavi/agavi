<?php
require_once dirname(__FILE__) . '/../test_environment.php';

class PassthruLayoutTest extends UnitTestCase
{
	public function testformat()
	{
		$layout = new PassthruLayout;
		$message = new Message('something');
		$this->assertEqual('something', $layout->format($message));
	}
}

?>

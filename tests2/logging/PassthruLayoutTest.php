<?php

class PassthruLayoutTest extends AgaviTestCase
{
	public function testformat()
	{
		$layout = new AgaviPassthruLayout;
		$message = new AgaviMessage('something');
		$this->assertEquals('something', $layout->format($message));
	}
}

?>
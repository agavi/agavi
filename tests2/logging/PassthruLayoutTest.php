<?php

class PassthruLayoutTest extends AgaviTestCase
{
	public function testformat()
	{
		$layout = new AgaviPassthruLoggerLayout;
		$message = new AgaviLoggerMessage('something');
		$this->assertEquals('something', $layout->format($message));
	}
}

?>
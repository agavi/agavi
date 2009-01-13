<?php

class AgaviTimeZoneTest extends AgaviUnitTestCase
{
	/**
	 * @expectedException AgaviException
	 */
	public function testTicket958()
	{
		$this->setExpectedException('AgaviException');
		$tm = $this->getContext()->getTranslationManager();
		$tz = AgaviTimeZone::createCustomTimeZone($tm, '+01:00');
	}
}

?>
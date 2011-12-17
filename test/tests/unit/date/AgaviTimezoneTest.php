<?php

class AgaviTimeZoneTest extends AgaviUnitTestCase
{
	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testTicket958()
	{
		$tm = $this->getContext()->getTranslationManager();
		$tz = AgaviTimeZone::createCustomTimeZone($tm, '+01:00');
	}
}

?>
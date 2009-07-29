<?php

class AgaviTranslationManagerTest extends AgaviUnitTestCase
{
	/**
	 * @dataProvider dateStrings957
	 */
	public function testTicket957($dateString, $expectedId, $expectedOffset)
	{
		$tm = $this->getContext()->getTranslationManager();
		
		$dt = new DateTime($dateString);
		$cal = $tm->createCalendar($dt);
		
		$tz = $cal->getTimeZone();
		$this->assertEquals($expectedId, $tz->getId(), 'Failed asserting that the created timezone is a custom timezone.');
		$this->assertTrue(($tz instanceof AgaviSimpleTimezone), 'Failed asserting that the created tz is an AgaviSimpleTimezone.');
		$this->assertEquals($expectedOffset, $tz->getRawOffset(), 'Failed asserting that the timezone has the proper offset.');
	}
	
	public function dateStrings957()
	{
		return array(
			array('2008-11-19 23:00:00+01:00', 'GMT+0100', 1 * 60 * 60 * 1000),
			array('2008-11-19 23:00:00+02:00', 'GMT+0200', 2 * 60 * 60 * 1000),
			array('2008-11-19 23:00:00-02:00', 'GMT-0200', - 2 * 60 * 60 * 1000),
			array('2008-11-19 23:00:00+02:30', 'GMT+0230', (2 * 60 + 30) * 60 * 1000),
		);
	}
	
	public function testTicket962()
	{
		$ctx = AgaviContext::getInstance();
		$tm = $ctx->getTranslationManager();
		$locale = $tm->getLocale('de@timezone=America/Los_Angeles');
		$inputFormat = new AgaviDateFormat('yyyy-MM-dd HH:mm:ssZZZ');

		$cal = $inputFormat->parse('2008-11-19 23:00:00America/Los_Angeles', $locale, false);
		$originalTimeZoneId = $cal->getTimeZone()->getId();
		
		$this->assertEquals('America/Los_Angeles', $cal->getTimeZone()->getId());
		$this->assertEquals('Donnerstag, 20. November 2008 2:00 Uhr GMT-05:00', $tm->_d($cal, null, 'de@timezone=America/New_York'));
		$this->assertEquals($originalTimeZoneId, $cal->getTimeZone()->getId());
	}
	
	public function testTicket1099()
	{
		$tm = $this->getContext()->getTranslationManager();
		$this->assertEquals('123,45', $tm->_n(123.45, 'ticket1099', 'de_DE'));
		$this->assertEquals('123,45', $tm->_n(123.4512, 'ticket1099', 'de_DE'));
		$this->assertEquals('123,46', $tm->_n(123.45678, 'ticket1099', 'de_DE'));
		$this->assertEquals('9.876,00', $tm->_n(9876, 'ticket1099', 'de_DE'));
		$this->assertEquals('9.876.543.210,00', $tm->_n(9876543210, 'ticket1099', 'de_DE'));

		$this->assertEquals('123,45', $tm->_c(123.45, 'ticket1099', 'de_DE'));
		$this->assertEquals('123,45', $tm->_c(123.4512, 'ticket1099', 'de_DE'));
		// note, 1.0 ALWAYS uses the fractionalDigits as defined in the ldml
		$this->assertEquals('123,45', $tm->_c(123.45678, 'ticket1099', 'de_DE'));
		$this->assertEquals('9.876,00', $tm->_c(9876, 'ticket1099', 'de_DE'));
		$this->assertEquals('9.876.543.210,00', $tm->_c(9876543210, 'ticket1099', 'de_DE'));
	}
}

?>
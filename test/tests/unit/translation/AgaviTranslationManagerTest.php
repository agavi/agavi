<?php

class AgaviTranslationManagerTest extends AgaviUnitTestCase
{
	/**
	 * @dataProvider dateStrings957
	 */
	public function testTicket957($dateString, $expectedOffset)
	{
		$tm = $this->getContext()->getTranslationManager();
		
		$dt = new DateTime($dateString);
		$cal = $tm->createCalendar($dt);
		
		$tz = $cal->getTimeZone();
		$this->assertEquals(AgaviTimezone::CUSTOM, $tz->getId(), 'Failed asserting that the created timezone is a custom timezone.');
		$this->assertTrue(($tz instanceof AgaviSimpleTimezone), 'Failed asserting that the created tz is an AgaviSimpleTimezone.');
		$this->assertEquals($expectedOffset, $tz->getRawOffset(), 'Failed asserting that the timezone has the proper offset.');
	}
	
	public function dateStrings957()
	{
		return array(
			array('2008-11-19 23:00:00+01:00', 1 * 60 * 60 * 1000),
			array('2008-11-19 23:00:00+02:00', 2 * 60 * 60 * 1000),
			array('2008-11-19 23:00:00-02:00', - 2 * 60 * 60 * 1000),
			array('2008-11-19 23:00:00+02:30', (2 * 60 + 30) * 60 * 1000),
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
}

?>
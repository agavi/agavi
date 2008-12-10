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
			array('2008-11-19 23:00:00+02:30', (-2 * 60 + 30) * 60 * 1000),
		);
	}
}

?>
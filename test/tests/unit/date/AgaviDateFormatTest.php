<?php

class AgaviDateFormatTest extends AgaviUnitTestCase
{

	public function testParse()
	{
		$locale = $this->getContext()->getTranslationManager()->getLocale('@timezone=GMT+0200');
		$inputFormat = new AgaviDateFormat('yyyy-MM-dd HH:mm:ss');

		$cal = $inputFormat->parse('2008-11-19 23:00:00', $locale, false);
		
		$tz = $cal->getTimeZone();
		$this->assertEquals(AgaviTimezone::CUSTOM, $tz->getId(), 'Failed asserting that the created timezone is a custom timezone.');
		$this->assertTrue(($tz instanceof AgaviSimpleTimezone), 'Failed asserting that the created tz is an AgaviSimpleTimezone.');
		$this->assertEquals(2 * 60 * 60 * 1000, $tz->getRawOffset(), 'Failed asserting that the timezone has the proper offset.');
	}

}


?>
<?php

class AgaviDateFormatTest extends AgaviUnitTestCase
{

	public function testParse()
	{
		$locale = $this->getContext()->getTranslationManager()->getLocale('@timezone=GMT+0200');
		$inputFormat = new AgaviDateFormat('yyyy-MM-dd HH:mm:ss');

		$cal = $inputFormat->parse('2008-11-19 23:00:00', $locale, false);
		
		$tz = $cal->getTimeZone();
		$this->assertEquals('GMT+0200', $tz->getId(), 'Failed asserting that the created timezone is a custom timezone.');
		$this->assertTrue(($tz instanceof AgaviSimpleTimezone), 'Failed asserting that the created tz is an AgaviSimpleTimezone.');
		$this->assertEquals(2 * 60 * 60 * 1000, $tz->getRawOffset(), 'Failed asserting that the timezone has the proper offset.');
	}
	
	public function testParseWithSystemDefaultTimeZone()
	{
		$systemZoneId = 'Europe/Moscow';
		$tm = $this->getContext()->getTranslationManager();
		$currentLocale = AgaviLocale::parseLocaleIdentifier($tm->getCurrentLocale()->getIdentifier());
		$tm->setLocale($currentLocale['locale_str'] . '@timezone=');
		$tm->setDefaultTimeZone($systemZoneId);
		$inputFormat = $tm->createDateFormat('yyy-MM-dd HH:mm:ss');
		
		$cal = $inputFormat->parse('2008-11-19 23:00:00');
		$this->assertEquals($systemZoneId, $cal->getTimeZone()->getId());
	}

	public function testParseWithCurrentLocaleTimeZone()
	{
		$zoneId = 'Europe/Berlin';
		$tm = $this->getContext()->getTranslationManager();
		$tm->setDefaultTimeZone('Europe/Moscow');
		$currentLocale = AgaviLocale::parseLocaleIdentifier($tm->getCurrentLocale()->getIdentifier());
		$tm->setLocale($currentLocale['locale_str'] . '@timezone=' . $zoneId);
		$inputFormat = $tm->createDateFormat('yyy-MM-dd HH:mm:ss');
		
		$cal = $inputFormat->parse('2008-11-19 23:00:00');
		$this->assertEquals($zoneId, $cal->getTimeZone()->getId());
	}

	public function testParseWithProvidedLocaleTimeZone()
	{
		$zoneId = 'Africa/Timbuktu';
		$tm = $this->getContext()->getTranslationManager();
		$tm->setDefaultTimeZone('Europe/Moscow');
		$tm->setLocale($tm->getCurrentLocale()->getIdentifier() . '@timezone=Europe/Berlin');
		$inputFormat = $tm->createDateFormat('yyy-MM-dd HH:mm:ss');
		
		$cal = $inputFormat->parse('2008-11-19 23:00:00', '@timezone=' . $zoneId);
		$this->assertEquals($zoneId, $cal->getTimeZone()->getId());
	}

	
	public function testFormatWithSystemDefaultTimeZone()
	{
		$systemZoneId = 'Europe/Moscow';
		$tm = $this->getContext()->getTranslationManager();
		$currentLocale = AgaviLocale::parseLocaleIdentifier($tm->getCurrentLocale()->getIdentifier());
		$tm->setLocale($currentLocale['locale_str'] . '@timezone=');
		$tm->setDefaultTimeZone($systemZoneId);
		$inputFormat = $tm->createDateFormat('yyy-MM-dd HH:mm:ss v');
		
		$cal = $inputFormat->parse('2008-11-19 23:00:00 America/New_York');
		
		$this->assertEquals('2008-11-20 07:00:00 ' . $systemZoneId, $inputFormat->format($cal));
	}

	public function testFormatWithCurrentLocaleTimeZone()
	{
		$zoneId = 'Europe/Berlin';
		$tm = $this->getContext()->getTranslationManager();
		$tm->setDefaultTimeZone('Europe/Moscow');
		$currentLocale = AgaviLocale::parseLocaleIdentifier($tm->getCurrentLocale()->getIdentifier());
		$tm->setLocale($currentLocale['locale_str'] . '@timezone=' . $zoneId);
		$inputFormat = $tm->createDateFormat('yyy-MM-dd HH:mm:ss v');
		
		$cal = $inputFormat->parse('2008-11-19 23:00:00 America/New_York');
		
		$this->assertEquals('2008-11-20 05:00:00 ' . $zoneId, $inputFormat->format($cal));
	}

	public function testFormatWithProvidedLocaleTimeZone()
	{
		$zoneId = 'Africa/Bamako';
		$tm = $this->getContext()->getTranslationManager();
		$tm->setDefaultTimeZone('Europe/Moscow');
		$tm->setLocale($tm->getCurrentLocale()->getIdentifier() . '@timezone=Europe/Berlin');
		$inputFormat = $tm->createDateFormat('yyy-MM-dd HH:mm:ss v');
		
		$cal = $inputFormat->parse('2008-11-19 23:00:00 America/New_York');
		
		$this->assertEquals('2008-11-20 04:00:00 ' . $zoneId, $inputFormat->format($cal, AgaviCalendar::GREGORIAN, '@timezone=' . $zoneId));
	}

}


?>
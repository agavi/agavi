<?php

class AgaviSessionStorageTest extends AgaviUnitTestCase
{
	
	/**
	 * @dataProvider dataStartupSetsCookieSecureFlag
	 */
	public function testStartupSetsCookieSecureFlag($iniValue, $secure)
	{
		ini_set('session.cookie_secure', $iniValue);
		$context = AgaviContext::getInstance('agavi-session-storage-test::tests-startup-sets-cookie-secure-flag');
		// test for bug #1541
		$storage = new AgaviSessionStorage();
		$storage->initialize($context);
		$storage->startup();
		$cookieParams = session_get_cookie_params();
		$this->assertSame($secure, $cookieParams['secure']);
	}
	
	public function dataStartupSetsCookieSecureFlag()
	{
		return array(
			array(null, true),
			array('', true),
			array('off', false),
			array('on', true),
		);
	}
	
}

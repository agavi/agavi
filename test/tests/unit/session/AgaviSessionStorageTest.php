<?php

class AgaviSessionStorageTest extends AgaviUnitTestCase
{
	
	public function testStartupSetsCookieSecureFlag($iniValue, $secure)
	{
		// test for bug #1541
		ini_set('session.cookie_secure', 0);
		$context = AgaviContext::getInstance('agavi-session-storage-test::tests-startup-sets-cookie-secure-flag');
		$storage = new AgaviSessionStorage();
		$storage->initialize($context);
		$storage->startup();
		$cookieParams = session_get_cookie_params();
		$this->assertTrue($cookieParams['secure']);
	}
	
}

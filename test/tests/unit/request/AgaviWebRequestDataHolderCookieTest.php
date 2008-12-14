<?php

class AgaviWebRequestDataHolderCookieTest extends AgaviWebRequestDataHolderTest
{
	/**
	 * @dataProvider parameterData
	 * 
	 */
	public function testRemoveCookie($key, $expected, $exists, $empty)
	{
		$dh = $this->getDefaultDataHolder();
		
		$message = 'Failed asserting that the cookie value is returned when removing an existing cookie.';
		if(!$exists) {
			$message = 'Failed asserting that the return value is null when removing a nonexistant cookie.';
		}
		
		$value = $dh->removeCookie($key);
		
		$this->assertEquals($expected, $value, $message);
		$this->assertFalse($dh->hasCookie($key), sprintf('Failed asserting that the key %1$s has been removed.', $key));
	}
	
	/**
	 * @dataProvider parameterData
	 * 
	 */
	public function testHasCookie($key, $expected, $exists, $empty)
	{
		$dh = $this->getDefaultDataHolder();
		
		if(!$exists) {
			$this->assertFalse($dh->hasCookie($key), sprintf('Failed asserting the the cookie named %1$s does not exist.', $key));
		} else {
			$this->assertTrue($dh->hasCookie($key), sprintf('Failed asserting the the cookie named %1$s exists.', $key));
		}
	}
	
	/**
	 * @dataProvider parameterData
	 * 
	 */
	public function testIsCookieValueEmpty($key, $expected, $exists, $empty)
	{
		$dh = $this->getDefaultDataHolder();
		
		if(!$empty) {
			$this->assertFalse($dh->isCookieValueEmpty($key), sprintf('Failed asserting the the cookie named %1$s has a non-empty value.', $key));
		} else {
			$this->assertTrue($dh->isCookieValueEmpty($key), sprintf('Failed asserting the the cookie named %1$s has an empty value.', $key));
		}
	}
	
	public function testGetCookies()
	{
		$dh = $this->getDefaultDataHolder();
		
		$this->assertEquals(
			$this->getDefaultNestedInputData(),
			$dh->getCookies()
		);
	}
	
	public function testSetCookies()
	{
		$dh = $this->getDefaultDataHolder();
		
		$dh->setCookies(
			array(
				'flat' => 'flatvalue merged',
				'set'  => 'setCookies',
			)
		);
		
		$expected = array_merge(
			$this->getDefaultNestedInputData(), 
			array(
				'flat' => 'flatvalue merged',
				'set'  => 'setCookies',
			)
		);
		
		$this->assertEquals($expected, $dh->getCookies());
	}
	
	public function testMergeCookies()
	{
		$dh = $this->getDefaultDataHolder();
		
		$dh2 = new AgaviWebRequestDataHolder(
			array(
				AgaviWebRequestDataHolder::SOURCE_COOKIES => array(
					'flat' => 'flatvalue merged',
					'set'  => 'setCookies',
				)
			)
		);
		
		$dh->mergeCookies($dh2);
		
		$expected = array_merge(
			$this->getDefaultNestedInputData(), 
			array(
				'flat' => 'flatvalue merged',
				'set'  => 'setCookies',
			)
		);
		
		$this->assertEquals($expected, $dh->getCookies());
	}
	
	public function testMergeCookies2()
	{
		$dh = $this->getDefaultDataHolder();
		
		$dh2 = new AgaviRequestDataHolder(
			array(
				AgaviWebRequestDataHolder::SOURCE_COOKIES => array(
					'flat' => 'flatvalue merged',
					'set'  => 'setCookies',
				)
			)
		);
		
		$dh->mergeCookies($dh2);
		
		$expected = $this->getDefaultNestedInputData();
		
		$this->assertEquals(
			$expected,
			$dh->getCookies(),
			'Failed asserting that cookies from a dataholder not implementing AgaviICookiesRequestDataHolder are not merged.'
		);
	}
	
	public function testClearCookies()
	{
		$dh = $this->getDefaultDataHolder();
		
		$dh->clearCookies();
		
		$this->assertEquals(array(), $dh->getCookies());
	}
	
	public function testGetCookieNames()
	{
		$dh = $this->getDefaultDataHolder();
		
		$this->assertEquals($this->getDefaultParameterNames(), $dh->getCookieNames());
	}
	
	public function testGetFlatCookieNames()
	{
		$dh = $this->getDefaultDataHolder();
		
		$this->assertEquals(
			$this->getFlatDefaultParameterNames(), 
			$dh->getFlatCookieNames()
		);
	}
}

?>
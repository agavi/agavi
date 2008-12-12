<?php
class AgaviWebRequestDataHolderTest extends AgaviPhpUnitTestCase
{
	public function testRemoveCookieArrayPart()
	{
		$dh = new AgaviWebRequestDataHolder(array(AgaviWebRequestDataHolder::SOURCE_COOKIES => array('nested' => array('foo' => 'bar'))));
		$this->assertTrue($dh->hasCookie('nested[foo]'));
		$this->assertFalse($dh->isCookieValueEmpty('nested[foo]'));
		$this->assertEquals('bar', $dh->getCookie('nested[foo]'), 'Failed asserting that the cookie value is "bar" when reading nested[foo].');
		$this->assertEquals('bar', $dh->removeCookie('nested[foo]'), 'Failed asserting that the return value is "bar" when removing nested[foo].');
		$this->$this->assertFalse($dh->hasCookie('nested[foo]'), 'Failed asserting that the nested cookie part "foo" was removed.');
	}
	
	public function testSetGetCookie()
	{
		$dh = new AgaviWebRequestDataHolder(array());
		$dh->setCookie('foo', 'bar');
		$this->assertEquals('bar', $dh->getCookie('foo'));
		
		$dh->setCookie('foo', array('bar' => 'baz'));
		$this->assertEquals(array('bar' => 'baz'), $dh->getCookie('foo'));
		$this->assertEquals('baz', $dh->getCookie('foo[bar]'));
	}
	
	/**
	 * @dataProvider parameterData
	 * 
	 */
	public function testGetCookie($key, $expected, $default, $exists, $message)
	{
		$dh = new AgaviWebRequestDataHolder(
			array(
				AgaviWebRequestDataHolder::SOURCE_COOKIES => array(
					'flat'   => 'flatvalue',
					'nested' => array(
						'level1' => 'level1 value',
						'level2' => array('level3' => 'level3 value'),
					)
				)
			)
		);
		
		if(null !== $default) {
			$value = $dh->getCookie($key, $default);
		} else {
			$value = $dh->getCookie($key);
		}
		
		$this->assertEquals($expected, $value, $message);
	}
	
	/**
	 * @dataProvider parameterData
	 * 
	 */
	public function testUnsetCookie($key, $expected, $default, $exists, $message)
	{
		$dh = new AgaviWebRequestDataHolder(
			array(
				AgaviWebRequestDataHolder::SOURCE_COOKIES => array(
					'flat'   => 'flatvalue',
					'nested' => array(
						'level1' => 'level1 value',
						'level2' => array('level3' => 'level3 value'),
					)
				)
			)
		);
		
		if(!$exists) {
			$expected = null;
		}
		
		$value = $dh->removeCookie($key);
		
		$this->assertEquals($expected, $value, $message);
		$this->assertFalse($dh->hasCookie($key), sprintf('Failed asserting that the key %1$s has been unset.', $key));
	}
	
	public function parameterData()
	{
		return array(
			'unsetkey,null' => array(
				'unset key', 
				null, 
				null,
				false,
				'Failed asserting that an unset key returns null when no default value is passed.',
			),
			'unsetkey,default' => array(
				'unset key', 
				'unset default value', 
				'unset default value',
				false,
				'Failed asserting that an unset key returns the passed default value.',
			),
			'flatkey' => array(
				'flat',
				'flatvalue',
				null,
				true,
				'Failed asserting that a flat key returns the proper value.',
			),
			'flatkey,default' => array(
				'flat',
				'flatvalue',
				'flatdefault',
				true,
				'Failed asserting that a flat key returns the proper value, not the default.',
			),
			'nestedkey-1,null' => array(
				'nested',
				array(
					'level1' => 'level1 value',
					'level2' => array('level3' => 'level3 value'),
				),
				null,
				true,
				'Failed asserting that a nested key returns the proper value.',
			),
			'nestedkey-1,default' => array(
				'nested',
				array(
					'level1' => 'level1 value',
					'level2' => array('level3' => 'level3 value'),
				),
				'nested-default',
				true,
				'Failed asserting that a nested key returns the proper value, not the default.',
			),
			'nestedkey-2,null' => array(
				'nested[level1]',
				'level1 value',
				null,
				true,
				'Failed asserting that a nested key returns the proper value.',
			),
			'nestedkey-2,default' => array(
				'nested[level1]',
				'level1 value',
				'nested-default',
				true,
				'Failed asserting that a nested key returns the proper value, not the default.',
			),
			'nestedkey-3,null' => array(
				'nested[level2][level3]',
				'level3 value',
				null,
				true,
				'Failed asserting that a nested key returns the proper value.',
			),
			'nestedkey-3,default' => array(
				'nested[level2][level3]',
				'level3 value',
				'nested-default',
				true,
				'Failed asserting that a nested key returns the proper value, not the default.',
			),
			'missing-nested,null' => array(
				'nested[missing]', 
				null,
				null,
				false,
				'Failed asserting that a nonexistent nested key returns null when no default value is passed.',
			),			
			'missing-nested,default' => array(
				'nested[missing]', 
				'missing-nested-default',
				'missing-nested-default',
				false,
				'Failed asserting that a nonexistent nested cookie key returns the passed default value.',
			),
		);
	}
	
	public function testNullParameterValue()
	{
		$dh = new AgaviWebRequestDataHolder(array(AgaviWebRequestDataHolder::SOURCE_PARAMETERS => array('nullvalue' => null)));
		$this->assertTrue(
			$dh->hasParameter('nullvalue'), 
			'Failed asserting that the dataholder has a parameter named "nullvalue".'
		);
		$this->assertTrue(
			$dh->isParameterValueEmpty('nullvalue'), 
			'Failed asserting that the dh->isParameterValueEmpty() returns true on the parameter named "nullvalue".'
		);
		$this->assertEquals(
			null, 
			$dh->getParameter('nullvalue'), 
			'Failed asserting that the parameter named "nullvalue" has the value "null".'
		);
	}
	
	/**
	 * @dataProvider dataTestParameterSet
	 */ 
	public function testSetParameter($setKey, $setValue, $readKey, $readValue)
	{
		$dh = new AgaviWebRequestDataHolder(array());
		$dh->setParameter($setKey, $setValue);
		$this->assertEquals($readValue, $dh->getParameter($readKey));
	}
	
	public function dataTestParameterSet()
	{
		return array(
			array(
				'flat',
				'flatvalue',
				'flat',
				'flatvalue',
			),
			array(
				'nested',
				array(0 => 1),
				'nested[0]',
				1,
			),
			array(
				'nested',
				array('foo' => 'bar'),
				'nested[foo]',
				'bar',
			),
			array(
				'nested',
				array(0 => 1),
				'nested',
				array(0 => 1),
			),
			array(
				'nested',
				array('foo' => 'bar'),
				'nested',
				array('foo' => 'bar'),
			),
		);
	}
}

?>
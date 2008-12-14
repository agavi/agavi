<?php
class AgaviWebRequestDataHolderTest extends AgaviPhpUnitTestCase
{
	protected function getDefaultDataHolder()
	{
		return new AgaviWebRequestDataHolder(
			array(
				AgaviWebRequestDataHolder::SOURCE_COOKIES => $this->getDefaultNestedInputData(),
				AgaviWebRequestDataHolder::SOURCE_PARAMETERS => $this->getDefaultNestedInputData(),
				AgaviWebRequestDataHolder::SOURCE_HEADERS => $this->getDefaultHeaders(),
			)
		);
	}
	
	protected function getDefaultNestedInputData()
	{
		return  array(
			'flat'   => 'flatvalue',
			'nested' => array(
				'level1' => 'level1 value',
				'level2' => array(
					'level3' => 'level3 value',
					'nullkey' => null,
					'emptystring' => '',
				),
			),
			'nullvalue'   => null,
			'falsevalue'  => false,
			'emptystring' => '',
			'zerovalue'   => 0,
		);
	}
	
	/**
	 * returns information on the default nested data set
	 * 
	 *  each row has the following information:
	 * 
	 *   - keyname
	 *   - expected return
	 *   - key exists
	 *   - key considered empty
	 */
	public function parameterData()
	{
		return array(
			'unsetkey' => array(
				'unset key', 
				null, 
				false,
				true,
			),
			'flatkey' => array(
				'flat',
				'flatvalue',
				true,
				false,
			),
			'nestedkey-1' => array(
				'nested',
				array(
					'level1' => 'level1 value',
					'level2' => array(
						'level3' => 'level3 value',
						'nullkey' => null,
						'emptystring' => '',
					),
				),
				true,
				false,
			),
			'nestedkey-2' => array(
				'nested[level1]',
				'level1 value',
				true,
				false,
			),
			'nestedkey-3' => array(
				'nested[level2][level3]',
				'level3 value',
				true,
				false,
			),
			'nestedkey-null' => array(
				'nested[level2][nullkey]',
				null,
				true,
				true,
			),
			'nestedkey-emptystring' => array(
				'nested[level2][emptystring]',
				'',
				true,
				true,
			),
			'nestedkey-missing' => array(
				'nested[missing]', 
				null,
				false,
				true,
			),
			'nullvalue' => array(
				'nullvalue',
				null, 
				true, 
				true,
			),
			'zerovalue' => array(
				'zerovalue',
				0,
				true,
				false,
			),
			'emptystring' => array(
				'emptystring',
				'',
				true,
				true,
			),
			'falsevalue' => array(
				'falsevalue',
				false,
				true,
				false,
			)
		);
	}
	
	public function getFlatDefaultParameterNames()
	{
		return array(
			'flat', 
			'nested[level1]', 
			'nested[level2][level3]', 
			'nested[level2][nullkey]',
			'nested[level2][emptystring]',
			'nullvalue', 
			'falsevalue', 
			'emptystring',
			'zerovalue'
		);
	}
	
	public function getDefaultParameterNames()
	{
		return array('flat', 'nested', 'nullvalue', 'falsevalue', 'emptystring', 'zerovalue');
	}
	
	public function getParameterReadInformation()
	{
		$readInformation = array();
		
		foreach ($this->parameterData() as $key => $parameterInfo)
		{
			$readInformation[$key] = $parameterInfo;
			$readInformation[$key][4] = false;
			$readInformation[$key.',default'] = $parameterInfo;
			$readInformation[$key.',default'][4] = true;
			if(true == $parameterInfo[3])
			{
				$readInformation[$key.',default'][1] = 'default';
			}
		}
		
		return $readInformation;
	}
	
	public function getDefaultHeaders()
	{
		return array(
			'FLAT_HEADER' => 'flatvalue',
			'NESTED_HEADER' => array(          // array headers don't exist, but we need to check 
				'NESTED_KEY' => 'nestedvalue', // that virtual array access does indeed not work
			),
			'NULL_VALUE' => null,
			'ZERO_VALUE' => 0,
			'FALSE_VALUE' => false,
			'EMPTY_STRING' => '',
			'CONTAINS[BRACKETS]' => 'contains_brackets',
		);
	}
	
	/**
	 * returns information on the default header data set
	 * 
	 *  each row has the following information:
	 * 
	 *   - keyname
	 *   - expected return
	 *   - key exists
	 *   - key considered empty
	 */
	public function getDefaultHeaderInformation()
	{
		return array(
			'FLAT_HEADER,caps,underscore' => array(
				'FLAT_HEADER',
				'flatvalue',
				true,
				false,
			),
			'FLAT_HEADER,caps,hyphen' => array(
				'FLAT-HEADER',
				'flatvalue',
				true,
				false,
			),
			'FLAT_HEADER,non-caps,underscore' => array(
				'flat_header',
				'flatvalue',
				true,
				false,
			),
			'FLAT_HEADER,non-caps,hyphen' => array(
				'flat-header',
				'flatvalue',
				true,
				false,
			),
			'MISSING_HEADER' => array(
				'MISSING_HEADER',
				null,
				false,
				true,
			),
			'NESTED_HEADER' => array(
				'NESTED_HEADER',
				array(
					'NESTED_KEY' => 'nestedvalue',
				),
				true,
				false,
			),
			'NESTED_HEADER-1' => array(
				'NESTED_HEADER[NESTED_KEY]',
				null,
				false,
				true,
			),
			'NULL_VALUE' => array(
				'NULL_VALUE',
				null,
				true,
				true,
			),
			'ZERO_VALUE' => array(
				'ZERO_VALUE',
				0,
				true,
				false,
			),
			'FALSE_VALUE' => array(
				'FALSE_VALUE',
				false,
				true,
				false,
			),
			'EMPTY_STRING' => array(
				'EMPTY_STRING',
				'',
				true,
				true,
			),
			'CONTAINS[BRACKETS]' => array(
				'CONTAINS[BRACKETS]',
				'contains_brackets',
				true,
				false,
			),
		);
	}
	
	public function getHeaderReadInformation()
	{
		$readInformation = array();
		
		foreach ($this->getDefaultHeaderInformation() as $key => $info)
		{
			$readInformation[$key] = $info;
			$readInformation[$key][4] = false;
			$readInformation[$key.',default'] = $info;
			$readInformation[$key.',default'][4] = true;
			if(true == $info[3])
			{
				$readInformation[$key.',default'][1] = 'default';
			}
		}
		
		return $readInformation;
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
	 * @dataProvider getParameterReadInformation
	 */
	public function testGetCookie($key, $expected, $exists, $empty, $hasDefault)
	{
		$dh = $this->getDefaultDataHolder();
		
		if($hasDefault) {
			$value = $dh->getCookie($key, 'default');
		} else {
			$value = $dh->getCookie($key);
		}
		
		$this->assertEquals($expected, $value);
	}
	
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
	
	/*** --------- parameter tests ------ ***/
	
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
	
	/**
	 * @dataProvider getParameterReadInformation
	 */
	public function testGetParameter($key, $expected, $exists, $empty, $hasDefault)
	{
		$dh = $this->getDefaultDataHolder();
		
		if($hasDefault) {
			$value = $dh->getParameter($key, 'default');
		} else {
			$value = $dh->getParameter($key);
		}
		
		$this->assertEquals($expected, $value);
	}
	
	/**
	 * @dataProvider parameterData
	 * 
	 */
	public function testRemoveParameter($key, $expected, $exists, $empty)
	{
		$dh = $this->getDefaultDataHolder();
		
		$message = 'Failed asserting that the parameter value is returned when removing an existing parameter.';
		if(!$exists) {
			$message = 'Failed asserting that the return value is null when removing a nonexistant parameter.';
		}
		
		$value = $dh->removeParameter($key);
		
		$this->assertEquals($expected, $value, $message);
		$this->assertFalse($dh->hasParameter($key), sprintf('Failed asserting that the key %1$s has been removed.', $key));
	}
	
	/**
	 * @dataProvider parameterData
	 * 
	 */
	public function testHasParameter($key, $expected, $exists, $empty)
	{
		$dh = $this->getDefaultDataHolder();
		
		if(!$exists) {
			$this->assertFalse($dh->hasParameter($key), sprintf('Failed asserting the the parameter named %1$s does not exist.', $key));
		} else {
			$this->assertTrue($dh->hasParameter($key), sprintf('Failed asserting the the parameter named %1$s exists.', $key));
		}
	}
	
	/**
	 * @dataProvider parameterData
	 * 
	 */
	public function testIsParameterValueEmpty($key, $expected, $exists, $empty)
	{
		$dh = $this->getDefaultDataHolder();
		
		if(!$empty) {
			$this->assertFalse($dh->isParameterValueEmpty($key), sprintf('Failed asserting the the parameter named %1$s has a non-empty value.', $key));
		} else {
			$this->assertTrue($dh->isParameterValueEmpty($key), sprintf('Failed asserting the the parameter named %1$s has an empty value.', $key));
		}
	}
	
	public function testGetParameters()
	{
		$dh = $this->getDefaultDataHolder();
		
		$this->assertEquals(
			$this->getDefaultNestedInputData(),
			$dh->getParameters()
		);
	}
	
	public function testSetParameters()
	{
		$dh = $this->getDefaultDataHolder();
		
		$dh->setParameters(
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
		
		$this->assertEquals($expected, $dh->getParameters());
	}
	
	public function testMergeParameters()
	{
		$dh = $this->getDefaultDataHolder();
		
		$dh2 = new AgaviWebRequestDataHolder(
			array(
				AgaviWebRequestDataHolder::SOURCE_PARAMETERS => array(
					'flat' => 'flatvalue merged',
					'set'  => 'setCookies',
				)
			)
		);
		
		$dh->mergeParameters($dh2);
		
		$expected = array_merge(
			$this->getDefaultNestedInputData(), 
			array(
				'flat' => 'flatvalue merged',
				'set'  => 'setCookies',
			)
		);
		
		$this->assertEquals($expected, $dh->getParameters());
	}
	
	public function testClearParameters()
	{
		$dh = $this->getDefaultDataHolder();
		
		$dh->clearParameters();
		
		$this->assertEquals(array(), $dh->getParameters());
	}
	
	public function testGetParameterNames()
	{
		$dh = $this->getDefaultDataHolder();
		
		$this->assertEquals($this->getDefaultParameterNames(), $dh->getParameterNames());
	}
	
	public function testGetFlatParameterNames()
	{
		$dh = $this->getDefaultDataHolder();
		
		$this->assertEquals(
			$this->getFlatDefaultParameterNames(), 
			$dh->getFlatParameterNames()
		);
	}
	
	/*  ------   header tests ------ */
		
	/**
	 * @dataProvider getHeaderReadInformation
	 */
	public function testGetHeader($key, $expected, $exists, $empty, $hasDefault)
	{
		$dh = $this->getDefaultDataHolder();
		
		if($hasDefault) {
			$value = $dh->getHeader($key, 'default');
		} else {
			$value = $dh->getHeader($key);
		}
		
		$this->assertEquals($expected, $value);
	}
	
	/**
	 * @dataProvider getDefaultHeaderInformation
	 * 
	 */
	public function testRemoveHeader($key, $expected, $exists, $empty)
	{
		$dh = $this->getDefaultDataHolder();
		
		$message = 'Failed asserting that the header value is returned when removing an existing header.';
		if(!$exists) {
			$message = 'Failed asserting that the return value is null when removing a nonexistant header.';
		}
		
		$value = $dh->removeHeader($key);
		
		$this->assertEquals($expected, $value, $message);
		$this->assertFalse($dh->hasParameter($key), sprintf('Failed asserting that the key %1$s has been removed.', $key));
	}
	
	/**
	 * @dataProvider getDefaultHeaderInformation
	 * 
	 */
	public function testHasHeader($key, $expected, $exists, $empty)
	{
		$dh = $this->getDefaultDataHolder();
		
		if(!$exists) {
			$this->assertFalse($dh->hasHeader($key), sprintf('Failed asserting the the header named %1$s does not exist.', $key));
		} else {
			$this->assertTrue($dh->hasHeader($key), sprintf('Failed asserting the the header named %1$s exists.', $key));
		}
	}
	
	/**
	 * @dataProvider getDefaultHeaderInformation
	 * 
	 */
	public function testIsHeaderValueEmpty($key, $expected, $exists, $empty)
	{
		$dh = $this->getDefaultDataHolder();
		
		if(!$empty) {
			$this->assertFalse($dh->isHeaderValueEmpty($key), sprintf('Failed asserting the the header named %1$s has a non-empty value.', $key));
		} else {
			$this->assertTrue($dh->isHeaderValueEmpty($key), sprintf('Failed asserting the the header named %1$s has an empty value.', $key));
		}
	}
	
	public function testGetHeaders()
	{
		$dh = $this->getDefaultDataHolder();
		
		$this->assertEquals($this->getDefaultHeaders(), $dh->getHeaders());
	}
	
	public function testSetHeaders()
	{
		$dh = $this->getDefaultDataHolder();
		
		$addHeaders = array(
			'FLAT_HEADER' => 'flatvalue merged',
			'SET'  => 'setHeaders',
		);
		
		$dh->setHeaders($addHeaders);
		
		$expected = array_merge(
			$this->getDefaultHeaders(),
			$addHeaders
		);
		
		$this->assertEquals($expected, $dh->getHeaders());
	}
	
	public function testMergeHeaders()
	{
		$dh = $this->getDefaultDataHolder();
		
		$addHeaders = array(
			'FLAT_HEADER' => 'flatvalue merged',
			'SET'  => 'setHeaders',
		);
		
		$dh2 = new AgaviWebRequestDataHolder(
			array(
				AgaviWebRequestDataHolder::SOURCE_HEADERS => $addHeaders,
			)
		);
		
		$dh->mergeHeaders($dh2);
		
		$expected = array_merge(
			$this->getDefaultHeaders(), 
			$addHeaders
		);
		
		$this->assertEquals($expected, $dh->getHeaders());
	}
	
	public function testMergeHeaders2()
	{
		$dh = $this->getDefaultDataHolder();
		
		$addHeaders = array(
			'FLAT_HEADER' => 'flatvalue merged',
			'SET'  => 'setHeaders',
		);
		
		$dh2 = new AgaviRequestDataHolder(
			array(
				AgaviWebRequestDataHolder::SOURCE_HEADERS => $addHeaders,
			)
		);
		
		$dh->mergeHeaders($dh2);
		
		$expected = $this->getDefaultHeaders();
		
		$this->assertEquals(
			$expected,
			$dh->getHeaders(),
			'Failed asserting that headers from a dataholder not implementing AgaviIHeadersRequestDataHolder are not merged.'
		);
	}
	
	public function testClearHeaders()
	{
		$dh = $this->getDefaultDataHolder();
		
		$dh->clearHeaders();
		
		$this->assertEquals(array(), $dh->getHeaders());
	}
	
	
	public function testGetHeaderNames()
	{
		$dh = $this->getDefaultDataHolder();
		$this->assertEquals(array_keys($this->getDefaultHeaders()), $dh->getHeaderNames());
	}
}

?>
<?php

class AgaviWebRequestDataHolderHeaderTest extends AgaviWebRequestDataHolderTest
{
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
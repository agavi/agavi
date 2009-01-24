<?php

class AgaviWebRequestDataHolderParameterTest extends AgaviWebRequestDataHolderTest
{	    
	/**
	 * returns information on the default nested data set
	 * 
	 *  each row has the following information:
	 * 
	 *   - keyname
	 *   - expected return
	 *   - key exists (returns 'true' on hasParameter/Cookie/...)
	 *   - key considered empty (returns 'true' on isParameter/Cookie/...ValueEmpty)
	 */
	public function parameterData()
	{
		return array(
			'unsetkey' => array(
				'unsetkey', 
				null, 
				false,
				true,
			),
			'nestedkey-unset' => array(
				'nested[unset]', 
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
			'falsevalue' => array(
				'falsevalue',
				false,
				true,
				false,
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
			'objectvalue' => array(
				'objectvalue',
				new stdClass(),
				true, 
				false,
			),
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
	
}
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
	
	public function testNullParameterValue()
	{
		$dh = new AgaviWebRequestDataHolder(array(AgaviWebRequestDataHolder::SOURCE_PARAMETERS => array('nullvalue' => null)));
		$this->assertTrue($dh->hasParameter('nullvalue'), 'Failed asserting that the dataholder has a parameter named "nullvalue".');
		$this->assertTrue(
			$dh->isParameterValueEmpty('nullvalue'), 
			'Failed asserting that the dh->isParameterValueEmpty() returns true on the parameter named "nullvalue".'
		);
		$this->assertEquals(null, $dh->getParameter('nullvalue'), 'Failed asserting that the parameter named "nullvalue" has the value "null".');
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
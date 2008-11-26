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
}

?>
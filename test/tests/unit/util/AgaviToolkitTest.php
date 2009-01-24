<?php

class AgaviToolkitTest extends AgaviPhpUnitTestCase
{
	
	/**
	 * @dataProvider stringBaseTestData
	 */
	public function testStringBase($baseString, $compString, $expectedBase, $expectedAmount)
	{
		$equalAmount = 0;
		$base = AgaviToolkit::stringBase($baseString, $compString, $equalAmount);
		$this->assertEquals($expectedBase, $base, sprintf('Failed asserting that the common base of "%1$s" and "%2$s" is "%3$s".', $baseString, $compString, $expectedBase));
		$this->assertEquals($expectedAmount, $equalAmount, sprintf('Failed asserting that the common base of "%1$s" and "%2$s" has a length of %3$d.', $baseString, $compString, $expectedAmount));
	}
	
	public function stringBaseTestData()
	{
		return array(array('foo', 'bar', '', 0),
					 array('foobar', 'foo', 'foo', 3),
					 array('foo', 'foobar', 'foo', 3),
					 array('foo', '', '', 0),
					 array('', 'bar', '', 0));
	}
}
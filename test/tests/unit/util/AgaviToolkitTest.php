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
	
	/**
	 * @dataProvider literalizeData
	 */
	public function testLiteralize($rawValue, $expectedResult, $settings)
	{
		foreach($settings as $key => $value) {
			AgaviConfig::set($key, $value);
		}
		
		$literalized = AgaviToolkit::literalize($rawValue);
		
		$this->assertEquals($expectedResult, $literalized);
	}
	
	public function literalizeData()
	{
		return array(
			'(string)true' => array('true', true, array()),
			'(string)false' => array('false', false, array()),
			'(string)yes' => array('yes', true, array()),
			'(string)no' => array('no', false, array()),
			'(string)on' => array('on', true, array()),
			'(string)off' => array('off', false, array()),
			'(string)single space' => array(' ', null, array()),
			'(string)multiple spaces' => array('    ', null, array()),
			'(string)newline' => array("\n", null, array()),
			'(string)newline and space' => array(" \n ", null, array()),
			'(string)space true space' => array(' true ', true, array()),
			'(string)%test.replace%' => array('%test.replace%', 'fooo', array('test.replace' => 'fooo')),
			'(int)5' => array(5, 5, array())
		);
	}
	
	/**
	 * @dataProvider pathData
	 */
	public function testIsPathAbsolute($path, $expected)
	{
		if($expected) {
			$this->assertTrue(AgaviToolkit::isPathAbsolute($path));
		} else {
			$this->assertFalse(AgaviToolkit::isPathAbsolute($path));
		}
	}
	
	public function pathData()
	{
		$data = array(
			'c:/' => array('c:/', true),
			'c:\\' => array('c:\\', true),
			'c:/Windows' => array('c:/Windows', true),
			'g:/Windows/bar' => array('g:/Windows/bar', true),
			'c:\\windows\\foo' => array('c:\\windows\\foo', true),
			// UNC paths are absolute too
			'(unc)\\\\some.host' => array('\\\\some.host', true),
			'(unc)\\\\some.host\\foo' => array('\\\\some.host\\foo', true),
			'(unc)\\some.host\\foo' => array('\\some.host\\foo', false),
			
			'/' => array('/', true),
			'/root' => array('/root', true),
			'/FoO/bAR' => array('/FoO/bAR', true),
			'./FoO/bAR' => array('./FoO/bAR', false),
			'../FoO/bAR' => array('../FoO/bAR', false),
			
			// (php does not support backslashes on *nix)
			'\\foo' => array('\\foo', false),
			'\\foo\\bar' => array('\\foo\\bar', false),
			
			'c:' => array('c:', false),
			's/foo/bar' => array('s/foo/bar', false),
			'c:foo' => array('c:foo', false)
		);
		foreach($data as $key => $value) {
			$data['file://' . $key] = array('file://' . $value[0], $value[1]);
		}
		return $data;
	}
}
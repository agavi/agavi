<?php

if(!class_exists('AgaviConfig')) {
	include('../../../../src/config/AgaviConfig.class.php');
}

class AgaviConfigTest extends PHPUnit_Framework_TestCase
{
	/**
	 * Constructs a test case with the given name.
	 *
	 * @param  string $name
	 * @param  array  $data
	 * @param  string $dataName
	 */
	public function __construct($name = NULL, array $data = array(), $dataName = '')
	{
		parent::__construct($name, $data, $dataName);
		// $this->setRunTestInSeparateProcess(true);
	}

	public function testInitiallyEmpty()
	{
		$this->assertEquals(array(), AgaviConfig::toArray());
		$this->assertNull(AgaviConfig::get('something'));
	}

	/**
	 * @dataProvider providerGetSet
	 */
	public function testGetSet($key, $value)
	{
		$this->assertTrue(AgaviConfig::set($key, $value));
		$this->assertEquals($value, AgaviConfig::get($key));
	}
	public function providerGetSet()
	{
		return array(
			'string key'                => array('foobar', 'baz'),
			'string key with period'    => array('some.thing', 'ohai'),
			'string key with null byte' => array("\0foo", 'nullbyte'),
			'integer key'               => array(123, 'qwe'),
			'octal number key'          => array(0123, 'yay'),
		);
	}

	public function testHas()
	{
		AgaviConfig::set('fubar', '123qwe');
		$this->assertTrue(AgaviConfig::has('fubar'));
	}

	public function testClear()
	{
		AgaviConfig::clear();
		$this->assertEquals(array(), AgaviConfig::toArray());
	}

	public function testRemove()
	{
		AgaviConfig::set('opa', 'yay');
		$this->assertTrue(AgaviConfig::remove('opa'));
		$this->assertFalse(AgaviConfig::remove('blu'));
		$this->assertFalse(AgaviConfig::has('opa'));
		$this->assertFalse(AgaviConfig::has('blu'));
	}

	public function testFromArray()
	{
		$data = array('foo' => 'bar', 'bar' => 'baz');
		AgaviConfig::clear();
		AgaviConfig::fromArray($data);
		$this->assertEquals($data, AgaviConfig::toArray());
	}

	public function testFromArrayMerges()
	{
		$data = array('foo' => 'bar', 'bar' => 'baz');
		AgaviConfig::clear();
		AgaviConfig::set('baz', 'lol');
		AgaviConfig::fromArray($data);
		$this->assertEquals(array('baz' => 'lol') + $data, AgaviConfig::toArray());
	}

	public function testFromArrayMergesAndOverwrites()
	{
		$data = array('foo' => 'bar', 'bar' => 'baz', 'baz' => 'qux');
		AgaviConfig::clear();
		AgaviConfig::set('baz', 'lol');
		AgaviConfig::fromArray($data);
		$this->assertEquals(array('baz' => 'qux') + $data, AgaviConfig::toArray());
	}

	public function testHasNullValue()
	{
		AgaviConfig::set('fubar', null);
		$this->assertTrue(AgaviConfig::has('fubar'));
		$this->assertFalse(AgaviConfig::has('fubaz'));
	}

	public function testGetDefault()
	{
		AgaviConfig::set('some.where', 'ohai');
		$this->assertEquals('ohai', AgaviConfig::get('some.where'));
		$this->assertEquals('ohai', AgaviConfig::get('some.where', 'bai'));
		$this->assertEquals('bai', AgaviConfig::get('not.there', 'bai'));
	}

	public function testSetOverwrite()
	{
		AgaviConfig::set('foo.bar', '123');
		$this->assertEquals('123', AgaviConfig::get('foo.bar'));
		$this->assertFalse(AgaviConfig::set('foo.bar', '456', false));
		$this->assertEquals('123', AgaviConfig::get('foo.bar'));
		$this->assertTrue(AgaviConfig::set('foo.bar', '456', true));
		$this->assertEquals('456', AgaviConfig::get('foo.bar'));
		$this->assertTrue(AgaviConfig::set('foo.bar', '789'));
		$this->assertEquals('789', AgaviConfig::get('foo.bar'));
	}

	public function testSetReadonly()
	{
		AgaviConfig::set('bulletproof', 'abc', true, true);
		$this->assertEquals('abc', AgaviConfig::get('bulletproof'));
		$this->assertFalse(AgaviConfig::set('bulletproof', '123'));
		$this->assertEquals('abc', AgaviConfig::get('bulletproof'));
		$this->assertFalse(AgaviConfig::set('bulletproof', '123', true));
		$this->assertEquals('abc', AgaviConfig::get('bulletproof'));
		$this->assertFalse(AgaviConfig::set('bulletproof', '123', true, true));
		$this->assertEquals('abc', AgaviConfig::get('bulletproof'));
	}

	public function testIsReadonly()
	{
		AgaviConfig::set('WORM', 'yay', true, true);
		AgaviConfig::set('WMRM', 'yay');
		$this->assertTrue(AgaviConfig::isReadonly('WORM'));
		$this->assertFalse(AgaviConfig::isReadonly('WMRM'));
	}

	public function testReadonlySurvivesClear()
	{
		AgaviConfig::set('WORM', 'yay', true, true);
		AgaviConfig::set('WMRM', 'yay');
		AgaviConfig::clear();
		$this->assertTrue(AgaviConfig::has('WORM'));
		$this->assertFalse(AgaviConfig::has('WMRM'));
	}

	public function testFromArrayMergesButDoesNotOverwriteReadonlies()
	{
		$data = array('foo' => 'bar', 'bar' => 'baz', 'baz' => 'qux');
		AgaviConfig::clear();
		AgaviConfig::set('baz', 'lol', true, true);
		AgaviConfig::fromArray($data);
		$this->assertEquals(array('baz' => 'lol', 'WORM' => 'yay', 'bulletproof' => 'abc') + $data, AgaviConfig::toArray());
		// once we use test isolation, use this one instead
		// $this->assertEquals(array('baz' => 'lol') + $data, AgaviConfig::toArray());
	}

	public function testReadonlySurvivesRemove()
	{
		AgaviConfig::set('bla', 'goo', true, true);
		$this->assertFalse(AgaviConfig::remove('bla'));
		$this->assertTrue(AgaviConfig::has('bla'));
	}
	

}
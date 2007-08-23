<?php

class AgaviConfigTest extends AgaviTestCase
{
	protected $config = null;

	public function setUp()
	{
		$this->config = AgaviConfig::toArray();
	}

	public function tearDown()
	{
		AgaviConfig::clear();
		AgaviConfig::fromArray($this->config);
		$this->config = null;
	}

	public function testGet()
	{
		$this->assertNull(AgaviConfig::get('some.unset.value'));
		$this->assertSame('default', AgaviConfig::get('some.unset.value', 'default'));
		AgaviConfig::set('some.unset.value', 12345);
		$this->assertSame(12345, AgaviConfig::get('some.unset.value', 'default'));
	}

	public function testHas()
	{
		$this->assertFalse(AgaviConfig::has('some.unset.value'));
		AgaviConfig::set('some.unset.value', true);
		$this->assertTrue(AgaviConfig::has('some.unset.value'));
	}

	public function testReadOnly()
	{
		AgaviConfig::set('some.readonly.value', 'read', true, true);
		$this->assertSame('read', AgaviConfig::get('some.readonly.value'));
		$this->assertTrue(AgaviConfig::isReadonly('some.readonly.value'));
		$this->assertFalse(AgaviConfig::set('some.readonly.value', 'write'));
		$this->assertSame('read', AgaviConfig::get('some.readonly.value'));
		$conf = AgaviConfig::toArray();
		$conf['some.readonly.value'] = 'write';
		AgaviConfig::fromArray($conf);
		$this->assertSame('read', AgaviConfig::get('some.readonly.value'));

		AgaviConfig::clear();
		$this->assertSame('read', AgaviConfig::get('some.readonly.value'));
	}

	public function testSet()
	{
		$ret = AgaviConfig::set('some.value', 'foo', false);
		$this->assertTrue($ret);
		$this->assertSame('foo', AgaviConfig::get('some.value'));

		$ret = AgaviConfig::set('some.value', 'bar', false);
		$this->assertFalse($ret);
		$this->assertSame('foo', AgaviConfig::get('some.value'));

		$ret = AgaviConfig::set('some.value', 'bar', true);
		$this->assertTrue($ret);
		$this->assertSame('bar', AgaviConfig::get('some.value'));

		$ret = AgaviConfig::set('some.value', 'baz');
		$this->assertTrue($ret);
		$this->assertSame('baz', AgaviConfig::get('some.value'));

		// readonly is completely tested in testReadOnly()
	}

	public function testRemove()
	{
		AgaviConfig::set('some.value', 'foo');
		$this->assertTrue(AgaviConfig::has('some.value'));
		$ret = AgaviConfig::remove('some.value');
		$this->assertTrue($ret);
		$this->assertFalse(AgaviConfig::has('some.value'));

		AgaviConfig::set('some.readonly.value', 'read', true, true);
		$this->assertTrue(AgaviConfig::has('some.readonly.value'));
		$ret = AgaviConfig::remove('some.readonly.value');
		$this->assertFalse($ret);
		$this->assertTrue(AgaviConfig::has('some.readonly.value'));
	}

	public function testFromArrayToArray()
	{
		AgaviConfig::fromArray(array('some.value.one' => 'foo', 'some.value.two' => 'bar'));
		$this->assertSame('foo', AgaviConfig::get('some.value.one'));
		$this->assertSame('bar', AgaviConfig::get('some.value.two'));
		$conf = AgaviConfig::toArray();
		$this->assertSame('foo', $conf['some.value.one']);
		$this->assertSame('bar', $conf['some.value.two']);
	}

	public function testClear()
	{
		AgaviConfig::set('some.value', 'foo');
		AgaviConfig::set('some.other.value', 'bar');
		$this->assertTrue(AgaviConfig::has('some.value'));
		$this->assertTrue(AgaviConfig::has('some.other.value'));
		AgaviConfig::clear();
		$this->assertFalse(AgaviConfig::has('some.value'));
		$this->assertFalse(AgaviConfig::has('some.other.value'));
	}

}

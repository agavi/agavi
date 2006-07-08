<?php

class MyPath extends AgaviPath
{
	public function setAbsolute($state) { $this->Absolute = $state; }
	public function setDirs($dirs) { $this->Dirs = $dirs; }
	public function getDirs() { return $this->Dirs; }
	public function cleanPath2() { $this->cleanPath(); }
}

class PathTest extends AgaviTestCase
{
	public function testconstruct()
	{
		$p = new AgaviPath('/test/foo/bar');
		$this->assertEquals($p->isAbsolute(), true);
		$this->assertEquals($p->length(), 3);
		
		$p = new AgaviPath('foo/bar');
		$this->assertEquals($p->isAbsolute(), false);
		$this->assertEquals($p->length(), 2);
		
		$p = new AgaviPath('');
		$this->assertEquals($p->isAbsolute(), false);
		$this->assertEquals($p->length(), 0);
	}
	
	public function testcleanPath()
	{
		$p = new MyPath('');
		$p->setDirs(array('foo', 'bar'));
		$p->cleanPath2();
		$this->assertEquals($p->getDirs(), array('foo', 'bar'));
		
		$p->setDirs(array('foo', '', '.', 'bar'));
		$p->cleanPath2();
		$this->assertEquals($p->getDirs(), array('foo', 'bar'));
		
		$p->setDirs(array('foo', 'bar', 'foobar', '..', 'test'));
		$p->cleanPath2();
		$this->assertEquals($p->getDirs(), array('foo', 'bar', 'test'));
		
		$p->setAbsolute(false);
		$p->setDirs(array('..', '..', 'foo'));
		$p->cleanPath2();
		$this->assertEquals($p->getDirs(), array('..', '..', 'foo'));
		
		$p->setAbsolute(true);
		$p->setDirs(array('..', '..', 'foo'));
		$p->cleanPath2();
		$this->assertEquals($p->getDirs(), array('foo'));
	}
	
	public function testisAbsolute()
	{
		$p = new MyPath('');
		$p->setAbsolute(false);
		$this->assertEquals($p->isAbsolute(), false);
		$p->setAbsolute(true);
		$this->assertEquals($p->isAbsolute(), true);
	}
	
	public function testtoString()
	{
		$p = new MyPath('');
		$p->setAbsolute(true);
		$p->setDirs(array('foo', 'bar'));
		$this->assertEquals($p->__toString(), '/foo/bar');
		$p->setAbsolute(false);
		$this->assertEquals($p->__toString(), 'foo/bar');
		$p->setDirs(array());
		$this->assertEquals($p->__toString(), '');
		$p->setAbsolute(true);
		$this->assertEquals($p->__toString(), '/');
	}
	
	public function testlength()
	{
		$p = new AgaviPath('foo/bar');
		$this->assertEquals($p->length(), 2);
		$p = new AgaviPath('');
		$this->assertEquals($p->length(), 0);
	}
	
	public function testleft()
	{
		$p = new AgaviPath('foo/bar');
		$this->assertEquals($p->left(), 'foo');
		$this->assertEquals($p->left(true), 'foo');

		$p = new AgaviPath('/foo/bar');
		$this->assertEquals($p->left(), 'foo');
		$this->assertEquals($p->left(true), '/foo');
		
		$p = new AgaviPath('/0/test');
		$this->assertSame($p->left(), 0);
		$this->assertSame($p->left(true), '/0');
		
		$p = new AgaviPath('');
		$this->assertNull($p->left());
	}
	
	public function testpop()
	{
		$p = new AgaviPath('foo/bar');
		$this->assertEquals($p->pop(), 'bar');
		$this->assertEquals($p->pop(), 'foo');
		$this->assertNull($p->pop());
		
		$p = new AgaviPath('/0');
		$this->assertSame($p->pop(), 0);
	}
	
	public function testpush()
	{
		$p = new AgaviPath('');
		$p->push('foo/../foo/bar');
		$this->assertEquals($p->__toString(), 'foo/bar');
	}
	
	public function testshift()
	{
		$p = new AgaviPath('foo/bar');
		$this->assertEquals($p->shift(), 'foo');
		$this->assertEquals($p->shift(), 'bar');
		
		$p = new AgaviPath('foo/bar');
		$this->assertEquals($p->shift(true), 'foo');
		
		$p = new AgaviPath('/foo/bar');
		$this->assertEquals($p->shift(), 'foo');
		$this->assertEquals($p->isAbsolute(), false);
		
		$p = new AgaviPath('/foo/bar');
		$this->assertEquals($p->shift(true), '/foo');
		
		$p = new AgaviPath('/foo/0');
		$this->assertEquals($p->shift(), 'foo');
		$this->assertSame($p->shift(), 0);
		$this->assertNull($p->shift());
	}
	
	public function testgetValueByPath()
	{
		$array = array(
			'foo',
			'test'	=> 1,
			'foo'	=> array(
				'bar'	=> 2,
				0 => 3
			)
		);
		
		$this->assertEquals(AgaviPath::getValueByPath($array, '/test'), 1);
		$this->assertEquals(AgaviPath::getValueByPath($array, '/foo'), array('bar' => 2, 3));
		$this->assertEquals(AgaviPath::getValueByPath($array, '/foo/bar'), 2);
		$this->assertEquals(AgaviPath::getValueByPath($array, '/0'), 'foo');
		$this->assertEquals(AgaviPath::getValueByPath($array, '/foo/0'), 3);
		$this->assertNull(AgaviPath::getValueByPath($array, '/foo/bar/foobar'));
		$this->assertEquals(AgaviPath::getValueByPath($array, '/foo/bar/foobar', 5), 5);
		$this->assertSame(AgaviPath::getValueByPath($array, '/foo/bar'), $array['foo']['bar']);
	}
	
	public function testsetValueByPath()
	{
		$array = array(
			'test'	=> 1,
			'foo'	=> array(
				'bar'	=> 2
			)
		);

		AgaviPath::setValueByPath($array, '/test', 2);
		$this->assertEquals($array['test'], 2);
		AgaviPath::setValueByPath($array, '/foo/bar/foobar/test1/test2', 2);
		$this->assertEquals($array['foo']['bar']['foobar']['test1']['test2'], 2);
		
	}
}
?>

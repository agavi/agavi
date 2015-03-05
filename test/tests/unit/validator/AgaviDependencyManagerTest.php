<?php

class MyDependencyManager extends AgaviDependencyManager
{
	public function setDepData($data) { $this->depData = $data; }
}

class AgaviDependencyManagerTest extends AgaviUnitTestCase
{
	public function testclear()
	{
		$m = new MyDependencyManager;
		
		$m->setDepData(array(1));
		$m->clear();
		$this->assertEquals($m->getDependTokens(), array());
	}
	
	public function testcheckDependencies()
	{
		$m = new MyDependencyManager;
		$m->setDepData(array('foo' => true, 'bar' => true));
		
		$this->assertEquals($m->checkDependencies(array('foo', 'bar'), new AgaviVirtualArrayPath('')), true);
		$this->assertEquals($m->checkDependencies(array('foo'), new AgaviVirtualArrayPath('')), true);
		$this->assertEquals($m->checkDependencies(array('foo', 'bar', 'foobar'), new AgaviVirtualArrayPath('')), false);
		$this->assertEquals($m->checkDependencies(array('foo'), new AgaviVirtualArrayPath('')), true);
		$this->assertEquals($m->checkDependencies(array('%s[foo]'), new AgaviVirtualArrayPath('bar')), false);
		
		$m->setDepData(array('foo' => array('bar' => true)));
		$this->assertEquals($m->checkDependencies(array('foo'), new AgaviVirtualArrayPath('')), true);
		$this->assertEquals($m->checkDependencies(array('%s[bar]'), new AgaviVirtualArrayPath('foo')), true);
		$this->assertEquals($m->checkDependencies(array(), new AgaviVirtualArrayPath('')), true);
	}
	
	public function testaddDependTokens()
	{
		$m = new MyDependencyManager;
		
		$m->addDependTokens(array('foo', 'bar'), new AgaviVirtualArrayPath(''));
		$this->assertEquals($m->getDependTokens(), array('foo' => true, 'bar' => true));
		$m->addDependTokens(array('%s[test]', '%s[test2]'), new AgaviVirtualArrayPath('foobar'));
		$this->assertEquals($m->getDependTokens(), array('foo' => true, 'bar' => true, 'foobar' => array('test' => true, 'test2' => true)));
	}
}
?>

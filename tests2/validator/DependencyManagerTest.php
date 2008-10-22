<?php

class MyDependencyManager extends AgaviDependencyManager
{
	public function setDepData($data) { $this->DepData = $data; }
	public function getDepData() { return $this->DepData; }
}

class DependencyManagerTest extends AgaviTestCase
{
	public function testclear()
	{
		$m = new MyDependencyManager;
		
		$m->setDepData(array(1));
		$m->clear();
		$this->assertEquals($m->getDepData(), array());
	}
	
	public function testcheckDependencies()
	{
		$m = new MyDependencyManager;
		$m->setDepData(array('foo' => true, 'bar' => true));
		
		$this->assertEquals($m->checkDependencies(array('foo', 'bar')), true);
		$this->assertEquals($m->checkDependencies(array('foo')), true);
		$this->assertEquals($m->checkDependencies(array('foo', 'bar', 'foobar')), false);
		$this->assertEquals($m->checkDependencies(array('foo'), '/'), true);
		$this->assertEquals($m->checkDependencies(array('foo'), '/bar'), false);
		
		$m->setDepData(array('foo' => array('bar' => true)));
		$this->assertEquals($m->checkDependencies(array('foo')), true);
		$this->assertEquals($m->checkDependencies(array('bar'), '/foo'), true);
		$this->assertEquals($m->checkDependencies(array()), true);
	}
	
	public function testaddDependTokens()
	{
		$m = new MyDependencyManager;
		
		$m->addDependTokens(array('foo', 'bar'));
		$this->assertEquals($m->getDepData(), array('foo' => true, 'bar' => true));
		$m->addDependTokens(array('test', 'test2'), '/foobar');
		$this->assertEquals($m->getDepData(), array('foo' => true, 'bar' => true, 'foobar' => array('test' => true, 'test2' => true)));
	}
}
?>

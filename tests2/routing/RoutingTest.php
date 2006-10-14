<?php

class SampleRouting extends AgaviRouting
{
	public function setInput($input)
	{
		$this->input = $input;
	}

	public function loadConfig($cfg, $ctx = null)
	{
		include(AgaviConfigCache::checkConfig($cfg, $ctx));
	}
}

class RoutingTest extends AgaviTestCase
{
	protected $_r = null;

	public function setUp()
	{
		$response = new AgaviWebResponse();
		$response->initialize(AgaviContext::getInstance('test'));
		$this->_r = new SampleRouting();
		$this->_r->initialize($response);
		AgaviConfig::set('core.use_routing', true);

	}

	public function testGetContext()
	{
		$ctx = AgaviContext::getInstance('test');
		$ctx_test = $this->_r->getContext();
		$this->assertReference($ctx, $ctx_test);
	}

	public function testSimple()
	{
		$r = $this->_r;
		$r->loadConfig(AgaviConfig::get('core.config_dir') . '/tests/routing_simple.xml', 'test1');

		$rq = $r->getContext()->getRequest();

		$rq->clearParameters();
		$this->assertEquals(array(), $rq->getParameters());

		$r->setInput('/anchor/child3/child2');
		$this->assertEquals(array('testWithChild', 't1child3'), $r->execute());
		$this->assertEquals(3, count($rq->getParameters()));
		$this->assertEquals('module3', $rq->getParameter('module'));
		$this->assertEquals('action3', $rq->getParameter('action'));
		$this->assertEquals('child2', $rq->getParameter('bar'));

		$rq->clearParameters();
		$r->setInput('/anchor/child4/nextChild');
		$this->assertEquals(array('testWithChild', 't1child4'), $r->execute());
		$this->assertEquals(3, count($rq->getParameters()));
		$this->assertEquals('module4', $rq->getParameter('module'));
		$this->assertEquals('action4', $rq->getParameter('action'));
		$this->assertEquals('nextChild', $rq->getParameter('bar'));

		$rq->clearParameters();
		$r->setInput('/anchor/child4/');
		$this->assertEquals(array('testWithChild', 't1child4'), $r->execute());
		$this->assertEquals(3, count($rq->getParameters()));
		$this->assertEquals('module4', $rq->getParameter('module'));
		$this->assertEquals('action4', $rq->getParameter('action'));
		$this->assertEquals('baz', $rq->getParameter('bar'));


		$this->assertEquals('/anchor/child1', $r->gen('t1child1'));
		$this->assertEquals('/anchor/child2', $r->gen('t1child2'));
		$this->assertEquals('/anchor/bar', $r->gen('t1child2', array('foo' => 'bar')));
		$this->assertEquals('/anchor/child3/baz', $r->gen('t1child3', array('bar' => 'baz')));
		$this->assertEquals('/anchor/child4/baz', $r->gen('t1child4'));
		$this->assertEquals('/anchor/foo/bar', $r->gen('t1child4', array('foo' => 'foo', 'bar' => 'bar')));

		$r->loadConfig(AgaviConfig::get('core.config_dir') . '/tests/routing_simple.xml', 'test2');

		$rq->clearParameters();
		$r->setInput('/parent/category1/MACHINE/');
		$this->assertEquals(array('test2parent', 'test2child1'), $r->execute());
		$this->assertEquals(4, count($rq->getParameters()));
		$this->assertEquals('category1', $rq->getParameter('category'));
		$this->assertEquals('MACHINE', $rq->getParameter('machine'));

		$rq->clearParameters();
		$r->setInput('/parent/MACHINE/');
		$this->assertEquals(array('test2parent', 'test2child1'), $r->execute());
		$this->assertEquals(3, count($rq->getParameters()));
//		$this->assertEquals('', $rq->getParameter('category'));
		$this->assertEquals('MACHINE', $rq->getParameter('machine'));

		$rq->clearParameters();
		$r->setInput('/parent/MACHINE');
		$this->assertEquals(array('test2parent', 'test2child1'), $r->execute());
		$this->assertEquals(3, count($rq->getParameters()));
		//$this->assertEquals('', $rq->getParameter('category'));
		$this->assertEquals('MACHINE', $rq->getParameter('machine'));

		$this->assertsame('/parent/MACHINE', $r->gen('test2child1', array('machine' => 'MACHINE')));
		$this->assertEquals('/parent/cat1/MACHINE', $r->gen('test2child1', array('machine' => 'MACHINE', 'category' => 'cat1')));
	}

	public function testErrors()
	{
		$r = $this->_r;

		try {
			$r->loadConfig(AgaviConfig::get('core.config_dir') . '/tests/routing_errors.xml', 'SameNameDirectChild');
			$this->fail('Expected AgaviException not thrown for declaring direct childs with the same name!');
		} catch(AgaviException $e) {
		}

		try {
			$r->loadConfig(AgaviConfig::get('core.config_dir') . '/tests/routing_errors.xml', 'SameNameIndirectChild');
			$this->fail('Expected AgaviException not thrown for declaring indirect childs with the same name!');
		} catch(AgaviException $e) {
		}

		try {
			$r->loadConfig(AgaviConfig::get('core.config_dir') . '/tests/routing_errors.xml', 'SameNameInOverwrittenHierarchy');
			$this->fail('Expected AgaviException not thrown for declaring childs with the same name when inserting a new child hierarchy on overwriting a pattern!');
		} catch(AgaviException $e) {
		}

	}

}

?>
<?php

class SampleRouting extends AgaviRouting
{
	public function setInput($input)
	{
		$this->input = $input;
	}

	public function loadTestConfig($cfg, $ctx = null)
	{
		include(AgaviConfigCache::checkConfig($cfg, $ctx));
	}
}

class RoutingTest extends AgaviTestCase
{
	protected $_r = null;

	public function setUp()
	{
		$this->_r = new SampleRouting();
		$this->_r->initialize(AgaviContext::getInstance('test'));
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
		$r->loadTestConfig(AgaviConfig::get('core.config_dir') . '/tests/routing_simple.xml', 'test1');

		$rq = $r->getContext()->getRequest();
		$rd = $rq->getRequestData();

		$rd->clearParameters();
		$this->assertEquals(array(), $rd->getParameters());

		$r->setInput('/anchor/child3/child2');
		$r->execute();
		$this->assertEquals(array('testWithChild', 't1child3'), $rq->getAttribute('matched_routes', 'org.agavi.routing'));
		$this->assertEquals(3, count($rd->getParameters()));
		$this->assertEquals('module3', $rd->getParameter('module'));
		$this->assertEquals('action3', $rd->getParameter('action'));
		$this->assertEquals('child2', $rd->getParameter('bar'));

		$r->loadTestConfig(AgaviConfig::get('core.config_dir') . '/tests/routing_simple.xml', 'test1');

		$rd->clearParameters();
		$r->setInput('/anchor/child4/nextChild');
		$r->execute();
		$this->assertEquals(array('testWithChild', 't1child4'), $rq->getAttribute('matched_routes', 'org.agavi.routing'));
		$this->assertEquals(3, count($rd->getParameters()));
		$this->assertEquals('module4', $rd->getParameter('module'));
		$this->assertEquals('action4', $rd->getParameter('action'));
		$this->assertEquals('nextChild', $rd->getParameter('bar'));

		$r->loadTestConfig(AgaviConfig::get('core.config_dir') . '/tests/routing_simple.xml', 'test1');

		$rd->clearParameters();
		$r->setInput('/anchor/child4/');
		$r->execute();
		$this->assertEquals(array('testWithChild', 't1child4'), $rq->getAttribute('matched_routes', 'org.agavi.routing'));
		$this->assertEquals(3, count($rd->getParameters()));
		$this->assertEquals('module4', $rd->getParameter('module'));
		$this->assertEquals('action4', $rd->getParameter('action'));
		$this->assertEquals('baz', $rd->getParameter('bar'));



		$this->assertEquals(array('/anchor/child1'), array_slice($r->gen('t1child1'), 0, 1));
		$this->assertEquals(array('/anchor/child2'), array_slice($r->gen('t1child2'), 0, 1));
		$this->assertEquals(array('/anchor/bar'), array_slice($r->gen('t1child2', array('foo' => 'bar')), 0, 1));
		$this->assertEquals(array('/anchor/child3/baz'), array_slice($r->gen('t1child3', array('bar' => 'baz')), 0, 1));
		$this->assertEquals(array('/anchor/child4/baz'), array_slice($r->gen('t1child4'), 0, 1));
		$this->assertEquals(array('/anchor/child4/'), array_slice($r->gen('t1child4', array(), array('omit_defaults' => true)), 0, 1));
		$this->assertEquals(array('/anchor/foo/bar'), array_slice($r->gen('t1child4', array('foo' => 'foo', 'bar' => 'bar')), 0, 1));

		$r->loadTestConfig(AgaviConfig::get('core.config_dir') . '/tests/routing_simple.xml', 'test2');

		$rd->clearParameters();
		$r->setInput('/parent/category1/MACHINE/');
		$r->execute();
		$this->assertEquals(array('test2parent', 'test2child1'), $rq->getAttribute('matched_routes', 'org.agavi.routing'));
		$this->assertEquals(4, count($rd->getParameters()));
		$this->assertEquals('category1', $rd->getParameter('category'));
		$this->assertEquals('MACHINE', $rd->getParameter('machine'));

		$r->loadTestConfig(AgaviConfig::get('core.config_dir') . '/tests/routing_simple.xml', 'test2');
		$rd->clearParameters();
		$r->setInput('/parent/MACHINE/');
		$r->execute();
		$this->assertEquals(array('test2parent', 'test2child1'), $rq->getAttribute('matched_routes', 'org.agavi.routing'));
		$this->assertEquals(3, count($rd->getParameters()));
		$this->assertEquals('MACHINE', $rd->getParameter('machine'));

		$r->loadTestConfig(AgaviConfig::get('core.config_dir') . '/tests/routing_simple.xml', 'test2');
		$rd->clearParameters();
		$r->setInput('/parent/MACHINE');
		$r->execute();
		$this->assertEquals(array('test2parent', 'test2child1'), $rq->getAttribute('matched_routes', 'org.agavi.routing'));
		$this->assertEquals(3, count($rd->getParameters()));
		$this->assertEquals('MACHINE', $rd->getParameter('machine'));

		$this->assertsame(array('/parent/MACHINE'), array_slice($r->gen('test2child1', array('category' => null, 'machine' => 'MACHINE')), 0, 1));
		$this->assertsame(array('/parent/MACHINE'), array_slice($r->gen('test2child1', array('machine' => 'MACHINE')), 0, 1));
		$this->assertEquals(array('/parent/cat1/MACHINE'), array_slice($r->gen('test2child1', array('machine' => 'MACHINE', 'category' => 'cat1')), 0, 1));
	}

	public function testErrors()
	{
		$r = $this->_r;

		try {
			$r->loadTestConfig(AgaviConfig::get('core.config_dir') . '/tests/routing_errors.xml', 'SameNameDirectChild');
			$this->fail('Expected AgaviException not thrown for declaring direct childs with the same name!');
		} catch(AgaviException $e) {
		}

		try {
			$r->loadTestConfig(AgaviConfig::get('core.config_dir') . '/tests/routing_errors.xml', 'SameNameIndirectChild');
			$this->fail('Expected AgaviException not thrown for declaring indirect childs with the same name!');
		} catch(AgaviException $e) {
		}

		try {
			$r->loadTestConfig(AgaviConfig::get('core.config_dir') . '/tests/routing_errors.xml', 'SameNameInOverwrittenHierarchy');
			$this->fail('Expected AgaviException not thrown for declaring childs with the same name when inserting a new child hierarchy on overwriting a pattern!');
		} catch(AgaviException $e) {
		}

	}

}

?>
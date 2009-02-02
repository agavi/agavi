<?php

class SampleRouting extends AgaviRouting
{
	public function setInput($input)
	{
		$this->input = $input;
	}

	public function loadTestConfig($cfg, $ctx = null)
	{
		$this->importRoutes(unserialize(file_get_contents(AgaviConfigCache::checkConfig($cfg, $ctx))));
	}
}

class BaseTestCallback extends AgaviRoutingCallback
{
	public function onGenerate(array $defaultParameters, array &$userParameters, array &$userOptions)
	{
		if(count($userParameters)) {
			foreach($userParameters as $key => $param) {
				if($param == 'null') {
					$userParameters[$key] = null;
				} elseif($param == 'remove') {
					unset($userParameters[$key]);
				}
			}
		}
		return true;
	}
}

class TestCallbackLAN extends AgaviRoutingCallback
{
	public function onGenerate(array $defaultParameters, array &$userParameters, array &$userOptions)
	{
		if(!array_key_exists('language', $userParameters)) {
			$userParameters['language'] = 'au';
		}
		return true;
	}
}
class TestCallbackRAN extends AgaviRoutingCallback
{
	public function onMatched(array &$parameters, AgaviExecutionContainer $container)
	{
		return false;
	}
}
class TestCallbackParent extends BaseTestCallback
{
}
class TestCallbackCN extends BaseTestCallback
{
}
class TestCallbackCC extends BaseTestCallback
{
}
class TestCallbackCS extends BaseTestCallback
{
}


class RoutingTest extends AgaviTestCase
{
	protected $_r = null;
	protected $_config = null;
	protected $_context = null;

	public function setUp()
	{
		AgaviConfig::set('core.use_routing', true);
		$this->_r = new SampleRouting();
		$this->_r->initialize(AgaviContext::getInstance('test'));
	}
	
	public function tearDown()
	{
		AgaviConfig::set('core.use_routing', false);
	}

	protected function setConfig($config, $context = 'test')
	{
		$this->_config = AgaviConfig::get('core.config_dir') . '/tests/' . $config;
		$this->_context = $context;
		$this->initConfig();
	}

	protected function initConfig()
	{
		$this->_r->loadTestConfig($this->_config, $this->_context);
	}

	protected function doTestMatch($input, $routes, $parameters, $message = null)
	{
		$this->initConfig();
		$r = $this->_r;
		$rq = $r->getContext()->getRequest();
		$rd = $rq->getRequestData();

		$rd->clearParameters();

		$r->setInput($input);
		$r->execute();

		$this->assertEquals($routes, $rq->getAttribute('matched_routes', 'org.agavi.routing'), $message);
		$this->assertEquals($parameters, $rd->getParameters(), $message);
	}

	public function doTestGen($url, $route, $params = array(), $options = array(), $message = null)
	{
		$result = $this->_r->gen($route, $params, $options);
		$this->assertEquals($url, $result[0], $message);
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
		$rq->setParameter('use_module_action_parameters', true);
		$rd = $rq->getRequestData();

		$rd->clearParameters();
		$this->assertEquals(array(), $rd->getParameters());

		$this->setConfig('routing_simple.xml', 'test1');

		$this->doTestMatch('/anchor/child3/child2', array('testWithChild', 't1child3'), array(
			'module' => 'module3', 'action' => 'action3',
			'bar' => 'child2',
		), 'Input matching a route and children');

		$this->doTestMatch('/anchor/child4/nextChild', array('testWithChild', 't1child4'), array(
			'module' => 'module4', 'action' => 'action4',
			'bar' => 'nextChild',
		), 'Input matching a route and children');

		$this->doTestMatch('/anchor/child4/', array('testWithChild', 't1child4'), array(
			'module' => 'module4', 'action' => 'action4',
			'bar' => 'baz',
		), 'Input matching a route and children');

		$this->doTestGen('/anchor/child1', 't1child1');
		$this->doTestGen('/anchor/child2', 't1child2');
		$this->doTestGen('/anchor/bar', 't1child2', array('foo' => 'bar'));
		$this->doTestGen('/anchor/child3/baz', 't1child3', array('bar' => 'baz'));
		$this->doTestGen('/anchor/child4/baz', 't1child4');
		$this->doTestGen('/anchor/child4/', 't1child4', array(), array('omit_defaults' => true));
		$this->doTestGen('/anchor/foo/bar', 't1child4', array('foo' => 'foo', 'bar' => 'bar'));


		$this->setConfig('routing_simple.xml', 'test2');

		$this->doTestMatch('/parent/category1/MACHINE/', array('test2parent', 'test2child1'), array(
			'module' => 't1Module1', 'action' => 't2Action1',
			'category' => 'category1',
			'machine' => 'MACHINE',
		), 'Input matching a route and children');

		$this->doTestMatch('/parent/MACHINE/', array('test2parent', 'test2child1'), array(
			'module' => 't1Module1', 'action' => 't2Action1',
			'machine' => 'MACHINE',
		), 'Input matching a route and children');

		$this->doTestMatch('/parent/MACHINE', array('test2parent', 'test2child1'), array(
			'module' => 't1Module1', 'action' => 't2Action1',
			'machine' => 'MACHINE',
		), 'Input matching a route and children');

		$this->doTestGen('/parent/MACHINE', 'test2child1', array('category' => null, 'machine' => 'MACHINE'));
		$this->doTestGen('/parent/MACHINE', 'test2child1', array('machine' => 'MACHINE'));
		$this->doTestGen('/parent/cat1/MACHINE', 'test2child1', array('machine' => 'MACHINE', 'category' => 'cat1'));

		$this->setConfig('routing_callbacks.xml', 'test_callbacks');

		$this->doTestMatch('/parent/opt,35/p1/part1match/p2/', array('parent', 'child_complex'), array(
			'module' => 'module_parent', 'action' => 'action_child_complex',
			'parent_id' => '23',
			'optional1' => '35',
			'part1' => 'part1match',
		), 'Matching complex route');
		
		$this->doTestGen('/parent/23/opt,35/p1/part1match/p2/', 'child_complex');
		// this results in the same url since omit defaults only removes matches from right to left
		$this->doTestGen('/parent/23/opt,35/p1/part1match/p2/', 'child_complex', array(), array('omit_defaults' => true));
		$this->doTestGen('/parent/42/opt,35/p1//p2/', 'child_complex', array('parent_id' => 42));
		$this->doTestGen('/parent/42/p1//p2/', 'child_complex', array('parent_id' => 42, 'optional1' => null));
		$this->doTestGen('/parent/42/opt,21/p1//p2/', 'child_complex', array('parent_id' => 42, 'optional1' => '21'));
		// this fails atm
		$this->doTestGen('/parent/42/opt,35/p1//p2/', 'child_complex', array('parent_id' => 42), array('omit_defaults' => true));

		$this->doTestGen('/parent/23/opt,35/p1/p1_given/p2/', 'child_complex', array('parent_id' => 'remove', 'optional1' => 'remove', 'part1' => 'p1_given'));
		$this->doTestGen('/parent/opt,35/p1//p2/part2_given', 'child_complex', array('parent_id' => 'null', 'optional1' => 'remove', 'part1' => 'null', 'part2' => 'part2_given'));
		$this->doTestGen('/parent/p1//p2/part2_given', 'child_complex', array('parent_id' => 'null', 'optional1' => 'null', 'part1' => 'remove', 'part2' => 'part2_given'));

		$this->doTestMatch('/de/parent/42/p1/part1match/p2/part2match', array('left_anchored_nonstop', 'parent', 'child_complex'), array(
			'module' => 'module_parent', 'action' => 'action_child_complex',
			'language' => 'de',
			'parent_id' => '42',
			'optional1' => '35',
			'part1' => 'part1match',
			'part2' => 'part2match',
		), 'Matching complex route');
		$this->doTestGen('/au/parent/42/opt,35/p1/part1match/p2/part2match', 'child_complex+left_anchored_nonstop');

		$this->doTestMatch('/en/parent/42/', array('left_anchored_nonstop', 'parent', 'child_simple'), array(
			'module' => 'module_parent', 'action' => 'action_child_simple',
			'language' => 'en',
			'parent_id' => '42',
			'part1' => 'part1_default',
			'part2' => 'part2_default',
			'part3' => 'part3_default',
		), 'Matching simple route');
		$this->doTestGen('/au/parent/42/part1_default/part2_default/xxpart3_defaultxx/', 'child_simple+left_anchored_nonstop');
		$this->doTestGen('/au/parent/42/', 'child_simple+left_anchored_nonstop', array(), array('omit_defaults' => true));

		$this->doTestMatch('/en/parent/42/title_match', array('left_anchored_nonstop', 'parent', 'child_nonstop', 'child_simple'), array(
			'module' => 'module_parent', 'action' => 'action_child_simple',
			'language' => 'en',
			'parent_id' => '42',
			'title' => 'title_match',
			'part1' => 'part1_default',
			'part2' => 'part2_default',
			'part3' => 'part3_default',
		), 'Matching simple route');
		$this->doTestGen('/au/parent/42/part1_default/part2_default/xxpart3_defaultxx/title_match', 'child_simple+child_nonstop+left_anchored_nonstop');
		$this->doTestGen('/au/parent/42/title_match', 'child_simple+child_nonstop+left_anchored_nonstop', array(), array('omit_defaults' => true));

		$this->doTestMatch('/parent/41/part1_match/part2_match/part3_match/title_match', array('parent', 'child_nonstop', 'child_simple'), array(
			'module' => 'module_parent', 'action' => 'action_child_simple',
			'parent_id' => '41',
			'title' => 'title_match',
			'part1' => 'part1_match',
			'part2' => 'part2_match',
			'part3' => 'part3_match',
		), 'Matching simple route');
		$this->doTestGen('/parent/41/part1_match/part2_match/xxpart3_matchxx/', 'child_simple');
		$this->doTestGen('/parent/41/part1_match/part2_match/xxpart3_matchxx/', 'child_simple', array(), array('omit_defaults' => true));
		$this->doTestGen('/parent/41/part1_given/part2_default/', 'child_simple', array('part1' => 'part1_given', 'part2' => 'remove', 'part3' => 'null'), array('omit_defaults' => false));

		$this->initConfig();

		$this->doTestGen('/parent/23/part1_default/', 'child_simple', array('part1' => 'remove', 'part2' => null, 'part3' => null));
		$this->doTestGen('/parent/23/part1_default/xxpart3_defaultxx/', 'child_simple', array('part1' => 'remove', 'part2' => null, 'part3' => 'remove'));
		$this->doTestGen('/parent/23/part2_default/xxpart3_defaultxx/', 'child_simple', array('part1' => 'null', 'part2' => 'remove', 'part3' => 'remove'));
		$this->doTestGen('/parent/23/xxpart3_defaultxx/', 'child_simple', array('part1' => 'null', 'part2' => 'null', 'part3' => 'remove'));
		$this->doTestGen('/parent/xxpart3_defaultxx/', 'child_simple', array('parent_id' => 'null', 'part1' => 'null', 'part2' => 'null', 'part3' => 'remove'));
		$this->doTestGen('/parent/23/xxpart3_defaultxx/', 'child_simple', array('parent_id' => 'remove', 'part1' => 'null', 'part2' => 'null', 'part3' => 'remove'));
		$this->doTestGen('/parent/', 'child_simple', array('part1' => 'remove', 'part2' => null, 'part3' => null), array('omit_defaults' => true));
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
<?php

class AgaviWebRoutingTest extends AgaviPhpUnitTestCase
{
	protected $routing;
	protected $parameters = array('enabled' => true);
	
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
		$this->setRunTestInSeparateProcess(true);
	}
	
	public function setUp()
	{
		$_SERVER['SCRIPT_NAME'] = ''; // takes care of php setting the commandline scriptname in $_SERVER, throwing the routing off guard
		$this->routing = new AgaviTestingWebRouting();
		$this->routing->initialize(AgaviContext::getInstance(null), $this->parameters);
		$this->routing->startup();
	}
	
	public function testGenDisabled()
	{
		$this->routing->setParameter('enabled', false);
		$url = $this->routing->gen('foo', array('bar' => '/shouldbeencoded'));
		$this->assertEquals('foo?bar=%2Fshouldbeencoded', $url);
	}
	
	public function testGenNonExistingRoute()
	{
		$url = $this->routing->gen('foo', array('bar' => '/shouldbeencoded'));
		$this->assertEquals('foo?bar=%2Fshouldbeencoded', $url);		
	}
	
	public function testGenSimpleRoute()
	{
		$url = $this->routing->gen('index');
		$this->assertEquals('/', $url);		
	}
	
	public function testGenSimpleRouteWithParam()
	{
		$url = $this->routing->gen('index', array('extra' => 'contains spaces'));
		$this->assertEquals('/?extra=contains+spaces', $url);		
	}
	
	public function testGenWithParam()
	{
		$url = $this->routing->gen('with_param', array('number' => 5));
		$this->assertEquals('/withparam/5', $url);		
	}
	
	public function testGenWithParamAndExtraParam()
	{
		$url = $this->routing->gen('with_param', array('number' => 5, 'extra' => 'contains spaces'));
		$this->assertEquals('/withparam/5?extra=contains+spaces', $url);		
	}
	
	public function testGenWithTwoParams()
	{
		$url = $this->routing->gen('with_two_params', array('number' => 5, 'string' => 'needs escaping /'));
		$this->assertEquals('/withmultipleparams/5/needs%20escaping%20%2F', $url);
	}
	
	public function testGenWithTwoParamsAndExtraParam()
	{
		$url = $this->routing->gen('with_two_params', array('number' => 5, 'string' => 'needs escaping /', 'extra' => 'contains spaces'));
		$this->assertEquals('/withmultipleparams/5/needs%20escaping%20%2F?extra=contains+spaces', $url);
	}
	
	public function testGenWithCallback()
	{
		$url = $this->routing->gen('callbacks.gen_with_param', array('number' => 5));
		$this->assertEquals('/callbacks/10', $url);
	}
	
	public function testGenWithCallbackUnescapedParam()
	{
		$url = $this->routing->gen('callbacks.gen_with_unescaped_param', array('number' => 5));
		$this->assertEquals('/callbacks//10', $url);
	}
	
	public function testGenWithCallbackUnsetRouteParam()
	{
		$url = $this->routing->gen('callbacks.gen_unset_route_param', array('number' => 5));
		$this->assertEquals('/callbacks/?number=5', $url);
	}
	
	public function testGenWithCallbackUnsetRouteParamWithDefault()
	{
		$url = $this->routing->gen('callbacks.gen_unset_route_param_with_default', array('number' => 5));
		$this->assertEquals('/callbacks/23', $url);
	}
	
	public function testGenWithCallbackUnsetOptionalRouteParam()
	{
		$url = $this->routing->gen('callbacks.gen_unset_route_optional_param', array('number' => 5));
		$this->assertEquals('/callbacks/optional/?number=5', $url);
	}
	
	public function testGenWithCallbackUnsetOptionalRouteParamWithDefault()
	{
		$url = $this->routing->gen('callbacks.gen_unset_route_optional_param_with_default', array('number' => 5));
		$this->assertEquals('/callbacks/optional/23', $url);
	}
	
	public function testGenWithCallbackUnsetExtraParam()
	{
		$url = $this->routing->gen('callbacks.gen_unset_extra_param', array('number' => 5, 'extra' => 'query string data'));
		$this->assertEquals('/callbacks/5?extra=query+string+data', $url);
	}
	
	public function testGenWithCallbackNullifyRouteParam()
	{
		$url = $this->routing->gen('callbacks.gen_nullify_route_param', array('number' => 5));
		$this->assertEquals('/callbacks/', $url);
	}
	
	public function testGenWithCallbackNullifyRouteParamWithDefault()
	{
		$url = $this->routing->gen('callbacks.gen_nullify_route_param_with_default', array('number' => 5));
		$this->assertEquals('/callbacks/23', $url);
	}
	
	public function testGenWithCallbackNullifyRouteParamWithOptionalDefault()
	{
		$url = $this->routing->gen('callbacks.gen_nullify_route_param_with_optional_default', array('number' => 5));
		$this->assertEquals('/callbacks/optional/', $url);
	}
	
	public function testGenWithCallbackNullifyExtraParam()
	{
		$url = $this->routing->gen('callbacks.gen_nullify_extra_param', array('number' => 5, 'extra' => 'query string data'));
		$this->assertEquals('/callbacks/5', $url);
	}
	
	public function testGenWithCallbackSetPrefixAndPostfix()
	{
		$url = $this->routing->gen('callbacks.gen_set_prefix_and_postfix', array());
		$this->assertEquals('/callbacks/prefix-value-postfix', $url);
	}
	
	public function testGenWithCallbackSetPrefixAndPostfixWithoutDefault()
	{
		$url = $this->routing->gen('callbacks.gen_set_prefix_and_postfix_without_default', array());
		$this->assertEquals('/callbacks/prefix-value-postfix', $url);
	}
	
	public function testGenWithCallbackSetPrefixAndPostfixIntoRoute()
	{
		$url = $this->routing->gen('callbacks.gen_set_prefix_and_postfix_into_route', array());
		$this->assertEquals('/callbacks/23/', $url);
	}
	
	public function testGenShortestPossibleUrl()
	{
		$url = $this->routing->gen('gen_shortest_possible_url', array(), array('omit_defaults' => true));
		$this->assertEquals('/gen_shortest_possible_url', $url);
		
		$url = $this->routing->gen('gen_shortest_possible_url', array('param1' => 1), array('omit_defaults' => true));
		$this->assertEquals('/gen_shortest_possible_url', $url);
		
		$url = $this->routing->gen('gen_shortest_possible_url', array('param1' => 2), array('omit_defaults' => true));
		$this->assertEquals('/gen_shortest_possible_url/2', $url);
		
		$url = $this->routing->gen('gen_shortest_possible_url', array('param2' => 2), array('omit_defaults' => true));
		$this->assertEquals('/gen_shortest_possible_url', $url);
		
		$url = $this->routing->gen('gen_shortest_possible_url', array('param2' => 1), array('omit_defaults' => true));
		$this->assertEquals('/gen_shortest_possible_url/1/1', $url);
		
		$url = $this->routing->gen('gen_shortest_possible_url', array('param3' => 4), array('omit_defaults' => true));
		$this->assertEquals('/gen_shortest_possible_url/1/2/4', $url);
	}
	
	public function testAbsoluteUrl()
	{
		$url = $this->routing->gen('index', array(), array('relative' => false));
		$this->assertEquals('http://localhost/', $url);
	}
	
	public function testTicket277()
	{
		$this->routing->setParameter('enabled', false);
		$url = $this->routing->gen('test_ticket_277');
		$this->assertEquals('?foo=bar&amp;module=Default&amp;action=Login', $url);
	}
	
	public function testTicket358()
	{
		$url = $this->routing->gen('index', array(), array('scheme' => 'https', 'authority' => 'localhost.localdomain:80443', 'fragment' => 'foo'));
		$this->assertEquals('https://localhost.localdomain:80443/#foo', $url);
		
		$url = $this->routing->gen('index', array(), array('scheme' => 'https', 'host' => 'localhost.localdomain', 'port' => '80443', 'fragment' => 'foo', 'relative'));
		$this->assertEquals('https://localhost.localdomain:80443/#foo', $url);
	}
	
	
	/**
	 * 
	 * @dataProvider dataTicket365
	 */
	public function testTicket365($expected, $data)
	{
		$url = $this->routing->gen('index', array('data' => $data));
		$this->assertEquals($expected, $url);
	}
	
	public function dataTicket365()
	{
		return array('indexed' => array('/?data%5B0%5D=baz&amp;data%5B1%5D=bar', array('baz', 'bar')),
					 'hashed'  => array('/?data%5Bfoo%5D=bar&amp;data%5Blol%5D=baz', array('foo' => 'bar', 'lol' => 'baz')));
	}
	
	public function testTicket437()
	{
		$url = $this->routing->gen('test_ticket_437');
		$this->assertEquals('/test_ticket_437/0', $url);
	}
	
	public function testTicket444()
	{
		$this->routing->setInput('/test_ticket_444/agavi/13/');
		$this->routing->execute();
		$url = $this->routing->gen('test_ticket_444', array('page' => 14));
		$this->assertEquals('/test_ticket_444/agavi/14/', $url);
		$url = $this->routing->gen('test_ticket_444', array('term' => 'snoopy', 'page' => 1));
		$this->assertEquals('/test_ticket_444/snoopy/1/', $url);
		$url = $this->routing->gen('test_ticket_444', array('term' => 'snoopy'));
		$this->assertEquals('/test_ticket_444/snoopy/1/', $url);
	}
	
	public function testTicket444Sample2()
	{
		$this->routing->setInput('/test_ticket_444_sample2/woodstock/2006/07/13');
		$this->routing->execute();
		$url = $this->routing->gen('test_ticket_444_sample2_external');
		$this->assertEquals('/test_ticket_444_sample2_external//', $url);
		$url = $this->routing->gen('test_ticket_444_sample2.archive', array('month' => 11));
		$this->assertEquals('/test_ticket_444_sample2/woodstock/2006/11/1/', $url);
		$url = $this->routing->gen('test_ticket_444_sample2.entry', array('id' => 22));
		$this->assertEquals('/test_ticket_444_sample2/woodstock/22.html', $url);
		$url = $this->routing->gen('test_ticket_444_sample2.archive', array('name' => 'snoopy'));
		$this->assertEquals('/test_ticket_444_sample2/snoopy/2007/1/1/', $url);
	}
	
	public function testTicket464()
	{
		$url = $this->routing->gen('test_ticket_464', array('page' => 5));
		$this->assertEquals('/test_ticket_464/0/5', $url);
	}
	
	public function testTicket609()
	{
		$this->routing->setInput('/test_ticket_609/name/DESC');
		$this->routing->execute();
		$url = $this->routing->gen('test_ticket_609', array('order' => 'name', 'set' => 'ASC'));
		$this->assertEquals('/test_ticket_609/name/ASC', $url);
	}
	
	public function testTicket695()
	{
		try {
			$this->routing->gen('callbacks.ticket_695');
			$this->fail('Failed asserting that onGenerate() is called');
		} catch (AgaviException $e) {
			// successfully called
		}
	}
	
	
	public function testTicket698()
	{
		$this->routing->setInput('/test_ticket_698/incoming');
		$this->routing->execute();
		$url = $this->routing->gen('test_ticket_698');
		$this->assertEquals('/test_ticket_698/overwritten', $url);
	}
	
	public function testTicket713()
	{
		$url = $this->routing->gen('test_ticket_713', array('zomg' => 'lol'));
		$this->assertEquals('/test_ticket_713/lol', $url);
	}
	

	public function testTicket717()
	{
		$this->routing->setInput('/');
		$this->routing->setInputParameters(array('foo' => '"><script>alert(\'hi\');</script>'));
		$this->routing->execute();
		$url = $this->routing->gen(null, array('bar' => 'baz'));
		$this->assertEquals('/?foo=%22%3E%3Cscript%3Ealert%28%27hi%27%29%3B%3C%2Fscript%3E&amp;bar=baz', $url);
	}
	
	public function testTicket764()
	{
		$url = $this->routing->gen('test_ticket_764.child');
		$this->assertEquals('/test_ticket_764/dummy/child', $url);
	}
}
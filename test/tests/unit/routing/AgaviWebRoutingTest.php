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
		$this->setRunTestInSeparateProcess(false);
	}
	
	public function setUp()
	{
		$_SERVER['SCRIPT_NAME'] = ''; // takes care of php setting the commandline scriptname in $_SERVER, throwing the routing off guard
		$this->routing = new AgaviWebRouting();
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
}
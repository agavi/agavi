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
}
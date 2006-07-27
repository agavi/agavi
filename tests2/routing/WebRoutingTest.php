<?php

class WebRoutingTest extends AgaviTestCase
{
	protected $_r = null;
	
	protected $_SERVER = array();
	protected $_ENV = array();

	protected $export = array();

	public function setExport($export)
	{
		$this->export = $export;
	}

	public function setUp()
	{
		$this->_SERVER = $_SERVER;
		$this->_ENV = $_ENV;
		AgaviConfig::set('core.use_routing', true);
	}
	
	public function testAutodetection()
	{
		$export = $this->export;
		$_SERVER = $export['_SERVER'];
		$_ENV = $export['_ENV'];
		$this->_r = new AgaviWebRouting();
		$this->_r->initialize(AgaviContext::getInstance());
		$this->assertEquals($export['prefix'], $this->_r->getPrefix(), '[' . $export['message'] . '] getPrefix()');
		$this->assertEquals($export['input'], $this->_r->getInput(), '[' . $export['message'] . '] getInput()');
		$this->assertEquals($export['basePath'], $this->_r->getBasePath(), '[' . $export['message'] . '] getBasePath()');
		$this->assertEquals($export['baseHref'], $this->_r->getBaseHref(), '[' . $export['message'] . '] getBaseHref()');
	}
	
	public function tearDown()
	{
		$_SERVER = $this->_SERVER;
		$_ENV = $this->_ENV;
	}

}

?>
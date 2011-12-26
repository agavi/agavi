<?php

class WebRoutingTest extends AgaviTestCase
{
	protected $_r = null;

	protected $_SERVER = array();
	protected $_ENV = array();
	protected $_GET = array();

	protected $export = array();

	public function setExport($export)
	{
		$this->export = $export;
	}

	public function setUp()
	{
		$this->_SERVER = $_SERVER;
		$this->_ENV = $_ENV;
		$this->_GET = $_GET;
		AgaviConfig::set('core.use_routing', true);
	}

	public function loadTestCases()
	{
		$retval = array();
		
		$d = dir(dirname(__FILE__) . '/../routing/cases/');
		while(false !== ($entry = $d->read())) {
			if(preg_match('#.*\\.case\\.php#i', $entry))
			{
				$cases = include($d->path . $entry);
				foreach($cases as $case) {
					$retval[$entry . ': ' . $case['message']] = array($case);
				}
			}
		}
		$d->close();
		
		return $retval;
	}

	/**
	 * @dataProvider loadTestCases
	 */
	public function testCases($export)
	{
		$_SERVER = $export['_SERVER'];
		$_ENV = $export['_ENV'];
		$_GET = $export['_GET'];
		$ctx = AgaviContext::getInstance('test');
		$ctx->getRequest()->initialize($ctx);
		$this->_r = new AgaviWebRouting();
		$this->_r->initialize($ctx);
		
		if(!isset($export['expectFailure'])) {
			$export['expectFailure'] = array();
		}
		
		if(in_array('prefix', $export['expectFailure'])) {
			$assertMethod = 'assertNotEquals';
		} else {
			$assertMethod = 'assertEquals';
		}
		$this->$assertMethod($export['prefix'], $this->_r->getPrefix(), '[' . $export['message'] . '] getPrefix() ('.$export['prefix'].':'.$this->_r->getPrefix().')');
		
		if(in_array('input', $export['expectFailure'])) {
			$assertMethod = 'assertNotEquals';
		} else {
			$assertMethod = 'assertEquals';
		}
		$this->$assertMethod($export['input'], $this->_r->getInput(), '[' . $export['message'] . '] getInput()('.$export['input'].':'.$this->_r->getInput().')');
		
		if(in_array('basePath', $export['expectFailure'])) {
			$assertMethod = 'assertNotEquals';
		} else {
			$assertMethod = 'assertEquals';
		}
		$this->$assertMethod($export['basePath'], $this->_r->getBasePath(), '[' . $export['message'] . '] getBasePath()');
		
		if(in_array('baseHref', $export['expectFailure'])) {
			$assertMethod = 'assertNotEquals';
		} else {
			$assertMethod = 'assertEquals';
		}
		$this->$assertMethod($export['baseHref'], $this->_r->getBaseHref(), '[' . $export['message'] . '] getBaseHref()');
	}


	public function tearDown()
	{
		$_SERVER = $this->_SERVER;
		$_ENV = $this->_ENV;
		$_GET = $this->_GET;
		$ctx = AgaviContext::getInstance('test');
		$ctx->getRequest()->initialize($ctx);
		
		AgaviConfig::set('core.use_routing', false);
	}

}

?>
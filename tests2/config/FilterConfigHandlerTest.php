<?php
require_once(dirname(__FILE__) . '/ConfigHandlerTestBase.php');

class FCHTestFilter1 implements AgaviIFilter
{
	public $context;
	public $params;

	public function initialize(AgaviContext $ctx, array $params = array())
	{
		$this->context = $ctx;
		$this->params = $params;
	}

	public function executeOnce(AgaviFilterChain $filterChain, AgaviResponse $response) {}
	public function execute(AgaviFilterChain $filterChain, AgaviResponse $response) {}
	public function getContext() {}
}

class FCHTestFilter2 extends FCHTestFilter1
{
}

class FilterConfigHandlerTest extends ConfigHandlerTestBase
{
	protected $context;

	public function setUp()
	{
		$this->context = AgaviContext::getInstance('test');
	}

	public function testFilterConfigHandler()
	{
		$ctx = AgaviContext::getInstance('test');
		$FCH = new AgaviFilterConfigHandler();

		$filters = array();

		$file = $this->getIncludeFile($FCH->execute(AgaviConfig::get('core.config_dir') . '/tests/filters.xml'));
		include($file);
		unlink($file);

		$this->assertSame(2, count($filters));

		$this->assertType('FCHTestFilter1', $filters[0]);
		$this->assertSame(array('comment' => true), $filters[0]->params);
		$this->assertReference($ctx, $filters[0]->context);

		$this->assertType('FCHTestFilter2', $filters[1]);
		$this->assertSame(array(), $filters[1]->params);
		$this->assertReference($ctx, $filters[1]->context);
	}
}
?>
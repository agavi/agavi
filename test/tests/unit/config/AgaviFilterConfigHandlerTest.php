<?php
require_once(__DIR__ . '/ConfigHandlerTestBase.php');

class FCHTestFilter1 implements AgaviIFilter
{
	public $context;
	public $params;

	public function initialize(AgaviContext $ctx, array $params = array())
	{
		$this->context = $ctx;
		$this->params = $params;
	}

	public function executeOnce(AgaviFilterChain $filterChain, AgaviExecutionContainer $container) {}
	public function execute(AgaviFilterChain $filterChain, AgaviExecutionContainer $container) {}
	public final function getContext() {}
}

class FCHTestFilter2 extends FCHTestFilter1
{
}

class AgaviFilterConfigHandlerTest extends ConfigHandlerTestBase
{
	protected $context;

	public function setUp()
	{
		$this->context = $this->getContext();
	}

	public function testFilterConfigHandler()
	{
		$ctx = $this->getContext();
		
		$FCH = new AgaviFilterConfigHandler();
		
		$document = $this->parseConfiguration(
			AgaviConfig::get('core.config_dir') . '/tests/filters.xml',
			AgaviConfig::get('core.agavi_dir') . '/config/xsl/filters.xsl'
		);

		$filters = array();

		$file = $this->getIncludeFile($FCH->execute($document));
		include($file);
		unlink($file);

		$this->assertCount(2, $filters);

		$this->assertInstanceOf('FCHTestFilter1', $filters['filter1']);
		$this->assertSame(array('comment' => true), $filters['filter1']->params);
		$this->assertSame($ctx, $filters['filter1']->context);

		$this->assertInstanceOf('FCHTestFilter2', $filters['filter2']);
		$this->assertSame(array(), $filters['filter2']->params);
		$this->assertSame($ctx, $filters['filter2']->context);
	}
}
?>
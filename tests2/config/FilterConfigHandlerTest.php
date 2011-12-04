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

	public function executeOnce(AgaviFilterChain $filterChain, AgaviExecutionContainer $container) {}
	public function execute(AgaviFilterChain $filterChain, AgaviExecutionContainer $container) {}
	public final function getContext() {}
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
		
		$document = AgaviXmlConfigParser::run(
			AgaviConfig::get('core.config_dir') . '/tests/filters.xml',
			'',
			'',
			array(
				AgaviXmlConfigParser::STAGE_SINGLE => array(
					AgaviConfig::get('core.agavi_dir') . '/config/xsl/filters.xsl',
				),
				AgaviXmlConfigParser::STAGE_COMPILATION => array(
				),
			),
			array(
				AgaviXmlConfigParser::STAGE_SINGLE => array(
					AgaviXmlConfigParser::STEP_TRANSFORMATIONS_BEFORE => array(
					),
					AgaviXmlConfigParser::STEP_TRANSFORMATIONS_AFTER => array(
						AgaviXmlConfigParser::VALIDATION_TYPE_RELAXNG => array(
							AgaviConfig::get('core.agavi_dir') . '/config/rng/filters.rng',
						),
					),
				),
				AgaviXmlConfigParser::STAGE_COMPILATION => array(
					AgaviXmlConfigParser::STEP_TRANSFORMATIONS_BEFORE => array(),
					AgaviXmlConfigParser::STEP_TRANSFORMATIONS_AFTER => array()
				),
			)
		);

		$filters = array();

		$file = $this->getIncludeFile($FCH->execute($document));
		include($file);
		unlink($file);

		$this->assertSame(2, count($filters));

		$this->assertInstanceOf('FCHTestFilter1', $filters[0]);
		$this->assertSame(array('comment' => true), $filters[0]->params);
		$this->assertReference($ctx, $filters[0]->context);

		$this->assertInstanceOf('FCHTestFilter2', $filters[1]);
		$this->assertSame(array(), $filters[1]->params);
		$this->assertReference($ctx, $filters[1]->context);
	}
}
?>
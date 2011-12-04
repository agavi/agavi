<?php
require_once(dirname(__FILE__) . '/ConfigHandlerTestBase.php');

class DCHTestDatabase
{
	public $params;

	public function initialize($dbm, $params)
	{
		$this->params = $params;
	}
}

class DatabaseConfigHandlerTest extends ConfigHandlerTestBase
{
	protected $databases;
	protected $defaultDatabaseName;

	public function setUp()
	{
		$this->databases = array();
	}

	public function testDatabaseConfigHandler()
	{
		$DBCH = new AgaviDatabaseConfigHandler();
		
		$document = AgaviXmlConfigParser::run(
			AgaviConfig::get('core.config_dir') . '/tests/databases.xml',
			'',
			'',
			array(
				AgaviXmlConfigParser::STAGE_SINGLE => array(
					AgaviConfig::get('core.agavi_dir') . '/config/xsl/databases.xsl',
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
							AgaviConfig::get('core.agavi_dir') . '/config/rng/databases.rng',
						),
					),
				),
				AgaviXmlConfigParser::STAGE_COMPILATION => array(
					AgaviXmlConfigParser::STEP_TRANSFORMATIONS_BEFORE => array(),
					AgaviXmlConfigParser::STEP_TRANSFORMATIONS_AFTER => array()
				),
			)
		);

		$this->includeCode($c = $DBCH->execute($document));

		$this->assertInstanceOf('DCHTestDatabase', $this->databases['test1']);
		$params_ex = array(
			'host' => 'localhost1',
			'user' => 'username1',
			'config' => AgaviConfig::get('core.app_dir') . '/config/project-conf.php',
		);
		$this->assertSame($params_ex, $this->databases['test1']->params);

		$this->assertReference($this->databases['test1'], $this->databases[$this->defaultDatabaseName]);
	}

	public function testOverwrite()
	{
		// TODO: cant overwrite the environment :s
	}
}
?>
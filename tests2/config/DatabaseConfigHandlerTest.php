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

		$this->includeCode($c = $DBCH->execute(AgaviConfig::get('core.config_dir') . '/tests/databases.xml'));

		$this->assertType('DCHTestDatabase', $this->databases['test1']);
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
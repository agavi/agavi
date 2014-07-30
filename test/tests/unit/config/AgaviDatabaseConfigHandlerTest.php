<?php
require_once(__DIR__ . '/ConfigHandlerTestBase.php');

class DCHTestDatabase
{
	public $params;

	public function initialize($dbm, $params)
	{
		$this->params = $params;
	}
}

class AgaviDatabaseConfigHandlerTest extends ConfigHandlerTestBase
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
		
		$document = $this->parseConfiguration(
			AgaviConfig::get('core.config_dir') . '/tests/databases.xml',
			AgaviConfig::get('core.agavi_dir') . '/config/xsl/databases.xsl'
		);

		$this->includeCode($DBCH->execute($document));

		$this->assertInstanceOf('DCHTestDatabase', $this->databases['test1']);
		$paramsExpected = array(
			'host' => 'localhost1',
			'user' => 'username1',
			'config' => AgaviConfig::get('core.app_dir') . '/config/project-conf.php',
		);
		$this->assertSame($paramsExpected, $this->databases['test1']->params);

		$this->assertSame($this->databases['test1'], $this->databases[$this->defaultDatabaseName]);
	}

	public function testOverwrite()
	{
		$DBCH = new AgaviDatabaseConfigHandler();
		
		$document = $this->parseConfiguration(
			AgaviConfig::get('core.config_dir') . '/tests/databases.xml',
			AgaviConfig::get('core.agavi_dir') . '/config/xsl/databases.xsl',
			'env2'
		);

		$this->includeCode($DBCH->execute($document));

		$this->assertInstanceOf('DCHTestDatabase', $this->databases['test1']);
		$paramsExpected = array(
			'host' => 'localhost1',
			'user' => 'testuser1',
			'config' => AgaviConfig::get('core.app_dir') . '/config/project-conf.php',
		);
		$this->assertSame($paramsExpected, $this->databases['test1']->params);

		$this->assertSame($this->databases['test2'], $this->databases[$this->defaultDatabaseName]);
	}
	
	/**
	 * @expectedException AgaviConfigurationException
	 */
	public function testNonExistentDefault() {
		$DBCH = new AgaviDatabaseConfigHandler();
		$document = $this->parseConfiguration(
			AgaviConfig::get('core.config_dir') . '/tests/databases.xml',
			AgaviConfig::get('core.agavi_dir') . '/config/xsl/databases.xsl',
			'nonexistent-default'
		);

		$DBCH->execute($document);
	}
}
?>
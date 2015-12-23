<?php
require_once(__DIR__ . '/ConfigHandlerTestBase.php');

class FCHTestBase
{
	public $context,
	       $params,
	       $suCalled;
	public function initialize($ctx, array $params = array())
	{
		$this->context = $ctx;
		$this->params = $params;
	}
	public final function getContext()
	{
		return $this->context;
	}
	public function startup()
	{
		$this->suCalled = true;
	}
}

class FCHTestExecutionContainer extends FCHTestBase {}
class FCHTestController         extends FCHTestBase {}
	
class FCHTestDispatchFilter     implements AgaviIGlobalFilter {
	public function executeOnce(AgaviFilterChain $filterChain, AgaviExecutionContainer $container) {}
	public function execute(AgaviFilterChain $filterChain, AgaviExecutionContainer $container) {}
	public final function getContext() {}
	public function initialize(AgaviContext $context, array $parameters = array()) {}
}

class FCHTestExecutionFilter    implements AgaviIActionFilter {
	public function executeOnce(AgaviFilterChain $filterChain, AgaviExecutionContainer $container) {}
	public function execute(AgaviFilterChain $filterChain, AgaviExecutionContainer $container) {}
	public final function getContext() {}
	public function initialize(AgaviContext $context, array $parameters = array()) {}
}

class FCHTestFilterChain        extends FCHTestBase {}
class FCHTestLoggerManager      extends FCHTestBase {}
class FCHTestRequest            extends FCHTestBase {}
class FCHTestResponse           extends FCHTestBase {}
class FCHTestRouting            extends FCHTestBase {}
class FCHTestStorage            extends FCHTestBase {}
class FCHTestTranslationManager extends FCHTestBase {}
class FCHTestValidationManager  extends FCHTestBase {}
class FCHTestDBManager          extends FCHTestBase {}

class FCHTestSecurityFilter     implements AgaviIActionFilter, AgaviISecurityFilter {
	public function executeOnce(AgaviFilterChain $filterChain, AgaviExecutionContainer $container) {}
	public function execute(AgaviFilterChain $filterChain, AgaviExecutionContainer $container) {}
	public final function getContext() {}
	public function initialize(AgaviContext $context, array $parameters = array()) {}
}
class FCHTestUser               extends FCHTestBase implements AgaviISecurityUser
{
	public function addCredential($credential) {}
	public function clearCredentials() {}
	public function hasCredentials($credential) {}
	public function isAuthenticated() {}
	public function removeCredential($credential) {}
	public function setAuthenticated($authenticated) {}
}

class AgaviFactoryConfigHandlerTest extends ConfigHandlerTestBase
{
	protected		$conf;

	protected		$factories;

	protected		$databaseManager,
							$request,
							$storage,
							$translationManager,
							$user,
							$loggerManager,
							$controller,
							$routing,
							$response;

	public function setUp()
	{
		$this->conf = AgaviConfig::toArray();
		$this->factories = array();
	}

	public function tearDown()
	{
		AgaviConfig::clear();
		AgaviConfig::fromArray($this->conf);
	}

	public function testFactoryConfigHandler()
	{
		$FCH = new AgaviFactoryConfigHandler();

		$paramsExpected = array('p1' => 'v1', 'p2' => 'v2');

		AgaviConfig::set('core.use_database', true);
		AgaviConfig::set('core.use_logging', true);
		AgaviConfig::set('core.use_security', true);
		$document = $this->parseConfiguration(
			AgaviConfig::get('core.config_dir') . '/tests/factories.xml',
			AgaviConfig::get('core.agavi_dir') . '/config/xsl/factories.xsl'
		);
		$this->includeCode($FCH->execute($document));


		// Execution container
		$this->assertSame(
			array(
				'class' => 'FCHTestExecutionContainer',
				'parameters' => $paramsExpected,
			),
			$this->factories['execution_container']
		);

		// Dispatch filter
		$this->assertSame(
			array(
				'class' => 'FCHTestDispatchFilter',
				'parameters' => $paramsExpected,
			),
			$this->factories['dispatch_filter']
		);

		// Execution filter
		$this->assertSame(
			array(
				'class' => 'FCHTestExecutionFilter',
				'parameters' => $paramsExpected,
			),
			$this->factories['execution_filter']
		);

		// Filter chain
		$this->assertSame(
			array(
				'class' => 'FCHTestFilterChain',
				'parameters' => $paramsExpected,
			),
			$this->factories['filter_chain']
		);

		// Security filter
		$this->assertSame(
			array(
				'class' => 'FCHTestSecurityFilter',
				'parameters' => $paramsExpected,
			),
			$this->factories['security_filter']
		);

		// Response
		$this->assertSame(
			array(
				'class' => 'FCHTestResponse',
				'parameters' => $paramsExpected,
			),
			$this->factories['response']
		);
		

		// Validation Manager
		$this->assertSame(
			array(
				'class' => 'FCHTestValidationManager',
				'parameters' => $paramsExpected,
			),
			$this->factories['validation_manager']
		);

		$this->assertInstanceOf('FCHTestDBManager', $this->databaseManager);
		$this->assertSame($this, $this->databaseManager->context);
		$this->assertSame($paramsExpected, $this->databaseManager->params);
		$this->assertTrue($this->databaseManager->suCalled);

		$this->assertInstanceOf('FCHTestRequest', $this->request);
		$this->assertSame($this, $this->request->context);
		$this->assertSame($paramsExpected, $this->request->params);
		$this->assertTrue($this->request->suCalled);

		$this->assertInstanceOf('FCHTestStorage', $this->storage);
		$this->assertSame($this, $this->storage->context);
		$this->assertSame($paramsExpected, $this->storage->params);
		$this->assertTrue($this->storage->suCalled);

		$this->assertInstanceOf('FCHTestTranslationManager', $this->translationManager);
		$this->assertSame($this, $this->translationManager->context);
		$this->assertSame($paramsExpected, $this->translationManager->params);
		$this->assertTrue($this->translationManager->suCalled);

		$this->assertInstanceOf('FCHTestUser', $this->user);
		$this->assertSame($this, $this->user->context);
		$this->assertSame($paramsExpected, $this->user->params);
		$this->assertTrue($this->user->suCalled);

		$this->assertInstanceOf('FCHTestLoggerManager', $this->loggerManager);
		$this->assertSame($this, $this->loggerManager->context);
		$this->assertSame($paramsExpected, $this->loggerManager->params);
		$this->assertTrue($this->loggerManager->suCalled);

		$this->assertInstanceOf('FCHTestController', $this->controller);
		$this->assertSame($this, $this->controller->context);
		$this->assertSame($paramsExpected, $this->controller->params);

		$this->assertInstanceOf('FCHTestRouting', $this->routing);
		$this->assertSame($this, $this->routing->context);
		$this->assertSame($paramsExpected, $this->routing->params);
		$this->assertTrue($this->routing->suCalled);
	}

}
?>
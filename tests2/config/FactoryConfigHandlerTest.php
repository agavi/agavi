<?php
require_once(dirname(__FILE__) . '/ConfigHandlerTestBase.php');

class FCHTestBase
{
	public	$context,
					$params;
	public function initialize($ctx, array $params = array())
	{
		$this->context = $ctx;
		$this->params = $params;
	}
	public final function getContext()
	{
		return $this->context;
	}
}

class FCHTestActionStack			extends FCHTestBase {}
class FCHTestController				extends FCHTestBase {
	protected $response;
	public function initialize($response, array $params = array())
	{
		$this->response = $response;
		$this->context = $response->getContext();
		$this->params = $params;
	}
}
class FCHTestDispatchFilter		extends FCHTestBase implements AgaviIGlobalFilter {
	public function executeOnce(AgaviFilterChain $filterChain, AgaviResponse $response) {}
	public function execute(AgaviFilterChain $filterChain, AgaviResponse $response) {}
	public final function getContext() {}
	public function initialize(AgaviContext $context, array $parameters = array()) {}
}

class FCHTestExecutionFilter	extends FCHTestBase implements AgaviIActionFilter {
	public function executeOnce(AgaviFilterChain $filterChain, AgaviResponse $response) {}
	public function execute(AgaviFilterChain $filterChain, AgaviResponse $response) {}
	public final function getContext() {}
	public function initialize(AgaviContext $context, array $parameters = array()) {}
}

class FCHTestFilterChain			extends FCHTestBase {}
class FCHTestLoggerManager		extends FCHTestBase {}
class FCHTestRequest					extends FCHTestBase {}
class FCHTestResponse					extends FCHTestBase {}
class FCHTestRouting					extends FCHTestBase {
	protected $response;
	public function initialize($response, array $params = array())
	{
		$this->response = $response;
		$this->context = $response->getContext();
		$this->params = $params;
	}
}
class FCHTestStorage					extends FCHTestBase
{
	public $suCalled = false;
	public function startup() { $this->suCalled = true; }
}
class FCHTestValidationManager	extends FCHTestBase {}

class FCHTestDBManager				extends FCHTestBase {}
class FCHTestSecurityFilter		extends FCHTestBase implements AgaviIActionFilter, AgaviISecurityFilter {
	public function executeOnce(AgaviFilterChain $filterChain, AgaviResponse $response) {}
	public function execute(AgaviFilterChain $filterChain, AgaviResponse $response) {}
	public final function getContext() {}
	public function initialize(AgaviContext $context, array $parameters = array()) {}
}
class FCHTestUser							extends FCHTestBase implements AgaviISecurityUser
{
	public function addCredential($credential) {}
	public function clearCredentials() {}
	public function hasCredentials($credential) {}
	public function isAuthenticated() {}
	public function removeCredential($credential) {}
	public function setAuthenticated($authenticated) {}
}

class FactoryConfigHandlerTest extends ConfigHandlerTestBase
{
	protected		$conf;

	protected		$factories;

	protected		$databaseManager,
							$request,
							$storage,
							$validationManager,
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

		$params_ex = array('p1' => 'v1', 'p2' => 'v2');

		AgaviConfig::set('core.use_database', true);
		AgaviConfig::set('core.use_logging', true);
		AgaviConfig::set('core.use_security', true);
		$this->includeCode($c = $FCH->execute(AgaviConfig::get('core.config_dir') . '/tests/factories.xml'));


		// Action stack
		$this->assertSame(
			array(
				'class' => 'FCHTestActionStack',
				'parameters' => $params_ex,
			),
			$this->factories['action_stack']
		);

		// Dispatch filter
		$this->assertSame(
			array(
				'class' => 'FCHTestDispatchFilter',
				'parameters' => $params_ex,
			),
			$this->factories['dispatch_filter']
		);

		// Execution filter
		$this->assertSame(
			array(
				'class' => 'FCHTestExecutionFilter',
				'parameters' => $params_ex,
			),
			$this->factories['execution_filter']
		);

		// Filter chain
		$this->assertSame(
			array(
				'class' => 'FCHTestFilterChain',
				'parameters' => $params_ex,
			),
			$this->factories['filter_chain']
		);

		// Security filter
		$this->assertSame(
			array(
				'class' => 'FCHTestSecurityFilter',
				'parameters' => $params_ex,
			),
			$this->factories['security_filter']
		);

		// Response
		$this->assertSame(
			array(
				'class' => 'FCHTestResponse',
				'parameters' => $params_ex,
			),
			$this->factories['response']
		);

		$this->assertInstanceOf('FCHTestDBManager', $this->databaseManager);
		$this->assertReference($this, $this->databaseManager->context);
		$this->assertSame($params_ex, $this->databaseManager->params);

		$this->assertInstanceOf('FCHTestRequest', $this->request);
		$this->assertReference($this, $this->request->context);
		$this->assertSame($params_ex, $this->request->params);

		$this->assertInstanceOf('FCHTestStorage', $this->storage);
		$this->assertReference($this, $this->storage->context);
		$this->assertSame($params_ex, $this->storage->params);
		$this->assertTrue($this->storage->suCalled);

		$this->assertInstanceOf('FCHTestValidationManager', $this->validationManager);
		$this->assertReference($this, $this->validationManager->context);
		$this->assertSame($params_ex, $this->validationManager->params);

		$this->assertInstanceOf('FCHTestUser', $this->user);
		$this->assertReference($this, $this->user->context);
		$this->assertSame($params_ex, $this->user->params);

		$this->assertInstanceOf('FCHTestLoggerManager', $this->loggerManager);
		$this->assertReference($this, $this->loggerManager->context);
		$this->assertSame($params_ex, $this->loggerManager->params);

		$this->assertInstanceOf('FCHTestController', $this->controller);
		$this->assertReference($this, $this->controller->context);
		$this->assertSame($params_ex, $this->controller->params);

		$this->assertInstanceOf('FCHTestRouting', $this->routing);
		$this->assertReference($this, $this->routing->context);
		$this->assertSame($params_ex, $this->routing->params);
	}

}
?>
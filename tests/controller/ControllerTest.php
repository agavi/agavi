<?php
require_once('core/AgaviObject.class.php');				// ParameterHolder's Parent Class
require_once('util/ParameterHolder.class.php'); 	// Controller's Parent Class
require_once('controller/Controller.class.php');	// This is what we're testing, son.

require_once('core/Context.class.php');						// Context provides the glueworks to the rest of the framework

require_once('action/ActionStack.class.php');	
require_once('request/Request.class.php');	
require_once('request/WebRequest.class.php');	
require_once('storage/Storage.class.php');	
require_once('storage/SessionStorage.class.php');	
require_once('user/User.class.php');	
require_once('user/SecurityUser.class.php');	
require_once('user/BasicSecurityUser.class.php');	
require_once('filter/Filter.class.php');	
require_once('filter/SecurityFilter.class.php');	
require_once('filter/BasicSecurityFilter.class.php');	

require_once('util/Toolkit.class.php');						// utilized by AgaviException, View, ConfigCache, ConfigHandler

require_once('exception/AgaviException.class.php');					// Base Exception class
require_once('exception/ControllerException.class.php');		// Thrown if the requested controller isnt implemented
require_once('exception/FactoryException.class.php');				// Thrown if sompn wasnt right in a newInstance call
require_once('exception/ForwardException.class.php');				// Thrown if sompn wasnt right in a newInstance call
require_once('exception/RenderException.class.php');				// Thrown if a view's pre-render check fails
require_once('exception/ConfigurationException.class.php');	// Thrown if something's bunk in a config
require_once('exception/CacheException.class.php');					// Thrown if something's bunk in a config
require_once('exception/ParseException.class.php');					// Thrown if there was a problem parsing something (inifile)

//require_once('config/ParameterParser.class.php');
require_once('view/View.class.php');								// Needed for some constants and stuff.. 
require_once('config/ConfigCache.class.php');				// needed in forward, possibly other methods

require_once('config/ConfigHandler.class.php');			
require_once('config/IniConfigHandler.class.php');	
require_once('config/RootConfigHandler.class.php');	
require_once('config/AutoloadConfigHandler.class.php');
require_once('config/DatabaseConfigHandler.class.php');
require_once('config/DefineConfigHandler.class.php');
require_once('config/FactoryConfigHandler.class.php');
require_once('config/CompileConfigHandler.class.php');
require_once('config/FilterConfigHandler.class.php');
require_once('config/LoggingConfigHandler.class.php');
require_once('config/ModuleConfigHandler.class.php');
require_once('config/ValidatorConfigHandler.class.php');


// setup some defines
define('AG_CONFIG_DIR',	dirname(__file__).'/sandbox/config');
define('AG_CACHE_DIR',	dirname(__file__).'/sandbox/cache');
define('AG_LIB_DIR',		dirname(__file__).'/sandbox/lib');
define('AG_MODULE_DIR',	dirname(__file__).'/sandbox/modules/');
define('AG_WEBAPP_DIR',	dirname(__file__).'/sandbox');
define('AG_USE_DATABASE', false);
define('AG_USE_SECURITY', false);
define('AG_PATH_INFO_ARRAY', 'SERVER');
define('AG_PATH_INFO_KEY', 'PATH_INFO');


// Our Mocked Collaborators
Mock::generate('DatabaseManager');
Mock::generate('ActionStack');
Mock::generate('WebRequest');
Mock::generate('SecurityFilter');
Mock::generate('SecurityUser');
Mock::generate('SessionStorage');

class MockContext extends Context {

	// Overide the getInstance method to load our mocks instead
	// added a test parameter to it's signature too, so we can get a reference to the test object for the mocks
	public static function getInstance($controller, &$test=null)
	{
		if (!isset(self::$instance)) {
			$class = __CLASS__;
			self::$instance = new $class;
		
			if (defined(AG_USE_DATABASE) && AG_USE_DATABASE) {
				self::$instance->databaseManager = new MockDatabaseManager($test);
				self::$instance->databaseManager->initialize();
			}
			self::$instance->controller 			= $controller;
			self::$instance->actionStack			= new MockActionStack($test);
			// In the live getInstance we would do this.. 
			// require_once(ConfigCache::checkConfig('config/factories.ini'));
			// which essentially establishes the request, storage, user,  and optionally securityFilter objects initializing each as well
			// so we'll just go ahead and do that here.
			self::$instance->request = new MockWebRequest($test);
			self::$instance->storage = new MockSessionStorage($test);
			self::$instance->user = new MockSecurityUser($test);
			self::$instance->request->initialize(self::$instance, null);
			self::$instance->storage->initialize(self::$instance, null);
			self::$instance->user->initialize(self::$instance, null);
		
			if (AG_USE_SECURITY) {
				self::$instance->securityFilter = new MockSecurityFilter($test);
				self::$instance->securityFilter->initialize(self::$instance);
			}
		}
		return self::$instance;
	}

}

class SampleController extends Controller {
	// normally, the dispatch will stuff any parameters found into the request object 
	// and forward to the requested module/action (or defaults) as well
	// for testing, we will only initialize the controller for now. 
	private $test;

	// we need a reference to the test object to pass into our mocks, so we pass it into the constructor. 
	public function __construct(&$test = null)
	{
		$this->test = $test;
	}
	
	public function dispatch() 
	{
		$this->initialize();
	}

	protected function loadContext()
	{
		$this->context = MockContext::getInstance($this, $this->test);
	}

}

class ControllerTest extends UnitTestCase
{
	private $_c = null;

	public function __construct()
	{
	}

	public function setUp()
	{
		$this->_c = new SampleController($this);
		$this->_c->dispatch();
	}

	public function tearDown()
	{
		$this->_c = null;
	}

	public function testNewController()
	{
		define('AG_USE_DATABASE', true);
		$this->assertTrue($this->_c instanceof SampleController);
		$context = $this->_c->getContext();
		$this->assertTrue($context instanceof Context);
		

	}

	public function testactionExists()
	{
		// actionExists actually checks the filesystem
		$this->assertTrue($this->_c->actionExists('Test', 'Test'));
		$this->assertFalse($this->_c->actionExists('Test', 'Bunk'));
		$this->assertFalse($this->_c->actionExists('Bunk', 'Bunk'));
	}

	public function testforward()
	{
		define('AG_ERROR_404_MODULE', 'Test');
		define('AG_ERROR_404_ACTION', 'Test');
		try {
			$this->_c->forward('Default', 'SOMEACTIONTHATSURELYDONUTEXIST');
		} catch (ConfigurationException $e) {
			$this->Pass('Correctly caught unconfigured exception.');
		} catch (ForwardException $e) {
			$this->Pass('Correctly caught forward exception when forwarding to bunk action.');
		}
	}

	public function testgetAction()
	{
		$this->assertIsA($this->_c->getAction('Test', 'Test'), 'Test_TestAction');
	}

	public function testgetActionStack()
	{
		$as = $this->_c->getActionStack();
		$this->assertTrue($as instanceof MockActionStack);
	}

	public function testgetContext()
	{
		$c = $this->_c->getContext();
		$this->assertTrue($c instanceof Context);
	}

	public function testgetGlobalModel()
	{
		$this->assertIsA($this->_c->getGlobalModel('Sample'), 'SampleModel');
	}
	
	public function testgetGlobalModel_recursive()
	{
		$this->assertIsA($this->_c->getGlobalModel('SampleRecursive'), 'SampleRecursiveModel');
	}

	public function testgetInstance()
	{
		try {
			SampleController::getInstance();
			$this->fail('Expected ControllerException not thrown!');
		} catch (ControllerException $e) {
			$this->pass();
		}
		$this->_c = Controller::newInstance('SampleController');
		$this->assertIdentical($this->_c, SampleController::getInstance());
	}

	public function testgetModel()
	{
		$this->assertIsA($this->_c->getModel('Test', 'Test'), 'Test_TestModel');
		$this->assertIsA($this->_c->getModel('Test', 'Test2'), 'Test2Model');
	}

	public function testgetRenderMode()
	{
		$this->assertEqual(View::RENDER_CLIENT, $this->_c->getRenderMode());
		$this->_c->setRenderMode(View::RENDER_NONE);
		$this->assertEqual(View::RENDER_NONE, $this->_c->getRenderMode());
	}

	public function testgetView()
	{
		$this->assertIsA($this->_c->getView('Test', 'TestSuccess'), 'Test_TestSuccessView');
		$this->assertIsA($this->_c->getView('Test', 'TestError'), 'TestErrorView');
	}

	public function testmodelExists()
	{
		$this->assertTrue($this->_c->modelExists('Test', 'Test'));
		$this->assertFalse($this->_c->modelExists('Test', 'Bunk'));
		$this->assertFalse($this->_c->modelExists('Bunk', 'Bunk'));
	}

	public function testmoduleExists()
	{
		$this->assertTrue($this->_c->moduleExists('Test'));
		$this->assertFalse($this->_c->moduleExists('Bunk'));
	}

	public function testnewInstance()
	{
		/* Since we're testing a singleton here, this set of tests fail since 
		 * we already called newInstance() in an earlier test.
		 * 
		 * How do we fix this?
		 */

		/*
		$this->assertIsA(Controller::newInstance('SampleController'), 'SampleController');
		try {
			SampleController::newInstance('Request');
			$this->fail('Expected FactoryException not thrown!');
		} catch (FactoryException $e) {
			$this->pass();
		}
		try {
			SampleController::newInstance('SampleController');
			$this->fail('Expected FactoryException not thrown!');
		} catch (FactoryException $e) {
			$this->pass();
		}
		*/
	}

	public function testsetRenderMode()
	{
		$good = array(View::RENDER_CLIENT, View::RENDER_VAR, VIEW::RENDER_NONE);
		$bad = array(932940, null, '');
		foreach ($good as &$value) {
			try {
				$this->_c->setRenderMode($value);
				$this->pass();
			} catch (RenderException $e) {
				$this->fail('Caught unexpected RenderException!');
			}
		}
		foreach ($bad as &$value) {
			try {
				$this->_c->setRenderMode($value);
				$this->fail('Expected RenderException not thrown!');
			} catch (RenderException $e) {
				$this->pass();
			}
		}
	}

	public function testshutdown()
	{
	}

	public function testviewExists()
	{
		$this->assertTrue($this->_c->viewExists('Test', 'TestSuccess'));
		$this->assertFalse($this->_c->viewExists('Test', 'Bunk'));
		$this->assertFalse($this->_c->viewExists('Bunk', 'Bunk'));
	}

	public function inCLI()
	{
		$this->assertEqual((php_sapi_name() == 'cli'), $this->_c->inCLI());
	}

}

?>

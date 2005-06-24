<?php 
// Include this file in tests which you wish to isolate from the rest of the system.
// eg: include_once dirname(__FILE__) . '../mockContext.php'; // assuming the tests/ dir is our parent dir.
//
// SomeTest extends UnitTestCase
// {
// 	private $_controller = null,
//					$_context = null;
//
//	public function setup() 
//	{
//		$this->_controller = new MockController($this);		// our mocked up controller, notice we pass a reference to this test case object
//		$this->_controller->dispatch();								// allow the controller to initialize the setup
//		$this->_context = MockContext::getInstance(); // convenience reference, we could just as well have done $this->_controller->getContext()
//	}
//
//	public function tearDown()
//	{
//		MockContext::cleanSlate(); // special method used to clear out all objects, etc so we can start fresh with next test. 
//	}
//
//	public function testSomething()
//	{
//		// Apply some behavior to our mocked request object so it can play actor
//		$this->_context->getRequest()->setReturn('getParameter', 'bar', array('foo')); // when getParameter is called w/arg of 'foo' it will return 'bar'
//		$this->_context->getRequest()->setReturn('getParameter', 'mike', array('looser'));
//		
//		// Setup some expectations so our mocked request object can play critic
//		$this->_context->getRequest()->expectAtLeastOnce('getParameter', 'foo'); // expect to see at least one call to getParameter('foo'); when we run our code below.
//
//		$this->assertTrue( $this->_something()->doSomething() ); // our doSomething method should return a boolean true
//
//		$this->tally(); // is only needed when we utilize calls on the mock object, like the expectAtLeastOnce
//	}


// Define some base configuration settings. 
define('AG_WEBAPP_DIR',	dirname(__FILE__) . '/sandbox');
define('AG_CONFIG_DIR',	AG_WEBAPP_DIR . '/sandbox/config');
define('AG_CACHE_DIR',	AG_WEBAPP_DIR . '/cache');
define('AG_LIB_DIR',		AG_WEBAPP_DIR . '/lib');
define('AG_MODULE_DIR',	AG_WEBAPP_DIR . '/modules/');

define('AG_PATH_INFO_ARRAY', 'SERVER');
define('AG_PATH_INFO_KEY', 'PATH_INFO');
define('AG_AVAILABLE', 'On');
define('AG_USE_DATABASE', true);
define('AG_USE_SECURITY', true);
define('AG_ERROR_404_MODULE', 'ErrorModule');
define('AG_ERROR_404_ACTION', 'Error404');
define('AG_MODULE_DISABLED_MODULE', 'ErrorModule');
define('AG_MODULE_DISABLED_ACTION', 'ModuleUnavailable');
define('AG_MAX_FORWARDS', 3);

// This is a pseudo Context that has been pre-loaded with a mock db mgr
class MockContext extends Context {

	// Overide the getInstance method to load our mocks instead
	// added a test parameter to it's signature too, so we can get a reference to the test object for the mocks
	public static function getInstance($controller, &$test=null)
	{
		if (!isset(self::$instance)) {
			$class = __CLASS__;
			self::$instance = new $class;
		
			if (AG_USE_DATABASE) { 
				if (class_exists('DatabaseManager')) { // trigger autoload
					Mock::generate('DatabaseManager');
					self::$instance->databaseManager = new MockDatabaseManager($test);
					self::$instance->databaseManager->initialize();
				} else {
					die("autoloader's busted.\n");
				}
			}
			self::$instance->controller 			= $controller;
			self::$instance->actionStack			= new ActionStack($test);
			// In the live getInstance we would do this.. 
			// require_once(ConfigCache::checkConfig('config/factories.ini'));
			// which essentially establishes the request, storage, user,  and optionally securityFilter objects initializing each as well
			// so we'll just go ahead and do that here.
			self::$instance->request = new WebRequest($test);
			if (class_exists('SessionStorage')) {
				Mock::generate('SessionStorage');
				self::$instance->storage = new MockSessionStorage($test);
				self::$instance->storage->initialize(self::$instance, null);
			}
			self::$instance->user = new BasicSecurityUser($test);
			self::$instance->request->initialize(self::$instance, null);
			self::$instance->user->initialize(self::$instance, null);
		
			if (AG_USE_SECURITY) {
				self::$instance->securityFilter = new BasicSecurityFilter($test);
				self::$instance->securityFilter->initialize(self::$instance);
			}
		}
		return self::$instance;
	}

	
	public static function cleanSlate()
	{
		self::$instance = null; // will this in turn free all the objs it held reference to, I wonder? (IT SHOULD, YEA?)
	}

	public static function useRealActionStack()
	{
		self::$instance->actionStack = new ActionStack();
		self::$instance->controller->replaceActionStack();
	}

	public static function replaceObj($objname, $new)
	{
		if (isset(self::$instance->$objname)) {
			self::$instance->$objname = $new;
			return true;
		}
		return false;
	}

}


class MockController extends Controller {
	// normally, the dispatch will stuff any parameters found into the request object 
	// and forward to the requested module/action (or defaults) as well
	// for testing, we will only initialize the controller for now. 
	protected $test;

	// we need a reference to the test object to pass into our mocks, so we pass it into the constructor. 
	public function __construct(&$test = null)
	{
		if (!$test) {
			die('The Mock Controller was created without passing a reference to what test we are testing!');
		}
		$this->test = $test;
	}
	
	// Create a new me and call this in your setup. 
	public function dispatch() 
	{
		$this->initialize();
	}

	// Call me in your tearDown()
	protected function loadContext()
	{
		$this->context = MockContext::getInstance($this, $this->test);
	}

	public function replaceActionStack()
	{
		$this->actionStack = $this->context->getActionStack();

	}


}

?>

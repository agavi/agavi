<?php
require_once 'core/AgaviObject.class.php';									// Mother of all things
require_once 'util/ParameterHolder.class.php';							// Controller inherits from ParameterHolder
require_once 'util/Toolkit.class.php';											// some filesystem helpers mainly used by exceptions
require_once 'controller/Controller.class.php';							// Gotta have a controller to getInstance
require_once 'core/Context.class.php';											// Holla
require_once 'action/ActionStack.class.php'; 								// Context::getInstance()
require_once 'config/ConfigCache.class.php'; 								// so we can parse config/factories.ini, in Context::getInstance()
require_once 'exception/AgaviException.class.php';					// Mother of all exceptions	
require_once 'exception/ConfigurationException.class.php';	// Bunk alert in config
require_once 'config/ConfigHandler.class.php';							// Mother of config handlers
require_once 'config/IniConfigHandler.class.php';						// Mother of all INI based config handlers
require_once 'config/RootConfigHandler.class.php';					// Base Config Class

// Base config constants
define('AG_WEBAPP_DIR',		dirname(__FILE__)	. '/sandbox');
define('AG_CONFIG_DIR',		AG_WEBAPP_DIR			. '/config');
define('AG_CACHE_DIR',		AG_WEBAPP_DIR			. '/cache');
define('AG_LIB_DIR',			AG_WEBAPP_DIR			. '/lib');
define('AG_MODULE_DIR',		AG_WEBAPP_DIR			. '/modules');
define('AG_TEMPLATE_DIR',	AG_WEBAPP_DIR			. '/templates');

// most basic of controllers, so we can setup the context
class ContextSampleController extends Controller {
	public function dispatch() 
	{
		$this->initialize();
	}
}

class ContextTest extends UnitTestCase 
{
	private $_controller;

	public function setup()
	{
		$this->_controller = new ContextSampleController();
	}

	public function testgetInstance()
	{
		$c = Context::getInstance($this->_controller);
	}
}


?>

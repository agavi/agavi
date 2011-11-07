<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2005-2011 the Agavi Project.                                |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE   |
// | file that was distributed with this source code. You can also view the    |
// | LICENSE file online at http://www.agavi.org/LICENSE.txt                   |
// |   vi: set noexpandtab:                                                    |
// |   Local Variables:                                                        |
// |   indent-tabs-mode: t                                                     |
// |   End:                                                                    |
// +---------------------------------------------------------------------------+

/**
 * A database adapter for the Doctrine ORM.
 *
 * @package    agavi
 * @subpackage database
 *
 * @author     Ross Lawley <ross.lawley@gmail.com>
 * @author     David Zülke <dz@bitxtender.com>
 * @author     TANAKA Koichi <tanaka@ensites.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.11.0
 *
 * @version    $Id$
 */
class AgaviDoctrineDatabase extends AgaviDatabase
{
	/**
	 * @var        Doctrine_Manager The Doctrine Manager instance we should use.
	 */
	protected $doctrineManager;
	
	/**
	 * Connect to the database.
	 *
	 * @throws     <b>AgaviDatabaseException</b> If a connection could not be
	 *                                           created.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function connect()
	{
		// this doesn't do anything, Doctrine is handling the lazy connection stuff
	}
	
	/**
	 * Retrieve a raw database resource associated with this Database
	 * implementation.
	 *
	 * @return     mixed A database resource.
	 *
	 * @throws     <b>AgaviDatabaseException</b> If no resource could be retrieved
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getResource()
	{
		return $this->getConnection()->getDbh();
	}

	/**
	 * Initialize Doctrine set the autoloading
	 *
	 * @param      AgaviDatabaseManager The database manager of this instance.
	 * @param      array                An assoc array of initialization params.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @author     Ross Lawley <ross.lawley@gmail.com>
	 * @author     TANAKA Koichi <tanaka@ensites.com>
	 * @since      0.11.0
	 */
	public function initialize(AgaviDatabaseManager $databaseManager, array $parameters = array())
	{
		parent::initialize($databaseManager, $parameters);
		
		$name = $this->getName();
		
		// try to autoload doctrine
		if(!class_exists('Doctrine')) {
			// okay that didn't work. last resort: include it. we assume it's on the include path by default
			require('Doctrine.php');
		}
		
		$is12 = version_compare(Doctrine::VERSION, '1.2', '>=');
		
		// in any case, it's loaded now. maybe we need to register the autoloading stuff for it!
		// we need this list further down
		$splAutoloadFunctions = spl_autoload_functions();
		if(!in_array(array('Doctrine', 'autoload'), $splAutoloadFunctions) && !in_array(array('Doctrine_Core', 'autoload'), $splAutoloadFunctions)) {
			// we do
			spl_autoload_register(array('Doctrine', 'autoload'));
		}
		
		// cool. Assign the Doctrine Manager instance
		$this->doctrineManager = Doctrine_Manager::getInstance();
		
		// now we're in business. we will set up connections right away, as Doctrine is handling the lazy-connecting stuff for us.
		// that way, you can just start using classes in your code
		try {
			$dsn = $this->getParameter('dsn');
			
			if($dsn === null) {
				// missing required dsn parameter
				$error = 'Database configuration specifies method "dsn", but is missing dsn parameter';
				throw new AgaviDatabaseException($error);
			}
			
			$this->connection = $this->doctrineManager->openConnection($dsn, $name);
			// do not assign the resource here. that would connect to the database
			// $this->resource = $this->connection->getDbh();
			
			// set our event listener that, on connect, sets the configured charset and runs init queries
			$cel = $this->getParameter('connection_event_listener_class', 'AgaviDoctrineDatabaseEventListener');
			$this->connection->setListener(new $cel($this));
			
			// set the context instance as a connection parameter
			$this->connection->setParam('context', $databaseManager->getContext(), 'org.agavi');
			
			// date format
			if($this->hasParameter('date_format')) {
				$this->connection->setDateFormat($this->getParameter('date_format'));
			}
			
			// options
			foreach((array)$this->getParameter('options') as $optionName => $optionValue) {
				$this->connection->setOption($optionName, $optionValue);
			}
			
			foreach(array(
				'manager_attributes' => $this->doctrineManager,
				'attributes' => $this->connection,
			) as $attributesKey => $attributesDestination) {
				foreach((array)$this->getParameter($attributesKey, array()) as $attributeName => $attributeValue) {
					if($is12) {
						if(!strpos($attributeName, '::')) {
							throw new AgaviDatabaseException(sprintf('For Doctrine 1.2 and newer, attribute names (and, if desired to be resolved against a constant, values) must be fully qualified, e.g. "Doctrine_Core::ATTR_VALIDATE" and "Doctrine_Core::VALIDATE_NONE". Given attribute with name "%s" in collection "%s" does not match this condition.', $attributeName, $attributesKey));
						}
						if(!defined($attributeName)) {
							throw new AgaviDatabaseException(sprintf('Unknown Attribute "%s"', $attributeName));
						}
					}
					
					// resolve from constant if possible
					if(strpos($attributeName, '::') && defined($attributeName)) {
						$attributeName = constant($attributeName);
					}
					
					if(strpos($attributeValue, '::') && defined($attributeValue)) {
						// resolve from constant if possible
						$attributeValue = constant($attributeValue);
					} elseif(ctype_digit($attributeValue)) {
						// cast numeric type to int
						$attributeValue = (int)$attributeValue;
					} elseif(($attributeName == Doctrine::ATTR_QUERY_CACHE || $attributeName == Doctrine::ATTR_RESULT_CACHE) && (is_string($attributeValue) || (is_array($attributeValue) && isset($attributeValue['class'])))) {
						// handle special case for query and result caches, where the attribute value needs to be an instance of Doctrine_Cache_Driver
						// we only allow basic cases where the ctor argument array for options requires scalar values
						// if people want to use e.g. Doctrine_Cache_Db, which requires an instance of Doctrine_Connection as the argument, they should use a custom connection event listener
						$driverClass = is_string($attributeValue) ? $attributeValue : $attributeValue['class'];
						$driverOptions = is_array($attributeValue) && isset($attributeValue['options']) && is_array($attributeValue['options']) ? $attributeValue['options'] : array();
						$attributeValue = new $driverClass($driverOptions);
					}
					
					$attributesDestination->setAttribute($attributeName, $attributeValue);
				}
			}
			
			foreach((array)$this->getParameter('impls', array()) as $templateName => $className) {
				$this->connection->setImpl($templateName, $className);
			}
			
			foreach((array)$this->getParameter('manager_impls', array()) as $templateName => $className) {
				$this->doctrineManager->setImpl($templateName, $className);
			}
			
			// load models (that'll just work with empty values too)
			Doctrine::loadModels($this->getParameter('load_models'));
			
			// for 1.2, handle model autoloading and base paths
			if($is12 && ($this->hasParameter('load_models') || $this->hasParameter('models_directory'))) {
				if(!in_array(array('Doctrine', 'modelsAutoload'), $splAutoloadFunctions) && !in_array(array('Doctrine_Core', 'modelsAutoload'), $splAutoloadFunctions)) {
					spl_autoload_register(array('Doctrine_Core', 'modelsAutoload'));
				}
				
				if($this->hasParameter('models_directory')) {
					Doctrine_Core::setModelsDirectory($this->getParameter('models_directory'));
				}
			}
			
			// for 1.2, handle extension autoloading, base paths and registration
			if($is12 && ($this->hasParameter('extensions_path') || $this->hasParameter('register_extensions'))) {
				if(!in_array(array('Doctrine', 'extensionsAutoload'), $splAutoloadFunctions) && !in_array(array('Doctrine_Core', 'extensionsAutoload'), $splAutoloadFunctions)) {
					spl_autoload_register(array('Doctrine_Core', 'extensionsAutoload'));
				}
				
				if($this->hasParameter('extensions_path')) {
					Doctrine_Core::setExtensionsPath($this->getParameter('extensions_path'));
				}
				foreach((array)$this->getParameter('register_extensions', array()) as $extensionName) {
					if(is_array($extensionName)) {
						call_user_func_array(array($this->doctrineManager, 'registerExtension'), $extensionName);
					} else {
						$this->doctrineManager->registerExtension($extensionName);
					}
				}
			}
			
			foreach((array)$this->getParameter('bind_components', array()) as $componentName) {
				$this->doctrineManager->bindComponent($componentName, $name);
			}
		} catch(Doctrine_Exception $e) {
			// the connection's foobar'd
			throw new AgaviDatabaseException($e->getMessage());
		}
	}
	
	/**
	 * Execute the shutdown procedure.
	 *
	 * @throws     <b>AgaviDatabaseException</b> If an error occurs while shutting
	 *                                           down this database.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function shutdown()
	{
		if($this->connection !== null) {
			$this->doctrineManager->closeConnection($this->connection);
			$this->connection = null;
			$this->resource = null;
		}
	}
	
	/**
	 * Get the Doctrine Manager instance.
	 *
	 * @return     Doctrine_Manager The Doctrine Manager instance.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getDoctrineManager()
	{
		return $this->doctrineManager;
	}
}

?>
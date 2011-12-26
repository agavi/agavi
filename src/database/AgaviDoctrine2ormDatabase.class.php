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
 * A database adapter for the Doctrine2 ORM. Can be used standalone, or by
 * referencing an AgaviDoctrine2dbalDatabase connection.
 * 
 * Users wishing to implement more advanced configuration options than supported
 * by this adapter or register event handlers should subclass this driver and
 * override the prepareConfiguration() and prepareEventManager() methods,
 * respectively (always remember to call the parent method in this case).
 *
 * Supported configuration parameters:
 *  - "connection": an array of connection details as expected by Doctrine2, or
 *                  the name (string) of a configured AgaviDoctrine2dbalDatabase
 *                  that should be used by this connection.
 *  - "configuration_class": the name of the class used for the configuration
 *                           object.
 *  - "event_manager_class": the name of the class used for the event manager
 *                           object.
 *  - "configuration": an array containing various configuration options:
 *   - "metadata_driver_impl_argument" (required): the argument for the metadata
 *                                                 driver constructor, usually
 *                                                 a path to the folder with the
 *                                                 entities or metadata files.
 *   - "metadata_driver_impl_class": the metadata driver implementation class.
 *                                   If omitted, the default annotation driver
 *                                   of Doctrine2 will be used.
 *   - "auto_generate_proxy_classes": boolean flag for proxy auto generation,
 *                                    defaults to on in debug mode.
 *   - "proxy_namespace": The namespace of the proxy classes.
 *   - "proxy_dir": The directory containing the proxy classes.
 *   - "metadata_cache_impl_class": The class to use for metadata caching.
 *                                  Defaults to Doctrine\Common\Cache\ApcCache
 *                                  (Doctrine\Common\Cache\ArrayCache in debug).
 *   - "query_cache_impl_class": The class to use for metadata caching.
 *                               Defaults to Doctrine\Common\Cache\ApcCache
 *                               (Doctrine\Common\Cache\ArrayCache in debug).
 *   - "result_cache_impl_class": The class to use for metadata caching.
 *                                Defaults to Doctrine\Common\Cache\ApcCache
 *                                (Doctrine\Common\Cache\ArrayCache in debug).
 *
 * @package    agavi
 * @subpackage database
 *
 * @author     David Zülke <david.zuelke@bitextender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      1.0.6
 *
 * @version    $Id$
 */
class AgaviDoctrine2ormDatabase extends AgaviDoctrine2Database
{
	/**
	 * Connect to the database.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.6
	 */
	public function connect()
	{
		$connection = $this->getParameter('connection');
		if(is_string($connection)) {
			try {
				$connection = $this->getDatabaseManager()->getDatabase($connection);
			} catch(AgaviDatabaseException $e) {
				throw new AgaviDatabaseException(sprintf('AgaviDoctrine2dbalDatabase connection "%s" configured for use in AgaviDoctrine2ormDatabase "%s" could not be found.', $connection, $this->getName()), 0, $e);
			}
			try {
				$connection = $connection->getConnection();
			} catch(AgaviDatabaseException $e) {
				throw new AgaviDatabaseException(sprintf("AgaviDoctrine2dbalDatabase connection '%s' configured for use in AgaviDoctrine2ormDatabase '%s' could not be initialized:\n\n%s", $this->getParameter('connection'), $this->getName(), $e->getMessage()), 0, $e);
			}
		} elseif(!is_array($connection)) {
			throw new AgaviDatabaseException('AgaviDoctrine2ormDatabase expects configuration parameter "connection" to be an array containing connection details or a string with the name of an AgaviDoctrine2dbalDatabase to use.');
		}
		
		// make new configuration
		$cc = $this->getParameter('configuration_class', '\Doctrine\ORM\Configuration');
		$config = new $cc();
		$this->prepareConfiguration($config);
		
		// make new event manager or take the one on the given named connection
		if($connection instanceof \Doctrine\DBAL\Connection) {
			$eventManager = $connection->getEventManager();
		} else {
			$ec = $this->getParameter('event_manager_class', '\Doctrine\Common\EventManager');
			$eventManager = new $ec();
		}
		$this->prepareEventManager($eventManager);
		
		try {
			$this->connection = \Doctrine\ORM\EntityManager::create($connection, $config, $eventManager);
		} catch(Exception $e) {
			throw new AgaviDatabaseException(sprintf("Failed to create Doctrine\ORM\EntityManager for connection '%s':\n\n%s", $this->getName(), $e->getMessage()), 0, $e);
		}
	}
	
	/**
	 * Prepare the configuration for this connection.
	 *
	 * @param      Doctrine\ORM\Configuration The configuration object.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.6
	 */
	protected function prepareConfiguration(\Doctrine\DBAL\Configuration $config)
	{
		parent::prepareConfiguration($config);
		
		// auto-generate proxy classes in debug mode by default
		$config->setAutoGenerateProxyClasses($this->getParameter('configuration[auto_generate_proxy_classes]', AgaviConfig::get('core.debug')));
		
		$mda = $this->getParameter('configuration[metadata_driver_impl_argument]');
		// check if a metadata driver class is configured (explicitly check with getParameter() to allow "deletion" of the parameter by using null)
		if($this->hasParameter('configuration[metadata_driver_impl_class]') && $md = $this->getParameter('configuration[metadata_driver_impl_class]')) {
			// yes, so we construct the class with the configured arguments
			// construct the given class and pass the path as the argument
			// in many cases, the argument may be a string with a path or an array of paths, which means that we cannot use reflection and newInstanceArgs()
			// for more elaborate cases with multiple ctor arguments or where the ctor expects a non-scalar value, people need to use prepareConfiguration() in a subclass
			$md = new $md($mda);
		} else {
			// no, that means we use the default annotation driver and the configured argument as the path
			$md = $config->newDefaultAnnotationDriver($mda);
		}
		$config->setMetadataDriverImpl($md);
		
		// set proxy namespace and dir
		// defaults to something including the connection name, or the app cache dir, respectively
		$config->setProxyNamespace($this->getParameter('configuration[proxy_namespace]', 'AgaviDoctrine2ormDatabase_Proxy_' . preg_replace('#\W#', '_', $this->getName())));
		$config->setProxyDir($this->getParameter('configuration[proxy_dir]', AgaviConfig::get('core.cache_dir')));
		
		// unless configured differently, use ArrayCache in debug mode and APC (if available) otherwise
		if(AgaviConfig::get('core.debug') || !extension_loaded('apc')) {
			$defaultCache = '\Doctrine\Common\Cache\ArrayCache';
		} else {
			$defaultCache = '\Doctrine\Common\Cache\ApcCache';
		}
		$metadataCache = $this->getParameter('configuration[metadata_cache_impl_class]', $defaultCache);
		$config->setMetadataCacheImpl(new $metadataCache);
		$queryCache = $this->getParameter('configuration[query_cache_impl_class]', $defaultCache);
		$config->setQueryCacheImpl(new $queryCache);
		$resultCache = $this->getParameter('configuration[result_cache_impl_class]', $defaultCache);
		$config->setResultCacheImpl(new $resultCache);
	}
	
	/**
	 * Retrieve the Doctrine\DBAL\Connection resource associated with our entity
	 * manager.
	 *
	 * @return     Doctrine\DBAL\Connection The underlying DBAL connection.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.6
	 */
	public function getResource()
	{
		return $this->getConnection()->getConnection();
	}
}

?>
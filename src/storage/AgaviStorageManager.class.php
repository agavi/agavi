<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2005-2009 the Agavi Project.                                |
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
 * AgaviStorageManager manages storage adapter definitions of an application.
 *
 * @package    agavi
 * @subpackage storage
 *
 * @author     David Zülke <david.zuelke@bitextender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      1.1.0
 *
 * @version    $Id$
 */
class AgaviStorageManager
{
	/**
	 * @var        string The name of the default storage.
	 */
	protected $defaultStorageName = null;
	
	/**
	 * @var        array An array of AgaviStorage objects.
	 */
	protected $storages = array();

	/**
	 * @var        AgaviContext An AgaviContext instance.
	 */
	protected $context = null;

	/**
	 * Retrieve the current application context.
	 *
	 * @return     AgaviContext The current AgaviContext instance.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.1.0
	 */
	public final function getContext()
	{
		return $this->context;
	}

	/**
	 * Retrieve the storage adapter instance associated with the given name.
	 *
	 * @param      string A storage name.
	 *
	 * @return     mixed An AgaviStorage instance.
	 *
	 * @throws     <b>AgaviStorageException</b> If the requested storage name does
	 *                                          not exist.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.1.0
	 */
	public function getStorage($name = null)
	{
		if($name === null) {
			$name = $this->defaultStorageName;
		}
		
		if(isset($this->storages[$name])) {
			return $this->storages[$name];
		}

		// nonexistent storage name
		$error = 'Storage "%s" does not exist';
		$error = sprintf($error, $name);
		throw new AgaviStorageException($error);
	}
	
	/**
	 * Retrieve the name of the given storage instance.
	 *
	 * @param      AgaviStorage The storage instance to fetch the name of.
	 *
	 * @return     string The name of the storage, or false if it was not found.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.1.0
	 */
	public function getStorageName(AgaviStorage $storage)
	{
		return array_search($storage, $this->storages, true);
	}

	/**
	 * Returns the name of the default storage.
	 *
	 * @return     string The name of the default storage.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.1.0
	 */
	public function getDefaultStorageName()
	{
		return $this->defaultStorageName;
	}

	/**
	 * Initialize this Storage Manager.
	 *
	 * @param      AgaviContext An AgaviContext instance.
	 * @param      array        An array of initialization parameters.
	 *
	 * @throws     <b>AgaviInitializationException</b> If an error occurs while
	 *                                                 initializing this 
	 *                                                 Storage Manager.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.1.0
	 */
	public function initialize(AgaviContext $context, array $parameters = array())
	{
		$this->context = $context;

		// load storage configuration
		require(AgaviConfigCache::checkConfig(AgaviConfig::get('core.config_dir') . '/storages.xml'));
	}

	/**
	 * Do any necessary startup work after initialization.
	 *
	 * This method is not called directly after initialize().
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.1.0
	 */
	public function startup()
	{
		foreach($this->storages as $storage) {
			$storage->startup();
		}
	}

	/**
	 * Execute the shutdown procedure.
	 *
	 * @throws     <b>AgaviStorageException</b> If an error occurs while shutting
	 *                                          down this Storage Manager.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.1.0
	 */
	public function shutdown()
	{
		// loop over storages and shut them down
		foreach($this->storage as $storage) {
			$storage->shutdown();
		}
	}
}

?>
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
 * An abstract database adapter for the Doctrine2 DBAL and ORM.
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
abstract class AgaviDoctrine2Database extends AgaviDatabase
{
	/**
	 * Prepare the configuration for this connection.
	 *
	 * @param      Doctrine\DBAL\Configuration The configuration object.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.6
	 */
	protected function prepareConfiguration(\Doctrine\DBAL\Configuration $config)
	{
	}
	
	/**
	 * Prepare the event manager for this connection.
	 *
	 * @param      Doctrine\Common\EventManager The event manager object.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.6
	 */
	protected function prepareEventManager(\Doctrine\Common\EventManager $eventManager)
	{
	}
	
	/**
	 * Initialize the Doctrine2 ORM.
	 *
	 * @param      AgaviDatabaseManager The database manager of this instance.
	 * @param      array                An assoc array of initialization params.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.6
	 */
	public function initialize(AgaviDatabaseManager $databaseManager, array $parameters = array())
	{
		parent::initialize($databaseManager, $parameters);
		
		if(!class_exists('Doctrine\Common\ClassLoader')) {
			// no soup for you!
			require('Doctrine/Common/ClassLoader.php'); // let's assume Doctrine2 is on ze include path...
		}
		
		// iterate over all declared class loaders and register them if necessary (checks performed to avoid duplicates for Doctrine's own namespaces)
		// by default, we assume an install via PEAR, with all of Doctrine in one folder and on the include path
		// if people want to do the smart thing and ship a Doctrine release with their app, they just need to point the entire "Doctrine" namespace to the path
		// for bleeding edge git stuff or similar, the paths for the namespaces can be given individually, see the Doctrine manual for examples
		foreach((array)$this->getParameter('class_loaders', array('Doctrine' => null)) as $namespace => $includePath) {
			if($namespace == 'Doctrine' && class_exists('Doctrine\ORM\Version')) {
				// the ORM namespace's Version class exists or could be autloaded; let's assume that the class loader for any Doctrine stuff won't need registration then
				continue;
			}
			if(strpos($namespace, 'Doctrine\\') === 0 && class_exists($namespace . '\Version')) {
				// it is a Doctrine namespace, and the namespace's Version class exists or could be autloaded; let's assume that the class loader won't need registration then
				continue;
			}
			
			// register the class loader for this namespace without further checks (there's unlikely to be further duplicates)
			$cl = new \Doctrine\Common\ClassLoader($namespace, $includePath);
			$cl->register();
		}
	}
	
	/**
	 * Execute the shutdown procedure.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.6
	 */
	public function shutdown()
	{
		$this->connection = $this->resource = null;
	}
}

?>
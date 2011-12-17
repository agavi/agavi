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
 * A database adapter for the Doctrine2 DBAL. Such a connection can be used on
 * its own, or in conjunction with AgaviDoctrine2ormDatabase (which in turn does
 * not require a separate AgaviDoctrine2dbalDatabase).
 *
 * Users wishing to implement more advanced configuration options than supported
 * by this adapter or register event handlers should subclass this driver and
 * override the prepareConfiguration() and prepareEventManager() methods,
 * respectively (always remember to call the parent method in this case).
 *
 * Supported configuration parameters:
 *  - "connection": an array of connection details as expected by Doctrine2.
 *  - "configuration_class": the name of the class used for the configuration
 *                           object.
 *  - "event_manager_class": the name of the class used for the event manager
 *                           object.
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
class AgaviDoctrine2dbalDatabase extends AgaviDoctrine2Database
{
	/**
	 * Retrieve the resource associated with our connection.
	 *
	 * @return     Doctrine\DBAL\Driver\Connection The underlying wrapped driver.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.6
	 */
	public function getResource()
	{
		return $this->getConnection()->getWrappedConnection();
	}
	
	/**
	 * Connect to the database.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.6
	 */
	public function connect()
	{
		// make new configuration
		$cc = $this->getParameter('configuration_class', '\Doctrine\DBAL\Configuration');
		$config = new $cc();
		$this->prepareConfiguration($config);
		
		// make new event manager
		$ec = $this->getParameter('event_manager_class', '\Doctrine\Common\EventManager');
		$eventManager = new $ec();
		$this->prepareEventManager($eventManager);
		
		// boom
		try {
			$this->connection = \Doctrine\DBAL\DriverManager::getConnection((array)$this->getParameter('connection'), $config, $eventManager);
		} catch(Exception $e) {
			throw new AgaviDatabaseException(sprintf("Failed to create connection '%s':\n\n%s" . $this->getName(), $e->getMessage()), 0, $e);
		}
	}
}

?>
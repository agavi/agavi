<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2005-2010 the Agavi Project.                                |
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
 * An event listener for AgaviDoctrineDatabase.
 *
 * @package    agavi
 * @subpackage database
 *
 * @author     David Zülke <david.zuelke@bitextender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      1.0.4
 *
 * @version    $Id$
 */
class AgaviDoctrineDatabaseEventListener extends Doctrine_EventListener
{
	/**
	 * @var        AgaviDoctrineDatabase The database adapter instance.
	 */
	protected $database;
	
	/**
	 * Constructor, accepts the AgaviDoctrineDatabase instance to operate on.
	 *
	 * @param      AgaviDoctrineDatabase The corresponding database adapter.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.4
	 */
	public function __construct(AgaviDoctrineDatabase $database)
	{
		$this->database = $database;
	}
	
	/**
	 * Post-connect listener. Will set charset and run init queries if configured.
	 *
	 * @param      Doctrine_Event The Doctrine event object.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.4
	 */
	public function postConnect(Doctrine_Event $event)
	{
		if($this->database->hasParameter('charset')) {
			$event->getInvoker()->setCharset($this->database->getParameter('charset'));
		}
		
		foreach((array)$this->database->getParameter('init_queries') as $query) {
			$event->getInvoker()->exec($query);
		}
	}
}

?>
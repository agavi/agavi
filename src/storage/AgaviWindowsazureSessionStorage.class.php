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
 * Provides support for session storage using a Windows Azure table store.
 *
 * <b>Optional parameters:</b>
 *
 * # <b>host</b>                    - The Azure table host to connect to.
 *                                    Defaults to local dev storage.
 * # <b>account_name</b>            - The account name to use for connecting.
 * # <b>account_key</b>             - The account key to use for connecting.
 * # <b>session_table</b>           - The name of the table to store to.
 *                                    Defaults to 'php-sessions'.
 * # <b>session_table_partition</b> - The table partition to store to.
 *                                    Defaults to 'sessions'.
 *
 * @package    agavi
 * @subpackage storage
 *
 * @author     David Zülke <david.zuelke@bitextender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      1.0.4
 *
 * @version    $Id$
 */
class AgaviWindowsazureSessionStorage extends AgaviSessionStorage
{
	/**
	 * @var        Microsoft_WindowsAzure_SessionHandler Session handler object.
	 */
	protected $sessionHandler;

	/**
	 * Initialize this Storage.
	 *
	 * @param      AgaviContext An AgaviContext instance.
	 * @param      array        An associative array of initialization parameters.
	 *
	 * @throws     <b>AgaviInitializationException</b> If an error occurs while
	 *                                                 initializing this Storage.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com
	 * @since      1.0.4
	 */
	public function initialize(AgaviContext $context, array $parameters = array())
	{
		// initialize the parent
		parent::initialize($context, $parameters);
		
		if(!class_exists('Microsoft_WindowsAzure_SessionHandler')) {
			require('Microsoft/WindowsAzure/SessionHandler.php');
		}
		
		$table = new Microsoft_WindowsAzure_Storage_Table(
			$this->getParameter('host', Microsoft_WindowsAzure_Storage::URL_DEV_TABLE),
			$this->getParameter('account_name', Microsoft_WindowsAzure_Credentials::DEVSTORE_ACCOUNT),
			$this->getParameter('account_key', Microsoft_WindowsAzure_Credentials::DEVSTORE_KEY)
		);
		
		$sessionHandler = new Microsoft_WindowsAzure_SessionHandler($table, $this->getParameter('session_table', 'phpsessions'), $this->getParameter('session_table_partition', 'sessions'));
		// this will do session_set_save_handler
		$sessionHandler->register();
	}
}

?>
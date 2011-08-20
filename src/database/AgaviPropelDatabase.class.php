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
 * An Agavi Database driver for Propel. Supports Propel 1.3 and later.
 * 
 * <b>Optional parameters:</b>
 *
 * # <b>config</b>         - [none]    - path to the Propel runtime config file
 * # <b>datasource</b>     - [default] - datasource to use for the connection
 * # <b>use_as_default</b> - [false]   - use as default if multiple connections
 *                                       are specified. The configuration file
 *                                       that has been flagged using this param
 *                                       is be used when Propel is initialized
 *                                       via PropelAutoload. By default, the
 *                                       last config file in database.ini will
 *                                       be used.
 * # <b>enable_instance_pooling</b> - [none] - set this to false if you want to 
 *                                             explicitly disable propel 1.3 
 *                                             instance pooling, to true if 
 *                                             you want to explicitly enable it.
 *                                             Leave empty to use propels default.
 * 
 *
 * @package    agavi
 * @subpackage database
 * 
 * @author     David Zülke <dz@bitxtender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.9.0
 *
 * @version    $Id$
 */
class AgaviPropelDatabase extends AgaviDatabase
{
	/**
	 * Connect to the database.
	 * 
	 * @throws     <b>AgaviDatabaseException</b> If a connection could not be 
	 *                                           created.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.9.0
	 */
	protected function connect()
	{
		$this->connection = Propel::getConnection($this->getParameter('datasource'));
	}

	/**
	 * Load Propel config
	 * 
	 * @param      AgaviDatabaseManager The database manager of this instance.
	 * @param      array                An assoc array of initialization params.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.10.0
	 */
	public function initialize(AgaviDatabaseManager $databaseManager, array $parameters = array())
	{
		parent::initialize($databaseManager, $parameters);
		$configPath = AgaviToolkit::expandDirectives($this->getParameter('config'));
		$datasource = $this->getParameter('datasource', null);
		$use_as_default = $this->getParameter('use_as_default', false);
		$config = require($configPath);
		
		if($datasource === null || $datasource == 'default') {
			if(isset($config['propel']['datasources']['default'])) {
				$datasource = $config['propel']['datasources']['default'];
			} elseif(isset($config['datasources']['default'])) {
				$datasource = $config['datasources']['default'];
			} else {
				throw new AgaviDatabaseException('No datasource given for Propel connection, and no default datasource specified in runtime configuration file.');
			}
		}
		
		if(!class_exists('Propel')) {
			include('propel/Propel.php');
		}
		if(!Propel::isInit()) {
			Propel::init($configPath);
		}
		
		$is13 = version_compare(Propel::VERSION, '1.4', '<');
		
		// grab the configuration values and inject possibly defined overrides for this data source
		if($is13) {
			// old-style config array; PropelConfiguration was added after 1.3.0, http://trac.agavi.org/ticket/1195
			$config = Propel::getConfiguration();
			$config['datasources'][$datasource]['adapter'] = $this->getParameter('overrides[adapter]', $config['datasources'][$datasource]['adapter']);
			$config['datasources'][$datasource]['connection'] = array_merge($config['datasources'][$datasource]['connection'], $this->getParameter('overrides[connection]', array()));
			
			// also the autoload classes 
			$config['datasources'][$datasource]['classes'] = array_merge($config['datasources'][$datasource]['classes'], $this->getParameter('overrides[classes]', array()));
			
			// and init queries
			if(!isset($config['datasources'][$datasource]['connection']['settings']['queries']['query'])) {
				$config['datasources'][$datasource]['connection']['settings']['queries']['query'] = array();
			}
			// array cast because "query" might be a string if just one init query was given, http://trac.agavi.org/ticket/1194
			$config['datasources'][$datasource]['connection']['settings']['queries']['query'] = array_merge((array)$config['datasources'][$datasource]['connection']['settings']['queries']['query'], (array)$this->getParameter('init_queries'));
			
			// set the new config
			Propel::setConfiguration($config);
		} else {
			$config = Propel::getConfiguration(PropelConfiguration::TYPE_OBJECT);
			
			$overrides = (array)$this->getParameter('overrides');
			
			// set override values
			foreach($overrides as $key => $value) {
				$config->setParameter($key, $value);
			}
			
			// handle init queries in a cross-adapter fashion (they all support the "init_queries" param)
			$queries = (array)$config->getParameter('datasources.' . $datasource . '.connection.settings.queries.query', array());
			// yes... it's one array, [connection][settings][queries][query], with all the init queries from the config, so we append to that
			$queries = array_merge($queries, (array)$this->getParameter('init_queries'));
			$config->setParameter('datasources.' . $datasource . '.connection.settings.queries.query', $queries);
		}
		
		if(true === $this->getParameter('enable_instance_pooling')) {
			Propel::enableInstancePooling();
		} elseif(false === $this->getParameter('enable_instance_pooling')) {
			Propel::disableInstancePooling();
		}
	}

	/**
	 * Get the path to the Propel config file for this connection which has been
	 * specified in databases.xml.
	 *
	 * @return     string The path to the Propel configuration file
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.10.0
	 */
	public function getConfigPath()
	{
		return $this->getParameter('config');
	}

	/**
	 * Execute the shutdown procedure.
	 *
	 * @throws     <b>AgaviDatabaseException</b> If an error occurs while shutting
	 *                                           down this database.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function shutdown()
	{
		$this->connection = null;
	}
}

?>
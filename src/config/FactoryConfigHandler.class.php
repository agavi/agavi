<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2003-2006 the Agavi Project.                                |
// | Based on the Mojavi3 MVC Framework, Copyright (c) 2003-2005 Sean Kerr.    |
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
 * AgaviFactoryConfigHandler allows you to specify which factory implementation 
 * the system will use.
 *
 * @package    agavi
 * @subpackage config
 *
 * @author     Sean Kerr <skerr@mojavi.org>
 * @author     Mike Vincent <mike@agavi.org>
 * @copyright  (c) Authors
 * @since      0.9.0
 *
 * @version    $Id$
 */
class AgaviFactoryConfigHandler extends AgaviConfigHandler
{

	/**
	 * Execute this configuration handler.
	 *
	 * @param      string An absolute filesystem path to a configuration file.
	 *
	 * @return     string Data to be written to a cache file.
	 *
	 * @throws     <b>AgaviUnreadableException</b> If a requested configuration file
	 *                                             does not exist or is not readable.
	 * @throws     <b>AgaviParseException</b> If a requested configuration file is
	 *                                        improperly formatted.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function execute($config, $context = null)
	{
		if($context == null) {
			$context = '';
		}

		// parse the config file
		$configurations = $this->orderConfigurations(AgaviConfigCache::parseConfig($config)->configurations, AgaviConfig::get('core.environment'), $context);
		
		$data = array();
		foreach($configurations as $cfg) {

			$ctx = $context;
			if($cfg->hasAttribute('context'))
				$ctx = $cfg->getAttribute('context');

			$requiredItems = array('action_stack', 'controller', 'database_manager', 'dispatch_filter', 'execution_filter', 'filter_chain', 'logger_manager', 'request', 'storage', 'user', 'validator_manager');
		


			// Class names for ExecutionFilter, FilterChain and SecurityFilter
			if(isset($cfg->dispatch_filter))		$data['dispatch_filter'] = '$this->classNames["dispatch_filter"] = "' . $cfg->dispatch_filter->class->getValue() . '";';
			if(isset($cfg->execution_filter))		$data['execution_filter'] = '$this->classNames["execution_filter"] = "' . $cfg->execution_filter->class->getValue() . '";';
			if(isset($cfg->filter_chain))				$data['filter_chain'] = '$this->classNames["filter_chain"] = "' . $cfg->filter_chain->class->getValue() . '";';
			if(isset($cfg->security_filter)) {
				$data['security_filter'] = '$this->classNames["security_filter"] = "' . $cfg->security_filter->class->getValue() . '";';
			}

			// Database
			if(AgaviConfig::get('core.use_database', false) && isset($cfg->database_manager)) {
				$data['database_manager'] = '$this->databaseManager = new ' . $cfg->database_manager->class->getValue() . '();' . "\n" .
																		'$this->databaseManager->initialize($this);';
			}

			// Actionstack
			if(isset($cfg->action_stack))				$data['action_stack'] = '$this->actionStack = new ' . $cfg->action_stack->class->getValue() . '();';

			// Request
			if(isset($cfg->request)) {
				$data['request'] = '$this->request = AgaviRequest::newInstance("' . $cfg->request->class->getValue() . '");';
				// Init Request
				$data['init_request'] = '$this->request->initialize($this, ' . $this->getSettings($cfg->request) . ');';
			}

			// Storage
			if(isset($cfg->storage)) {
				$data['storage'] = '$this->storage = AgaviStorage::newInstance("' . $cfg->storage->class->getValue() . '");' . "\n" .
														'$this->storage->initialize($this, ' . $this->getSettings($cfg->storage) . ');' . "\n" .
														'$this->storage->startup();';
			}

			// ValidatorManager
			if(isset($cfg->validator_manager)) {
				$data['validator_manager'] = '$this->validatorManager = new ' . $cfg->validator_manager->class->getValue() . '();' . "\n" .
																			'$this->validatorManager->initialize($this);';
			}

			// User
			if(AgaviConfig::get('core.use_security', true) && isset($cfg->user)) {
				$data['user'] = '$this->user = AgaviUser::newInstance("' . $cfg->user->class->getValue() . '");' . "\n" .
												'$this->user->initialize($this, ' . $this->getSettings($cfg->user) . ');';
			}

			// LoggerManager
			if(AgaviConfig::get('core.use_logging', false) && isset($cfg->logger_manager)) {
				$data['logger_manager'] = '$this->loggerManager = new ' . $cfg->logger_manager->class->getValue() . '();' . "\n" .
																	'$this->loggerManager->initialize($this);';

			}

			// Controller 
			if(isset($cfg->controller)) {
				$data['controller'] = '$this->controller = AgaviController::newInstance("' . $cfg->controller->class->getValue() . '");' . "\n" .
															'$this->controller->initialize($this, ' . $this->getSettings($cfg->controller) . ');';
			}
				
		
			if(isset($cfg->routing)) {
				// Routing
				$data['routing'] = '$this->routing = new ' . $cfg->routing->class->getValue() . '();' . "\n" .
														'$this->routing->initialize($this);' . "\n" .
														'include(AgaviConfigCache::checkConfig(AgaviConfig::get("core.config_dir") . "/routing.xml", $profile));';
			}
		}

		// The order of this initialisiation code is fixed, to not change

		// name => required?
		$requiredItems = array('dispatch_filter' => true, 'execution_filter' => true, 'filter_chain' => true, 'security_filter' => false, 'database_manager' => false, 'action_stack' => true, 
					'request' => true, 'storage' => true, 'validator_manager' => true, 'user' => false, 'logger_manager' => false, 'controller' => true, 'init_request' => true, 'routing' => false);

		$code = '';

		foreach($requiredItems as $item => $required) {
			if($required && !isset($data[$item])) {
				$error = 'Configuration file "%s" is missing an entry for %s in the current configuration';
				$error = sprintf($error, $config, $item);
				throw new AgaviParseException($error);
			}

			if(isset($data[$item])) {
				$code .= $data[$item] . "\n";
			}
		}

		// compile data
		$retval = "<?php\n" .
		"// auto-generated by FactoryConfigHandler\n" .
		"// date: %s\n%s\n?>";
		$retval = sprintf($retval, date('m/d/Y H:i:s'), $code);

		return $retval;

	}

	protected function getSettings($itemNode)
	{
		$data = array();
		if($itemNode->hasChildren('parameters')) {
			foreach($itemNode->parameters as $node) {
				$data[$node->getAttribute('name')] = $node->getValue();
			}
		}
		return var_export($data, true);
	}

}

?>
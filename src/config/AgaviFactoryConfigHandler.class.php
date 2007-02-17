<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2003-2007 the Agavi Project.                                |
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
 * @author     David Zülke <dz@bitxtender.com>
 * @author     Dominik del Bondio <ddb@bitxtender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
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
	 * @param      string An optional context in which we are currently running.
	 *
	 * @return     string Data to be written to a cache file.
	 *
	 * @throws     <b>AgaviUnreadableException</b> If a requested configuration
	 *                                             file does not exist or is not
	 *                                             readable.
	 * @throws     <b>AgaviParseException</b> If a requested configuration file is
	 *                                        improperly formatted.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.9.0
	 */
	public function execute($config, $context = null)
	{
		if($context == null) {
			$context = '';
		}

		// parse the config file
		$configurations = $this->orderConfigurations(AgaviConfigCache::parseConfig($config, true, $this->getValidationFile(), $this->parser)->configurations, AgaviConfig::get('core.environment'), $context);
		
		$data = array();
		foreach($configurations as $cfg) {
			// Class names for Execution Container, DispatchFilter, ExecutionFilter, FilterChain, Response and SecurityFilter
			if(isset($cfg->execution_container)) {
				$data['execution_container'] = isset($data['execution_container']) ? $data['execution_container'] : array('class' => null, 'params' => array());
				$data['execution_container']['class'] = $cfg->execution_container->hasAttribute('class')? $cfg->execution_container->getAttribute('class') : $data['execution_container']['class'];
				$data['execution_container']['params'] = $this->getItemParameters($cfg->execution_container, $data['execution_container']['params']);

				$data['execution_container_code'] = '$this->factories["execution_container"] = array("class" => "' . $data['execution_container']['class'] . '", "parameters" => ' . var_export($data['execution_container']['params'], true) . ');';
			}

			if(isset($cfg->dispatch_filter)) {
				$data['dispatch_filter'] = isset($data['dispatch_filter']) ? $data['dispatch_filter'] : array('class' => null, 'params' => array());
				$data['dispatch_filter']['class'] = $cfg->dispatch_filter->hasAttribute('class')? $cfg->dispatch_filter->getAttribute('class') : $data['dispatch_filter']['class'];
				$data['dispatch_filter']['params'] = $this->getItemParameters($cfg->dispatch_filter, $data['dispatch_filter']['params']);

				$data['dispatch_filter_code'] = '$this->factories["dispatch_filter"] = array("class" => "' . $data['dispatch_filter']['class'] . '", "parameters" => ' . var_export($data['dispatch_filter']['params'], true) . ');';
				
				$rc = new ReflectionClass($data['dispatch_filter']['class']);
				if(!$rc->implementsInterface('AgaviIGlobalFilter')) {
					throw new AgaviFactoryException('Specified Dispatch Filter does not implement interface "AgaviIGlobalFilter"');
				}
			}

			if(isset($cfg->execution_filter)) {
				$data['execution_filter'] = isset($data['execution_filter']) ? $data['execution_filter'] : array('class' => null, 'params' => array());
				$data['execution_filter']['class'] = $cfg->execution_filter->hasAttribute('class')? $cfg->execution_filter->getAttribute('class') : $data['execution_filter']['class'];
				$data['execution_filter']['params'] = $this->getItemParameters($cfg->execution_filter, $data['execution_filter']['params']);

				$data['execution_filter_code'] = '$this->factories["execution_filter"] = array("class" => "' . $data['execution_filter']['class'] . '", "parameters" => ' . var_export($data['execution_filter']['params'], true) . ');';
				
				$rc = new ReflectionClass($data['execution_filter']['class']);
				if(!$rc->implementsInterface('AgaviIActionFilter')) {
					throw new AgaviFactoryException('Specified Execution Filter does not implement interface "AgaviIActionFilter"');
				}
			}

			if(isset($cfg->filter_chain)) {
				$data['filter_chain'] = isset($data['filter_chain']) ? $data['filter_chain'] : array('class' => null, 'params' => array());
				$data['filter_chain']['class'] = $cfg->filter_chain->hasAttribute('class')? $cfg->filter_chain->getAttribute('class') : $data['filter_chain']['class'];
				$data['filter_chain']['params'] = $this->getItemParameters($cfg->filter_chain, $data['filter_chain']['params']);

				$data['filter_chain_code'] = '$this->factories["filter_chain"] = array("class" => "' . $data['filter_chain']['class'] . '", "parameters" => ' . var_export($data['filter_chain']['params'], true) . ');';
			}

			// Response
			if(isset($cfg->response)) {
				$data['response'] = isset($data['response']) ? $data['response'] : array('class' => null, 'params' => array());
				$data['response']['class'] = $cfg->response->hasAttribute('class')? $cfg->response->getAttribute('class') : $data['response']['class'];
				$data['response']['params'] = $this->getItemParameters($cfg->response, $data['response']['params']);
				$data['response_code'] =	'$this->factories["response"] = array("class" => "' . $data['response']['class'] . '", "parameters" => ' . var_export($data['response']['params'], true) . ');' . "\n";
			}

			if(AgaviConfig::get('core.use_security', false) && isset($cfg->security_filter)) {
				$data['security_filter'] = isset($data['security_filter']) ? $data['security_filter'] : array('class' => null, 'params' => array());
				$data['security_filter']['class'] = $cfg->security_filter->hasAttribute('class')? $cfg->security_filter->getAttribute('class') : $data['security_filter']['class'];
				$data['security_filter']['params'] = $this->getItemParameters($cfg->security_filter, $data['security_filter']['params']);
				$data['security_filter_code'] = '$this->factories["security_filter"] = array("class" => "' . $data['security_filter']['class'] . '", "parameters" => ' . var_export($data['security_filter']['params'], true) . ');';
				
				$rc = new ReflectionClass($data['security_filter']['class']);
				if(!$rc->implementsInterface('AgaviISecurityFilter') || !$rc->implementsInterface('AgaviIActionFilter')) {
					throw new AgaviFactoryException('Specified Security Filter does not implement interfaces "AgaviISecurityFilter" and "AgaviIActionFilter"');
				}
			}

			// Database
			if(AgaviConfig::get('core.use_database', false) && isset($cfg->database_manager)) {
				$data['database_manager'] = isset($data['database_manager']) ? $data['database_manager'] : array('class' => null, 'params' => array());
				$data['database_manager']['class'] = $cfg->database_manager->hasAttribute('class')? $cfg->database_manager->getAttribute('class') : $data['database_manager']['class'];
				$data['database_manager']['params'] = $this->getItemParameters($cfg->database_manager, $data['database_manager']['params']);

				$data['database_manager_code'] =	'$this->databaseManager = new ' . $data['database_manager']['class'] . '();' . "\n" .
																					'$this->databaseManager->initialize($this, ' . var_export($data['database_manager']['params'], true) . ');';
			}

			// Request
			if(isset($cfg->request)) {
				$data['request'] = isset($data['request']) ? $data['request'] : array('class' => null, 'params' => array());
				$data['request']['class'] = $cfg->request->hasAttribute('class')? $cfg->request->getAttribute('class') : $data['request']['class'];
				$data['request']['params'] = $this->getItemParameters($cfg->request, $data['request']['params']);

				$data['request_code'] =	'$this->request = new ' . $data['request']['class'] . '();' . "\n" . 
																'$this->request->initialize($this, ' . var_export($data['request']['params'], true) . ');';
			}

			// Storage
			if(isset($cfg->storage)) {
				$data['storage'] = isset($data['storage']) ? $data['storage'] : array('class' => null, 'params' => array());
				$data['storage']['class'] = $cfg->storage->hasAttribute('class')? $cfg->storage->getAttribute('class') : $data['storage']['class'];
				$data['storage']['params'] = $this->getItemParameters($cfg->storage, $data['storage']['params']);

				$data['storage_code'] =	'$this->storage = new ' . $data['storage']['class'] . '();' . "\n" .
																'$this->storage->initialize($this, ' . var_export($data['storage']['params'], true) . ');' . "\n" .
																'if($this->storage->getParameter("auto_start", true)) $this->storage->startup();';
			}

			// ValidationManager
			if(isset($cfg->validation_manager)) {
				$data['validation_manager'] = isset($data['validation_manager']) ? $data['validation_manager'] : array('class' => null, 'params' => array());
				$data['validation_manager']['class'] = $cfg->validation_manager->hasAttribute('class')? $cfg->validation_manager->getAttribute('class') : $data['validation_manager']['class'];
				$data['validation_manager']['params'] = $this->getItemParameters($cfg->validation_manager, $data['validation_manager']['params']);

				$data['validation_manager_code'] =	'$this->factories["validation_manager"] = array("class" => "' . $data['validation_manager']['class'] . '", "parameters" => ' . var_export($data['validation_manager']['params'], true) . ');';
			}

			// User
			if(isset($cfg->user)) {
				$data['user'] = isset($data['user']) ? $data['user'] : array('class' => null, 'params' => array());
				$data['user']['class'] = $cfg->user->hasAttribute('class')? $cfg->user->getAttribute('class') : $data['user']['class'];
				$data['user']['params'] = $this->getItemParameters($cfg->user, $data['user']['params']);

				$data['user_code'] =	'$this->user = new ' . $data['user']['class'] . '();' . "\n" .
															'$this->user->initialize($this, ' . var_export($data['user']['params'], true) . ');' .
															'$this->user->startup();';
				
				if(AgaviConfig::get('core.use_security', false)) {
					$rc = new ReflectionClass($data['user']['class']);
					if(!$rc->implementsInterface('AgaviISecurityUser')) {
						throw new AgaviFactoryException('Specified User does not implement interface "AgaviISecurityUser"');
					}
				}
			}

			// LoggerManager
			if(AgaviConfig::get('core.use_logging', false) && isset($cfg->logger_manager)) {
				$data['logger_manager'] = isset($data['logger_manager']) ? $data['logger_manager'] : array('class' => null, 'params' => array());
				$data['logger_manager']['class'] = $cfg->logger_manager->hasAttribute('class')? $cfg->logger_manager->getAttribute('class') : $data['logger_manager']['class'];
				$data['logger_manager']['params'] = $this->getItemParameters($cfg->logger_manager, $data['logger_manager']['params']);

				$data['logger_manager_code'] =	'$this->loggerManager = new ' . $data['logger_manager']['class'] . '();' . "\n" .
																				'$this->loggerManager->initialize($this, ' . var_export($data['logger_manager']['params'], true) . ');';

			}

			// Controller 
			if(isset($cfg->controller)) {
				$data['controller'] = isset($data['controller']) ? $data['controller'] : array('class' => null, 'params' => array());
				$data['controller']['class'] = $cfg->controller->hasAttribute('class')? $cfg->controller->getAttribute('class') : $data['controller']['class'];
				$data['controller']['params'] = $this->getItemParameters($cfg->controller, $data['controller']['params']);

				$data['controller_code'] =	'$this->controller = new ' . $data['controller']['class'] . '();' . "\n" .
																		'$this->controller->initialize($this, ' . var_export($data['controller']['params'], true) . ');';
			}

			// Routing
			// do _not_ check for core.use_routing here
			if(isset($cfg->routing)) {
				$data['routing'] = isset($data['routing']) ? $data['routing'] : array('class' => null, 'params' => array());
				$data['routing']['class'] = $cfg->routing->hasAttribute('class')? $cfg->routing->getAttribute('class') : $data['routing']['class'];
				$data['routing']['params'] = $this->getItemParameters($cfg->routing, $data['routing']['params']);

				$data['routing_code'] =	'$this->routing = new ' . $data['routing']['class'] . '();' . "\n" .
																'$this->routing->initialize($this, ' . var_export($data['routing']['params'], true) . ');';
			}

			// Translation Manager
			if(AgaviConfig::get('core.use_translation', false) && isset($cfg->translation_manager)) {
				$data['translation_manager'] = isset($data['translation_manager']) ? $data['translation_manager'] : array('class' => null, 'params' => array());
				$data['translation_manager']['class'] = $cfg->translation_manager->hasAttribute('class')? $cfg->translation_manager->getAttribute('class') : $data['translation_manager']['class'];
				$data['translation_manager']['params'] = $this->getItemParameters($cfg->translation_manager, $data['translation_manager']['params']);

				$data['translation_manager_code'] =	'$this->translationManager = new ' . $data['translation_manager']['class'] . '();' . "\n" .
																'$this->translationManager->initialize($this, ' . var_export($data['translation_manager']['params'], true) . ');';
			}

		}

		// The order of this initialisiation code is fixed, to not change
		// name => required?
		$requiredItems = array(
			'execution_container' => true,
			'validation_manager' => true,
			'dispatch_filter' => true,
			'execution_filter' => true,
			'security_filter' => AgaviConfig::get('core.use_security', false),
			'filter_chain' => true,
			'response' => true,
			'database_manager' => AgaviConfig::get('core.use_database', false),
			'logger_manager' => AgaviConfig::get('core.use_logging', false),
			'translation_manager' => AgaviConfig::get('core.use_translation', false),
			'controller' => true,
			'storage' => true,
			'request' => true,
			'user' => true,
			'routing' => true,
		);

		$code = '';

		foreach($requiredItems as $item => $required) {
			if($required && !isset($data[$item])) {
				$error = 'Configuration file "%s" is missing an entry for %s in the current configuration';
				$error = sprintf($error, $config, $item);
				throw new AgaviParseException($error);
			}

			if(isset($data[$item])) {
				$code .= $data[$item . '_code'] . "\n\n";
			}
		}

		// compile data
		$retval = "<?php\n" .
		"// auto-generated by ".__CLASS__."\n" .
		"// date: %s GMT\n\n%s?>";
		$retval = sprintf($retval, gmdate('m/d/Y H:i:s'), $code);

		return $retval;

	}
}

?>
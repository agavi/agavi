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
 * AgaviExecutionFilter is the last filter registered for each filter chain.
 * This filter does all action and view execution.
 *
 * @package    agavi
 * @subpackage filter
 *
 * @author     David Zuelke <dz@bitxtender.com>
 * @author     Sean Kerr <skerr@mojavi.org>
 * @copyright  (c) Authors
 * @since      0.9.0
 *
 * @version    $Id$
 */
class AgaviExecutionFilter extends AgaviFilter implements AgaviIActionFilter
{
	/*
	 * The directory inside %core.cache_dir% where cached stuff is stored.
	 */
	const CACHE_SUBDIR = 'content';
	
	/*
	 * The name of the file that holds the cached action data.
	 * Minuses because these are not allowed in an output type name.
	 */
	const ACTION_CACHE_ID = '4-8-15-16-23-42';

	/**
	 * Check if a cache exists and is up-to-date
	 *
	 * @param      array An array of cache groups
	 *
	 * @return     bool true, if the cache is up to date, otherwise false
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function checkCache(array $groups, $lifetime = null)
	{
		foreach($groups as &$group) {
			$group = base64_encode($group);
		}
		$filename = AgaviConfig::get('core.cache_dir') . DIRECTORY_SEPARATOR . self::CACHE_SUBDIR . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $groups) . '.cefcache';
		$isReadable = is_readable($filename);
		if($lifetime === null || !$isReadable) {
			return $isReadable;
		} else {
			$expiry = strtotime('+' . $lifetime, filemtime($filename));
			if($expiry !== false) {
				return $isReadable && ($expiry >= time());
			} else {
				return false;
			}
		}
	}

	/**
	 * Read the contents of a cache
	 *
	 * @param      array An array of cache groups
	 *
	 * @return     array The cache data
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function readCache(array $groups)
	{
		foreach($groups as &$group) {
			$group = base64_encode($group);
		}
		return include(AgaviConfig::get('core.cache_dir') . DIRECTORY_SEPARATOR . self::CACHE_SUBDIR . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $groups) . '.cefcache');
	}

	/**
	 * Write cache content
	 *
	 * @param      array An array of cache groups
	 * @param      array The cache data
	 *
	 * @return     bool true, if the cache is up to date, otherwise false
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function writeCache(array $groups, $data)
	{
		foreach($groups as &$group) {
			$group = base64_encode($group);
		}
		@mkdir(AgaviConfig::get('core.cache_dir') . DIRECTORY_SEPARATOR  . self::CACHE_SUBDIR . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR , array_slice($groups, 0, -1)), 0777, true);
		return file_put_contents(AgaviConfig::get('core.cache_dir') . DIRECTORY_SEPARATOR . self::CACHE_SUBDIR . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $groups) . '.cefcache', '<' . '?' . 'php return ' . var_export($data, true) . ';');
	}

	/**
	 * Flushes the cache for a group
	 *
	 * @param      array An array of cache groups
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public static function clearCache(array $groups = array())
	{
		foreach($groups as &$group) {
			$group = base64_encode($group);
		}
		$path = AgaviConfig::get('core.cache_dir') . DIRECTORY_SEPARATOR . self::CACHE_SUBDIR . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $groups) . '.cefcache';
		if(is_file($path)) {
			AgaviToolkit::clearCache($path);
		} else {
			AgaviToolkit::clearCache(self::CACHE_SUBDIR . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, array_slice($groups, 0, -1)));
		}
	}

	/**
	 * Builds an array of cache groups
	 *
	 * @param      array  The configuration
	 * @param      string The Action's Module name
	 * @param      string The Action's name
	 *
	 * @return     array An array of groups
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function determineGroups(array $cfg, $moduleName, $actionName)
	{
		$groups = array();

		if(isset($cfg['groups'])) {
			foreach($cfg['groups'] as $group) {
				$group += array('name' => null, 'source' => null, 'namespace' => null);
				$val = $this->getVariable($group['name'], $group['source'], $group['namespace']);
				if($val === null) {
					$val = "0";
				}
				$groups[] = $val;
			}
		}

		$groups[] = $moduleName . '_' . $actionName;

		return $groups;
	}
	
	public function getVariable($name, $source = 'string', $namespace = null)
	{
		switch($source) {
			case 'constant':
				$val = constant($name);
				break;
			case 'locale':
				$val = $this->context->getTranslationManager()->getCurrentLocaleIdentifier();
				break;
			case 'requestParameter':
				$val = $this->context->getRequest()->getParameter($name);
				break;
			case 'requestAttribute':
				$val = $this->context->getRequest()->getAttribute($name, $namespace);
				break;
			case 'userParameter':
				$val = $this->context->getUser()->getParameter($name);
				break;
			case 'userAttribute':
				$val = $this->context->getUser()->getAttribute($name, $namespace);
				break;
			case 'userCredential':
				$val = $this->context->getUser()->hasCredential($name);
				break;
			default:
				$val = $name;
		}
		return $val;
	}

	/**
	 * Execute this filter.
	 *
	 * @param      AgaviFilterChain        The filter chain.
	 * @param      AgaviExecutionContainer The current execution container.
	 *
	 * @throws     <b>AgaviInitializationException</b> If an error occurs during
	 *                                                 View initialization.
	 * @throws     <b>AgaviViewException</b>           If an error occurs while
	 *                                                 executing the View.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function execute(AgaviFilterChain $filterChain, AgaviExecutionContainer $container)
	{
		$response = $container->getResponse();
		
		$lm = $this->context->getLoggerManager();
		// get the context, controller and validator manager
		$controller = $this->context->getController();

		// get the current action information
		$actionName = $container->getActionName();
		$moduleName = $container->getModuleName();

		$request = $this->context->getRequest();

		$isCacheable = false;
		if($this->getParameter('enable_caching', false) && is_readable($cachingDotXml = AgaviConfig::get('core.module_dir') . '/' . $moduleName . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'caching.xml')) {
			// $lm->log(new AgaviLoggerMessage('Caching enabled, configuration file found, loading...'));
			$defaultConfig = array(
				'enabled' => true,
				// lifetime. null = forever
				'lifetime' => null,
				// request methods to cache. null = any
				'methods' => null,
				// group definitions
				'groups' => array(
				),
				// views to cache. null = any
				'views' => null,
				// a list of names of action attributes to cache and restore so they are available in view initialization
				'actionAttributes' => array(
				),
				// a list of request attributes to cache and restore so they are available for subsequent stuff
				'requestAttributes' => array(
				),
				'decorator' => array(
					'include' => true,
					'slots' => array(
					),
					'variables' => array(
					),
				)
			);
			// // $lm->log(new AgaviLoggerMessage(print_r(include(AgaviConfigCache::checkConfig($cachingDotXml)), true)));
			//			$config = array_merge($config, include(AgaviConfigCache::checkConfig($cachingDotXml)));
			$config['SearchEngineSpam'] = array_merge($defaultConfig, array(
				'enabled' => false,
				'lifetime' => '10 seconds',
				// group definitions
				'groups' => array(
					array(
						'name' => 'index',
					),
					array(
						'source' => 'requestParameter',
						'name' => 'name',
					),
					array(
						'source' => 'locale',
					),
				),
				'actionAttributes' => array(
					'testing'
				),
				// other variables to cache and restore so they are available for subsequent stuff
				'requestAttributes' => array(
					// array(
					// 	'namespace' => 'foo.bar',
					// 	'name' => 'testing',
					// ),
				),
				'decorator' => array(
					'include' => true,
					'slots' => array(
					),
					'variables' => array(
					),
				),
			));
			if(isset($config[$actionName]) && $config[$actionName]['enabled'] && (!is_array($config[$actionName]['methods']) || in_array($method = $request->getMethod(), $config[$actionName]['methods']))) {
				// $lm->log(new AgaviLoggerMessage('Current action and request method are configured for caching, proceeding...'));
				$config = $config[$actionName];
				$groups = $this->determineGroups($config, $moduleName, $actionName);
				// $lm->log(new AgaviLoggerMessage('Fetched groups "' . implode('", "', $groups) . '"'));
				$isCacheable = true;
			}
		}

		$isActionCached = false;

		if($isCacheable) {
			$isActionCached = $this->checkCache(array_merge($groups, array(self::ACTION_CACHE_ID)), $config['lifetime']);
		} else {
			// $lm->log(new AgaviLoggerMessage('Action is not cacheable!'));
		}

		if($isActionCached) {
			// $lm->log(new AgaviLoggerMessage('Action is cached, loading...'));
			$viewModule = '';
			$viewName = '';
			// cache/dir/4-8-15-16-23-42 contains the action cache
			$actionCache = $this->readCache(array_merge($groups, array(self::ACTION_CACHE_ID)));
			$viewModule = $actionCache['viewModule'];
			$viewName = $actionCache['viewName'];
			$actionAttributes = $actionCache['actionAttributes'];
		} else {
			// $lm->log(new AgaviLoggerMessage('Action not cached, executing...'));
			// execute the Action and get the View to execute
			list($viewModule, $viewName) = $this->runAction($container);
			
			// check if the returned view is even cacheable
			if($isCacheable && is_array($config['views']) && !(in_array($viewName, $config['views'], true) || in_array(array($viewModule, $viewName), $config['views']))) {
				$isCacheable = false;
				// $lm->log(new AgaviLoggerMessage('Returned View is not cleared for caching, setting cacheable status to false.'));
			} else {
				// $lm->log(new AgaviLoggerMessage('Returned View is cleared for caching, proceeding...'));
			}
			
			$actionAttributes = $container->getAttributes();
		}

		if($viewName !== AgaviView::NONE) {

			$container->setViewModuleName($viewModule);
			$container->setViewName($viewName);

			// get the view instance
			$viewInstance = $controller->createViewInstance($viewModule, $viewName);

			// initialize the view
			$viewInstance->initialize($container);

			$isViewCached = false;

			if($isCacheable) {
				$outputType = $this->context->getController()->getOutputType();

				if($isActionCached) {
					$isViewCached = $this->checkCache(array_merge($groups, array($outputType)));
				}
			}

			if($isViewCached) {
				// $lm->log(new AgaviLoggerMessage('View is cached, loading...'));
				$viewCache = $this->readCache(array_merge($groups, array($outputType)));
				$response->import($viewCache['response']);
				foreach($viewCache['requestAttributes'] as $requestAttribute) {
					$request->setAttribute($requestAttribute['name'], $requestAttribute['value'], $requestAttribute['namespace']);
				}
			} else {
				// $lm->log(new AgaviLoggerMessage('View is not cached, executing...'));
				// view initialization completed successfully
				$executeMethod = 'execute' . $this->context->getController()->getOutputType();
				if(!method_exists($viewInstance, $executeMethod)) {
					$executeMethod = 'execute';
				}
				$key = $request->toggleLock();
				$next = $viewInstance->$executeMethod($container);
				$request->toggleLock($key);
				
				if($next instanceof AgaviExecutionContainer) {
					$container->setNext($next);
				} else {
					$attributes =& $viewInstance->getAttributes();

					$output = array();
					$nextOutput = null;
					foreach($viewInstance->getLayers() as $layerName => $layer) {
						foreach($layer->getSlots() as $slotName => $slotContainer) {
							$slotResponse = $slotContainer->execute();
							// FIXME: this if is always true
							if($slotResponse) {
								// set the presentation data as a template attribute
								$output[$name] = $slotResponse->getContent();
								// let our response grab the stuff it needs from the slot's response
								$response->merge($slotResponse);
							} else {
								$output[$name] = null;
							}
						}
						$nextOutput = $layer->getRenderer()->render($layer, $attributes, $output);
						$output = array();
						$output[$layerName] = $nextOutput;
					}
					$response->setContent($nextOutput);
				}
			}

			if($isCacheable) {
				if(!$isActionCached) {
					$actionCache = array();

					$actionCache['viewModule'] = $viewModule;
					$actionCache['viewName'] = $viewName;

					$actionCache['actionAttributes'] = array();
					foreach($config['actionAttributes'] as $attributeName) {
						$actionCache['actionAttributes'][$attributeName] = $actionAttributes[$attributeName];
					}
					
					// $lm->log(new AgaviLoggerMessage('Writing Action cache...'));
					
					$this->writeCache(array_merge($groups, array(self::ACTION_CACHE_ID)), $actionCache);
				}
				if(!$isViewCached) {
					$viewCache = array();
					
					$viewCache['response'] = $response->export();
					
					$viewCache['requestAttributes'] = array();
					foreach($config['requestAttributes'] as $requestAttribute) {
						$viewCache['requestAttributes'][] = $requestAttribute + array('value' => $request->getAttribute($requestAttribute['name'], $requestAttribute['namespace']));
					}
					
					$this->writeCache(array_merge($groups, array($outputType)), $viewCache);
					
					// $lm->log(new AgaviLoggerMessage('Writing View cache...'));
				}
			}
		}
		// $lm->log(new AgaviLoggerMessage(print_r($request->getAttributeNamespace('foo.bar'), true)));
	}

	/**
	 * Execute the Action
	 *
	 * @param      AgaviExecutionContainer The current execution container.
	 *
	 * @return     mixed The processed View information returned by the Action.
	 *
	 * @throws     AgaviViewException If the returned View does not exist.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	protected function runAction(AgaviExecutionContainer $container)
	{
		$viewName = null;

		$controller = $this->context->getController();
		$request = $this->context->getRequest();
		$validationManager = $this->context->getValidationManager();
		// clear the validator manager for reuse
		$validationManager->clear();

		// get the current action instance
		$actionInstance = $container->getActionInstance();

		// get the current action information
		$moduleName = $container->getModuleName();
		$actionName = $container->getActionName();

		// get the (already formatted) request method
		$method = $request->getMethod();

		$useGenericMethods = false;
		$executeMethod = 'execute' . $method;
		if(!method_exists($actionInstance, $executeMethod)) {
			$executeMethod = 'execute';
			$useGenericMethods = true;
		}

		if($useGenericMethods && !method_exists($actionInstance, $executeMethod) ) {
			// this action will skip validation/execution for this method
			// get the default view
			$viewName = $actionInstance->getDefaultViewName();
		} else {
			// set default validated status
			$validated = true;

			// get the current action validation configuration
			$validationConfig = AgaviConfig::get('core.module_dir') . '/' . $moduleName . '/validate/' . $actionName . '.xml';

			if(is_readable($validationConfig)) {
				// load validation configuration
				// do NOT use require_once
				require(AgaviConfigCache::checkConfig($validationConfig));
			}

			// manually load validators
			$registerValidatorsMethod = 'register' . $method . 'Validators';
			if(!method_exists($actionInstance, $registerValidatorsMethod)) {
				$registerValidatorsMethod = 'registerValidators';
			}
			$actionInstance->$registerValidatorsMethod();

			// process validators
			$validated = $validationManager->execute($container);

			$validateMethod = 'validate' . $method;
			if(!method_exists($actionInstance, $validateMethod)) {
				$validateMethod = 'validate';
			}

			// prevent access to Request::getParameters()
			// process manual validation
			if($actionInstance->$validateMethod($container) && $validated) {
				// execute the action
				$key = $request->toggleLock();
				$viewName = $actionInstance->$executeMethod($container);
				$request->toggleLock($key);
			} else {
				// validation failed
				$handleErrorMethod = 'handle' . $method . 'Error';
				if(!method_exists($actionInstance, $handleErrorMethod)) {
					$handleErrorMethod = 'handleError';
				}
				$key = $request->toggleLock();
				$viewName = $actionInstance->$handleErrorMethod($container);
				$request->toggleLock($key);
			}
		}

		if(is_array($viewName)) {
			// we're going to use an entirely different action for this view
			$viewModule = $viewName[0];
			$viewName   = $viewName[1];
		} elseif($viewName !== AgaviView::NONE) {
			// use a view related to this action
			$viewName = $actionName . $viewName;
			$viewModule = $moduleName;
		} else {
			$viewName = AgaviView::NONE;
			$viewModule = AgaviView::NONE;
		}

		return array($viewModule, $viewName);
	}
}

?>
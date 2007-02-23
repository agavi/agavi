<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2003-2007 the Agavi Project.                                |
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
 * @author     David Zülke <dz@bitxtender.com>
 * @author     Sean Kerr <skerr@mojavi.org>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
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
	 * @author     David Zülke <dz@bitxtender.com>
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
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function readCache(array $groups)
	{
		foreach($groups as &$group) {
			$group = base64_encode($group);
		}
		return unserialize(file_get_contents(AgaviConfig::get('core.cache_dir') . DIRECTORY_SEPARATOR . self::CACHE_SUBDIR . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $groups) . '.cefcache'));
	}

	/**
	 * Write cache content
	 *
	 * @param      array An array of cache groups
	 * @param      array The cache data
	 *
	 * @return     bool true, if the cache is up to date, otherwise false
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function writeCache(array $groups, $data)
	{
		foreach($groups as &$group) {
			$group = base64_encode($group);
		}
		@mkdir(AgaviConfig::get('core.cache_dir') . DIRECTORY_SEPARATOR  . self::CACHE_SUBDIR . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR , array_slice($groups, 0, -1)), 0777, true);
		return file_put_contents(AgaviConfig::get('core.cache_dir') . DIRECTORY_SEPARATOR . self::CACHE_SUBDIR . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $groups) . '.cefcache', serialize($data));
	}

	/**
	 * Flushes the cache for a group
	 *
	 * @param      array An array of cache groups
	 *
	 * @author     David Zülke <dz@bitxtender.com>
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
	 * Builds an array of cache groups using the configuration and a container.
	 *
	 * @param      array                   The group array from the configuration.
	 * @param      AgaviExecutionContainer The execution container.
	 *
	 * @return     array An array of groups.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function determineGroups(array $groups, $container)
	{
		$retval = array();
		
		foreach($groups as $group) {
			$group += array('name' => null, 'source' => null, 'namespace' => null);
			$val = $this->getVariable($group['name'], $group['source'], $group['namespace']);
			if($val === null) {
				$val = "0";
			}
			$retval[] = $val;
		}
		
		$retval[] = $container->getModuleName() . '_' . $container->getActionName();
		
		return $retval;
	}
	
	/**
	 * Read a variable from the given source and, optionally, namespace.
	 *
	 * @param      string The variable name.
	 * @param      string The optional variable source.
	 * @param      string The optional namespace in the source.
	 *
	 * @return     mixed The variable.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getVariable($name, $source = 'string', $namespace = null)
	{
		switch($source) {
			case 'constant':
				$val = constant($name);
				break;
			case 'locale':
				$val = $this->context->getTranslationManager()->getCurrentLocaleIdentifier();
				break;
			case 'request_parameter':
				$val = $this->context->getRequest()->getRequestData()->getParameter($name);
				break;
			case 'request_attribute':
				$val = $this->context->getRequest()->getAttribute($name, $namespace);
				break;
			case 'user_parameter':
				$val = $this->context->getUser()->getParameter($name);
				break;
			case 'user_attribute':
				$val = $this->context->getUser()->getAttribute($name, $namespace);
				break;
			case 'user_authenticated':
				if(($user = $this->context->getUser()) instanceof AgaviISecurityUser) {
					$val = $user->isAuthenticated();
					break;
				}
			case 'user_credential':
				if(($user = $this->context->getUser()) instanceof AgaviISecurityUser) {
					$val = $user->hasCredentials($name);
					break;
				}
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
	 * @author     David Zülke <dz@bitxtender.com>
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function execute(AgaviFilterChain $filterChain, AgaviExecutionContainer $container)
	{
		// $lm = $this->context->getLoggerManager();
		
		// get the context, controller and validator manager
		$controller = $this->context->getController();
		
		// get the current action information
		$actionName = $container->getActionName();
		$moduleName = $container->getModuleName();
		
		$request = $this->context->getRequest();
		
		$isCacheable = false;
		if($this->getParameter('enable_caching', true) && is_readable($cachingDotXml = AgaviConfig::get('core.module_dir') . '/' . $moduleName . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . $actionName . '.xml')) {
			// $lm->log('Caching enabled, configuration file found, loading...');
			// no _once please!
			include(AgaviConfigCache::checkConfig($cachingDotXml));
		}
		
		$isActionCached = false;
		
		
		if($isCacheable) {
			$groups = $this->determineGroups($config["groups"], $container);
			$isActionCached = $this->checkCache(array_merge($groups, array(self::ACTION_CACHE_ID)), $config['lifetime']);
		} else {
			// $lm->log('Action is not cacheable!');
		}
		
		if($isActionCached) {
			// $lm->log('Action is cached, loading...');
			// cache/dir/4-8-15-16-23-42 contains the action cache
			$actionCache = $this->readCache(array_merge($groups, array(self::ACTION_CACHE_ID)));
		} else {
			// $lm->log('Action not cached, executing...');
			// execute the Action and get the View to execute
			list($actionCache['view_module'], $actionCache['view_name']) = $this->runAction($container);
			
			// check if the returned view is cacheable
			if($isCacheable && is_array($config['views']) && !in_array(array('module' => $actionCache['view_module'], 'name' => $actionCache['view_name']), $config['views'], true)) {
				$isCacheable = false;
				// $lm->log('Returned View is not cleared for caching, setting cacheable status to false.');
			} else {
				// $lm->log('Returned View is cleared for caching, proceeding...');
			}
			
			$actionAttributes = $container->getAttributes();
		}
		
		// create a new response instance for this action
		$rfi = $this->context->getFactoryInfo('response');
		$response = new $rfi['class'];
		$response->initialize($this->context, $rfi['parameters']);
		$container->setResponse($response);
		
		if($actionCache['view_name'] !== AgaviView::NONE) {
			
			$container->setViewModuleName($actionCache['view_module']);
			$container->setViewName($actionCache['view_name']);
			
			// get the view instance
			$viewInstance = $controller->createViewInstance($actionCache['view_module'], $actionCache['view_name']);
			
			// initialize the view
			$viewInstance->initialize($container);
			
			$outputType = $container->getOutputType()->getName();
			
			$isViewCached = false;
			
			if($isCacheable) {
				if(isset($config['output_types'][$otConfig = $outputType]) || isset($config['output_types'][$otConfig = '*'])) {
					$otConfig = $config['output_types'][$otConfig];
					
					if($isActionCached) {
						$isViewCached = $this->checkCache(array_merge($groups, array($outputType)));
					}
				} else {
					$isCacheable = false;
				}
			}
			
			if($isViewCached) {
				// $lm->log('View is cached, loading...');
				$viewCache = $this->readCache(array_merge($groups, array($outputType)));
			} else {
				$viewCache = array();
				
				// $lm->log('View is not cached, executing...');
				// view initialization completed successfully
				$executeMethod = 'execute' . $outputType;
				if(!method_exists($viewInstance, $executeMethod)) {
					$executeMethod = 'execute';
				}
				$key = $request->toggleLock();
				$viewCache['next'] = $viewInstance->$executeMethod($container->getRequestData());
				$request->toggleLock($key);
			}
			
			if($viewCache['next'] instanceof AgaviExecutionContainer) {
				// $lm->log('Forwarding request, skipping rendering...');
				$container->setNext($viewCache['next']);
			} else {
				if($isViewCached) {
					$layers = $viewCache['layers'];
					$response = $viewCache['response'];
					$container->setResponse($response);
				
					foreach($viewCache['template_variables'] as $name => $value) {
						$viewInstance->setAttribute($name, $value);
					}
				
					foreach($viewCache['request_attributes'] as $requestAttribute) {
						$request->setAttribute($requestAttribute['name'], $requestAttribute['value'], $requestAttribute['namespace']);
					}
				
					$output = array();
					$nextOutput = $response->getContent();
				} else {
					$layers = $viewInstance->getLayers();
				
					if($isCacheable) {
						$viewCache['template_variables'] = array();
						foreach($otConfig['template_variables'] as $varName) {
							$viewCache['template_variables'][$varName] = $viewInstance->getAttribute($varName);
						}
					
						$viewCache['response'] = clone $response;
					
						$viewCache['layers'] = array();
					
						$viewCache['slots'] = array();
					
						$lastCacheableLayer = -1;
						if(is_array($otConfig['layers'])) {
							if(count($otConfig['layers'])) {
								for($i = count($layers)-1; $i >= 0; $i--) {
									$layer = $layers[$i];
									$layerName = $layer->getName();
									$cacheSlots[$layerName] = array();
									if(isset($otConfig['layers'][$layerName])) {
										if(is_array($otConfig['layers'][$layerName])) {
											$lastCacheableLayer = $i - 1;
										} else {
											$lastCacheableLayer = $i;
										}
									}
								}
							}
						} else {
							$lastLayer = end($layers);
							if($lastLayer !== false) {
								$lastCacheableLayer = $lastLayer->getName();
							}
						}
					
						for($i = $lastCacheableLayer + 1; $i < count($layers); $i++) {
							// $lm->log('Adding non-cacheable layer "' . $layers[$i]->getName() . '" to list');
							$viewCache['layers'][] = clone $layers[$i];
						}
					}
				
					$output = array();
					$nextOutput = null;
				}
				
				$attributes =& $viewInstance->getAttributes();
				
				// $lm->log('Starting rendering...');
				for($i = 0; $i < count($layers); $i++) {
					$layer = $layers[$i];
					$layerName = $layer->getName();
					// $lm->log('Running layer "' . $layerName . '"...');
					foreach($layer->getSlots() as $slotName => $slotContainer) {
						if($isViewCached && isset($viewCache['slots'][$layerName][$slotName])) {
							// $lm->log('Loading cached slot "' . $slotName . '"...');
							$slotResponse = $viewCache['slots'][$layerName][$slotName];
						} else {
							// $lm->log('Running slot "' . $slotName . '"...');
							$slotResponse = $slotContainer->execute();
							if($isCacheable && !$isViewCached && isset($otConfig['layers'][$layerName]) && in_array($slotName, $otConfig['layers'][$layerName])) {
								// $lm->log('Adding response of slot "' . $slotName . '" to cache...');
								$viewCache['slots'][$layerName][$slotName] = $slotResponse;
							}
						}
						// set the presentation data as a template attribute
						if(($output[$slotName] = $slotResponse->getContent()) !== null) {
							// $lm->log('Merging in response from slot "' . $slotName . '"...');
							// the slot really output something
							// let our response grab the stuff it needs from the slot's response
							$response->merge($slotResponse);
						}
					}
					$moreAssigns = array(
						'container' => $container,
						'inner' => $nextOutput,
						'request_data' => $container->getRequestData(),
						'validation_manager' => $container->getValidationManager(),
						'view' => $viewInstance,
					);
					$nextOutput = $layer->getRenderer()->render($layer, $attributes, $output, $moreAssigns);
					
					$response->setContent($nextOutput);
					
					if($isCacheable && !$isViewCached && $i === $lastCacheableLayer) {
						$viewCache['response'] = clone $response;
					}
					
					$output = array();
					$output[$layer->getName()] = $nextOutput;
				}
			}
			
			if($isCacheable) {
				if(!$isActionCached) {
					$actionCache['action_attributes'] = array();
					foreach($config['action_attributes'] as $attributeName) {
						$actionCache['action_attributes'][$attributeName] = $actionAttributes[$attributeName];
					}
					
					// $lm->log('Writing Action cache...');
					
					$this->writeCache(array_merge($groups, array(self::ACTION_CACHE_ID)), $actionCache);
				}
				if(!$isViewCached) {
					$viewCache['request_attributes'] = array();
					foreach($otConfig['request_attributes'] as $requestAttribute) {
						$viewCache['request_attributes'][] = $requestAttribute + array('value' => $request->getAttribute($requestAttribute['name'], $requestAttribute['namespace']));
					}
					
					$this->writeCache(array_merge($groups, array($outputType)), $viewCache);
					
					// $lm->log('Writing View cache...');
				}
			}
		}
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
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	protected function runAction(AgaviExecutionContainer $container)
	{
		$viewName = null;

		$controller = $this->context->getController();
		$request = $this->context->getRequest();
		$validationManager = $container->getValidationManager();

		// get the current action instance
		$actionInstance = $container->getActionInstance();

		// get the current action information
		$moduleName = $container->getModuleName();
		$actionName = $container->getActionName();

		// get the (already formatted) request method
		$method = $request->getMethod();
		
		$requestData = $container->getRequestData();

		$useGenericMethods = false;
		$executeMethod = 'execute' . $method;
		if(!method_exists($actionInstance, $executeMethod)) {
			$executeMethod = 'execute';
			$useGenericMethods = true;
		}

		if($actionInstance->isSimple() || ($useGenericMethods && !method_exists($actionInstance, $executeMethod))) {
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
			$validated = $validationManager->execute($requestData);
			
			$validateMethod = 'validate' . $method;
			if(!method_exists($actionInstance, $validateMethod)) {
				$validateMethod = 'validate';
			}
			
			// prevent access to Request::getParameters()
			// process manual validation
			if($actionInstance->$validateMethod($requestData) && $validated) {
				// execute the action
				$key = $request->toggleLock();
				$viewName = $actionInstance->$executeMethod($requestData);
				$request->toggleLock($key);
			} else {
				// validation failed
				$handleErrorMethod = 'handle' . $method . 'Error';
				if(!method_exists($actionInstance, $handleErrorMethod)) {
					$handleErrorMethod = 'handleError';
				}
				$key = $request->toggleLock();
				$viewName = $actionInstance->$handleErrorMethod($requestData);
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
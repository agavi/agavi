<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2005-2011 the Agavi Project.                                |
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

	/*
	 * Constants for the cache callback event types.
	 */
	const CACHE_CALLBACK_ACTION_NOT_CACHED = 0;
	const CACHE_CALLBACK_ACTION_CACHE_GONE = 1;
	const CACHE_CALLBACK_VIEW_NOT_CACHEABLE = 2;
	const CACHE_CALLBACK_VIEW_NOT_CACHED = 3;
	const CACHE_CALLBACK_OUTPUT_TYPE_NOT_CACHEABLE = 4;
	const CACHE_CALLBACK_VIEW_CACHE_GONE = 5;
	const CACHE_CALLBACK_ACTION_CACHE_USELESS = 6;
	const CACHE_CALLBACK_VIEW_CACHE_WRITTEN = 7;
	const CACHE_CALLBACK_ACTION_CACHE_WRITTEN = 8;
	
	/**
	 * Method that's called when a cacheable Action/View with a stale cache is
	 * about to be run.
	 * Can be used to prevent stampede situations where many requests to an action
	 * with an out-of-date cache are run in parallel, slowing down everything.
	 * For instance, you could set a flag into memcached with the groups of the
	 * action that's currently run, and in checkCache check for those and return
	 * an old, stale cache until the flag is gone.
	 *
	 * @param      int                     The type of the event that occurred.
	 *                                     See CACHE_CALLBACK_* constants.
	 * @param      array                   The groups.
	 * @param      array                   The caching configuration.
	 * @param      AgaviExecutionContainer The current execution container.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.0
	 */
	public function startedCacheCreationCallback($eventType, array $groups, array $config, AgaviExecutionContainer $container)
	{
	}
	
	/**
	 * Method that's called when an Action/View that was assumed to be cacheable
	 * turned out not to be (because the View or Output Type isn't).
	 *
	 * @see        AgaviExecutionFilter::startedCacheCreationCallback()
	 *
	 * @param      int                     The type of the event that occurred.
	 *                                     See CACHE_CALLBACK_* constants.
	 * @param      array                   The groups.
	 * @param      array                   The caching configuration.
	 * @param      AgaviExecutionContainer The current execution container.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.0
	 */
	public function abortedCacheCreationCallback($eventType, array $groups, array $config, AgaviExecutionContainer $container)
	{
	}
	
	/**
	 * Method that's called when a cacheable Action/View with a stale cache has
	 * finished execution and all caches are written.
	 *
	 * @see        AgaviExecutionFilter::startedCacheCreationCallback()
	 *
	 * @param      int                     The type of the event that occurred.
	 *                                     See CACHE_CALLBACK_* constants.
	 * @param      array                   The groups.
	 * @param      array                   The caching configuration.
	 * @param      AgaviExecutionContainer The current execution container.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.0
	 */
	public function finishedCacheCreationCallback($eventType, array $groups, array $config, AgaviExecutionContainer $container)
	{
	}
	
	/**
	 * Check if a cache exists and is up-to-date
	 *
	 * @param      array  An array of cache groups
	 * @param      string The lifetime of the cache as a strtotime relative string
	 *                    without the leading plus sign.
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
		$filename = AgaviConfig::get('core.cache_dir') . DIRECTORY_SEPARATOR . self::CACHE_SUBDIR . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $groups) . '.cefcache';
		$data = @file_get_contents($filename);
		if($data !== false) {
			return unserialize($data);
		} else {
			throw new AgaviException(sprintf('Failed to read cache file "%s"', $filename));
		}
	}

	/**
	 * Write cache content
	 *
	 * @param      array  An array of cache groups
	 * @param      array  The cache data
	 * @param      string The lifetime of the cache as a strtotime relative string
	 *                    without the leading plus sign.
	 *
	 * @return     bool The result of the write operation
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function writeCache(array $groups, $data, $lifetime = null)
	{
		// lifetime is not used in this implementation!
		
		foreach($groups as &$group) {
			$group = base64_encode($group);
		}
		@mkdir(AgaviConfig::get('core.cache_dir') . DIRECTORY_SEPARATOR  . self::CACHE_SUBDIR . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR , array_slice($groups, 0, -1)), 0777, true);
		return file_put_contents(AgaviConfig::get('core.cache_dir') . DIRECTORY_SEPARATOR . self::CACHE_SUBDIR . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $groups) . '.cefcache', serialize($data), LOCK_EX);
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
		$path = self::CACHE_SUBDIR . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $groups);
		if(is_file(AgaviConfig::get('core.cache_dir') . DIRECTORY_SEPARATOR . $path . '.cefcache')) {
			AgaviToolkit::clearCache($path . '.cefcache');
		} else {
			AgaviToolkit::clearCache($path);
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
	public function determineGroups(array $groups, AgaviExecutionContainer $container)
	{
		$retval = array();

		foreach($groups as $group) {
			$group += array('name' => null, 'source' => null, 'namespace' => null);
			$val = $this->getVariable($group['name'], $group['source'], $group['namespace'], $container);
			
			if(is_object($val) && is_callable(array($val, '__toString'))) {
				$val = $val->__toString();
			} elseif(is_object($val)) {
				$val = spl_object_hash($val);
			}
			
			if($val === null || $val === false || $val === '') {
				$val = '0';
			}
			
			if(!is_scalar($val)) {
				throw new AgaviUncacheableException('Group value is not a scalar, cannot construct a meaningful string representation.');
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
	 * @param      AgaviExecutionContainer The container to use, if necessary.
	 *
	 * @return     mixed The variable.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getVariable($name, $source = 'string', $namespace = null, AgaviExecutionContainer $container = null)
	{
		$val = $name;
		
		switch($source) {
			case 'callback':
				$val = $container->getActionInstance()->$name();
				break;
			case 'configuration_directive':
				$val = AgaviConfig::get($name);
				break;
			case 'constant':
				$val = constant($name);
				break;
			case 'container_parameter':
				$val = $container->getParameter($name);
				break;
			case 'global_request_data':
				$val = $this->context->getRequest()->getRequestData()->get($namespace ? $namespace : AgaviRequestDataHolder::SOURCE_PARAMETERS, $name);
				break;
			case 'locale':
				$val = $this->context->getTranslationManager()->getCurrentLocaleIdentifier();
				break;
			case 'request_attribute':
				$val = $this->context->getRequest()->getAttribute($name, $namespace);
				break;
			case 'request_data':
				$val = $container->getRequestData()->get($namespace ? $namespace : AgaviRequestDataHolder::SOURCE_PARAMETERS, $name);
				break;
			case 'request_parameter':
				$val = $this->context->getRequest()->getRequestData()->getParameter($name);
				break;
			case 'user_attribute':
				$val = $this->context->getUser()->getAttribute($name, $namespace);
				break;
			case 'user_authenticated':
				if(($user = $this->context->getUser()) instanceof AgaviISecurityUser) {
					$val = $user->isAuthenticated();
				}
				break;
			case 'user_credential':
				if(($user = $this->context->getUser()) instanceof AgaviISecurityUser) {
					$val = $user->hasCredentials($name);
				}
				break;
			case 'user_parameter':
				$val = $this->context->getUser()->getParameter($name);
				break;
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
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
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
		
		// the action instance
		$actionInstance = $container->getActionInstance();

		$request = $this->context->getRequest();

		$isCacheable = false;
		$cachingDotXml = AgaviToolkit::evaluateModuleDirective(
			$moduleName,
			'agavi.cache.path',
			array(
				'moduleName' => $moduleName,
				'actionName' => $actionName,
			)
		);
		if($this->getParameter('enable_caching', true) && is_readable($cachingDotXml)) {
			// $lm->log('Caching enabled, configuration file found, loading...');
			// no _once please!
			include(AgaviConfigCache::checkConfig($cachingDotXml, $this->context->getName()));
		}

		$isActionCached = false;

		if($isCacheable) {
			try {
				$groups = $this->determineGroups($config['groups'], $container);
				$actionGroups = array_merge($groups, array(self::ACTION_CACHE_ID));
			} catch(AgaviUncacheableException $e) {
				// a group callback threw an exception. that means we're not allowed t cache
				$isCacheable = false;
			}
			if($isCacheable) {
				// this is not wrapped in the try/catch block above as it might throw an exception itself
				$isActionCached = $this->checkCache(array_merge($groups, array(self::ACTION_CACHE_ID)), $config['lifetime']);
			
				if(!$isActionCached) {
					// cacheable, but action is not cached. notify our callback so it can prevent the stampede that follows
					$this->startedCacheCreationCallback(self::CACHE_CALLBACK_ACTION_NOT_CACHED, $actionGroups, $config, $container);
				}
			}
		} else {
			// $lm->log('Action is not cacheable!');
		}

		if($isActionCached) {
			// $lm->log('Action is cached, loading...');
			// cache/dir/4-8-15-16-23-42 contains the action cache
			try {
				$actionCache = $this->readCache($actionGroups);
				// and restore action attributes
				$actionInstance->setAttributes($actionCache['action_attributes']);
			} catch(AgaviException $e) {
				// cacheable, but action is not cached. notify our callback so it can prevent the stampede that follows
				$this->startedCacheCreationCallback(self::CACHE_CALLBACK_ACTION_CACHE_GONE, $actionGroups, $config, $container);
				$isActionCached = false;
			}
		}
		
		$isViewCached = false;
		$rememberTheView = null;
		
		while(true) {
			if(!$isActionCached) {
				$actionCache = array();
			
				// $lm->log('Action not cached, executing...');
				// execute the Action and get the View to execute
				list($actionCache['view_module'], $actionCache['view_name']) = $container->runAction();
				
				// check if we've just run the action again after a previous cache read revealed that the view is not cached for this output type and we need to go back to square one due to the lack of action attribute caching configuration...
				// if yes: is the view module/name that we got just now different from what was in the cache?
				if(isset($rememberTheView) && $actionCache != $rememberTheView) {
					// yup. clear it!
					$ourClass = get_class($this);
					call_user_func(array($ourClass, 'clearCache'), $groups);
				}
				
				// check if the returned view is cacheable
				if($isCacheable && is_array($config['views']) && !in_array(array('module' => $actionCache['view_module'], 'name' => $actionCache['view_name']), $config['views'], true)) {
					$isCacheable = false;
					$this->abortedCacheCreationCallback(self::CACHE_CALLBACK_VIEW_NOT_CACHEABLE, $actionGroups, $config, $container);
					
					// so that view is not cacheable? okay then:
					// check if we've just run the action again after a previous cache read revealed that the view is not cached for this output type and we need to go back to square one due to the lack of action attribute caching configuration...
					// 'cause then we need to flush all those existing caches - obviously, that data is stale now, as we learned, since we are not allowed to cache anymore for the view that was returned now
					if(isset($rememberTheView)) {
						// yup. clear it!
						$ourClass = get_class($this);
						call_user_func(array($ourClass, 'clearCache'), $groups);
					}
					// $lm->log('Returned View is not cleared for caching, setting cacheable status to false.');
				} else {
					// $lm->log('Returned View is cleared for caching, proceeding...');
				}

				$actionAttributes = $actionInstance->getAttributes();
			}

			// clear the response
			$response = $container->getResponse();
			$response->clear();

			// clear any forward set, it's ze view's job
			$container->clearNext();

			if($actionCache['view_name'] !== AgaviView::NONE) {

				$container->setViewModuleName($actionCache['view_module']);
				$container->setViewName($actionCache['view_name']);

				$key = $request->toggleLock();
				try {
					// get the view instance
					$viewInstance = $controller->createViewInstance($actionCache['view_module'], $actionCache['view_name']);
					// initialize the view
					$viewInstance->initialize($container);
				} catch(Exception $e) {
					// we caught an exception... unlock the request and rethrow!
					$request->toggleLock($key);
					throw $e;
				}
				$request->toggleLock($key);

				// Set the View Instance in the container
				$container->setViewInstance($viewInstance);
			
				$outputType = $container->getOutputType()->getName();

				if($isCacheable) {
					if(isset($config['output_types'][$otConfig = $outputType]) || isset($config['output_types'][$otConfig = '*'])) {
						$otConfig = $config['output_types'][$otConfig];
						
						$viewGroups = array_merge($groups, array($outputType));

						if($isActionCached) {
							$isViewCached = $this->checkCache($viewGroups, $config['lifetime']);
							if(!$isViewCached) {
								// cacheable, but view is not cached. notify our callback so it can prevent the stampede that follows
								$this->startedCacheCreationCallback(self::CACHE_CALLBACK_VIEW_NOT_CACHED, $viewGroups, $config, $container);
							}
						}
					} else {
						$this->abortedCacheCreationCallback(self::CACHE_CALLBACK_OUTPUT_TYPE_NOT_CACHEABLE, $actionGroups, $config, $container);
						$isCacheable = false;
					}
				}

				if($isViewCached) {
					// $lm->log('View is cached, loading...');
					try {
						$viewCache = $this->readCache($viewGroups);
					} catch(AgaviException $e) {
						$this->startedCacheCreationCallback(self::CACHE_CALLBACK_VIEW_CACHE_GONE, $viewGroups, $config, $container);
						$isViewCached = false;
					}
				}
				if(!$isViewCached) {
					// view not cached
					// has the cache config a list of action attributes?
					if($isActionCached && !$config['action_attributes']) {
						// no. that means we must run the action again!
						$isActionCached = false;
						
						if($isCacheable) {
							// notify our callback so it can remove the lock that's on the view
							// but only if we're still marked as cacheable (if not, then that means the OT is not cacheable, so there wouldn't be a $viewGroups)
							$this->abortedCacheCreationCallback(self::CACHE_CALLBACK_ACTION_CACHE_USELESS, $viewGroups, $config, $container);
						}
						// notify our callback so it can prevent the stampede that follows
						$this->startedCacheCreationCallback(self::CACHE_CALLBACK_ACTION_CACHE_USELESS, $actionGroups, $config, $container);
						
						// but remember the view info, just in case it differs if we run the action again now
						$rememberTheView = array(
							'view_module' => $actionCache['view_module'],
							'view_name' => $actionCache['view_name'],
						);
						continue;
					}
				
					$viewCache = array();
					$viewCache['next'] = $this->executeView($container);
				}

				if($viewCache['next'] instanceof AgaviExecutionContainer) {
					// $lm->log('Forwarding request, skipping rendering...');
					$container->setNext($viewCache['next']);
				} else {
					$output = array();
					$nextOutput = null;
				
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
					
						foreach($viewCache['request_attribute_namespaces'] as $ranName => $ranValues) {
							$request->setAttributes($ranValues, $ranName);
						}

						$nextOutput = $response->getContent();
					} else {
						if($viewCache['next'] !== null) {
							// response content was returned from view execute()
							$response->setContent($nextOutput = $viewCache['next']);
							$viewCache['next'] = null;
						}

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
								$lastCacheableLayer = count($layers) - 1;
							}

							for($i = $lastCacheableLayer + 1; $i < count($layers); $i++) {
								// $lm->log('Adding non-cacheable layer "' . $layers[$i]->getName() . '" to list');
								$viewCache['layers'][] = clone $layers[$i];
							}
						}
					}

					$attributes =& $viewInstance->getAttributes();

					// whether or not we should assign the previous' layer's output to the $slots array
					$assignInnerToSlots = $this->getParameter('assign_inner_to_slots', false);
					
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
								if($isCacheable && !$isViewCached && isset($otConfig['layers'][$layerName]) && is_array($otConfig['layers'][$layerName]) && in_array($slotName, $otConfig['layers'][$layerName])) {
									// $lm->log('Adding response of slot "' . $slotName . '" to cache...');
									$viewCache['slots'][$layerName][$slotName] = $slotResponse;
								}
							}
							// set the presentation data as a template attribute
							$output[$slotName] = $slotResponse->getContent();
							// and merge the other slot's response (this used to be conditional and done only when the content was not null)
							// $lm->log('Merging in response from slot "' . $slotName . '"...');
							$response->merge($slotResponse);
						}
						$moreAssigns = array(
							'container' => $container,
							'inner' => $nextOutput,
							'request_data' => $container->getRequestData(),
							'validation_manager' => $container->getValidationManager(),
							'view' => $viewInstance,
						);
						// lock the request. can't be done outside the loop for the whole run, see #628
						$key = $request->toggleLock();
						try {
							$nextOutput = $layer->getRenderer()->render($layer, $attributes, $output, $moreAssigns);
						} catch(Exception $e) {
							// we caught an exception... unlock the request and rethrow!
							$request->toggleLock($key);
							throw $e;
						}
						// and unlock the request again
						$request->toggleLock($key);

						$response->setContent($nextOutput);

						if($isCacheable && !$isViewCached && $i === $lastCacheableLayer) {
							$viewCache['response'] = clone $response;
						}

						$output = array();
						if($assignInnerToSlots) {
							$output[$layer->getName()] = $nextOutput;
						}
					}
				}

				if($isCacheable && !$isViewCached) {
					// we're writing the view cache first. this is just in case we get into a situation with really bad timing on the leap of a second
					$viewCache['request_attributes'] = array();
					foreach($otConfig['request_attributes'] as $requestAttribute) {
						$viewCache['request_attributes'][] = $requestAttribute + array('value' => $request->getAttribute($requestAttribute['name'], $requestAttribute['namespace']));
					}
					$viewCache['request_attribute_namespaces'] = array();
					foreach($otConfig['request_attribute_namespaces'] as $requestAttributeNamespace) {
						$viewCache['request_attribute_namespaces'][$requestAttributeNamespace] = $request->getAttributes($requestAttributeNamespace);
					}

					$this->writeCache($viewGroups, $viewCache, $config['lifetime']);

					// notify callback that the execution has finished and caches have been written
					$this->finishedCacheCreationCallback(self::CACHE_CALLBACK_VIEW_CACHE_WRITTEN, $viewGroups, $config, $container);
					// $lm->log('Writing View cache...');
				}
			}
		
			// action cache writing must occur here, so actions that return AgaviView::NONE also get their cache written
			if($isCacheable && !$isActionCached) {
				$actionCache['action_attributes'] = array();
				foreach($config['action_attributes'] as $attributeName) {
					$actionCache['action_attributes'][$attributeName] = $actionAttributes[$attributeName];
				}

				// $lm->log('Writing Action cache...');

				$this->writeCache($actionGroups, $actionCache, $config['lifetime']);
			
				// notify callback that the execution has finished and caches have been written
				$this->finishedCacheCreationCallback(self::CACHE_CALLBACK_ACTION_CACHE_WRITTEN, $actionGroups, $config, $container);
			}
			
			// we're done here. bai.
			break;
		}
	}

	/**
	 * Execute the Action
	 *
	 * @param      AgaviExecutionContainer The current execution container.
	 *
	 * @return     mixed The processed View information returned by the Action.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 * @since      0.11.0
	 * 
	 * @deprecated since 1.0.0, use AgaviExecutionContainer::runAction()
	 */
	protected function runAction(AgaviExecutionContainer $container)
	{
		return $container->runAction();
	}
	
	/**
	 * execute this containers view instance
	 * 
	 * @return     mixed the view's result
	 * 
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 * @since      1.0.0
	 */
	protected function executeView(AgaviExecutionContainer $container)
	{
		$outputType = $container->getOutputType()->getName();
		$request = $this->context->getRequest();
		$viewInstance = $container->getViewInstance();
		
		// $lm->log('View is not cached, executing...');
		// view initialization completed successfully
		$executeMethod = 'execute' . $outputType;
		if(!is_callable(array($viewInstance, $executeMethod))) {
			$executeMethod = 'execute';
		}
		$key = $request->toggleLock();
		try {
			$viewResult = $viewInstance->$executeMethod($container->getRequestData());
		} catch(Exception $e) {
			// we caught an exception... unlock the request and rethrow!
			$request->toggleLock($key);
			throw $e;
		}
		$request->toggleLock($key);
		return $viewResult;
	}
	
}

?>
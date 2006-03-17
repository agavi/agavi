<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2003-2006 the Agavi Project.                                |
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
 * AgaviCachingExecutionFilter is a ExecutionFilter implementation that allows
 * the caching of the output of Actions based on various parameters.
 *
 * @package    agavi
 * @subpackage filter
 *
 * @author     David Zuelke <dz@bitxtender.com>
 * @author     Agavi Project <info@agavi.org>
 * @copyright  (c) Authors
 * @since      0.11.0
 *
 * @version    $Id$
 */

class AgaviCachingExecutionFilter extends AgaviExecutionFilter
{
	const CACHE_SUBDIR = 'content';
	
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
	public function checkCache($groups)
	{
		foreach($groups as &$group) {
			$group = base64_encode($group);
		}
		return is_readable(AgaviConfig::get('core.cache_dir') . DIRECTORY_SEPARATOR . self::CACHE_SUBDIR . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $groups) . '.cefcache');
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
	public function readCache($groups)
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
	public function writeCache($groups, $data)
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
	 * @return     void
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public static function clearCache($groups = array())
	{
		$SPL_RIT_CHILD_FIRST = null;
		if(defined('RecursiveIteratorIterator::CHILD_FIRST')) {
			$SPL_RIT_CHILD_FIRST = RecursiveIteratorIterator::CHILD_FIRST;
		} else {
			$SPL_RIT_CHILD_FIRST = RIT_CHILD_FIRST;
		}
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
	public function determineGroups($cfg, $moduleName, $actionName)
	{
		$context = $this->getContext();
		
		$groups = array();
		
		if(isset($cfg['groups'])) {
			for($i = 0, $groupCount = count($cfg['groups']); $i < $groupCount; $i++) {
				$val = $cfg['groups'][$i]['name'];
				if(!isset($cfg['groups'][$i]['source'])) {
					$cfg['groups'][$i]['source'] = 'string';
				}
				switch($cfg['groups'][$i]['source']) {
					case 'constant': 
						$val = constant($val);
					break;
					case 'request.parameter':
						$val = $context->getRequest()->getParameter($val);
					break;
					case 'request.attribute':
						$val = $context->getRequest()->getAttribute($val);
					break;
					case 'user.parameter':
						$val = $context->getUser()->getParameter($val);
					break;
					case 'user.attribute':
						$val = $context->getUser()->getAttribute($val);
					break;
					case 'user.credential':
						$val = (int)$context->getUser()->hasCredential($val);
					break;
				}
				if($val === null) {
					$val = "0";
				}
				$groups[] = $val;
			}
		}
		
		$groups[] = $moduleName . '_' . $actionName;
		
		return $groups;
	}
	
	/**
	 * @see        ExecutionFilter::execute()
	 */
	public function execute($filterChain)
	{
		static $config = array();
		static $context, $controller, $validatorManager;
		
		if(!isset($context)) {
			// get the context and controller
			$context    = $this->getContext();
			$controller = $context->getController();
		}
		
		// get the current action instance
		$actionEntry    = $controller->getActionStack()->getLastEntry();
		$actionInstance = $actionEntry->getActionInstance();
		// get the current action information
		$moduleName = $context->getModuleName();
		$actionName = $context->getActionName();
		// get the request method
		$method = $context->getRequest()->getMethod();
		
		$aName = str_replace('/', '.', $actionName);
		
		if(!isset($config[$moduleName])) {
			try {
				$config[$moduleName] = null;
				$configFile = AgaviConfigCache::checkConfig(AgaviConfig::get('core.modules_dir') . '/' . $moduleName . '/config/caching.ini');
				$config[$moduleName] = include($configFile);
			} catch(Exception $e) {
			}
		}
		
		if(!$config[$moduleName] || !isset($config[$moduleName][$aName]) || !isset($config[$moduleName][$aName]['enabled']) || !$config[$moduleName][$aName]['enabled']) {
			return parent::execute($filterChain);
		}

		if(!isset($validatorManager)) {
			// create validator manager
			$validatorManager = $context->getValidatorManager();
		} else {
			// clear the validator manager for reuse
			$validatorManager->clear();
		}

		// let the party begin
		
		$cfg = $config[$moduleName][$aName];

		$groups = $this->determineGroups($cfg, $moduleName, $aName);

		if($this->checkCache($groups)) {
			$cache = $this->readCache($groups);
			$viewData = $cache['content'];
			$viewInstance = $controller->getView($cache['view']['module_name'], $cache['view']['view_name']);
			if($viewInstance->initialize($context)) {
				// maybe some smartass is subclassing his views and putting stuff into initialize()
				$viewInstance->clearDecorator();
				$viewInstance->clearSlots();
				
				if(isset($cfg['variables'])) {
					for($i = 0, $varCount = count($cfg['variables']); $i < $varCount; $i++) {
						$val = $cache['variables'][$i];
						switch($cfg['variables'][$i]['source']) {
							case 'request.parameter':
								$context->getRequest()->setParameter($cfg['variables'][$i]['name'], $val);
							break;
							case 'request.attribute':
								$context->getRequest()->setAttribute($cfg['variables'][$i]['name'], $val);
							break;
						}
					}
				}

				$viewInstance->setTemplate($cache['view']['template']);
				if(isset($cache['decorator'])) {
					$viewInstance->setDecoratorTemplate($cache['decorator']['template']);
					$viewInstance->setDecoratorDirectory($cache['decorator']['directory']);
					$viewInstance->setSlots($cache['decorator']['slots']);
					for($i = 0, $decoratorVarCount = count($cfg['decorator']['variables']); $i < $decoratorVarCount; $i++) {
						// cached vars for the decorator
						$viewInstance->setAttribute($cfg['decorator']['variables'][$i], $cache['decorator']['variables'][$i]);
					}
					foreach($cache['decorator']['variables'] as $key => $value) {
						// cached slots
						if(is_string($key)) {
							$viewInstance->setAttribute($key, $value);
						}
					}
					$viewData =& $viewInstance->decorate($viewData);
				}
				
				if($controller->getRenderMode() == AgaviView::RENDER_VAR) {
					$actionEntry->setPresentation($viewData);
				} else {
					echo $viewData;
				}
			}
		} else {
			// --------------------------------------------------------------
			// ExecutionFilter starting
			// --------------------------------------------------------------
			if(($actionInstance->getRequestMethods() & $method) != $method) {
				// this action will skip validation/execution for this method
				// get the default view
				$viewName = $actionInstance->getDefaultView();
			} else {
				// set default validated status
				$validated = true;
				// get the current action validation configuration
				$validationConfig = AgaviConfig::get('core.modules_dir') . '/' . $moduleName .
											'/validate/' . $actionName . '.ini';
				if(is_readable($validationConfig)) {
					// load validation configuration
					// do NOT use require_once
					$validationConfig = 'modules/' . $moduleName .
													'/validate/' . $actionName . '.ini';
					require(AgaviConfigCache::checkConfig($validationConfig));
				}

				// manually load validators
				$actionInstance->registerValidators($validatorManager);
				// process validators
				$validated = $validatorManager->execute();
				// process manual validation
				if($actionInstance->validate() && $validated) {
					// execute the action
					$viewName = $actionInstance->execute();
				} else {
					// validation failed
					$viewName = $actionInstance->handleError();
				}
			}
			$returnedViewName = $viewName;
			if($viewName != AgaviView::NONE) {
				if(is_array($viewName)) {
					// we're going to use an entirely different action for this view
					$moduleName = $viewName[0];
					$viewName   = $viewName[1];
				} else {
					// use a view related to this action
					$viewName = $actionName . $viewName;
				}
				// display this view
				if(!$controller->viewExists($moduleName, $viewName)) {
					// the requested view doesn't exist
					$file = AgaviConfig::get('core.modules_dir') . '/' . $moduleName . '/views/' .
							$viewName . 'View.class.php';
					$error = 'Module "%s" does not contain the view "%sView" or ' .
							 'the file "%s" is unreadable';
					$error = sprintf($error, $moduleName, $viewName, $file);
					throw new AgaviViewException($error);
				}
				// get the view instance
				$viewInstance = $controller->getView($moduleName, $viewName);
				// initialize the view

				if($viewInstance->initialize($context)) {
					// view initialization completed successfully
					$viewInstance->execute();
					// render the view and if data is returned, stick it in the
					// action entry which was retrieved from the execution chain


					ob_start();
					if($viewInstance->isDecorator() && isset($cfg['decorator']) && isset($cfg['decorator']['include']) && !$cfg['decorator']['include']) {
						$output['decorator'] = array();
						$output['decorator']['template'] = $viewInstance->getDecoratorTemplate();
						$output['decorator']['directory'] = $viewInstance->getDecoratorDirectory();
						$output['decorator']['slots'] = $viewInstance->getSlots();
						$viewInstance->clearDecorator();
						$viewInstance->clearSlots();
						ob_start();
						$viewData =& $viewInstance->render();
						$viewData = ob_get_contents();
						ob_end_clean();
						$output['content'] = $viewData;
						$viewInstance->setDecoratorTemplate($output['decorator']['template']);
						$viewInstance->setDecoratorDirectory($output['decorator']['directory']);
						$viewInstance->setSlots($output['decorator']['slots']);
						$viewData =& $viewInstance->decorate($viewData);
						if($controller->getRenderMode() == AgaviView::RENDER_CLIENT) {
							echo $viewData;
							$viewData = null;
						}
						$output['decorator']['variables'] = array();
						if(isset($cfg['decorator']['variables'])) {
							for($i = 0, $decoratorVarCount = count($cfg['decorator']['variables']); $i < $decoratorVarCount; $i++) {
								$output['decorator']['variables'][$i] = $viewInstance->getAttribute($cfg['decorator']['variables'][$i]);
							}
						}
						if(isset($cfg['decorator']['slots'])) {
							foreach($cfg['decorator']['slots'] as $slot) {
								unset($output['decorator']['slots'][$slot]);
								$output['decorator']['variables'][$slot] = $viewInstance->getAttribute($slot);
							}
						}
					} else {
						$viewData =& $viewInstance->render();
						$output['content'] = ob_get_contents();
					}
					ob_end_flush();
					
					if($controller->getRenderMode() == AgaviView::RENDER_VAR) {
						$actionEntry->setPresentation($viewData);
						$output['content'] = $viewData;
					}

					$output['variables'] = array();
					if(isset($cfg['variables'])) {
						for($i = 0, $varCount = count($cfg['variables']); $i < $varCount; $i++) {
							$val = $cfg['variables'][$i]['name'];
							switch($cfg['variables'][$i]['source']) {
								case 'request.parameter':
									$val = $context->getRequest()->getParameter($val);
								break;
								case 'request.attribute':
									$val = $context->getRequest()->getAttribute($val);
								break;
							}
							$output['variables'][$i] = $val;
						}
					}

					$output['render_mode'] = $controller->getRenderMode();
					$output['view'] = array('module_name' => $moduleName, 'view_name' => $viewName, 'template' => $viewInstance->getTemplate());
					
					if(isset($cfg['views']) && isset($cfg['views']['whitelist']) && in_array($returnedViewName, $cfg['views']['whitelist'])) {
//						$output['content'] .= "<!-- cached on " . date("c") . " " . (isset($cfg['decorator']) && count($cfg['decorator']['slots']) ? "including slots: " . implode(',', $cfg['decorator']['slots']) : "") . "-->";
						$this->writeCache($this->determineGroups($cfg, $moduleName, $aName), $output);
					}
				} else {
					// view failed to initialize
					$error = 'View initialization failed for module "%s", ' .
							 'view "%sView"';
					$error = sprintf($error, $moduleName, $viewName);
					throw new AgaviInitializationException($error);
				}
			}
			// --------------------------------------------------------------
			// ExecutionFilter ending
			// --------------------------------------------------------------
		}
	}
}

?>
<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2005-2008 the Agavi Project.                                |
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
 * AgaviCachingConfigHandler compiles the per-action configuration files placed
 * in the "cache" subfolder of a module directory.
 *
 * @package    agavi
 * @subpackage config
 *
 * @author     David Zülke <dz@bitxtender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.11.0
 *
 * @version    $Id$
 */
class AgaviCachingConfigHandler extends AgaviConfigHandler
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
	 * @since      0.11.0
	 */
	public function execute($config, $context = null)
	{
		// parse the config file
		$configurations = $this->orderConfigurations(AgaviConfigCache::parseConfig($config, false, $this->getValidationFile(), $this->parser)->configurations, AgaviConfig::get('core.environment'), $context);

		$cachings = array();

		foreach($configurations as $cfg) {
			foreach($cfg->cachings as $caching) {
				if(!AgaviToolkit::literalize($caching->getAttribute('enabled', true))) {
					continue;
				}
				
				$groups = array();
				if(isset($caching->groups)) {
					foreach($caching->groups as $group) {
						$groups[] = array('name' => $group->getValue(), 'source' => $group->getAttribute('source', 'string'), 'namespace' => $group->getAttribute('namespace')) ;
					}
				}
				
				$actionAttributes = array();
				if(isset($caching->action_attributes)) {
					foreach($caching->action_attributes as $actionAttribute) {
						$actionAttributes[] = $actionAttribute->getValue();
					}
				}
				
				$views = null;
				if(isset($caching->views)) {
					$views = array();
					foreach($caching->views as $view) {
						if($view->hasAttribute('module')) {
							$views[] = array('module' => $view->getAttribute('module'), 'view' => $view->getValue());
						} else {
							$views[] = $view->getValue();
						}
					}
				}
				
				$outputTypes = array();
				if(isset($caching->output_types)) {
					foreach($caching->output_types as $outputType) {
						$layers = null;
						if(isset($outputType->layers)) {
							$layers = array();
							foreach($outputType->layers as $layer) {
								$include = AgaviToolkit::literalize($layer->getAttribute('include', 'true'));
								if((isset($layer->slots) && !$layer->hasAttribute('include')) || !$include) {
									$slots = array();
									if(isset($layer->slots)) {
										foreach($layer->slots as $slot) {
											$slots[] = $slot->getValue();
										}
									}
									$layers[$layer->getAttribute('name')] = $slots;
								} else {
									$layers[$layer->getAttribute('name')] = true;
								}
							}
						}
						
						$templateVariables = array();
						if(isset($outputType->template_variables)) {
							foreach($outputType->template_variables as $templateVariable) {
								$templateVariables[] = $templateVariable->getValue();
							}
						}
						
						$requestAttributes = array();
						if(isset($outputType->request_attributes)) {
							foreach($outputType->request_attributes as $requestAttribute) {
								$requestAttributes[] = array('name' => $requestAttribute->getValue(), 'namespace' => $requestAttribute->getAttribute('namespace'));
							}
						}
						
						$requestAttributeNamespaces = array();
						if(isset($outputType->request_attribute_namespaces)) {
							foreach($outputType->request_attribute_namespaces as $requestAttributeNamespace) {
								$requestAttributeNamespaces[] = $requestAttributeNamespace->getValue();
							}
						}
						
						$otnames = array_map('trim', explode(' ', $outputType->getAttribute('name', '*')));
						foreach($otnames as $otname) {
							$outputTypes[$otname] = array(
								'layers' => $layers,
								'template_variables' => $templateVariables,
								'request_attributes' => $requestAttributes,
								'request_attribute_namespaces' => $requestAttributeNamespaces,
							);
						}
					}
				}
				
				$methods = array_map('trim', explode(' ', $caching->getAttribute('method', '*')));
				foreach($methods as $method) {
					$cachings[$method] = array(
						'lifetime' => $caching->getAttribute('lifetime'),
						'groups' => $groups,
						'views' => $views,
						'action_attributes' => $actionAttributes,
						'output_types' => $outputTypes,
					);
				}
			}
		}
		
		$code = array(
			'$configs = ' . var_export($cachings, true) . ';',
			'if(isset($configs[$index = $request->getMethod()]) || isset($configs[$index = "*"])) {',
			'	$isCacheable = true;',
			'	$config = $configs[$index];',
			'	if(is_array($config["views"])) {',
			'		foreach($config["views"] as &$view) {',
			'			if(!is_array($view)) {',
			'				$view = array("module" => $moduleName, "name" => $actionName . $view);',
			'			}',
			'		}',
			'	}',
			'}',
		);
		
		return $this->generate($code);
	}
}

?>
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
 * AgaviOutputTypeConfigHandler handles output type configuration files.
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
class AgaviOutputTypeConfigHandler extends AgaviConfigHandler
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

		$data = array();
		$defaultOt = null;
		foreach($configurations as $cfg) {
			if(!isset($cfg->output_types)) {
				continue;
			}
			
			$otnames = array();
			foreach($cfg->output_types as $outputType) {
				$otname = $outputType->getAttribute('name');
				if(in_array($otname, $otnames)) {
					throw new AgaviConfigurationException('Duplicate Output Type "' . $otname . '" in ' . $config);
				}
				$otnames[] = $otname;
			}

			if(!$cfg->output_types->hasAttribute('default')) {
				throw new AgaviConfigurationException('No default Output Type specified in ' . $config);
			}

			if(!in_array($cfg->output_types->getAttribute('default'), $otnames)) {
				throw new AgaviConfigurationException('Non-existent Output Type "' . $cfg->output_types->getAttribute('default') . '" specified as default in ' . $config);
			}

			foreach($cfg->output_types as $outputType) {
				$outputTypeName = $outputType->getAttribute('name');
				$data[$outputTypeName] = isset($data[$outputTypeName]) ? $data[$outputTypeName] : array('parameters' => array(), 'default_renderer' => null, 'renderers' => array(), 'layouts' => array(), 'default_layout' => null, 'exception_template' => null);
				if(isset($outputType->renderers)) {
					foreach($outputType->renderers as $renderer) {
						$rendererName = $renderer->getAttribute('name');
						$data[$outputTypeName]['renderers'][$rendererName] = array(
							'class' => $renderer->getAttribute('class'),
							'instance' => null,
							'parameters' => $this->getItemParameters($renderer, array()),
						);
					}
					$data[$outputTypeName]['default_renderer'] = $outputType->renderers->getAttribute('default');
				}
				if(isset($outputType->layouts)) {
					foreach($outputType->layouts as $layout) {
						$layers = array();
						
						if(isset($layout->layers)) {
							foreach($layout->layers as $layer) {
								$slots = array();
								
								if(isset($layer->slots)) {
									foreach($layer->slots as $slot) {
										$slots[$slot->getAttribute('name')] = array(
											'action' => $slot->getAttribute('action'),
											'module' => $slot->getAttribute('module'),
											'output_type' => $slot->getAttribute('output_type'),
											'request_method' => $slot->getAttribute('method'),
											'parameters' => $this->getItemParameters($slot, array()),
										);
									}
								}
								
								$layers[$layer->getAttribute('name')] = array(
									'class' => $layer->getAttribute('class', $this->getParameter('default_layer_class', 'AgaviFileTemplateLayer')),
									'parameters' => $this->getItemParameters($layer, array()),
									'renderer' => $layer->getAttribute('renderer'),
									'slots' => $slots,
								);
							}
						}
						
						$data[$outputTypeName]['layouts'][$layout->getAttribute('name')] = array(
							'layers' => $layers,
							'parameters' => $this->getItemParameters($layout, array()),
						);
					}
					$data[$outputTypeName]['default_layout'] = $outputType->layouts->getAttribute('default');
				}
				if($outputType->hasAttribute('exception_template')) {
					$data[$outputTypeName]['exception_template'] = AgaviToolkit::expandDirectives($outputType->getAttribute('exception_template'));
					if(!is_readable($data[$outputTypeName]['exception_template'])) {
						throw new AgaviConfigurationException('Exception template "' . $data[$outputTypeName]['exception_template'] . '" does not exist or is unreadable');
					}
				}
				$data[$outputTypeName]['parameters'] = $this->getItemParameters($outputType, $data[$outputTypeName]['parameters']);
			}
			$defaultOt = $cfg->output_types->getAttribute('default');
		}

		$code = array();
		foreach($data as $outputTypeName => $outputType) {
			$code[] = '$ot = new AgaviOutputType();';
			$code[] = sprintf(
				'$ot->initialize($this->context, %s, %s, %s, %s, %s, %s, %s);',
				var_export($outputType['parameters'], true),
				var_export($outputTypeName, true),
				var_export($outputType['renderers'], true),
				var_export($outputType['default_renderer'], true),
				var_export($outputType['layouts'], true),
				var_export($outputType['default_layout'], true),
				var_export($outputType['exception_template'], true)
			);
			$code[] = sprintf('$this->outputTypes[%s] = $ot;', var_export($outputTypeName, true));
		}
		$code[] = sprintf('$this->defaultOutputType = %s;', var_export($defaultOt, true));
		
		return $this->generate($code);
	}
}

?>
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
 * AgaviModuleConfigHandler reads module configuration files to determine the 
 * status of a module.
 *
 * @package    agavi
 * @subpackage config
 *
 * @author     David Zuelke <dz@bitxtender.com>
 * @copyright  (c) Authors
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
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function execute($config, $context = null)
	{
		// parse the config file
		$configurations = $this->orderConfigurations(AgaviConfigCache::parseConfig($config, false, $this->getValidationFile(), $this->parser)->configurations, AgaviConfig::get('core.environment'), $context);

		$data = array();
		$defaultOt = null;
		foreach($configurations as $cfg) {
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
				$data[$outputTypeName] = isset($data[$outputTypeName]) ? $data[$outputTypeName] : array('parameters' => array(), 'default_renderer' => null, 'renderers' => array(), 'exception_template' => null);
				if(isset($outputType->renderers)) {
					foreach($outputType->renderers as $renderer) {
						$rendererName = $renderer->getAttribute('name');
						$data[$outputTypeName]['renderers'][$rendererName] = array('instance' => null, 'class' => null, 'extension' => null, 'parameters' => array());
						$data[$outputTypeName]['renderers'][$rendererName]['class'] = $renderer->getAttribute('class');
						$data[$outputTypeName]['renderers'][$rendererName]['extension'] = $renderer->getAttribute('extension');
						$data[$outputTypeName]['renderers'][$rendererName]['parameters'] = $this->getItemParameters($renderer, $data[$outputTypeName]['renderers'][$rendererName]['parameters']);
					}
					$data[$outputTypeName]['default_renderer'] = $outputType->renderers->getAttribute('default');
				}
				if($outputType->hasAttribute('exception_template')) {
					$data[$outputTypeName]['exception_template'] = $this->replaceConstants($outputType->getAttribute('exception_template'));
					if(!is_readable($data[$outputTypeName]['exception_template'])) {
						throw new AgaviConfigurationException('Exception template "' . $data[$outputTypeName]['exception_template'] . '" does not exist or is unreadable');
					}
				}
				$data[$outputTypeName]['parameters'] = $this->getItemParameters($outputType, $data[$outputTypeName]['parameters']);
			}
			$defaultOt = $cfg->output_types->getAttribute('default');
		}

		$code = '';
		foreach($data as $outputTypeName => $outputType) {
			$code[] = implode("\n", array(
				'$ot = new AgaviOutputType();',
				'$ot->initialize($this->context, ' . var_export($outputType['parameters'], true) . ', ' . var_export($outputTypeName, true) . ', ' . var_export($outputType['renderers'], true) . ', ' . var_export($outputType['default_renderer'], true) . ', ' . var_export($outputType['exception_template'], true) . ');',
				'$this->outputTypes["' . $outputTypeName . '"] = $ot;',
			));
		}
		$code[] = '$this->defaultOutputType = "' . $defaultOt . '";';
		
		// compile data
		$retval = "<?php\n" .
							"// auto-generated by ".__CLASS__."\n" .
							"// date: %s GMT\n%s\n?>";
							
		$retval = sprintf($retval, gmdate('m/d/Y H:i:s'), implode("\n", $code));
		
		return $retval;
	}
}

?>
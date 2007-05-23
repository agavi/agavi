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
 * AgaviConfigHandlersConfigHandler allows you to specify configuration handlers
 * for the application or on a module level.
 *
 * @package    agavi
 * @subpackage config
 *
 * @author     Dominik del Bondio <ddb@bitxtender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.11.0
 *
 * @version    $Id$
 */
class AgaviConfigHandlersConfigHandler extends AgaviConfigHandler
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
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function execute($config, $context = null)
	{
		// parse the config file
		$configurations = $this->orderConfigurations(AgaviConfigCache::parseConfig($config, false, $this->getValidationFile(), $this->parser)->configurations, AgaviConfig::get('core.environment'));
		
		// init our data arrays
		$data = array();
		
		foreach($configurations as $cfg) {
			// let's do our fancy work
			foreach($cfg->handlers as $handler) {
				$pattern = $handler->getAttribute('pattern');
				
				$category = var_export(AgaviToolkit::normalizePath($this->replaceConstants($pattern)), true);
				
				$class = var_export($handler->getAttribute('class'), true);
				
				$validation = array(
					AgaviXmlConfigParser::VALIDATION_TYPE_RELAXNG    => array(
					),
					AgaviXmlConfigParser::VALIDATION_TYPE_SCHEMATRON => array(
					),
					AgaviXmlConfigParser::VALIDATION_TYPE_XMLSCHEMA  => array(
					),
				);
				if($handler->hasAttribute('validate')) {
					$validation[AgaviXmlConfigParser::VALIDATION_TYPE_XMLSCHEMA][] = $this->literalize($handler->getAttribute('validate'));
				} elseif(false) {
					// TODO: check for <validations><validation type="schematron"> children here
				}
				$validation = var_export($validation, true);
				
				$parameters = var_export($this->getItemParameters($handler), true);
				
				// append new data
				$tmp    = "self::\$handlers[%s] = array('class' => %s, 'parameters' => %s, 'validation' => %s);";
				$data[] = sprintf($tmp, $category, $class, $parameters, $validation);
			}
		}
		
		return $this->generate($data);
	}
}

?>
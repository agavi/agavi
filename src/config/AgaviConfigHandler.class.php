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
 * AgaviConfigHandler allows a developer to create a custom formatted
 * configuration file pertaining to any information they like and still
 * have it auto-generate PHP code.
 *
 * @package    agavi
 * @subpackage config
 *
 * @author     Sean Kerr <skerr@mojavi.org>
 * @copyright  (c) Authors
 * @since      0.9.0
 *
 * @version    $Id$
 */
abstract class AgaviConfigHandler extends AgaviParameterHolder
{
	/**
	 * @var        string An absolute filesystem path to a validation filename.
	 */
	protected $validationFile = null;

	/**
	 * @var        string A class name of the class which should be used to parse
	 *                    Input files of this config handler.
	 */
	protected $parser = null;

	/**
	 * Retrieves the stored validation filename.
	 *
	 * @return     string An absolute filesystem path to a validation filename.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getValidationFile()
	{
		return $this->validationFile;
	}

	/**
	 * Retrieve the parameter node values of the given item's parameters element.
	 *
	 * @param      ConfigValueHolder The node that contains a parameters chiild.
	 * @param      array             As associative array of parameters that will
	 *                               be overwritten if appropriate.
	 * @param      boolean           Whether or not values should be literalized.
	 *
	 * @return     array An associative array of parameters
	 *
	 * @author     Dominik del Bondio
	 * @since      0.11.0
	 */
	protected function getItemParameters($itemNode, $oldValues = array(), $literalize = true)
	{
		$data = array();
		if($itemNode->hasChildren('parameters')) {
			foreach($itemNode->parameters as $node) {
				if(!$node->hasAttribute('name')) {
					// create a new entry in in the array and get they key of the new
					// created entry (the last in the array). The value doesn't matter
					// since it will be overwritten anyways
					$data[] = 0;
					end($data);
					$name = key($data);
				} else {
					$name = $node->getAttribute('name');
				}
				if($node->hasChildren('parameters')) {
					$data[$name] = (isset($oldValues[$name]) && is_array($oldValues[$name])) ? $oldValues[$name] : array();
					$data[$name] = $this->getItemParameters($node, $data[$name], $literalize);
				} else {
					$data[$name] = $literalize ? $this->literalize($node->getValue()) : $node->getValue();
				}
			}
		}
		$data = array_merge($oldValues, $data);
		return $data;
	}

	/**
	 * Execute this configuration handler.
	 *
	 * @param      string An absolute filesystem path to a configuration file.
	 * @param      string Name of the executing context (if any).
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
	abstract function execute($config, $context = null);

	/**
	 * Initialize this ConfigHandler.
	 *
	 * @param      string The path to a validation file for this config handler.
	 * @param      array An associative array of initialization parameters.
	 *
	 * @throws     <b>AgaviInitializationException</b> If an error occurs while
	 *                                                 initializing the ConfigHandler
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function initialize($validationFile = null, $parser = null, $parameters = array())
	{
		$this->validationFile = $validationFile;
		$this->parser = $parser;
		$this->setParameters($parameters);
	}

	/**
	 * Literalize a string value.
	 *
	 * @param      string The value to literalize.
	 *
	 * @return     string A literalized value.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public static function literalize($value)
	{
		if($value == null) {
			// null value
			return null;
		}

		if(!is_string($value)) {
			return $value;
		}

		// lowercase our value for comparison
		$value  = trim($value);
		$lvalue = strtolower($value);

		if($lvalue == 'on' || $lvalue == 'yes' || $lvalue == 'true') {

			// replace values 'on' and 'yes' with a boolean true value
			return true;

		} elseif($lvalue == 'off' || $lvalue == 'no' || $lvalue == 'false') {

			// replace values 'off' and 'no' with a boolean false value
			return false;

		} elseif(!is_numeric($value)) {

			return self::replaceConstants($value);

		}

		// numeric value
		return $value;

	}

	/**
	 * Replace constant identifiers in a string.
	 *
	 * @param      string The value on which to run the replacement procedure.
	 *
	 * @return     string The new value.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     Johan Mjones(johan.mjones@ongame.co)
	 * @since      0.9.0
	 */
	public static function replaceConstants($value)
	{
		$newvalue = $value;
		do {
			$value = $newvalue;
			$newvalue = preg_replace_callback(
				'/\%([\w\.]+?)\%/',
				create_function(
					'$match',
					'$constant = $match[1];' .
					'return (AgaviConfig::has($constant) ? AgaviConfig::get($constant) : "%".$constant."%");'
				),
				$value
			);

		} while($newvalue != $value);

		return $value;
	}

	/**
	 * Replace a relative filesystem path with an absolute one.
	 *
	 * @param      string A relative filesystem path.
	 *
	 * @return     string The new path.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public static function replacePath($path)
	{
		if(!AgaviToolkit::isPathAbsolute($path)) {
			// not an absolute path so we'll prepend to it
			$path = AgaviConfig::get('core.app_dir') . '/' . $path;
		}

		return $path;
	}

	/**
	 * Returns a properly ordered array of AgaviConfigValueHolder configuration
	 * elements for given env and context.
	 *
	 * @param      AgaviConfigValueHolder The root config element
	 * @param      string                 An environment name.
	 * @param      string                 A context name.
	 * @param      bool                   Whether parser class should be autoloaded or not.
	 *
	 * @return     array An array of ConfigValueHolder configuration elements.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function orderConfigurations(AgaviConfigValueHolder $configurations, $environment = null, $context = null, $autoloadParser = true)
	{
		$configs = array();

		if($configurations->hasAttribute('parent')) {
			$parent = self::literalize($configurations->getAttribute('parent'));
			$parentConfigs = $this->orderConfigurations(AgaviConfigCache::parseConfig($parent, $autoloadParser, $this->getValidationFile())->configurations, $environment, $context, $autoloadParser);
			$configs = array_merge($configs, $parentConfigs);
		}


		foreach($configurations as $cfg) {
			if(!$cfg->hasAttribute('environment') && !$cfg->hasAttribute('context')) {
				$configs[] = $cfg;
			}
		}
		foreach($configurations as $cfg) {
			if($environment !== null && $cfg->hasAttribute('environment') && in_array($environment, array_map('trim', explode(' ', $cfg->getAttribute('environment')))) && !$cfg->hasAttribute('context')) {
				$configs[] = $cfg;
			}
		}
		foreach($configurations as $cfg) {
			if(!$cfg->hasAttribute('environment') && $context !== null && $cfg->hasAttribute('context') && in_array($context, array_map('trim', explode(' ', $cfg->getAttribute('context'))))) {
				$configs[] = $cfg;
			}
		}
		foreach($configurations as $cfg) {
			if($environment !== null && $cfg->hasAttribute('environment') && in_array($environment, array_map('trim', explode(' ', $cfg->getAttribute('environment')))) && $context !== null && $cfg->hasAttribute('context') && in_array($context, array_map('trim', explode(' ', $cfg->getAttribute('context'))))) {
				$configs[] = $cfg;
			}
		}

		return $configs;
	}

}

?>
<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2003-2005  Sean Kerr.                                       |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE   |
// | file that was distributed with this source code. You can also view the    |
// | LICENSE file online at http://www.agavi.org.                              |
// +---------------------------------------------------------------------------+

/**
 * IniConfigHandler is a base class for .ini configuration handlers. This class
 * provides a central location for parsing ini files and detecting required
 * categories.
 *
 * @package    agavi
 * @subpackage config
 *
 * @author    Sean Kerr (skerr@mojavi.org)
 * @copyright (c) Sean Kerr, {@link http://www.mojavi.org}
 * @since     0.9.0
 * @version   $Id$
 */
abstract class IniConfigHandler extends ConfigHandler
{

	// +-----------------------------------------------------------------------+
	// | METHODS                                                               |
	// +-----------------------------------------------------------------------+

	/**
	 * Parse an .ini configuration file.
	 *
	 * @param string An absolute filesystem path to a configuration file.
	 *
	 * @return string A parsed .ini configuration.
	 *
	 * @throws <b>ConfigurationException</b> If a requested configuration file
	 *                                       does not exist or is not readable.
	 * @throws <b>ParseException</b> If a requested configuration file is
	 *                               improperly formatted.
	 *
	 * @author Sean Kerr (skerr@mojavi.org)
	 * @since  0.9.0
	 */
	protected function & parseIni ($config)
	{
		if (!is_readable($config)) {
			$error = 'Configuration file "' . $config . '" does not exist or is not readable';
			throw new ConfigurationException($error);
		}

		// parse our config
		$ini = @parse_ini_file($config, true);

		if ($ini === false)	{
			$error = 'Configuration file "' . $config . '" could not be parsed';
			throw new ParseException($error);
		}

		// get a list of the required categories
		if ($this->hasParameter('required_categories')) {
			$categories = $this->getParameter('required_categories');

			foreach ($categories as $category) {
				if (!isset($ini[$category])) {
			    $error = 'Configuration file "' . $config . '" is missing "' . $category . '" category';
			    throw new ParseException($error);
				}

			}

		}
		return $ini;

	}

}

?>

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
 * AgaviIniConfigParser is a base class for .ini configuration handlers. 
 * This class provides a central location for parsing ini files and 
 * detecting required categories.
 *
 * @package    agavi
 * @subpackage config
 *
 * @author     Dominik del Bondio <ddb@bitxtender.com>
 * @copyright  (c) Authors
 * @since      0.11.0
 *
 * @version    $Id: IniConfigHandler.class.php 419 2006-03-07 18:44:23Z dominik $
 */
abstract class AgaviIniConfigParser extends AgaviConfigParser
{

	/**
	 * Parse an .ini configuration file.
	 *
	 * @param      string An absolute filesystem path to a configuration file.
	 *
	 * @return     string A parsed .ini configuration.
	 *
	 * @throws     <b>AgaviUnreadableException</b> If a requested configuration file
	 *                                             does not exist or is not readable.
	 * @throws     <b>AgaviParseException</b> If a requested configuration file is
	 *                                        improperly formatted.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function parse($config)
	{
		if (!is_readable($config)) {
			$error = 'Configuration file "' . $config . '" does not exist or is not readable';
			throw new AgaviUnreadableException($error);
		}

		// parse our config
		$ini = @parse_ini_file($config, true);

		if ($ini === false)	{
			$error = 'Configuration file "' . $config . '" could not be parsed';
			throw new AgaviParseException($error);
		}



		// get a list of the required categories
		if ($this->hasParameter('required_categories')) {
			$categories = $this->getParameter('required_categories');

			foreach ($categories as $category) {
				if (!isset($ini[$category])) {
			    $error = 'Configuration file "' . $config . '" is missing "' . $category . '" category';
			    throw new AgaviParseException($error);
				}

			}

		}
		return $ini;

	}

}

?>
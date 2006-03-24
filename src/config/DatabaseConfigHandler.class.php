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
 * AgaviDatabaseConfigHandler allows you to setup database connections in a
 * configuration file that will be created for you automatically upon first
 * request.
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
class AgaviDatabaseConfigHandler extends AgaviIniConfigHandler
{

	/**
	 * Execute this configuration handler.
	 *
	 * @param      string An absolute filesystem path to a configuration file.
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
	public function execute($config, $context = null)
	{

		// set our required categories list and initialize our handler
		$categories = array('required_categories' => array('databases'));

		$this->initialize($categories);

		// parse the ini
		$ini = $this->parseIni($config);

		// init our data and includes arrays
		$data      = array();
		$databases = array();
		$includes  = array();

		// get a list of database connections
		foreach ($ini['databases'] as $key => &$value)
		{

			$value = trim($value);

			// is this category already registered?
			if (in_array($value, $databases))
			{

				// this category is already registered
				$error = 'Configuration file "%s" specifies previously ' .
						 'registered category "%s"';
				$error = sprintf($error, $config, $value);

				throw new AgaviParseException($error);

			}

			// see if we have the category registered for this database
			if (!isset($ini[$value]))
			{

				// missing required key
				$error = 'Configuration file "%s" specifies nonexistent ' .
						 'category "%s"';
				$error = sprintf($error, $config, $value);

				throw new AgaviParseException($error);

			}

			// add this database
			$databases[$key] = $value;

		}

		// make sure we have a default database registered
		if (!isset($databases['default']))
		{

			// missing default database
			$error = 'Configuration file "%s" must specify a default ' .
				     'database configuration';
			$error = sprintf($error, $config);

			throw new AgaviParseException($error);

		}

		// let's do our fancy work
		foreach ($ini as $category => &$keys)
		{

			if (!in_array($category, $databases))
			{

				// skip this unspecified category
				continue;

			}

			if (!isset($keys['class']))
			{

				// missing class key
				$error = 'Configuration file "%s" specifies category ' .
						 '"%s" with missing class key';
				$error = sprintf($error, $config, $category);

				throw new AgaviParseException($error);

			}

			$class =& $keys['class'];

			if (isset($keys['file']))
			{

				// we have a file to include
				$file =& $keys['file'];
				$file =  $this->replaceConstants($file);
				$file =  $this->replacePath($file);

				if (!is_readable($file))
				{

				    // database file doesn't exist
				    $error = 'Configuration file "%s" specifies class "%s" ' .
						     'with nonexistent or unreadable file "%s"';
				    $error = sprintf($error, $config, $class, $file);

				    throw new AgaviParseException($error);

				}

				// append our data
				$tmp        = "require_once('%s');";
				$includes[] = sprintf($tmp, $file);

			}

			// parse parameters
			$parameters =& AgaviParameterParser::parse($keys);

			// append new data
			$tmp = "\$database = new %s();\n" .
				   "\$database->initialize(%s);\n" .
				   "\$this->databases['%s'] = \$database;";

			$data[] = sprintf($tmp, $class, $parameters,
						      array_search($category, $databases));

		}

		// compile data
		$retval = "<?php\n" .
				"// auth-generated by DatabaseConfigHandler\n" .
				"// date: %s\n%s\n%s\n?>";

		$retval = sprintf($retval, date('m/d/Y H:i:s'),
						  implode("\n", $includes), implode("\n", $data));

		return $retval;

	}

}

?>
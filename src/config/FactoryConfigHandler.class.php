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
 * FactoryConfigHandler allows you to specify which factory implementation the
 * system will use.
 *
 * @package    agavi
 * @subpackage config
 *
 * @author    Sean Kerr (skerr@mojavi.org)
 * @copyright (c) Sean Kerr, {@link http://www.mojavi.org}
 * @since     3.0.0
 * @version   $Id$
 */
class FactoryConfigHandler extends IniConfigHandler
{

	// +-----------------------------------------------------------------------+
	// | METHODS                                                               |
	// +-----------------------------------------------------------------------+

	/**
	 * Execute this configuration handler.
	 *
	 * @param string An absolute filesystem path to a configuration file.
	 *
	 * @return string Data to be written to a cache file.
	 *
	 * @throws <b>ConfigurationException</b> If a requested configuration file
	 *                                       does not exist or is not readable.
	 * @throws <b>ParseException</b> If a requested configuration file is
	 *                               improperly formatted.
	 *
	 * @author Sean Kerr (skerr@mojavi.org)
	 * @since  3.0.0
	 */
	public function & execute ($config)
	{
  	// available list of controllers
		$required_controllers = array('Controller');
  
  	// available list of factories
  	$required_factories = array('request', 'storage', 'user', 'security_filter');
  
		// set our required categories list and initialize our handler
		$categories = array('required_categories' => $required_controllers);
		$this->initialize($categories);
  
		// parse the ini
		$ini = $this->parseIni($config);
  
  	// Revers the order of the controllers
  	$ini = array_reverse($ini, true);
  
		// init our data and includes arrays
  	$controllers = array();
  
  	// check that every controller has the right paramers
  	foreach($ini as $controllerName => $factories) {
   		// init our data and includes arrays
	  	$includes  = array();
	  	$inits     = array();
	  	$instances = array();
  
   		// Build all classes  
   		foreach($required_factories as $factory) {
				if (!array_key_exists($factory, $factories)) {
	 				$error = 'Configuration file "%s" is missing "%s" key in "%s" category';
					$error = sprintf($error, $config, $factory, $controllerName);
					throw new ParseException($error);
				}
	
				// Get class name
				$class = $factories[$factory];
	
				// parse parameters
				$parameters = ParameterParser::parse($factories, $factory .'.param');
	
				// append new data
				switch ($factory) {
					case 'request':
	
						// append instance creation
						$tmp = "\t\$this->request = " .  "Request::newInstance('%s');";
						$instances[] = sprintf($tmp, $class);
	
						// append instance initialization
						$tmp = "\t\$this->request->initialize(\$this->context, " .  "%s);";
						$inits[] = sprintf($tmp, $parameters);
	
						break;
					case 'security_filter':
	
						// append creation/initialization in one swipe
						$tmp = "\n\tif (MO_USE_SECURITY)\n\t{\n" .
						       "\t\t\$this->securityFilter = " .
						       "SecurityFilter::newInstance('%s');\n" .
						       "\t\t\$this->securityFilter->initialize(" .
						       "\$this->context, %s);\n\t}\n";
						$inits[] = sprintf($tmp, $class, $parameters);
	
						break;
					case 'storage':
	
						// append instance creation
						$tmp = "\t\$this->storage = " .
						       "Storage::newInstance('%s');";
						$instances[] = sprintf($tmp, $class);
	
						// append instance initialization
						$tmp = "\t\$this->storage->initialize(\$this->context, " . "%s);";
						$inits[] = sprintf($tmp, $parameters);
	
						break;
					case 'user':
	
						// append instance creation
						$tmp = "\t\$this->user = User::newInstance('%s');";
						$instances[] = sprintf($tmp, $class);
	
						// append instance initialization
						$tmp = "\t\$this->user->initialize(\$this->context, %s);";
						$inits[] = sprintf($tmp, $parameters);
						break;
					default:
					 continue;
				}

				if (isset($factories[$factory.'.file'])) {
					// we have a file to include
					$file =& $factories[$factory.'.file'];
					$file =  $this->replaceConstants($file);
					$file =  $this->replacePath($file);
	
					if (!is_readable($file)) {
		
						// factory file doesn't exist
						$error = 'Configuration file "%s" specifies class ' .
						         '"%s" with nonexistent or unreadablefile ' .
						         '"%s"';
						$error = sprintf($error, $config, $class, $file);
	
						throw new ParseException($error);
		
					}
		
					// append our data
					$tmp        = "\trequire_once('%s');";
					$includes[] = sprintf($tmp, $file);
		
				}
			}
  	
			// context creation
			$context = "\t\$this->context = new Context(%s, %s, %s, %s, %s);";
			$context = sprintf($context, '$this', '$this->request', '$this->user', '$this->storage',
			'$this->databaseManager');
		
			$tmp = "if (\$this instanceof $controllerName)\n{\n%s\n%s\n%s\n%s\n\treturn;\n}";
			$controllers[] = sprintf($tmp, implode("\n", $includes),
			implode("\n", $instances), $context, implode("\n", $inits));
		}
	
		// compile data
		$retval = "<?php\n" .
		"// auth-generated by FactoryConfigHandler\n" .
		"// date: %s\n%s\n?>";
		$retval = sprintf($retval, date('m/d/Y H:i:s'),
		implode("\n", $controllers));

		return $retval;

	}

}

?>

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
 * @author    Sean Kerr (skerr@mojavi.org) {@link http://www.mojavi.org}
 * @author    Mike Vincent (mike@agavi.org) {@link http://www.agavi.org}
 * @copyright (c) authors
 * @since     0.9.0
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
	 * @since  0.9.0
	 */
	public function & execute ($config)
	{
  	// We need to at least have a base controller defined
		$required_controllers = array('Controller');
  
  	// These factories must be defined. 
  	$required_factories = array('request', 'storage', 'user', 'security_filter');
  
		// set our required categories list and initialize our handler
		$categories = array('required_categories' => $required_controllers);
		$this->initialize($categories);
  
		// parse the ini
		$ini = $this->parseIni($config);
  
  	// Reverse the order of the controllers
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
						$instances[] = sprintf("\tself::\$instance->request = " .  "Request::newInstance('%s');", $class);
						$inits[] = sprintf("\tself::\$instance->request->initialize(self::\$instance, " .  "%s);", $parameters);
						break;
					case 'security_filter':
						$tmp = "\n\tif (AG_USE_SECURITY) {\n" .
						       "\t\tself::\$instance->securityFilter = SecurityFilter::newInstance('%s');\n" .
						       "\t\tself::\$instance->securityFilter->initialize(self::\$instance);\n" .
									 "\t}\n";
						$inits[] = sprintf($tmp, $class, $parameters);
						break;
					case 'storage':
						$instances[] = sprintf("\tself::\$instance->storage = Storage::newInstance('%s');", $class);
						$inits[] = sprintf("\tself::\$instance->storage->initialize(self::\$instance, " . "%s);", $parameters);
						break;
					case 'user':
						$instances[] = sprintf("\tself::\$instance->user = User::newInstance('%s');", $class);
						$inits[] = sprintf("\tself::\$instance->user->initialize(self::\$instance, %s);", $parameters);
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
					$includes[] = sprintf("\trequire_once('%s');", $file);
		
				}
			}
  	
			$tmp = "if (self::\$instance->controller instanceof $controllerName)\n{\n%s\n%s\n%s\n\treturn;\n}";
			$controllers[] = sprintf($tmp, implode("\n", $includes), implode("\n", $instances), implode("\n", $inits));
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

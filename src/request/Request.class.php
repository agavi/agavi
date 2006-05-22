<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2003-2006 the Agavi Project.                                |
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
 * AgaviRequest provides methods for manipulating client request information
 * such as attributes, errors and parameters. It is also possible to manipulate
 * the request method originally sent by the user.
 *
 * @package    agavi
 * @subpackage request
 *
 * @author     Sean Kerr <skerr@mojavi.org>
 * @author     Agavi Project <info@agavi.org>
 * @copyright  (c) Authors
 * @since      0.9.0
 *
 * @version    $Id$
 */
abstract class AgaviRequest extends AgaviAttributeHolder
{

	// +-----------------------------------------------------------------------+
	// | CONSTANTS                                                             |
	// +-----------------------------------------------------------------------+

	/**
	 * Process validation and execution for only GET requests.
	 *
	 * @since      0.9.0
	 */
	const GET = 2;

	/**
	 * Skip validation and execution for any request method.
	 *
	 * @since      0.9.0
	 */
	const NONE = 1;

	/**
	 * Process validation and execution for only POST requests.
	 *
	 * @since      0.9.0
	 */
	const POST = 4;

	/**
	 * Process validation and execution for only CONSOLE requests.
	 *
	 * @since      0.9.0
	 */
	const CONSOLE = 8;

	protected
		$attributes = array(),
		$errors     = array(),
		$method     = null,
		$context    = null,
		$moduleAccessor = 'module',
		$actionAccessor = 'action';

	/**
	 * Retrieve the current application context.
	 *
	 * @return     Context A Context instance.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getContext()
	{
		return $this->context;
	}

	/**
	 * Extract parameter values from the request.
	 *
	 * @param      array An indexed array of parameter names to extract.
	 *
	 * @return     array An associative array of parameters and their values. 
	 *                   If a specified parameter doesn't exist then it's value 
	 *                   will be null. Also note that the value is a reference
	 *                   to the parameter's value.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     Mike Vincent <mike@agavi.org>
	 * @since      0.9.0
	 */
	public function & extractParameters($names)
	{
		$array = array();
		foreach ((array) $names as $name) {
			if (array_key_exists($name, $this->parameters)) {
				$array[$name] = &$this->parameters[$name];
			} else {
				$array[$name] = null;
			}
		}
		return $array;
	}

	/**
	 * Retrieve an error message.
	 *
	 * @param      string An error name.
	 *
	 * @return     string An error message, if the error exists, otherwise null.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function getError($name)
	{
		$retval = null;

		if (isset($this->errors[$name]))
		{

			$retval = $this->errors[$name];

		}

		return $retval;
	}

	/**
	 * Retrieve an array of error names.
	 *
	 * @return     array An indexed array of error names.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function getErrorNames()
	{
		return array_keys($this->errors);
	}

	/**
	 * Retrieve an array of errors.
	 *
	 * @return     array An associative array of errors.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function getErrors()
	{
		return $this->errors;
	}

	/**
	 * Retrieve this request's method.
	 *
	 * @return     int One of the following constants:
	 *                 - AgaviRequest::GET
	 *                 - AgaviRequest::POST
	 *                 - AgaviRequest::CONSOLE
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function getMethod()
	{
		return $this->method;
	}

	/**
	 * Indicates whether or not an error exists.
	 *
	 * @param      string An error name.
	 *
	 * @return     bool true, if the error exists, otherwise false.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function hasError($name)
	{
		return isset($this->errors[$name]);
	}


	/**
	 * Indicates whether or not any errors exist.
	 *
	 * @return     bool true, if any error exist, otherwise false.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function hasErrors()
	{
		return (count($this->errors) > 0);
	}

	/**
	 * Initialize this Request.
	 *
	 * @param      AgaviContext A Context instance.
	 * @param      array        An associative array of initialization parameters.
	 *
	 * @return     bool true, if initialization completes successfully
	 *                  otherwise false.
	 *
	 * @throws     <b>AgaviInitializationException</b> If an error occurs while
	 *                                                 initializing this Request.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.9.0
	 */
	function initialize($context, $parameters = null)
	{
		$this->context = $context;
		
		if(isset($parameters['default_namespace'])) {
			$this->defaultNamespace = $parameters['default_namespace'];
		}
		
		if(isset($parameters['module_accessor'])) {
			$this->moduleAccessor = $parameters['module_accessor'];
		}
		if(isset($parameters['action_accessor'])) {
			$this->actionAccessor = $parameters['action_accessor'];
		}
		
		return true;
	}

	/**
	 * Retrieve a new Request implementation instance.
	 *
	 * @param      string A Request implementation name.
	 *
	 * @return     AgaviRequest A Request implementation instance.
	 *
	 * @throws     <b>AgaviFactoryException</b> If a request implementation instance
	 *                                          cannot be created.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public static function newInstance($class)
	{
		// the class exists
		$object = new $class();

		if (!($object instanceof AgaviRequest))
		{

			// the class name is of the wrong type
			$error = 'Class "%s" is not of the type Request';
			$error = sprintf($error, $class);

			throw new AgaviFactoryException($error);

		}

		return $object;
	}

	/**
	 * Remove an error.
	 *
	 * @param      string An error name.
	 *
	 * @return     string An error message, if the error was removed, otherwise
	 *                    null.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.9.0
	 */
	public function removeError($name)
	{
		$retval = null;

		if (isset($this->errors[$name]))
		{

			$retval = $this->errors[$name];

			unset($this->errors[$name]);

		}

		return $retval;
	}

	/**
	 * Set an error.
	 *
	 * @param      name    An error name.
	 * @param      message An error message.
	 *
	 * @return     void
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function setError($name, $message)
	{
		$this->errors[$name] = $message;
	}


	/**
	 * Set an array of errors
	 *
	 * If an existing error name matches any of the keys in the supplied
	 * array, the associated message will be overridden.
	 *
	 * @param      array An associative array of errors and their associated
	 *                   messages.
	 *
	 * @return     void
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function setErrors($errors)
	{
		$this->errors = array_merge($this->errors, $errors);
	}

	/**
	 * Set the request method.
	 *
	 * @param      int One of the following constants:
	 *                 - AgaviRequest::GET
	 *                 - AgaviRequest::POST
	 *                 - AgaviRequest::CONSOLE
	 *
	 * @return     void
	 *
	 * @throws     <b>AgaviException</b> - If the specified request method is
	 *                                          invalid.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function setMethod($method)
	{
		if ($method == self::GET || $method == self::POST || $method == self::CONSOLE) {
			$this->method = $method;
			return;
		}

		// invalid method type
		$error = 'Invalid request method: %s';
		$error = sprintf($error, $method);

		throw new AgaviException($error);
	}
	
	/**
	 * Get the name of the request parameter that defines which module to use.
	 *
	 * @return     string The module accessor name.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getModuleAccessor()
	{
		return $this->moduleAccessor;
	}

	/**
	 * Get the name of the request parameter that defines which action to use.
	 *
	 * @return     string The action accessor name.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getActionAccessor()
	{
		return $this->actionAccessor;
	}

	/**
	 * Execute the shutdown procedure.
	 *
	 * @return     void
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	abstract function shutdown();

}

?>
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
	 * @return     AgaviContext An AgaviContext instance.
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
	 * @return     string The request method name
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     David Zuelke <dz@bitxtender.com>
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
	 * @param      AgaviContext An AgaviContext instance.
	 * @param      array        An associative array of initialization parameters.
	 *
	 * @throws     <b>AgaviInitializationException</b> If an error occurs while
	 *                                                 initializing this Request.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.9.0
	 */
	function initialize(AgaviContext $context, $parameters = array())
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
	 * @param      string The request method name.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.9.0
	 */
	public function setMethod($method)
	{
		$this->method = $method;
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
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	function shutdown()
	{
	}

	/**
	 * @see        AgaviParameterHolder::getParameter()
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.9.0
	 */
	public function & getParameter($name, $default = null)
	{
		if(($pos = strpos($name, '[')) === false) {
			$retval =& parent::getParameter($name, $default);
			return $retval;
		} else {
			$array = explode('][', rtrim(ltrim(substr($name, $pos), '['), ']'));
			$name = substr($name, 0, $pos);
			$val = $this->getParameter($name);
			foreach($array as $key) {
				if($key == '') {
					$key = 0;
				} elseif(is_numeric($key)) {
					$key = intval($key);
				}
				if(isset($val[$key])) {
					$val = $val[$key];
				} else {
					return $default;
				}
			}
			return $val;
		}
	}

	/**
	 * @see        AgaviParameterHolder::hasParameter()
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.9.0
	 */
	public function hasParameter($name)
	{
		if(($pos = strpos($name, '[')) === false) {
			return parent::hasParameter($name);
		} else {
			$array = explode('][', rtrim(ltrim(substr($name, $pos), '['), ']'));
			$name = substr($name, 0, $pos);
			$val = $this->getParameter($name);
			foreach($array as $key) {
				if($key == '') {
					$key = 0;
				} elseif(is_numeric($key)) {
					$key = intval($key);
				}
				if(isset($val[$key])) {
					$val = $val[$key];
				} else {
					return false;
				}
			}
			return true;
		}
	}

}

?>
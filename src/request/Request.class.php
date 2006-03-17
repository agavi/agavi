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
abstract class AgaviRequest extends AgaviParameterHolder
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
		$method     = null;

	/**
	 * Clear all attributes associated with this request.
	 *
	 * @return     void
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function clearAttributes ()
	{

		$this->attributes = null;
		$this->attributes = array();

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
	public function & extractParameters ($names)
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
	 * Retrieve an attribute.
	 *
	 * @param      string An attribute name.
	 * @param      string An attribute namespace.
	 * @param      mixed  A default attribute value.
	 *
	 * @return     mixed An attribute value, if the attribute exists, otherwise
	 *                   null.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     Bob Zoller <bob@agavi.org>
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.9.0
	 */
	public function &getAttribute($name, $ns = null, $default = null)
	{
		if($ns === null) {
			$ns = AgaviConfig::get('request.default_namespace');
		}
		
		$retval =& $default;

		if (isset($this->attributes[$ns]) && isset($this->attributes[$ns][$name])) {
			$retval =& $this->attributes[$ns][$name];
		}

		return $retval;
	}

	/**
	 * Retrieve an array of attributes.
	 *
	 * @param      string An attribute namespace.
	 *
	 * @return     array An associative array of attributes.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @author     Bob Zoller <bob@agavi.org>
	 * @since      0.10.0
	 */
	public function &getAttributes($ns = null)
	{
		if($ns === null) {
			$ns = AgaviConfig::get('request.default_namespace');
		}
		
		$retval = array();
		if(isset($this->attributes[$ns])) {
			return $this->attributes[$ns];
		}
		return $retval;
	}

	/**
	 * Retrieve an array of attribute names.
	 *
	 * @param      string An attribute namespace.
	 *
	 * @return     array An indexed array of attribute names, if the namespace
	 *                   exists, otherwise null.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getAttributeNames($ns = null)
	{
		if($ns === null) {
			$ns = AgaviConfig::get('request.default_namespace');
		}
		
		if(isset($this->attributes[$ns])) {
			return array_keys($this->attributes[$ns]);
		}
		return null;
	}

	/**
	 * Retrieve all attributes within a namespace.
	 *
	 * @param      string An attribute namespace.
	 *
	 * @return     array An associative array of attributes if the namespace
	 *                   exists, otherwise null.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.11.0
	 */
	public function &getAttributeNamespace($ns = null)
	{
		if($ns === null) {
			$ns = AgaviConfig::get('request.default_namespace');
		}
		
		$retval = null;
		if(isset($this->attributes[$ns])) {
			return $this->attributes[$ns];
		}
		return $retval;
	}

	/**
	 * Retrieve an array of attribute namespaces.
	 *
	 * @return     array An indexed array of attribute namespaces.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getAttributeNamespaces()
	{
		return array_keys($this->attributes);
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
	public function getError ($name)
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
	public function getErrorNames ()
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
	public function getErrors ()
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
	public function getMethod ()
	{

		return $this->method;

	}

	/**
	 * Indicates whether or not an attribute exists.
	 *
	 * @param      string An attribute name.
	 * @param      string An attribute namespace.
	 *
	 * @return     bool true, if the attribute exists, otherwise false.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function hasAttribute ($name, $ns = null)
	{
		if($ns === null) {
			$ns = AgaviConfig::get('request.default_namespace');
		}
		
		if(isset($this->attributes[$ns]))
		{
			return isset($this->attributes[$ns][$name]);
		}
		
		return false;
	}

	/**
	 * Indicates whether or not an attribute namespace exists.
	 *
	 * @param      string An attribute namespace.
	 *
	 * @return     bool true, if the namespace exists, otherwise false.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function hasAttributeNamespace ($ns)
	{
		return isset($this->attributes[$ns]);
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
	public function hasError ($name)
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
	public function hasErrors ()
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
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	abstract function initialize ($context, $parameters = null);

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
	public static function newInstance ($class)
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
	public function removeError ($name)
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
	 * Remove an attribute.
	 *
	 * @param      string An attribute name.
	 * @param      string An attribute namespace.
	 *
	 * @return     mixed An attribute value, if the attribute was removed,
	 *                   otherwise null.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.9.0
	 */
	public function & removeAttribute ($name, $ns = null)
	{
		if($ns === null) {
			$ns = AgaviConfig::get('request.default_namespace');
		}
		
		$retval = null;

		if (isset($this->attributes[$ns]) &&
			isset($this->attributes[$ns][$name]))
		{

			$retval =& $this->attributes[$ns][$name];

			unset($this->attributes[$ns][$name]);

		}

		return $retval;

	}

	/**
	 * Remove an attribute namespace and all of its associated attributes.
	 *
	 * @param      string An attribute namespace.
	 *
	 * @return     void
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.10.0
	 */
	public function removeAttributeNamespace ($ns)
	{

		if (isset($this->attributes[$ns]))
		{

			unset($this->attributes[$ns]);

		}

	}

	/**
	 * Set an attribute.
	 *
	 * If an attribute with the name already exists the value will be
	 * overridden.
	 *
	 * @param      string An attribute name.
	 * @param      mixed  An attribute value.
	 * @param      string An attribute namespace.
	 *
	 * @return     void
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.9.0
	 */
	public function setAttribute($name, $value, $ns = null)
	{
		if($ns === null) {
			$ns = AgaviConfig::get('request.default_namespace');
		}
		
		if (!isset($this->attributes[$ns])) {
			$this->attributes[$ns] = array();
		}

		$this->attributes[$ns][$name] = $value;

	}

	/**
	 * Append an attribute.
	 *
	 * If an attribute with the name already exists, it will be converted to an
	 * array and the new value appended.
	 *
	 * @param      string An attribute name.
	 * @param      mixed  An attribute value.
	 * @param      string An attribute namespace.
	 *
	 * @return     void
	 *
	 * @author     Bob Zoller <bob@agavi.org>
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.10.0
	 */
	public function appendAttribute($name, $value, $ns = null)
	{
		if($ns === null) {
			$ns = AgaviConfig::get('request.default_namespace');
		}
		
		if (!isset($this->attributes[$ns])) {
			$this->attributes[$ns] = array();
		}

		if (!isset($this->attributes[$ns][$name]) || !is_array($this->attributes[$ns][$name])) {
			settype($this->attributes[$ns][$name], 'array');
		}
		$this->attributes[$ns][$name][] = $value;

	}

	/**
	 * Set an attribute by reference.
	 *
	 * If an attribute with the name already exists the value will be
	 * overridden.
	 *
	 * @param      string An attribute name.
	 * @param      mixed  A reference to an attribute value.
	 * @param      string An attribute namespace.
	 *
	 * @return     void
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.9.0
	 */
	public function setAttributeByRef($name, &$value, $ns = null)
	{
		if($ns === null) {
			$ns = AgaviConfig::get('request.default_namespace');
		}
		
		if (!isset($this->attributes[$ns])) {
			$this->attributes[$ns] = array();
		}

		$this->attributes[$ns][$name] =& $value;

	}

	/**
	 * Append an attribute by reference.
	 *
	 * If an attribute with the name already exists, it will be converted to an
	 * array and the reference to the new value appended.
	 *
	 * @param      string An attribute name.
	 * @param      mixed  A reference to an attribute value.
	 * @param      string An attribute namespace.
	 *
	 * @return     void
	 *
	 * @author     Bob Zoller <bob@agavi.org>
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.10.0
	 */
	public function appendAttributeByRef($name, &$value, $ns = null)
	{
		if($ns === null) {
			$ns = AgaviConfig::get('request.default_namespace');
		}
		
		if (!isset($this->attributes[$ns])) {
			$this->attributes[$ns] = array();
		}

		if (!isset($this->attributes[$ns][$name]) || !is_array($this->attributes[$ns][$name])) {
			settype($this->attributes[$ns][$name], 'array');
		}
		$this->attributes[$ns][$name][] =& $value;

	}

	/**
	 * Set an array of attributes.
	 *
	 * If an existing attribute name matches any of the keys in the supplied
	 * array, the associated value will be overridden.
	 *
	 * @param      array  An associative array of attributes and their
	 *                    associated values.
	 * @param      string An attribute namespace.
	 *
	 * @return     void
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.9.0
	 */
	public function setAttributes ($attributes, $ns = null)
	{
		if($ns === null) {
			$ns = AgaviConfig::get('request.default_namespace');
		}
		
		if (!isset($this->attributes[$ns]))
		{

			$this->attributes[$ns] = array();

		}

		$this->attributes[$ns] = array_merge($this->attributes[$ns],
						                     $attributes);

	}

	/**
	 * Set an array of attributes by reference.
	 *
	 * If an existing attribute name matches any of the keys in the supplied
	 * array, the associated value will be overridden.
	 *
	 * @param      array  An associative array of attributes and references to
	 *                    their associated values.
	 * @param      string An attribute namespace.
	 *
	 * @return     void
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.9.0
	 */
	public function setAttributesByRef (&$attributes, $ns = null)
	{
		if($ns === null) {
			$ns = AgaviConfig::get('request.default_namespace');
		}
		
		if (!isset($this->attributes[$ns]))
		{

			$this->attributes[$ns] = array();

		}

		foreach ($attributes as $key => &$value)
		{

			$this->attributes[$ns][$key] =& $value;

		}

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
	public function setError ($name, $message)
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
	public function setErrors ($errors)
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
	public function setMethod ($method)
	{

		if ($method == self::GET || $method == self::POST || $method == self::CONSOLE)
		{

			$this->method = $method;

			return;

		}

		// invalid method type
		$error = 'Invalid request method: %s';
		$error = sprintf($error, $method);

		throw new AgaviException($error);

	}

	/**
	 * Execute the shutdown procedure.
	 *
	 * @return     void
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	abstract function shutdown ();

}

?>
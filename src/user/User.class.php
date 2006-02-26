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
 * User wraps a client session and provides accessor methods for user
 * attributes. It also makes storing and retrieving multiple page form data
 * rather easy by allowing user attributes to be stored in namespaces, which
 * help organize data.
 *
 * @package    agavi
 * @subpackage user
 *
 * @author     Sean Kerr <skerr@mojavi.org>
 * @author     Agavi Project <info@agavi.org>
 * @copyright  (c) Authors
 * @since      0.9.0
 *
 * @version    $Id$
 */
class User extends ParameterHolder
{

	// +-----------------------------------------------------------------------+
	// | CONSTANTS                                                             |
	// +-----------------------------------------------------------------------+

	/**
	 * The namespace under which attributes will be stored.
	 *
	 * @since      0.9.0
	 */
	const ATTRIBUTE_NAMESPACE = 'org/agavi/user/User/attributes';

	protected
		$attributes = null,
		$context = null;

	/**
	 * Clear all attributes associated with this user.
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
	 * @since      0.9.0
	 */
	public function & getAttribute ($name, $ns = AG_USER_NAMESPACE, $default=null)
	{

		$retval =& $default;

		if (isset($this->attributes[$ns]) &&
			isset($this->attributes[$ns][$name]))
		{

			$retval =& $this->attributes[$ns][$name];

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
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function getAttributeNames ($ns = AG_USER_NAMESPACE)
	{

		if (isset($this->attributes[$ns]))
		{

			return array_keys($this->attributes[$ns]);

		}

		return null;

	}

	/**
	 * Retrieve all attributes within a namespace.
	 *
	 * @param      string An attribute namespace.
	 *
	 * @return     array An associative array of attributes.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function &getAttributes($ns = AG_USER_NAMESPACE)
	{
		$retval =& $this->getAttributeNamespace($ns);
		return $retval;
	}

	/**
	 * Retrieve all attributes within a namespace.
	 *
	 * @param      string An attribute namespace.
	 *
	 * @return     array An associative array of attributes.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function & getAttributeNamespace ($ns = AG_USER_NAMESPACE)
	{

		$retval = null;

		if (isset($this->attributes[$ns]))
		{

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
	 * @since      0.9.0
	 */
	public function getAttributeNamespaces ()
	{

		return array_keys($this->attributes);

	}

	/**
	 * Retrieve the current application context.
	 *
	 * @return     Context A Context instance.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function getContext ()
	{

		return $this->context;

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
	 * @since      0.9.0
	 */
	public function hasAttribute ($name, $ns = AG_USER_NAMESPACE)
	{

		if (isset($this->attributes[$ns]))
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
	 * @since      0.9.0
	 */
	public function hasAttributeNamespace ($ns)
	{

		return isset($this->attributes[$ns]);

	}

	/**
	 * Initialize this User.
	 *
	 * @param      Context A Context instance.
	 * @param      array   An associative array of initialization parameters.
	 *
	 * @return     bool true, if initialization completes successfully,
	 *                  otherwise false.
	 *
	 * @throws     <b>InitializationException</b> If an error occurs while
	 *                                            initializing this User.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function initialize ($context, $parameters = null)
	{

		$this->context = $context;

		if ($parameters != null)
		{

			$this->parameters = array_merge($this->parameters, $parameters);

		}

		// read data from storage
		$this->attributes = $context->getStorage()->read(self::ATTRIBUTE_NAMESPACE);

		if ($this->attributes == null)
		{

			// initialize our attributes array
			$this->attributes = array();

		}

	}

	/**
	 * Retrieve a new User implementation instance.
	 *
	 * @param      string A User implementation name
	 *
	 * @return     User A User implementation instance.
	 *
	 * @throws     <b>FactoryException</b> If a user implementation instance
	 *                                     cannot be created.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public static function newInstance ($class)
	{

		// the class exists
		$object = new $class();

		if (!($object instanceof User))
		{

			// the class name is of the wrong type
			$error = 'Class "%s" is not of the type User';
			$error = sprintf($error, $class);

			throw new FactoryException($error);

		}

		return $object;

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
	 * @since      0.9.0
	 */
	public function & removeAttribute ($name, $ns = AG_USER_NAMESPACE)
	{

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
	 * @since      0.9.0
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
	 * @since      0.9.0
	 */
	public function setAttribute($name, $value, $ns = AG_USER_NAMESPACE)
	{

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
	 * @since      0.10.0
	 */
	public function appendAttribute($name, $value, $ns = AG_USER_NAMESPACE)
	{

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
	 * @since      0.9.0
	 */
	public function setAttributeByRef($name, &$value, $ns = AG_USER_NAMESPACE)
	{

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
	 * @since      0.10.0
	 */
	public function appendAttributeByRef($name, &$value, $ns = AG_USER_NAMESPACE)
	{

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
	 * @since      0.9.0
	 */
	public function setAttributes ($attributes, $ns = AG_USER_NAMESPACE)
	{

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
	 * @since      0.9.0
	 */
	public function setAttributesByRef (&$attributes, $ns = AG_USER_NAMESPACE)
	{

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
	 * Execute the shutdown procedure.
	 *
	 * @return     void
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function shutdown ()
	{

		// write attributes to the storage
		$this->getContext()->getStorage()->write(self::ATTRIBUTE_NAMESPACE, $this->attributes);

	}

}

?>
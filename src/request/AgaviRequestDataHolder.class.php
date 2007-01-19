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
 * AgaviRequestDataHolder provides methods for retrieving client request 
 * information parameters.
 *
 * @package    agavi
 * @subpackage request
 *
 * @author     Dominik del Bondio <ddb@bitxtender.com>
 * @author     Agavi Project <info@agavi.org>
 * @copyright  (c) Authors
 * @since      0.11.0
 *
 * @version    $Id$
 */
class AgaviRequestDataHolder extends AgaviParameterHolder
{
	/**
	 * @constant   Constant for source name of parameters.
	 */
	const SOURCE_PARAMETERS = 'parameters';

	/*
	 * @var        array An array of source names and references to their data
	 *                   containers.
	 */
	private $_sources = array();

	/*
	 * @var        array An array of plural source names and their singular forms.
	 */
	private $_singularSourceNames = array();

	/**
	 * Retrieves a field from one of the stored data types.
	 *
	 * @param      string The name of the source to operate on.
	 * @param      string A field name.
	 * @param      mixed  A default value.
	 *
	 * @return     mixed The field value.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function & get($source, $field, $default = null)
	{
		if(isset($this->$source)) {
			$funcname = 'get' . $this->_singularSourceNames[$source];
			return $this->$funcname($field, $default);
		}
	}

	/**
	 * Retrieves all fields of a stored data types.
	 *
	 * @param      string The name of the source to operate on.
	 *
	 * @return     mixed The values.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function & getAll($source)
	{
		if(isset($this->$source)) {
			$funcname = 'get' . $source;
			return $this->$funcname();
		}
	}

	/**
	 * Checks if a field exists.
	 *
	 * @param      string The name of the source to operate on.
	 * @param      string A field name.
	 *
	 * @return     bool The result.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function has($source, $field)
	{
		if(isset($this->$source)) {
			$funcname = 'get' . $this->_singularSourceNames[$source];
			return $this->$funcname($field);
		}
	}

	/**
	 * Removes a field.
	 *
	 * @param      string The name of the source to operate on.
	 * @param      string A field name.
	 *
	 * @return     mixed The removed value.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function & remove($source, $field)
	{
		if(isset($this->$source)) {
			$funcname = 'get' . $this->_singularSourceNames[$source];
			return $this->$funcname($field);
		}
	}

	/**
	 * Sets a field.
	 *
	 * @param      string The name of the source to operate on.
	 * @param      string A field name.
	 * @param      mixed  A value.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function set($source, $field, $value)
	{
		if(isset($this->$source)) {
			$funcname = 'get' . $this->_singularSourceNames[$source];
			$this->$funcname($field, $value);
		}
	}
	
	/**
	 * Register a source with the holder. Must be called in constructors, and
	 * prior to calling the parent ctor.
	 *
	 * @param      string The source name, typically passed using a constant.
	 * @param      array  The variable that will hold the data for the source.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	final protected function registerSource($name, array &$holder)
	{
		$this->_sources[$name] =& $holder;
		$this->_singularSourceNames[$name] = AgaviInflector::singularize($name);
	}

	/**
	 * Constructor
	 *
	 * @param      array An associative array of request data source names and
	 *                   data arrays.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function __construct(array $data)
	{
		$this->registerSource(self::SOURCE_PARAMETERS, $this->parameters);
		
		foreach($this->_sources as $name => &$container) {
			if(isset($data[$name]) && is_array($data[$name])) {
				$container = $data[$name];
			} else {
				$container = array();
			}
		}
	}
}

?>
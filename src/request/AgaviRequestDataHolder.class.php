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
	 *                   containers. Unset again after construction is complete.
	 */
	private $_sources = array();

	/*
	 * @var        array An array of plural source names and their singular forms.
	 */
	private $_sourceNames = array();

	/**
	 * Merge in parameters from another request data holder.
	 *
	 * @param      AgaviRequestDataHolder The other request data holder.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	protected function mergeParameters(AgaviRequestDataHolder $other)
	{
		$this->setParameters($other->getParameters());
	}

	/**
	 * Checks if there is a value of a parameter is empty.
	 *
	 * @param      string The field name.
	 *
	 * @retrun     bool The result.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	protected function isParameterValueEmpty($field)
	{
		return !$this->hasParameter($field);
	}
	
	/**
	 * Deletes all fields in a given source.
	 *
	 * @param      string The name of the source to operate on.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function clear($source)
	{
		if(isset($this->$source)) {
			$funcname = 'clear' . $source;
			$this->$funcname();
		}
	}

	/**
	 * Deletes all fields in all sources.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function clearAll()
	{
		foreach($this->_sourceNames as $sourceName => $source) {
			$funcname = 'clear' . $sourceName;
			$this->$funcname();
		}
	}

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
			$funcname = 'get' . $this->_sourceNames[$source];
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
			$funcname = 'has' . $this->_sourceNames[$source];
			return $this->$funcname($field);
		}
	}

	/**
	 * Checks if a field has no value (In web context this would only return true
	 * when the strings length is 0 or the field is not set.
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
	public function isValueEmpty($source, $field)
	{
		if(isset($this->$source)) {
			$funcname = 'is' . $this->_sourceNames[$source] . 'ValueEmpty';
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
			$funcname = 'remove' . $this->_sourceNames[$source];
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
			$funcname = 'set' . $this->_sourceNames[$source];
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
		$this->_sourceNames[$name] = AgaviInflector::singularize($name);
	}
	
	/**
	 * Merge in another request data holder.
	 *
	 * This method calls mergeSourcename for each source.
	 *
	 * @param      AgaviRequestDataHolder The other request data holder.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function merge(AgaviRequestDataHolder $other)
	{
		foreach(array_keys($this->_sourceNames) as $source) {
			$fn = 'merge' . $source; // plural form!
			$this->$fn($other);
		}
	}
	
	/**
	 * Returns all the registered source names.
	 *
	 * @return     array A list of source names.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	final public function getSourceNames()
	{
		return array_keys($this->_sourceNames);
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
		
		// unset it to clean up references that otherwise would mess up cloning
		unset($this->_sources);
	}
}

?>
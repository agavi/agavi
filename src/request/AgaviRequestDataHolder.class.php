
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
	const PARAMETER = 'parameter';

	/*
	 * @var        AgaviRequest The request instance.
	 */
	protected $request = null;

	/**
	 * Retrieve the current application context.
	 *
	 * @return     AgaviContext An AgaviContext instance.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getContext()
	{
		return $this->request->getContext();
	}

	/**
	 * Retrieve the request instance.
	 *
	 * @return     AgaviRequest An AgaviRequest instance.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getRequest()
	{
		return $this->request;
	}

	/**
	 * Retrieves a field from one of the stored data types.
	 *
	 * @param      string The type to search in (PARAMETER, ...)
	 * @param      string A field name.
	 * @param      mixed  A default value.
	 *
	 * @return     mixed The field value.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function & get($type, $field, $default = null)
	{
		$funcname = 'get' . ucfirst($type);
		if(!is_callable(array($this, $funcname))) {
			throw new InvalidArgumentException('Could not get item of type: ' . $type . '');
		}

		return $this->$funcname($field, $default);
	}

	/**
	 * Retrieves all fields of a stored data types.
	 *
	 * @param      string The type.
	 *
	 * @return     mixed The values.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function & getAll($type)
	{
		$funcname = 'get' . ucfirst(AgaviInflector::pluralize($type));
		if(!is_callable(array($this, $funcname))) {
			throw new InvalidArgumentException('Could not get item of type: ' . $type . '');
		}

		return $this->$funcname();
	}

	/**
	 * Checks if a field exists.
	 *
	 * @param      string The type.
	 * @param      string A field name.
	 *
	 * @return     bool The result.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function has($type, $field)
	{
		$funcname = 'has' . ucfirst($type);
		if(!is_callable(array($this, $funcname))) {
			throw new InvalidArgumentException('Could not check for item of type: ' . $type . '');
		}

		return $this->$funcname($field);
	}

	/**
	 * Removes a field.
	 *
	 * @param      string The type.
	 * @param      string A field name.
	 *
	 * @return     mixed The removed value.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function & remove($type, $field)
	{
		$funcname = 'remove' . ucfirst($type);
		if(!is_callable(array($this, $funcname))) {
			throw new InvalidArgumentException('Could not remove item of type: ' . $type . '');
		}

		return $this->$funcname($field);
	}

	/**
	 * Sets a field.
	 *
	 * @param      string The type to.
	 * @param      string A field name.
	 * @param      mixed  A value.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function set($type, $field, $value)
	{
		$funcname = 'get' . ucfirst($type);
		if(!is_callable(array($this, $funcname))) {
			throw new InvalidArgumentException('Could not set item of type: ' . $type . '');
		}

		$this->$funcname($field, $value);
	}

	/**
	 * Initialize this RequestDataHolder.
	 *
	 * @param      AgaviRequest An AgaviRequest instance.
	 * @param      array        An associative array of request parameters.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function initialize(AgaviRequest $request, array $parameters = array())
	{
		$this->request = $request;
		$this->setParameters($parameters);
	}

}

?>
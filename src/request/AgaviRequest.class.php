<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2005-2010 the Agavi Project.                                |
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
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.9.0
 *
 * @version    $Id$
 */
abstract class AgaviRequest extends AgaviAttributeHolder
{
	/**
	 * @var        array An associative array of attributes
	 */
	protected $attributes = array();

	/**
	 * @var        array An associative array of errors
	 */
	protected $errors     = array();

	/**
	 * @var        string The request method name
	 */
	protected $method     = null;

	/**
	 * @var        AgaviContext An AgaviContext instance.
	 */
	protected $context    = null;

	/**
	 * @var        AgaviRequestDataHolder The request data holder instance.
	 */
	private $requestData = null;

	/**
	 * @var        string The key used to lock the request, or null if no lock set
	 */
	private $key = null;

	/**
	 * Retrieve the current application context.
	 *
	 * @return     AgaviContext An AgaviContext instance.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public final function getContext()
	{
		return $this->context;
	}

	/**
	 * Retrieve this requests method.
	 *
	 * @return     string The request method name
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.9.0
	 */
	public function getMethod()
	{
		return $this->method;
	}

	/**
	 * Constructor.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function __construct()
	{
		$this->setParameters(array(
			'use_module_action_parameters' => false,
			'module_accessor' => 'module',
			'action_accessor' => 'action',
			'request_data_holder_class' => 'AgaviRequestDataHolder',
		));
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
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.9.0
	 */
	public function initialize(AgaviContext $context, array $parameters = array())
	{
		$this->context = $context;
		
		if(isset($parameters['default_namespace'])) {
			$this->defaultNamespace = $parameters['default_namespace'];
			unset($parameters['default_namespace']);
		}
		
		$this->setParameters($parameters);
	}

	/**
	 * Set the request method.
	 *
	 * @param      string The request method name.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.9.0
	 */
	public function setMethod($method)
	{
		$this->method = $method;
	}

	/**
	 * Set the data holder instance of this request.
	 *
	 * @param      AgaviRequestDataHolder The request data holder.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	final protected function setRequestData(AgaviRequestDataHolder $rd)
	{
		if(!$this->isLocked()) {
			$this->requestData = $rd;
		}
	}

	/**
	 * Get the data holder instance of this request.
	 *
	 * @return     AgaviRequestDataHolder The request data holder.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	final public function getRequestData()
	{
		if($this->isLocked()) {
			throw new AgaviException("Access to request data is locked during Action and View execution and while templates are rendered. Please use the local request data holder passed to your Action's or View's execute*() method to access request data.");
		}
		return $this->requestData;
	}

	/**
	 * Do any necessary startup work after initialization.
	 *
	 * This method is not called directly after initialize().
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function startup()
	{
		if($this->getParameter('unset_input', true)) {
			// remove raw post data
			// can still be read from php://input, but we can't prevent that
			unset($GLOBALS['HTTP_RAW_POST_DATA']);
			
			// nuke argc and argc if necessary
			$rla = ini_get('register_long_arrays');
			
			if(isset($_SERVER['argc'])) {
				$_SERVER['argc'] = 0;
				if(isset($GLOBALS['argc'])) {
					$GLOBALS['argc'] = 0;
				}
				if($rla) {
					$GLOBALS['HTTP_SERVER_VARS']['argc'] = 0;
				}
			}
			if(isset($_SERVER['argv'])) {
				$_SERVER['argv'] = array();
				if(isset($GLOBALS['argv'])) {
					$GLOBALS['argv'] = array();
				}
				if($rla) {
					$GLOBALS['HTTP_SERVER_VARS']['argv'] = array();
				}
			}
		}
	}

	/**
	 * Execute the shutdown procedure.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function shutdown()
	{
	}
	
	/**
	 * Get a value by trying to find the given key in $_SERVER first, then in
	 * $_ENV. If nothing was found, return the key, or the given default value.
	 *
	 * @param      mixed  The key (or an array of keys) of the value to fetch.
	 * @param      mixed  A default return value, or null if the key should be
	 *                    returned (static return values can be defined this way).
	 *
	 * @author     David Zülke
	 * @since      0.11.0
	 */
	public static function getSourceValue($keys, $default = null)
	{
		$keys = (array)$keys;
		// walk over all possible keys
		foreach($keys as $key) {
			if(isset($_SERVER[$key])) {
				return $_SERVER[$key];
			} elseif(isset($_ENV[$key])) {
				return $_ENV[$key];
			}
		}
		if($default !== null) {
			return $default;
		}
		// nothing found so far. remember that the keys list is an array
		if($keys) {
			return end($keys);
		}
	}

	/**
	 * Whether or not the Request is locked.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public final function isLocked()
	{
		return $this->key !== null;
	}

	/**
	 * Lock or unlock the Request so request data can(not) be fetched anymore.
	 *
	 * @param      string The key to unlock, if the lock should be removed, or
	 *                    null if the lock should be set.
	 *
	 * @return     mixed The key, if a lock was set, or a boolean value indicating
	 *                   whether or not the unlocking was successful.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public final function toggleLock($key = null)
	{
		if(!$this->isLocked() && $key === null) {
			$this->locked = true;
			return $this->key = AgaviToolkit::uniqid();
		} elseif($this->isLocked()) {
			if($this->key === $key) {
				$this->key = null;
				return true;
			}
			return false;
		}
	}
}

?>
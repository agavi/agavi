<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2003-2007 the Agavi Project.                                |
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
	protected $requestData = null;

	/**
	 * @var        bool A boolean value indicating whether or not the request is 
	 *                  locked.
	 */
	private $locked = false;

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
	 * Retrieve this request's method.
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
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function __construct()
	{
		$this->setParameters(array(
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
	 * Get the name of the request parameter that defines which module to use.
	 *
	 * @return     string The module accessor name.
	 *
	 * @deprecated Use getParameter('module_accessor') instead.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getModuleAccessor()
	{
		return $this->getParameter('module_accessor');
	}

	/**
	 * Get the name of the request parameter that defines which action to use.
	 *
	 * @return     string The action accessor name.
	 *
	 * @deprecated Use getParameter('action_accessor') instead.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getActionAccessor()
	{
		return $this->getParameter('action_accessor');
	}

	/**
	 * Get the data holder instance of this request.
	 *
	 * @return     AgaviRequestDataHolder The request data holder.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getRequestData()
	{
		if($this->locked) {
			throw new AgaviException("Access to request data is locked during Action and View execution, please use the local request data holder passed to your Action's or View's execute*() method to access request data.");
		}
		return $this->requestData;
	}

	/**
	 * Do any necessary startup work after initialization.
	 *
	 * This method is not called directly after initialize().
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function startup()
	{
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
	 * Whether or not the Request is locked.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public final function isLocked()
	{
		return $this->locked;
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
		static $keys = array();
		if(!$this->locked && $key === null) {
			$this->locked = true;
			return $this->keys[$this->context->getName()] = uniqid();
		} elseif($this->locked) {
			if(isset($this->keys[$this->context->getName()]) && $this->keys[$this->context->getName()] == $key) {
				$this->locked = false;
				return true;
			}
			return false;
		}
	}
}

?>
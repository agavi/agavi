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
	 * @var        string The module accessor name.
	 */
	protected $moduleAccessor = 'module';

	/**
	 * @var        string The action accessor name.
	 */
	protected $actionAccessor = 'action';

	/**
	 * @var        string The locale of this request.
	 * @since      0.11.0
	 */
	protected $locale = null;

	/**
	 * @var        bool A boolean value indicating whether or not the request is locked.
	 */
	private $locked = false;


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
	 * Retrieve an error message.
	 *
	 * @param      string An error name.
	 *
	 * @return     string The error message.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.9.0
	 */
	public function getError($name)
	{
		$errors = $this->getAttribute('errors', 'org.agavi.validation.result', array());
		$retval = null;

		if(isset($errors[$name]['messages'][0])) {
			$retval = $errors[$name]['messages'][0];
		}

		return $retval;
	}

	/**
	 * Retrieve an array of error names.
	 *
	 * @return     array An indexed array of error names.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.9.0
	 */
	public function getErrorNames()
	{
		$errors = $this->getAttribute('errors', 'org.agavi.validation.result', array());
		if(isset($errors[''])) {
			unset($errors['']);
		}
		return array_keys($errors);
	}

	/**
	 * Retrieve an array of errors.
	 *
	 * @param      string An optional error name.
	 *
	 * @return     array An associative array of errors(if no name was given) as
	 *                   an array with the error messages (key 'messages') and
	 *                   the validators (key 'validators') which failed.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.9.0
	 */
	public function getErrors($name = null)
	{
		$errors = $this->getAttribute('errors', 'org.agavi.validation.result', array());
		if($name === null) {
			return $errors;
		} else {
			return isset($errors[$name]) ? $errors[$name] : null;
		}
	}

	/**
	 * Retrieve an array of error Messages.
	 *
	 * @param      string An optional error name.
	 *
	 * @return     array An indexed array of error messages (if a name was given)
	 *                   or an indexed array in this format:
	 *                   array('message' => string, 'errors' => array(string))
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getErrorMessages($name = null)
	{
		$errors = $this->getAttribute('errors', 'org.agavi.validation.result', array());

		if($name !== null) {
			return isset($errors[$name]['messages']) ? $errors[$name]['messages'] : null;
		} else {
			$msgs = array();

			foreach($errors as $errorName => $error) {
				foreach($error['messages'] as $message) {
					if(!isset($msgs[$message])) {
						$msgs[$message] = array();
					}
					$msgs[$message][] = $errorName;
				}
			}

			$retMsgs = array();
			$i = 0;
			foreach($msgs as $message => $errorNames) {
				$retMsgs[$i] = array('message' => $message, 'errors' => $errorNames);
				++$i;
			}
			return $retMsgs;
		}
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
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.9.0
	 */
	public function hasError($name)
	{
		$errors = $this->getAttribute('errors', 'org.agavi.validation.result', array());
		return isset($errors[$name]);
	}


	/**
	 * Indicates whether or not any errors exist.
	 *
	 * @return     bool true, if any error exist, otherwise false.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.9.0
	 */
	public function hasErrors()
	{
		return (count($this->getAttribute('errors', 'org.agavi.validation.result', array())) > 0);
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

		if(AgaviConfig::has('core.default_locale')) {
			$this->setLocale(AgaviConfig::get('core.default_locale'));
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
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.9.0
	 */
	public function removeError($name)
	{
		$errors =& $this->getAttribute('errors', 'org.agavi.validation.result', array());
		$retval = null;

		if(isset($errors[$name])) {
			$retval = $errors[$name];
			unset($errors[$name]);
		}

		return $retval;
	}

	/**
	 * Set an error.
	 *
	 * @param      string An error name.
	 * @param      string An error message.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.9.0
	 */
	public function setError($name, $message)
	{
		// set the attribute first if it doesn't exist, else we will not a proper 
		// reference to the attribute.
		if(!$this->hasAttribute('errors', 'org.agavi.validation.result')) {
			$this->setAttribute('errors', array(), 'org.agavi.validation.result');
		}
		$errors =& $this->getAttribute('errors', 'org.agavi.validation.result');
		if(!isset($errors[$name])) {
			$errors[$name] = array('messages' => array(), 'validators' => array());
		}
		$errors[$name]['messages'][] = $message;
	}


	/**
	 * Set an array of errors
	 *
	 * If an existing error name matches any of the keys in the supplied
	 * array, the associated message will be appended to the messages array.
	 *
	 * @param      array An associative array of errors and their associated
	 *                   messages.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.9.0
	 */
	public function setErrors($errors)
	{
		// set the attribute first if it doesn't exist, else we will not a proper 
		// reference to the attribute.
		if(!$this->hasAttribute('errors', 'org.agavi.validation.result')) {
			$this->setAttribute('errors', array(), 'org.agavi.validation.result');
		}
		$storedErrors =& $this->getAttribute('errors', 'org.agavi.validation.result', array());
		foreach($errors as $name => $error) {
			if(!isset($storedErrors[$name])) {
				$storedErrors[$name] = array('messages' => array(), 'validators' => array());
			}
			if(!is_array($error)) {
				$storedErrors[$name]['messages'][] = $error;
			} else {
				$storedErrors[$name]['messages'] = array_merge($storedErrors[$name]['messages'], $error);
			}
		}
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
	final public function & getParameter($name, $default = null)
	{
		if($this->locked) {
			throw new AgaviException('For security reasons, Request Parameters cannot be accessed directly. Please use the ParameterHolder object passed to your Action or View execute method to access Request Parameters.');
		}
		$retval =& parent::getParameter($name, $default);
		return $retval;
	}

	/**
	 * @see        AgaviParameterHolder::hasParameter()
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.9.0
	 */
	final public function hasParameter($name)
	{
		if($this->locked) {
			throw new AgaviException('For security reasons, Request Parameters cannot be accessed directly. Please use the ParameterHolder object passed to your Action or View execute method to access Request Parameters.');
		}
		return parent::hasParameter($name);
	}


	/**
	 * @see        AgaviParameterHolder::clearParameters()
	 */
	final public function clearParameters()
	{
		if($this->locked) {
			throw new AgaviException('For security reasons, Request Parameters cannot be accessed directly. Please use the ParameterHolder object passed to your Action or View execute method to access Request Parameters.');
		}
		parent::clearParameters();
	}

	/**
	 * @see        AgaviParameterHolder::getParameterNames()
	 */
	final public function getParameterNames()
	{
		if($this->locked) {
			throw new AgaviException('For security reasons, Request Parameters cannot be accessed directly. Please use the ParameterHolder object passed to your Action or View execute method to access Request Parameters.');
		}
		return parent::getParameterNames();
	}

	/**
	 * @see        AgaviParameterHolder::getParameters()
	 */
	final public function getParameters()
	{
		if($this->locked) {
			throw new AgaviException('For security reasons, Request Parameters cannot be accessed directly. Please use the ParameterHolder object passed to your Action or View execute method to access Request Parameters.');
		}
		return parent::getParameters();
	}

	/**
	 * @see        AgaviParameterHolder::removeParameter()
	 */
	final public function & removeParameter($name)
	{
		if($this->locked) {
			throw new AgaviException('For security reasons, Request Parameters cannot be accessed directly. Please use the ParameterHolder object passed to your Action or View execute method to access Request Parameters.');
		}
		$retval =& parent::removeParameter($name);
		return $retval;
	}

	/**
	 * @see        AgaviParameterHolder::setParameter()
	 */
	final public function setParameter($name, $value)
	{
		if($this->locked) {
			throw new AgaviException('For security reasons, Request Parameters cannot be accessed directly. Please use the ParameterHolder object passed to your Action or View execute method to access Request Parameters.');
		}
		parent::setParameter($name, $value);
	}

	/**
	 * @see        AgaviParameterHolder::appendParameter()
	 */
	final public function appendParameter($name, $value)
	{
		if($this->locked) {
			throw new AgaviException('For security reasons, Request Parameters cannot be accessed directly. Please use the ParameterHolder object passed to your Action or View execute method to access Request Parameters.');
		}
		parent::appendParameter($name, $value);
	}

	/**
	 * @see        AgaviParameterHolder::setParameterByRef()
	 */
	final public function setParameterByRef($name, &$value)
	{
		if($this->locked) {
			throw new AgaviException('For security reasons, Request Parameters cannot be accessed directly. Please use the ParameterHolder object passed to your Action or View execute method to access Request Parameters.');
		}
		parent::setParameterByRef($name, $value);
	}

	/**
	 * @see        AgaviParameterHolder::appendParameterByRef()
	 */
	final public function appendParameterByRef($name, &$value)
	{
		if($this->locked) {
			throw new AgaviException('For security reasons, Request Parameters cannot be accessed directly. Please use the ParameterHolder object passed to your Action or View execute method to access Request Parameters.');
		}
		parent::appendParameterByRef($name, $value);
	}

	/**
	 * @see        AgaviParameterHolder::setParameters()
	 */
	final public function setParameters($parameters)
	{
		if($this->locked) {
			throw new AgaviException('For security reasons, Request Parameters cannot be accessed directly. Please use the ParameterHolder object passed to your Action or View execute method to access Request Parameters.');
		}
		parent::setParameters($parameters);
	}

	/**
	 * @see        AgaviParameterHolder::setParametersByRef()
	 */
	final public function setParametersByRef(&$parameters)
	{
		if($this->locked) {
			throw new AgaviException('For security reasons, Request Parameters cannot be accessed directly. Please use the ParameterHolder object passed to your Action or View execute method to access Request Parameters.');
		}
		parent::setParametersByRef($parameters);
	}

	/**
	 * Lock or unlock the Request so parameters can(not) be get or set anymore.
	 *
	 * @param      string The key to unlock, if the lock should be removed, or
	 *                    null if the lock should be set.
	 *
	 * @return     mixed The key, if a lock was set, or a boolean value indicating
	 *                   whether or not the unlocking was successful.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	final public function toggleLock($key = null)
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

	/**
	 * Sets the locale of the request.
	 *
	 * @param      string The new locale.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function setLocale($locale)
	{
		$this->locale = $locale;
		if(($tm = $this->getContext()->getTranslationManager())) {
			$tm->localeChanged($locale);
		}
	}

	/**
	 * Get the locale of the request.
	 *
	 * @return     string The locale.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getLocale()
	{
		$this->locale;
	}

}

?>
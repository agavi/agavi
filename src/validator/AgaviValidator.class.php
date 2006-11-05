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
 * AgaviValidator allows you to validate input
 *
 * Parameters for use in most validators:
 *   'name'       name of validator
 *   'base'       base path for validation of arrays
 *   'arguments'  an array of input parameter keys to validate
 *   'export'     destination for exportet data
 *   'depends'    list of dependencies needed by the validator
 *   'provides'   list of dependencies the validator provides after success
 *   'severity'   error severity in case of failure
 *   'error'      error message when validation fails
 *   'errors'     an array of errors with the reason as key
 *   'affects'    list of fields that are affected by an error
 *   'required'   if true the validator will fail when the input parameter is 
 *                not set
 *
 * @package    agavi
 * @subpackage validator
 *
 * @author     Uwe Mesecke <uwe@mesecke.net>
 * @copyright  (c) Authors
 * @since      0.11.0
 *
 * @version    $Id$
 */
abstract class AgaviValidator extends AgaviParameterHolder
{
	/**
	 * validator error severity (the validator succeeded)
	 */
	const SUCCESS = 0;

	/**
	 * validator error severity (validator failed but without impact on result
	 * of whole validation process)
	 */
	const NONE = 1;

	/**
	 * validation error severity (validator failed but validation process
	 * continues)
	 */
	const ERROR = 2;

	/**
	 * validation error severty (validator failed and validation process will
	 * be aborted)
	 */
	const CRITICAL = 3;

	/**
	 * @var        AgaviContext An AgaviContext instance.
	 */
	protected $context = null;

	/**
	 * @var        AgaviIValidatorContainer parent validator container (in
	 *                                      most cases the validator manager)
	 */
	protected $parentContainer = null;

	/**
	 * @var        AgaviVirtualArrayPath The current base for input names, 
	 *                                   dependencies etc.
	 */
	protected $curBase = null;


	/**
	 * @var        string The name of this validator instance. This will either
	 *                    be the user supplied name (if any) or a random string
	 */
	protected $name = null;

	/**
	 * @var        AgaviParameterHolder The parameters which should be validated
	 *                                  in the current validation run.
	 */
	protected $validationParameters = null;

	/**
	 * @var        array The request methods where this validator should validate
	 */
	protected $requestMethods = array();

	/**
	 * @var        array The field names which have been validated by this
	 *                   validator
	 */
	protected $validatedFieldnames = array();

	/**
	 * Returns the base path of this validator.
	 *
	 * @return     AgaviVirtualArrayPath The basepath of this validator
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getBase()
	{
		return $this->curBase;
	}

	/**
	 * Returns the "keys" in the path of the base
	 *
	 * @return     array The keys from left to right
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getBaseKeys()
	{
		$keys = array();
		$l = $this->curBase->length();
		for($i = 1; $i < $l; ++$i) {
			$keys[] = $this->curBase->get($i);
		}

		return $keys;
	}

	/**
	 * Returns the last "keys" in the path of the base
	 *
	 * @return     mixed The key
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getLastKey()
	{
		$base = $this->curBase;
		if($base->length() == 0 || ($base->length() == 1 && $base->isAbsolute()))
			return null;

		return $base->get($base->length() - 1);
	}

	/**
	 * Returns the name of this validator.
	 *
	 * @return     string The name
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getName()
	{
		return $this->name;
	}


	/**
	 * Checks whether this validator validates in the given request method.
	 *
	 * @param      string The request method.
	 *
	 * @return     bool Whether the validator validates.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function validatesInMethod($requestMethod)
	{
		if(count($this->requestMethods) > 0) {
			return in_array($requestMethod, $this->requestMethods);
		} else {
			return true;
		}
	}

	/**
	 * constructor
	 *
	 * @param      AgaviIValidatorContainer parent validator container
	 *                                      (mostly the validator manager)
	 * @param      array                    The parameters from the config file.
	 * @param      string                   The name of this validator.
	 *
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	public function __construct(AgaviIValidatorContainer $parent, array $parameters = array(), $name = '')
	{
		$this->parentContainer = $parent;

		$from = $this;
		while(!($from instanceof AgaviIValidatorManager)) {
			$from = $from->getParentContainer();
		}
		$this->context = $from->getContext();
		unset($from);

		if(!isset($parameters['depends']) or !is_array($parameters['depends'])) {
			$parameters['depends'] = (isset($parameters['depends']) and strlen($parameters['depends'])) ? explode(' ', $parameters['depends']) : array();
		}
		if(!isset($parameters['provides']) or !is_array($parameters['provides'])) {
			$parameters['provides'] = (isset($parameters['provides']) and strlen($parameters['provides'])) ? explode(' ', $parameters['provides']) : array();
		}

		if(isset($parameters['method'])) {
			foreach(explode(' ', $parameters['method']) as $method) {
				$this->requestMethods[] = trim($method);
			}
		}

		parent::__construct($parameters);
		// we need a reference here, so when looping happens in a parent
		// we always have the right base
		$this->curBase = $parent->getBase();
		$this->name = $name;
	}

	/**
	 * Retrieve the current application context.
	 *
	 * @return     AgaviContext The current Context instance.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	final public function getContext()
	{
		return $this->context;
	}

	/**
	 * Retrieve the parent container.
	 *
	 * @return     AgaviIValidatorContainer The parent container.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	final public function getParentContainer()
	{
		return $this->parentContainer;
	}

	/**
	 * Validates the input.
	 *
	 * This is the method where all the validation stuff is going to happen.
	 * Inherited classes have to implement their validation logic here. It
	 * returns only true or false as validation results. The handling of
	 * error severities is done by the validator itself and should not concern
	 * the writer of a new validator.
	 *
	 * @return     bool The result of the validation.
	 *
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	protected abstract function validate();

	/**
	 * Shuts the validator down.
	 *
	 * This method can be used in validators to shut down used models or
	 * other activities before the validator is killed.
	 *
	 * @see        AgaviValidatorManager::shutdown()
	 *
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	public function shutdown()
	{
	}

	/**
	 * Returns the specified input value.
	 *
	 * The given parameter is fetched from the request. You should _always_
	 * use this method to fetch data from the request because it pays attention
	 * to specified paths.
	 *
	 * @param      string The name of the parameter to fetch from request.
	 *
	 * @return     mixed The input value from the validation input.
	 *
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	protected function getData($paramName)
	{
		$array = $this->validationParameters->getParameters();
		return $this->curBase->getValueByChildPath($paramName, $array);
	}

	/**
	 * Returns true if this validator has multiple arguments which need to be 
	 * validated.
	 *
	 * @return     bool Whether this validator has multiple arguments or not.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	protected function hasMultipleArguments()
	{
		return count($this->getParameter('arguments')) > 1;
	}

	/**
	 * Returns the first argument which should be validated.
	 *
	 * This method is to be used by validators which only expect 1 input
	 * argument.
	 *
	 * @return     string The input argument name.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	protected function getArgument()
	{
		$argNames = $this->getParameter('arguments', array());
		reset($argNames);
		return current($argNames);
	}

	/**
	 * Returns all arguments which should be validated.
	 *
	 * @return     array A list of input arguments names.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	protected function getArguments()
	{
		return $this->getParameter('arguments', array());
	}

	/**
	 * Returns whether all arguments are set in the validation input parameters.
	 * Set means anything but empty string.
	 *
	 * @return     bool Whether the arguments are set.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	protected function hasAllArgumentsSet()
	{
		$array = $this->validationParameters->getParameters();
		$baseParts = $this->curBase->getParts();
		foreach($this->getArguments() as $argument) {
			$new = $this->curBase->pushRetNew($argument);
			$pName = $this->curBase->pushRetNew($argument)->__toString();
			if(!$this->validationParameters->hasParameter($pName) || $this->validationParameters->getParameter($pName) === "") {
				return false;
			}
		}
		return true;
	}

	/**
	 * Submits an error to the error manager.
	 *
	 * The stuff in the parameter specified in $index is submitted to the
	 * error manager. If there is no parameter with this name, then 'error'
	 * is tryed as an parameter and if even this fails, the stuff in
	 * $backupError is sent.
	 *
	 * @param      string The name of the error parameter to fetch the message 
	 *                    from.
	 * @param      string An default error message to be used if the given error 
	 *                    has no message set.
	 *
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	protected function throwError($index = null, $backupError = null)
	{
		if($index !== null && $this->hasParameter('errors')) {
			$errors = $this->getParameter('errors');
			if(isset($errors[$index])) {
				$this->reportError($this, $errors[$index]);
				return;
			}
		}

		if($this->hasParameter('error')) {
			$error = $this->getParameter('error');
		} else {
			$error = $backupError;
		}

		$this->reportError($this, $error);
	}


	/**
	 * Reports an error to the parent container.
	 *
	 * @param      AgaviValidator The validator where the error occured.
	 * @param      string         An error message.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 * @see        AgaviIValidatorContainer::reportError
	 */
	public function reportError(AgaviValidator $validator, $errorMsg)
	{
		$this->parentContainer->reportError($validator, $errorMsg);
	}

	/**
	 * Returns a list of input fields that are per default affected by a failure
	 * of the validator
	 *
	 * The list consists of the fields in the parameters that are lists in
	 * affectedFieldNames and the space seperated list of fields in the
	 * parameter 'affects'.
	 *
	 * @return     array The list of fields that are affected by an error.
	 *
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	public function getAffectedFields()
	{
		$fields = array();
		$base = $this->curBase->__toString();

		if($this->hasParameter('affects')) {
			$f = array_map('trim', explode(' ', trim($this->getParameter('affects'))));
			foreach($f as $n) {
				if(!strlen($n)) {
					continue;
				}
				$fields[] = $n;
			}
		}

		$fields = array_merge($fields, $this->validatedFieldnames);
		// filter out empty strings
		$fields = array_filter($fields, 'strlen');

		return array_unique($fields);
	}

	/**
	 * Exports a value back into the request.
	 *
	 * Exports data into the request at the index given in the parameter
	 * 'export'. If there is no such parameter, then the method returns
	 * without exporting.
	 *
	 * Similar to getData() you should always use export() to submit data to
	 * the request because it pays attention to paths and otherwise you could
	 * overwrite stuff you don't want to.
	 *
	 * @param      mixed The value to be exported.
	 *
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	protected function export($value)
	{
		if(!$this->hasParameter('export')) {
			return;
		}

		$array = $this->validationParameters->getParameters();
		$this->curBase->setValueByChildPath($this->getParameter('export'), $array, $value);
		$this->validationParameters->setParameters($array);
	}

	/**
	 * Validates this validator in the given base.
	 *
	 * @param      AgaviVirtualArrayPath The base in which the input should be 
	 *                                   validated.
	 *
	 * @return     int AgaviValidator::SUCCESS if validation succeeded or given
	 *                 error severity.
	 *
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	protected function validateInBase(AgaviVirtualArrayPath $base)
	{
		$base = clone $base;
		if($base->length() == 0) {
			// we have an empty base so we do the actual validation
			if(count($this->getParameter('depends')) > 0 && !$this->parentContainer->getDependencyManager()->checkDependencies($this->getParameter('depends'), $this->curBase)) {
				// dependencies not met, exit with success
				return self::SUCCESS;
			}

			foreach($this->getArguments() as $argument) {
				$this->validatedFieldnames[] = $this->curBase->pushRetNew($argument)->__toString();
			}

			if($this->hasAllArgumentsSet()) {
				if(!$this->validate()) {
					// validation failed, exit with configured error code
					return self::mapErrorCode($this->getParameter('severity'));
				}
			} else {
				if($this->getParameter('required', true)) {
					$this->throwError();
					return self::mapErrorCode($this->getParameter('severity'));
				}
			}

			// put dependencies provided by this validator into manager
			if(count($this->getParameter('provides')) > 0) {
				$this->parentContainer->getDependencyManager()->addDependTokens($this->getParameter('provides'), $this->curBase);
			}
			return self::SUCCESS;

		} elseif($base->left() !== '') {
			/*
			 * the next component in the base is no wildcard so we
			 * just put it into our own base and validate further
			 * into the base.
			 */

			$this->curBase->push($base->shift());
			$ret = $this->validateInBase($base);
			$this->curBase->pop();

			return $ret;

		} else {
			/*
			 * now we have a wildcard as next component so we collect
			 * all defined value names in the request at the path
			 * specified by our own base and validate in each of that
			 * names
			 */
			$array = $this->validationParameters->getParameters();
			$names = $this->curBase->getValue($array, array());

			// throw the wildcard away
			$base->shift();

			$ret = self::SUCCESS;

			// validate in every name defined in the request
			foreach(array_keys($names) as $name) {
				$t = $this->validateInBase($base->pushRetNew($name));

				if($t == self::CRITICAL) {
					return $t;
				}

				// remember the highest error severity
				$ret = max($ret, $t);
			}

			return $ret;
		}
	}

	/**
	 * Executes the validator.
	 *
	 * @param      AgaviParameterHolder The parameters which should be validated.
	 *
	 * @return     int The validation result (see severity constants).
	 *
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	public function execute(AgaviParameterHolder $parameters)
	{
		$this->validationParameters = $parameters;
		$base = new AgaviVirtualArrayPath($this->getParameter('base'));

		return $this->validateInBase($base);
	}

	/**
	 * Converts string severity codes into integer values
	 * (see severity constants)
	 *
	 * critical -> AgaviValidator::CRITICAL
	 * error    -> AgaviValidator::ERROR
	 * none     -> AgaviValidator::NONE
	 * success  -> AgaviValidator::SUCCESS
	 *
	 * @param      string The error severity as string.
	 *
	 * @return     int The error severity as in (see severity constants).
	 *
	 * @throws     <b>AgaviValidatorException<b> if the input was no known 
	 *                                           severity
	 *
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	public static function mapErrorCode($code)
	{
		switch(strtolower($code)) {
			case 'critical':
				return self::CRITICAL;
			case 'error':
				return self::ERROR;
			case 'none':
				return self::NONE;
			case 'success':
				return self::SUCCESS;
			default:
				throw new AgaviValidatorException('unknown error code: '.$code);
		}
	}

}

?>

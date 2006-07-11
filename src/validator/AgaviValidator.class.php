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
 *   'name'     name of validator
 *   'base'     base path for validation of arrays
 *   'param'    name of input parameter to validate
 *   'export'   destination for exportet data
 *   'depends'  list of dependencies needed by the validator
 *   'provides' list of dependencies the validator provides after success
 *   'severity' error severity in case of failure
 *   'error'    error message when validation fails
 *   'affects'  list of fields that are affected by an error
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
	 * validation error severity (validator failed but validation process continues)
	 */
	const ERROR = 2;

	/**
	 * validation error severty (validator failed and validation process will
	 * be aborted)
	 */
	const CRITICAL = 3;

	/**
	 * @var        AgaviIValidatorContainer parent validator container (in
	 *                                      most cases the validator manager)
	 */
	protected $ParentContainer = null;

	/**
	 * @var        AgaviPath current base for input names, dependencies etc.
	 */
	protected $CurBase = null;
	
	/**
	 * @var        array list of parameter names with names of affected fields
	 *                   in case of failure of the validator (besides 'affects')
	 */
	protected $AffectedFieldNames = array();

	/**
	 * constructor
	 * 
	 * @param      AgaviIValidatorContainer parent validator container
	 *                                      (mostly the validator manager)
	 * @param      array                    parameters from the config file
	 *
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	public function __construct(AgaviIValidatorContainer $parent, $parameters = array())
	{
		$this->ParentContainer = $parent;
		if (!isset($parameters['depends']) or !is_array($parameters['depends'])) {
			$parameters['depends'] = (isset($parameters['depends']) and strlen($parameters['depends'])) ? split(',', $parameters['depends']) : array();
		}
		if (!isset($parameters['provides']) or !is_array($parameters['provides'])) {
			$parameters['provides'] = (isset($parameters['provides']) and strlen($parameters['provides'])) ? split(',', $parameters['provides']) : array();
		}
		$this->setParameters($parameters);
		$this->CurBase = new AgaviPath($parent->getBase());
		$this->AffectedFieldNames = array('param');
	}

	/**
	 * validates the input
	 * 
	 * This is the method where all the validation stuff is going to happen.
	 * Inherited classes have to implement their validation logic here. It
	 * returns only true or false as validation results. The handling of
	 * error severities is done by the validator itself and should not concern
	 * the writer of a new validator.
	 * 
	 * @return     bool result of the validation
	 *
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	protected abstract function validate();

	/**
	 * shuts down the validator
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
	 * returns the specified input value
	 * 
	 * The given parameter is fetched from the request. You should _allways_
	 * use this method to fetch data from the request because it pays attention
	 * to specified paths.
	 * 
	 * @param      string name of parameter to fetch from request
	 * 
	 * @return     mixed input value from request
	 *
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	protected function getData($paramname = 'param')
	{
		$array = $this->ParentContainer->getRequest()->getParameters();
		return AgaviPath::getValueByPath(
			$array,
			$this->CurBase->__toString().'/'.$this->getParameter($paramname)
		);
	}

	/**
	 * submits an error to the error manager
	 * 
	 * The stuff in the parameter specified in $index is submitted to the
	 * error manager. If there is no parameter with this name, then 'error'
	 * is tryed as an parameter and if even this fails, the stuff in
	 * $backupError is sent.
	 * 
	 * @param      string name of parameter the message is saved in
	 * @param      bool   do not use as error message even
	 *                    if error message is of type string
	 * @param      array  array of fields that are affected by the error
	 * @param      mixed  error value to be used if no other value was found
	 *
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	protected function throwError($index = 'error', $ignoreAsMessage = false, $affectedFields = null, $backupError = null)
	{
		if($this->hasParameter($index)) {
			$error = $this->getParameter($index);
		} elseif($this->hasParameter('error')) {
			$error = $this->getParameter('error');
		} else {
			$error = $backupError;
		}
		
		if($affectedFields == null) {
			$affectedFields = $this->getAffectedFields();
		} elseif(!is_array($affectedFields)) {
			$affectedFields = array($affectedFields);
		}
		
		$this->ParentContainer->getErrorManager()->submitError(
			$this->CurBase->__toString().'/'.$this->getParameter('name'),
			$error,
			$affectedFields,
			self::mapErrorCode($this->getParameter('severity')),
			$this->CurBase->__toString(),
			$ignoreAsMessage
		);
	}

	/**
	 * returns a list of input fields that are per default affected by a failure of the validator
	 * 
	 * The list consists of the fields in the parameters that are lists in
	 * AffectedFieldNames and the comma seperated list of fields in the
	 * parameter 'affects'.
	 * 
	 * @return     array list of fields that are affected by an error
	 *
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	protected function getAffectedFields() {
		$fields = array();
		$base = $this->CurBase->__toString();

		if($this->hasParameter('affects')) {
			$f = explode(',', $this->getParameter('affects'));
			foreach($f as $n) {
				if(!strlen($n)) {
					continue;
				}
				array_push($fields, $n);
			}
		}
		
		foreach($this->AffectedFieldNames as $name) {
			array_push($fields, $this->getParameter($name));
		}
		
		return array_unique($fields);
	}

	/**
	 * exports a value back into the request
	 * 
	 * Exports data into the request at the index given in the parameter
	 * 'export'. If there is no such parameter, then the method returns
	 * without exporting.
	 * 
	 * Similar to getData() you should always use export() to submit data to
	 * the request because it pays attention to paths and otherwise you could
	 * overwrite stuff you don't want to.
	 * 
	 * @param      mixed value to be exported
	 *
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	protected function export($value)
	{
		if(!$this->hasParameter('export')) {
			return;
		}
		
		$array = $this->ParentContainer->getRequest()->getParameters();
		AgaviPath::setValueByPath(
			$array,
			$this->CurBase->__toString().'/'.$this->getParameter('export'),
			$value
		);
		$this->ParentContainer->getRequest()->setParameters($array);
	}

	/**
	 * validates in the given base
	 * 
	 * @param      string base in with the input should be validated
	 * 
	 * @return     int self::SUCCESS if validation succeeded or given error severity 
	 *
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	protected function validateInBase($basedir)
	{
		$base = new AgaviPath($basedir);
		if($base->length() == 0) {
			// we have an empty base so we do the actual validation
			if(count($this->getParameter('depends')) > 0 and $this->ParentContainer->getDependencyManager()->checkDependencies($this->getParameter('depends'), $this->CurBase->__toString())) {
				// dependencies not met, exit with success
				return self::SUCCESS;
			}

			if(!$this->validate()) {
				// validation failed, exit with configured error code
				return self::mapErrorCode($this->getParameter('severity'));
			}

			// put dependencies provided by this validator into manager
			if(count($this->getParameter('provides')) > 0) {
				$this->ParentContainer->getDependencyManager()->addDependTokens($this->getParameter('provides'), $this->CurBase->__toString());
			}
			return self::SUCCESS;

		} elseif($base->left() != '*') {
			/*
			 * the next component in the base is no wildcard so we
			 * just put it into our own base and validate further
			 * into the base. 
			 */ 
			$this->CurBase->push($base->shift());
			$ret = $this->validateInBase($base->__toString());
			$this->CurBase->pop();
			
			return $ret;

		} else {
			/*
			 * now we have a wildcard as next component so we collect
			 * all defined value names in the request at the path
			 * specified by our own base and validate in each of that
			 * names
			 */
			$array = $this->ParentContainer->getRequest()->getParameters();
			$names = AgaviPath::getValueByPath(
				$array,
				$this->CurBase->__toString()
			);
			
			// throw the wildcard away
			$base->shift();
			
			$ret = self::SUCCESS;
			
			// validate in every name defined in the request
			foreach(array_keys($names) as $name) {
				$this->CurBase->push($name);
				$t = $this->validateInBase($base->__toString());
				$this->CurBase->pop();
				
				if($t == self::CRITICAL) {
					return $t;
				}
				
				// remember the highest error severity
				if($t > $ret) {
					$ret = $t;
				}
			}
			
			return $ret;
		}
	}

	/**
	 * executes the validator
	 * 
	 * @return     int validation result (see severity constants)
	 *
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	public function execute()
	{
		$base = $this->getParameter('base');
		
		return $this->validateInBase($base);
	}

	/**
	 * converts string severity codes into integer values (see severity constants)
	 * 
	 * critical -> AgaviValidator::CRITICAL
	 * error    -> AgaviValidator::ERROR
	 * none     -> AgaviValidator::NONE
	 * success  -> AgaviValidator::SUCCESS
	 * 
	 * @param      string error severity as string
	 * 
	 * @return     int error severity as in (see severity constants)
	 * 
	 * @throws     AgaviValidatorException throws exception if the input was no known severity
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
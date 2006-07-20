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
 * AgaviErrorManager handles the errors in the validation process
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
class AgaviErrorManager
{
	/**
	 * @var        array error messages sorted by validators
	 */
	protected $validatorArray = array();
	/**
	 * @var        array error messages sorted by input fields
	 */
	protected $inputArray = array();
	/**
	 * @var        string first submitted error message of type string
	 */
	protected $errorMessage = '';
	/**
	 * @var        int highest error severity in the container
	 */
	protected $result = AgaviValidator::SUCCESS;
	
	/**
	 * clears the error manager
	 * 
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	public function clear()
	{
		$this->validatorArray = array();
		$this->inputArray = array();
		$this->errorMessage = '';
		$this->result = AgaviValidator::SUCCESS;
	}
	
	/**
	 * submits an error from the validator
	 * 
	 * @param      string name of validator that failed
	 * @param      array  affected input fields
	 * @param      mixed  error stuff that should be saved
	 * @param      int    error severity
	 * @param      string base path for validator and field names
	 * @param      bool   ignore error as error message even if type is string
	 * 
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	public function submitError($validator, $error, $fields, $severity, AgaviVirtualArrayPath $base, $ignoreAsMessage = false)
	{
		if($severity > $this->result) {
			$this->result = $severity;
		}
		if(is_string($error) and $this->errorMessage == '' and !$ignoreAsMessage) {
			$this->errorMessage = $error;
		}
		
		if($validator[0] == '[' and $base->length()) {
			$validator = $base->appendRetNew($validator)->__toString();
		}

		if($base->length()) {
			foreach($fields as &$field) {
				$field = $base->appendRetNew($field)->__toString();
			}
		}
		
		// fill validator array
		$this->validatorArray[$validator] = array(
			'error'		=> $error,
			'fields'	=> $fields
		);
		
		// fill input array
		foreach($fields as $field) {
			if(!isset($this->inputArray[$field])) {
				$this->inputArray[$field] = array(
					'message'	=> '',
					'validators'	=> array()
				);
			}
			
			if(is_string($error) and $this->inputArray[$field]['message'] == '' and !$ignoreAsMessage) {
				$this->inputArray[$field]['message'] = $error;
			}
			
			$this->inputArray[$field]['validators'][$validator] = $error;
		}
	}
	
	/**
	 * returns the array of errors sorted by validator names
	 * 
	 * Format:
	 * 
	 * array(
	 *   <i>validatorName</i> => array(
	 *     'error'  => <i>error</i>,
	 *     'fields' => <i>array of field names</i>
	 *   )
	 * 
	 * @return     array array of errors
	 * 
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	public function getErrorArrayByValidator()
	{
		return $this->validatorArray;
	}
	
	/**
	 * returns the array of errors sorted by input names
	 * 
	 * Format:
	 * 
	 * array(
	 *   <i>fieldName</i> => array(
	 *     'message'    => <i>error message</i>,
	 *     'validators' => array(
	 *       <i>validatorName</i> => <i>error</i>
	 *     )
	 * )
	 * 
	 * <i>error message</i> is the first submitted error with type string.
	 * 
	 * @return     array array of errors
	 * 
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	public function getErrorArrayByInput()
	{
		return $this->inputArray;
	}
	
	/**
	 * returns error message
	 * 
	 * @return     string error message (first reportet error of type string)
	 * 
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	public function getErrorMessage()
	{
		return $this->errorMessage;
	}
	
	/**
	 * returns the result (highest error severity)
	 * 
	 * @return     int return highes severity of reportet errors
	 * 
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	public function getResult()
	{
		return $this->result;
	}
}
?>

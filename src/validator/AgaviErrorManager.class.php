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
	protected $ValidatorArray = array();
	/**
	 * @var        array error messages sorted by input fields
	 */
	protected $InputArray = array();
	/**
	 * @var        string first submitted error message of type string
	 */
	protected $ErrorMessage = '';
	/**
	 * @var        int highest error severity in the container
	 */
	protected $Result = AgaviValidator::SUCCESS;
	
	/**
	 * clears the error manager
	 * 
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	public function clear()
	{
		$this->ValidatorArray = array();
		$this->InputArray = array();
		$this->ErrorMessage = '';
		$this->Result = AgaviValidator::SUCCESS;
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
	public function submitError($validator, $error, $fields, $severity, $base = '', $ignoreAsMessage = false)
	{
		if($severity > $this->Result) {
			$this->Result = $severity;
		}
		if(is_string($error) and $this->ErrorMessage == '' and !$ignoreAsMessage) {
			$this->ErrorMessage = &$error;
		}
		
		if($validator[0] != '/' and $base != '') {
			$p = new AgaviPath($base.'/'.$validator);
			$validator = $p->__toString();
		}
		if($base != '') {
			$fields = array_map(create_function(
				'$a',
				'if ($a[0] == \'/\') { return $a; } else {$p = new AgaviPath(\''.$base.'/'.'\'.$a); return $p->__toString();}'
			), $fields);
		}
		
		// fill validator array
		$this->ValidatorArray[$validator] = array(
			'error'		=> &$error,
			'fields'	=> $fields
		);
		
		// fill input array
		foreach($fields AS $field) {
			if(!isset($this->InputArray[$field])) {
				$this->InputArray[$field] = array(
					'message'	=> '',
					'validators'	=> array()
				);
			}
			
			if(is_string($error) and $this->InputArray[$field]['message'] == '' and !$ignoreAsMessage) {
				$this->InputArray[$field]['message'] = &$error;
			}
			
			$this->InputArray[$field]['validators'][$validator] = &$error;
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
		return $this->ValidatorArray;
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
		return $this->InputArray;
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
		return $this->ErrorMessage;
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
		return $this->Result;
	}
}
?>

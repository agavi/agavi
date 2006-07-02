<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2003-2006 the Agavi Project.                                |
// | Based on the Mojavi3 MVC Framework, Copyright (c) 2003-2005 Sean Kerr.    |
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
	private $ValidatorArray = array();
	/**
	 * @var        array error messages sorted by input fields
	 */
	private $InputArray = array();
	/**
	 * @var        string first submitted error message of type string
	 */
	private $ErrorMessage = '';
	/**
	 * @var        int highest error severity in the container
	 */
	private $Result = AgaviValidator::SUCCESS;
	
	/**
	 * clears the error manager
	 */
	public function clear ()
	{
		$this->ErrorArray = array();
		$this->ErrorMessage = '';
		$this->Result = AgaviValidator::SUCCESS;
	}
	
	/**
	 * submits an error from the validator
	 * 
	 * @param      string $validator name of validator that failed
	 * @param      array  $fields affected input fields
	 * @param      mixed  $error error stuff that should be saved
	 * @param      int    $severity error severity
	 * @param      bool   $ignoreAsMessage ignore error as error message
	 *                                     even if type is string
	 */
	public function submitError ($validator, $error, $fields, $severity, $ignoreAsMessage = false)
	{
		if ($severity > $this->Result) {
			$this->Result = $severity;
		}
		if (is_string($error) and $this->ErrorMessage == '' and !$ignoreAsMessage) {
			$this->ErrorMessage = &$error;
		}
		
		// fill validator array
		$this->ValidatorArray[$validator] = array(
			'error'		=> &$error,
			'fields'	=> $fields
		);
		
		// fill input array
		foreach ($fields AS $field) {
			if (!isset($this->InputArray[$field])) {
				$this->InputArray[$field] = array(
					'message'	=> '',
					'validators'	=> array()
				);
			}
			
			if (is_string($error) and $this->InputArray[$field]['message'] and !$ignoreAsMessage) {
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
	 */
	public function getErrorArrayByValidator ()
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
	 */
	public function getErrorArrayByInput ()
	{
		return $this->InputArray;
	}
	
	/**
	 * returns error message
	 * 
	 * @return     string error message (first reportet error of type string)
	 */
	public function getErrorMessage ()
	{
		return $this->ErrorMessage;
	}
	
	/**
	 * returns the result (highest error severity)
	 * 
	 * @return     int return highes severity of reportet errors
	 */
	public function getResult ()
	{
		return $this->Result;
	}
}
?>

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
 * AgaviBaseFileValidator is the base validator when validating files. 
 * It provides checking of the size and extension of a file for implementing 
 * validators.
 * 
 * Parameters:
 *   'min_size'     The minimum file size in byte
 *   'max_size'     The maximum file size in byte
 *   'extension'    list of valid extensions (delimited by ' ')
 *
 * Errors:
 *   'upload_failed' The upload of the file failed
 *   'min_size'      
 *   'max_size'      
 *   'extension'     The file doesn't have the required extension
 *
 * @package    agavi
 * @subpackage validator
 *
 * @author     Dominik del Bondio <ddb@bitxtender.com>
 * @copyright  (c) Authors
 * @since      0.11.0
 *
 * @version    $Id$
 */
abstract class AgaviBaseFileValidator extends AgaviValidator
{

	/**
	 * Returns whether all arguments are set in the validation input parameters.
	 * Set means anything but empty string.
	 *
	 * @param      bool Whether an error should be thrown for each missing 
	 *                  argument if this validator is required.
	 *
	 * @return     bool Whether the arguments are set.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	protected function checkAllArgumentsSet($throwError = true)
	{
		$request = $this->getContext()->getRequest();

		$isRequired = $this->getParameter('required', true);
		$result = true;

		$array = $this->validationParameters->getParameters();
		$baseParts = $this->curBase->getParts();
		foreach($this->getArguments() as $argument) {
			$new = $this->curBase->pushRetNew($argument);
			$pName = $this->curBase->pushRetNew($argument)->__toString();
			if(!$request->hasFile($pName)) {
				if($throwError && $isRequired) {
					$this->throwError(null, $pName);
				}
				$result = false;
			}
		}
		return $result;
	}

	/**
	 * Validates the input
	 * 
	 * @return     bool The file is valid according to given parameters.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	protected function validate()
	{
		$request = $this->getContext()->getRequest();

		foreach($this->getArguments() as $argument) {
			if($argument) {
				$name = $this->curBase->pushRetNew($argument)->__toString();
			} else {
				$name = $this->curBase->__toString();
			}

			if($request->getFileError($name) != UPLOAD_ERR_OK) {
				$this->throwError('upload_failed');
				return false;
			}
			
			$size = $request->getFileSize($name);
			if($this->hasParameter('min_size') && $size < $this->getParameter('min_size')) {
				$this->throwError('min_size');
				return false;
			}
			if($this->hasParameter('max_size') && $size > $this->getParameter('max_size')) {
				$this->throwError('max_size');
				return false;
			}

			if($this->hasParameter('extension')) {
				$fileinfo = pathinfo($request->getFileName($name));
				$ext = isset($fileinfo['extension']) ? $fileinfo['extension'] : '';

				if(in_array($ext, explode(' ', $this->getParameter('extension')))) {
					continue;
				}

				$this->throwError('extension');
				return false;
			}

		}

		return true;
	}

	/**
	 * Returns all available keys in the currently set base.
	 *
	 * @return     array The available keys.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com
	 * @since      0.11.0
	 */
	protected function getKeysInCurrentBase()
	{
		$files = $this->getContext()->getRequest()->getFiles(false);

		$names = $this->curBase->getValue($files, array());
		return array_keys($names);
	}

}

?>
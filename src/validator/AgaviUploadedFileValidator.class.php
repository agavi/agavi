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
 * AgaviIsUploadedImageValidator verifies the size and extension of a file
 * 
 * Parameters:
 *   'min_size'     The minimum file size in byte
 *   'max_size'     The maximum file size in byte
 *   'extension'    list of valid extensions (delimited by ',')
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
class AgaviUploadedFileValidator extends AgaviValidator
{
	/**
	 * Validates the input
	 * 
	 * @return     bool The file is valid according to given parameters
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	protected function validate()
	{
		$name = $this->getArgument();

		$request = $this->parentContainer->getContext()->getRequest();

		if($request->getFileError($name) != UPLOAD_ERR_OK) {
			$this->throwError();
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

		if(!$this->hasParameter('extension')) {
			return true;
		}

		$fileinfo = pathinfo($request->getFileName($name));
		$ext = isset($fileinfo['extension']) ? $fileinfo['extension'] : '';

		if(in_array($ext, explode(',', $this->getParameter('extension')))) {
			return true;
		}

		$this->throwError('extension');
		return false;
	}
}

?>
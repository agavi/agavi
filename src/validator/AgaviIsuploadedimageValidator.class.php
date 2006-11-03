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
 * AgaviIsUploadedImageValidator verifies a parameter is an uploaded image
 * 
 * Parameters:
 *   'php_error'    error message when there was an php error with the file
 *   'img_error'    error message when the file is no image according to exif_imagetype()
 *   'format'       list of valid formats (gif,jpeg,png,bmp)
 *   'format_error' image has none of the specified formats
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
class AgaviIsuploadedimageValidator extends AgaviValidator
{
	/**
	 * validates the input
	 * 
	 * @return     bool file is valid image according to given parameters
	 * 
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	protected function validate()
	{
		$name = $this->getData();

		$request = $this->parentContainer->getContext()->getRequest();

		if($request->getFileError($name) != UPLOAD_ERR_OK) {
			$this->throwError('php_error');
			return false;
		}
		
		$type = exif_imagetype($request->getFileName($name));
		if($type === false) {
			$this->throwError('img_error');
			return false;
		}
		
		if(!$this->hasParameter('format')) {
			return true;
		}
		
		$formats = array(
			'gif'	=> 1,
			'jpeg'	=> 2,
			'jpg'	=> 2,
			'png'	=> 3,
			'bmp'	=> 6
		);
		
		
		foreach(explode(' ', $this->getParameter('format')) as $format) {
			if($formats[strtolower($format)] == $type) {
				return true;
			}
		}
		
		$this->throwError('format_error');
		return false;
	}
}

?>
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
 * AgaviImageFileValidator verifies a parameter is an uploaded image
 * 
 * Parameters:
 *   'min_width'    The minimum width of the image
 *   'max_width'    The maximum width of the image
 *   'min_height'   The minimum height of the image
 *   'max_height'   The maximum height of the image
 *   'format'       list of valid formats (gif,jpeg,png,bmp,psd,swf)
 *
 * Errors:
 *   'no_image'      The uploaded file is no image
 *   'min_width'
 *   'max_width'
 *   'min_height'
 *   'max_height'
 *   'format'        The image was not in the required format
 *
 * @see        AgaviBaseFileValidator
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
class AgaviImageFileValidator  extends AgaviBaseFileValidator
{
	/**
	 * Validates the input.
	 * 
	 * @return     bool File is valid image according to given parameters.
	 * 
	 * @author     Uwe Mesecke <uwe@mesecke.net>
	 * @since      0.11.0
	 */
	protected function validate()
	{
		if(!parent::validate()) {
			return false;
		}

		$name = $this->getArgument();
		if($name) {
			$name = $this->curBase->pushRetNew($name)->__toString();
		} else {
			$name = $this->curBase->__toString();
		}

		$request = $this->getContext()->getRequest();

		$type = @getimagesize($request->getFilePath($name));
		if($type === false) {
			$this->throwError('no_image');
			return false;
		}

		list($width, $height, $imageType) = $type;

		if($this->hasParameter('max_width') && $width > $this->getParameter('max_width')) {
			$this->throwError('max_width');
			return false;
		}
		if($this->hasParameter('min_width') && $width < $this->getParameter('min_width')) {
			$this->throwError('min_width');
			return false;
		}

		if($this->hasParameter('max_height') && $height > $this->getParameter('max_height')) {
			$this->throwError('max_height');
			return false;
		}
		if($this->hasParameter('min_height') && $height < $this->getParameter('min_height')) {
			$this->throwError('min_height');
			return false;
		}

		if(!$this->hasParameter('format')) {
			return true;
		}
		
		$formats = array(
			'gif'	=> IMAGETYPE_GIF,
			'jpeg'	=> IMAGETYPE_JPEG,
			'jpg'	=> IMAGETYPE_JPEG,
			'png'	=> IMAGETYPE_PNG,
			'bmp'	=> IMAGETYPE_BMP,
			'psd' => IMAGETYPE_PSD,
			'swf' => IMAGETYPE_SWF,
		);
		
		
		foreach(explode(' ', $this->getParameter('format')) as $format) {
			if($formats[strtolower($format)] == $imageType) {
				return true;
			}
		}
		
		$this->throwError('format');
		return false;
	}
}

?>
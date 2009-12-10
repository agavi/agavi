<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2005-2009 the Agavi Project.                                |
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
 * AgaviBooleanValidator verifies a parameter is a valid boolean
 * 
 * Accepted values are string 0/1, int 0/1, bool true/false, string yes/no,
 * string true/false, string on/off - basically all values that 
 * {@see AgaviToolkit::literalize()} will accept.
 *
 * @package    agavi
 * @subpackage validator
 *
 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      1.1.0
 *
 * @version    $Id$
 */
class AgaviBooleanValidator extends AgaviValidator
{
	/**
	 * Validates the input.
	 * 
	 * @return     bool The value is a valid boolean
	 * 
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 * @since      1.1.0
	 */
	protected function validate()
	{
		$value = $this->getData($this->getArgument());
		
		if(is_bool($value)) {
			// noop
		} elseif(1 === $value || '1' === $value) {
			$value = true;
		} elseif (0 === $value || '0' === $value) {
			$value = false;
		} elseif(is_string($value)) {
			$value = AgaviToolkit::literalize($value);
		}
		
		if(is_bool($value)) {
			$this->export($value);
			return true;
		}
		
		$this->throwError('type');
		
		return false;
	}
}

?>
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
 * AgaviArraylengthValidator verifies the length (count()) constraints for an array
 *
 * Parameters:
 *   'min'       The array should contain at least 'min' elements
 *   'max'       The array should contain at most 'max' elements
 *
 * @package    agavi
 * @subpackage validator
 *
 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.11.6
 *
 * @version    $id$
 */
class AgaviArraylengthValidator extends AgaviValidator
{
	/**
	 * Validates the input.
	 * 
	 * @return     bool
	 * 
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @since      0.11.6
	 */
	protected function validate()
	{
		$data = $this->getData($this->getArgument());
		if(!is_array($data)) {
			// we can only count() arrays
			$this->throwError();
			return false;
		}
		
		$count = count($data);
		
		if($this->hasParameter('min') && $count < $this->getParameter('min')) {
			$this->throwError('min');
			return false;
		}
		
		if($this->hasParameter('max') && $count > $this->getParameter('max')) {
			$this->throwError('max');
			return false;
		}
		
		return true;
	}
}

?>
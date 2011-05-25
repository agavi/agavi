<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2005-2011 the Agavi Project.                                |
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
 * AgaviTestingRouting allows access to some internal routing properties and
 * extends the abtract base class to make it testable.
 *
 * @package    agavi
 * @subpackage routing
 *
 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      1.0.0
 *
 * @version    $Id$
 */
class AgaviTestingRouting extends AgaviRouting
{
	public function setInput($input)
	{
		$this->input = $input;
	}
	
	public function setRoutingSource($name, $data, $type = null)
	{
		if(null === $type) {
			$type = 'AgaviRoutingArraySource';
		}
		$this->sources[$name] = new $type($data);
	}
	
	public function parseRouteString($str)
	{
		return parent::parseRouteString($str);
	}
}

?>
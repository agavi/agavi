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
 * AgaviRoutingArraySource allows you to provide array sources for the routing
 *
 * @package    agavi
 * @subpackage routing
 *
 * @author     Dominik del Bondio <ddb@bitxtender.com>
 * @copyright  (c) Authors
 * @since      0.11.0
 *
 * @version    $Id$
 */
class AgaviRoutingArraySource implements AgaviIRoutingSource
{
	/**
	 * @var        array An array with data.
	 */
	protected $data = array();

	/**
	 * Constructor.
	 *
	 * @param      array An array with data.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function __construct($data)
	{
		$this->data = $data;
	}

	/**
	 * Retrieves the source for a given entry.
	 *
	 * @param      array        An array with the parts of the entry.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getSource($parts)
	{
		return AgaviArrayPathDefinition::getValue($parts, $this->data);
	}
}

?>
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
 * Implements a basic Agavi build system event.
 *
 * @package    agavi
 * @subpackage build
 *
 * @author     Noah Fontes <noah.fontes@bitextender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      1.0.0
 *
 * @version    $Id$
 */
class AgaviEvent implements AgaviIEvent
{
	/**
	 * @var        object The source for this event.
	 */
	protected $source;
	
	/**
	 * Retrieves the source object that generated this event.
	 *
	 * @return     object This event's source.
	 *
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 * @since      1.0.0
	 */
	public function getSource()
	{
		return $this->source;
	}
	
	/**
	 * Sets the source object for this event.
	 *
	 * @param      object This event's source.
	 *
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 * @since      1.0.0
	 */
	public function setSource($source)
	{
		if($this->source !== null) {
			throw new AgaviEventBuildException('An event source may not be set multiple times');
		}
		$this->source = $source;
	}
}

?>
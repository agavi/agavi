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
 * Extends the Phing XML context to add support for notifying a project when a
 * configuration event occurs.
 *
 * @package    agavi
 * @subpackage build
 *
 * @author     Noah Fontes <noah.fontes@bitextender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      1.0.4
 *
 * @version    $Id$
 */
class AgaviProxyXmlContext extends PhingXMLContext
{
	/**
	 * Adds a new configurator to the parsing stack.
	 *
	 * @param      ProjectConfigurator The configurator.
	 *
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 * @since      1.0.4
	 */
	public function startConfigure($configurator)
	{
		parent::startConfigure($configurator);
		$this->getProject()->fireConfigureStarted($configurator);
	}

	/**
	 * Removes the current configurator from the parsing stack.
	 *
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 * @since      1.0.4
	 */
	public function endConfigure()
	{
		$this->getProject()->fireConfigureFinished();
		parent::endConfigure();
	}
}

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

require_once(dirname(__FILE__) . '/AgaviBuildLogger.php');

/**
 * Logs events through Agavi's default Phing logger, but ignores all proxy
 * names.
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
class AgaviProxyBuildLogger extends AgaviBuildLogger
{
	/**
	 * Logs the start of a target.
	 *
	 * The event is only logged if the target is not an instance of
	 * <code>AgaviProxyTarget</code>.
	 *
	 * @param      BuildEvent An event containing the data to be logged.
	 *
	 * @see        AgaviProxyTarget
	 * @see        DefaultLogger::targetStarted()
	 *
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 * @since      1.0.0
	 */
	public function targetStarted(BuildEvent $event)
	{
		if(!$event->getTarget() instanceof AgaviProxyTarget) {
			parent::targetStarted($event);
		}
	}
	
	/**
	 * Logs the end of a target.
	 *
	 * The event is only logged if the target is not an instance of
	 * <code>AgaviProxyTarget</code>.
	 *
	 * @param      BuildEvent An event containing the data to be logged.
	 *
	 * @see        AgaviProxyTarget
	 * @see        DefaultLogger::targetStarted()
	 *
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 * @since      1.0.0
	 */
	public function targetFinished(BuildEvent $event)
	{
		if(!$event->getTarget() instanceof AgaviProxyTarget) {
			parent::targetFinished($event);
		}
	}
}

?>
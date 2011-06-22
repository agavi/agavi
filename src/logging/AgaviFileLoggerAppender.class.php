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
 * AgaviFileLoggerAppender appends AgaviLoggerMessages to a given file.
 *
 * @package    agavi
 * @subpackage logging
 *
 * @author     David Zülke <dz@bitxtender.com>
 * @author     Bob Zoller <bob@agavi.org>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.10.0
 *
 * @version    $Id$
 */
class AgaviFileLoggerAppender extends AgaviStreamLoggerAppender
{
	/**
	 * Initialize the object.
	 *
	 * @param      AgaviContext An AgaviContext instance.
	 * @param      array        An associative array of initialization parameters.
	 *
	 * @author     Bob Zoller <bob@agavi.org>
	 * @since      0.10.0
	 */
	public function initialize(AgaviContext $context, array $parameters = array())
	{
		// for < 0.11.2 BC
		if(isset($parameters['file'])) {
			$parameters['destination'] = $parameters['file'];
			unset($parameters['file']);
		}
		
		parent::initialize($context, $parameters);

	}

	/**
	 * Retrieve the file handle for this FileAppender.
	 *
	 * @throws     <b>AgaviLoggingException</b> if file cannot be opened for
	 *                                          appending.
	 *
	 * @return     resource The open file handle.
	 *
	 * @author     Bob Zoller <bob@agavi.org>
	 * @since      0.10.0
	 */
	protected function getHandle()
	{
		$destination = $this->getParameter('destination');
		if(is_null($this->handle) && (!is_writable(dirname($destination)) || (file_exists($destination) && !is_writable($destination)))) {
			throw new AgaviLoggingException('Cannot open file "' . $destination . '", please check permissions on file or directory.');
		}
		
		return parent::getHandle();
	}
}

?>
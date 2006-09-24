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
 * AgaviAppender allows you to specify a destination for log data and provide
 * a custom layout for it, through which all log messages will be formatted.
 *
 * @package    agavi
 * @subpackage logging
 *
 * @author     Bob Zoller <bob@agavi.org>
 * @copyright  (c) Authors
 * @since      0.10.0
 *
 * @version    $Id$
 */
abstract class AgaviAppender
{
	/**
	 * @var        AgaviContext An AgaviContext instance.
	 */
	protected $context = null;

	/**
	 * @var        AgaviLayout An AgaviLayout instance.
	 */
	protected $layout = null;

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
		$this->context = $context;
	}

	/**
	 * Retrieve the current application context.
	 *
	 * @return     AgaviContext An AgaviContext instance.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.10.0
	 */
	public function getContext()
	{
		return $this->context;
	}

	/**
	 * Retrieve the layout.
	 *
	 * @return     AgaviLayout A Layout instance, if it has been set, otherwise null.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function getLayout()
	{
		return $this->layout;
	}

	/**
	 * Set the layout.
	 *
	 * @param      AgaviLayout A Layout instance.
	 *
	 * @return     AgaviAppender
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function setLayout(AgaviLayout $layout)
	{
		$this->layout = $layout;
		return $this;
	}

	/**
	 * Execute the shutdown procedure.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	abstract function shutdown();

	/**
	 * Write log data to this appender.
	 *
	 * @param      string Log data to be written.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	abstract function write($message);
}

?>
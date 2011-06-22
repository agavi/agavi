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
 * AgaviLoggerLayout allows you to specify a message layout for log messages.
 *
 * @package    agavi
 * @subpackage logging
 *
 * @author     Sean Kerr <skerr@mojavi.org>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.9.0
 *
 * @version    $Id$
 */
abstract class AgaviLoggerLayout extends AgaviParameterHolder
{
	/**
	 * @var        AgaviContext An AgaviContext instance.
	 */
	protected $context = null;

	/**
	 * @var        string A message layout.
	 */
	protected $layout = null;

	/**
	 * Initialize the Layout.
	 *
	 * @param      AgaviContext An AgaviContext instance.
	 * @param      array        An associative array of initialization parameters.
	 *
	 * @author     Veikko Mäkinen <mail@veikkomakinen.com>
	 * @since      0.10.0
	 */
	public function initialize(AgaviContext $context, array $parameters = array())
	{
		$this->context = $context;
		$this->parameters = $parameters;
	}

	/**
	 * Retrieve the current application context.
	 *
	 * @return     AgaviContext An AgaviContext instance.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.10.0
	 */
	public final function getContext()
	{
		return $this->context;
	}

	/**
	 * Format a message.
	 *
	 * @param      AgaviLoggerMessage A Message instance.
	 *
	 * @return     string A formatted message.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	abstract function format(AgaviLoggerMessage $message);

	/**
	 * Retrieve the message layout.
	 *
	 * @return     string A message layout.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function getLayout()
	{
		return $this->layout;
	}

	/**
	 * Set the message layout.
	 *
	 * @param      string A message layout.
	 *
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function setLayout($layout)
	{
		$this->layout = $layout;
	}
}

?>
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
 * AgaviTestingConfigCache allows access to some internal config cache properties 
 *
 * @package    agavi
 * @subpackage config
 *
 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      1.0.0
 *
 * @version    $Id$
 */
class AgaviTestingConfigCache extends AgaviConfigCache
{
	public static function handlersDirty()
	{
		return self::$handlersDirty;
	}
	
	public static function getHandlerFiles()
	{
		return self::$handlerFiles;
	}
	
	public static function getHandlers()
	{
		return self::$handlers;
	}

	public static function resetHandlers()
	{
		self::$handlers = null;
	}

	public static function setupHandlers()
	{
		parent::setupHandlers();
	}

	public static function getHandlerInfo($name)
	{
		return parent::getHandlerInfo($name);
	}

	public static function callHandler($name, $config, $cache, $context, array $handlerInfo = null)
	{
		parent::callHandler($name, $config, $cache, $context, $handlerInfo);
	}
}


?>
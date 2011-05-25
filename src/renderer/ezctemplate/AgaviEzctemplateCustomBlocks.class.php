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
 * A renderer produces the output as defined by a View
 *
 * @package    agavi
 * @subpackage renderer
 *
 * @author     Felix Weis <mail@felixweis.com>
 * @author     David Zülke <dz@bitxtender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.11.0
 *
 * @version    $Id$
 */
class AgaviEzctemplateCustomBlocks implements ezcTemplateCustomBlock
{
	/**
	 * Get the definition for a CustomBlock (for ezcTemplate parser)
	 *
	 * @param      string Name of the CustomBlock
	 *
	 * @return     ezcTemplateCustomBlockDefinition Definition of the CustomBlock
	 *
	 * @author     Felix Weis <mail@felixweis.com>
	 * @since      0.11.1
	 */
	public static function getCustomBlockDefinition($name)
	{
		$def = new ezcTemplateCustomBlockDefinition();
		
		$def->class = __CLASS__;
		$def->sendTemplateObject = true;
		$def->method = $name;
		
		switch($name) {
			case '_':
			case '_c':
			case '_d':
			case '_n':
				$def->startExpressionName = 'value';
				$def->requiredParameters = array('value');
				$def->optionalParameters  = array('domain', 'locale');
				if($name == '_') {
					$def->optionalParameters[] = 'parameters';
				}
				return $def;
			
			case '__':
				$def->startExpressionName = 'singularMessage';
				$def->optionalParameters  = array('domain', 'locale', 'parameters');
				$def->requiredParameters = array('singularMessage', 'pluralMessage', 'amount');
				return $def;
			
			case 'route':
				$def->startExpressionName = 'name';
				$def->requiredParameters = array('name');
				$def->optionalParameters  = array('params', 'options');
				return $def;
			
			default:
				return false;
		}
	}
	
	
	/**
	 * Translate a message into the current locale.
	 *
	 * @param      ezcTemplate The current template object instance.
	 * @param      array       The call arguments
	 *
	 * @return     string The translated message.
	 *
	 * @author     Felix Weis <mail@felixweis.com>
	 * @since      0.11.0
	 */
	public static function _($obj, $params = array())
	{
		$params = $params + array('domain' => null, 'locale' => null, 'parameters' => null);
		return $obj->getContext()->getTranslationManager()->_($params['value'], $params['domain'], $params['locale'], $params['parameters']);
	}

	/**
	 * Translate a singular/plural message into the current locale.
	 *
	 * @param      ezcTemplate The current template object instance.
	 * @param      array       The call arguments
	 *
	 * @return     string The translated message.
	 *
	 * @author     Felix Weis <mail@felixweis.com>
	 * @since      0.11.0
	 */
	public static function __($obj, $params = array())
	{
		$params = $params + array('domain' => null, 'locale' => null, 'parameters' => null);
		return $obj->getContext()->getTranslationManager()->__($obj, $params['singularMessage'], $params['pluralMessage'], $params['amount'], $params['domain'], $params['locale'], $params['parameters']);
	}

	/**
	 * Formats a currency amount in the current locale.
	 *
	 * @param      ezcTemplate The current template object instance.
	 * @param      array       The call arguments
	 *
	 * @return     string The formatted number.
	 *
	 * @author     Felix Weis <mail@felixweis.com>
	 * @since      0.11.0
	 */
	public static function _c($obj, $params = array())
	{
		$params = $params + array('domain' => null, 'locale' => null);
		return $obj->getContext()->getTranslationManager()->_c($params['value'], $params['domain'], $params['locale']);;
	}

	/**
	 * Formats a date in the current locale.
	 *
	 * @param      ezcTemplate The current template object instance.
	 * @param      array       The call arguments
	 *
	 * @return     string The formatted date.
	 *
	 * @author     Felix Weis <mail@felixweis.com>
	 * @since      0.11.0
	 */
	public static function _d($obj, $params = array())
	{
		$params = $params + array('domain' => null, 'locale' => null);
		return $obj->getContext()->getTranslationManager()->_d($params['value'], $params['domain'], $params['locale']);;
	}

	/**
	 * Formats a number in the current locale.
	 *
	 * @param      ezcTemplate The current template object instance.
	 * @param      array       The call arguments
	 *
	 * @return     string The formatted number.
	 *
	 * @author     Felix Weis <mail@felixweis.com>
	 * @since      0.11.0
	 */
	public static function _n($obj, $params = array())
	{
		$params = $params + array('domain' => null, 'locale' => null);
		return $obj->getContext()->getTranslationManager()->_n($params['value'], $params['domain'], $params['locale']);
	}

	/**
	 * Generate an Agavi route.
	 *
	 * @param      ezcTemplate The current template object instance.
	 * @param      array       The call arguments
	 *
	 * @return     string The generated route.
	 *
	 * @author     Felix Weis <mail@felixweis.com>
	 * @since      0.11.0
	 */
	public static function route($obj, $params = array())
	{
		$params = $params + array('params' => array(), 'options' => array());
		return $obj->getContext()->getRouting()->gen($params['name'], $params['params'], $params['options']);
	}
}

?>
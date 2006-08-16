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
 * AgaviGettextTranslator defines the translator interface for gettext.
 * 
 * @package    agavi
 * @subpackage translation
 * 
 * @since      0.11.0 
 * @author     Dominik del Bondio <ddb@bitxtender.com>
 * @copyright  (c) Authors
 *
 * @version    $Id$
 */
class AgaviGettextTranslator extends AgaviBasicTranslator
{
	/**
	 * Initialize this Filter.
	 *
	 * @param      AgaviContext The current application context.
	 * @param      array        An associative array of initialization parameters
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com
	 * @since      0.11.0
	 */
	public function initialize(AgaviContext $context, $parameters = array())
	{
		parent::initialize($context);

	}

	/**
	 * Translates a message into the defined language.
	 *
	 * @param      string The message to be translated.
	 * @param      string The language to which the message should be translated.
	 *
	 * @return     string The translated message.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com
	 * @since      0.11.0
	 */
	public function translate($message, $language)
	{
	}
}

?>
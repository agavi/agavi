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
 * Template layer implementation for templates fetched using a PHP stream.
 *
 * @package    agavi
 * @subpackage view
 *
 * @author     David Zülke <dz@bitxtender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.11.0
 *
 * @version    $Id$
 */
class AgaviStreamTemplateLayer extends AgaviTemplateLayer
{
	/**
	 * Constructor.
	 *
	 * @param      array Initial parameters.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function __construct(array $parameters = array())
	{
		parent::__construct(array_merge(array(
			'check' => false,
			'scheme' => null,
			'targets' => array(
				'${template}',
			),
		), $parameters));
	}
	
	/**
	 * Get the full, resolved stream location name to the template resource.
	 *
	 * @return     string A PHP stream resource identifier.
	 *
	 * @throws     AgaviException If the template could not be found.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getResourceStreamIdentifier()
	{
		$template = $this->getParameter('template');
		
		if($template === null) {
			// no template set, we return null so nothing gets rendered
			return null;
		}
		
		if(AgaviConfig::get('core.use_translation')) {
			// i18n is enabled, build a list of sprintf args with the locale identifier
			foreach(AgaviLocale::getLookupPath($this->context->getTranslationManager()->getCurrentLocaleIdentifier()) as $identifier) {
				$args[] = array('locale' => $identifier);
			}
		}
		$args[] = array();
		
		$scheme = $this->getParameter('scheme');
		// FIXME: a simple workaround for broken ubuntu and debian packages (fixed already), we can remove that for final 0.11
		if($scheme != 'file' && !in_array($scheme, stream_get_wrappers())) {
			throw new AgaviException('Unknown stream wrapper "' . $scheme . '", must be one of "' . implode('", "', stream_get_wrappers()) . '".');
		}
		$check = $this->getParameter('check');
		
		$attempts = array();
		
		// try each of the patterns
		foreach((array)$this->getParameter('targets', array()) as $pattern) {
			// try pattern with each argument list
			foreach($args as $arg) {
				$target = AgaviToolkit::expandVariables($pattern, array_merge(array_filter($this->getParameters(), 'is_scalar'), array_filter($this->getParameters(), 'is_null'), $arg));
				// FIXME (should they fix it): don't add file:// because suhosin's include whitelist is empty by default, does not contain 'file' as allowed uri scheme
				if($scheme != 'file') {
					$target = $scheme . '://' . $target;
				}
				if(!$check || is_readable($target)) {
					return $target;
				}
				$attempts[] = $target;
			}
		}
		
		// no template found, time to throw an exception
		throw new AgaviException('Template "' . $template . '" could not be found. Paths tried:' . "\n" . implode("\n", $attempts));
	}
}

?>
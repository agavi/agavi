<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2005-2009 the Agavi Project.                                |
// | Based on the Mojavi3 MVC Framework, Copyright (c) 2003-2005 Sean Kerr.    |
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
 * AgaviModuleConfigHandler reads module configuration files to determine the
 * status of a module.
 *
 * @package    agavi
 * @subpackage config
 *
 * @author     Dominik del Bondio <ddb@bitxtender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.9.0
 *
 * @version    $Id$
 */
class AgaviModuleConfigHandler extends AgaviConfigHandler
{
	/**
	 * Execute this configuration handler.
	 *
	 * @param      string An absolute filesystem path to a configuration file.
	 * @param      string An optional context in which we are currently running.
	 *
	 * @return     string Data to be written to a cache file.
	 *
	 * @throws     <b>AgaviUnreadableException</b> If a requested configuration
	 *                                             file does not exist or is not
	 *                                             readable.
	 * @throws     <b>AgaviParseException</b> If a requested configuration file is
	 *                                        improperly formatted.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.9.0
	 */
	public function execute($config, $context = null)
	{
		// parse the config file
		$configurations = $this->orderConfigurations(AgaviConfigCache::parseConfig($config, false, $this->getValidationFile(), $this->parser)->configurations, AgaviConfig::get('core.environment'), $context);

		$data = array();
		foreach($configurations as $cfg) {
			$authors = array();
			if(isset($cfg->authors)) {
				foreach($cfg->authors as $author) {
					if($author->hasAttribute('email')) {
						$authors[$author->getAttribute('email')] = $author->getValue();
					} else {
						$authors[] = $author->getValue();
					}
				}
			}

			$name = strtolower($cfg->name->getValue());
			$prefix = 'modules.' . $name . '.';
			$data[$prefix . 'enabled']     = AgaviToolkit::literalize($cfg->enabled->getValue());
			if(isset($cfg->title)) {
				$data[$prefix . 'title']       = $cfg->title->getValue();
			}
			if(isset($cfg->version)) {
				$data[$prefix . 'version']     = $cfg->version->getValue();
			}
			$data[$prefix . 'authors']     = $authors;
			if(isset($cfg->homepage)) {
				$data[$prefix . 'homepage']    = $cfg->homepage->getValue();
			}
			if(isset($cfg->update_url)) {
				$data[$prefix . 'update_url']  = $cfg->update_url->getValue();
			}
			if(isset($cfg->description)) {
				$data[$prefix . 'description'] = $cfg->description->getValue();
			}
		}

		$code = 'AgaviConfig::fromArray(' . var_export($data, true) . ');';

		return $this->generate($code);
	}
}

?>
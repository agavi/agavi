<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2005-2011 the Agavi Project.                                |
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
 * AgaviAutoloadConfigHandler allows you to specify a list of classes that will
 * automatically be included for you upon first use.
 *
 * @package    agavi
 * @subpackage config
 *
 * @author     Sean Kerr <skerr@mojavi.org>
 * @author     Dominik del Bondio <ddb@bitxtender.com>
 * @author     David Zülke <david.zuelke@bitextender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.9.0
 *
 * @version    $Id$
 */
class AgaviAutoloadConfigHandler extends AgaviXmlConfigHandler
{
	const XML_NAMESPACE = 'http://agavi.org/agavi/config/parts/autoload/1.1';
	
	/**
	 * Execute this configuration handler.
	 *
	 * @param      AgaviXmlConfigDomDocument The document to parse.
	 *
	 * @return     string Data to be written to a cache file.
	 *
	 * @throws     <b>AgaviParseException</b> If a requested configuration file is
	 *                                        improperly formatted.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      0.9.0
	 */
	public function execute(AgaviXmlConfigDomDocument $document)
	{
		// set up our default namespace
		$document->setDefaultNamespace(self::XML_NAMESPACE, 'autoload');

		$classes = $namespaces = array();
		
		foreach($document->getConfigurationElements() as $configuration) {
			if(!$configuration->has('autoloads')) {
				continue;
			}
			
			// let's do our fancy work
			foreach($configuration->get('autoloads') as $autoload) {
				// we can have variables in the filename
				$path = AgaviToolkit::expandDirectives($autoload->getValue());
				
				// sanity check; XML Schema can't do <xs:choice> on attributes...
				if(($isClass = $autoload->hasAttribute('class')) && $autoload->hasAttribute('namespace')) {
					$error = sprintf(
						'Configuration file "%s" specifies both "class" and "namespace" attribute for path "%s"',
						$document->documentURI,
						$path
					);
					throw new AgaviParseException($error);
				}
				
				// prepend the app dir if the path is not absolute
				$file = self::replacePath($path);
				
				// check if absolute path is readable or try to resolve it against the include path
				if(!file_exists($file) && ($path == $file || !($file = stream_resolve_include_path($path)))) {
					// the class path doesn't exist and couldn't be resolved against the include path either
					$error = sprintf(
						'Configuration file "%s" specifies %s "%s" with non-existent path "%s"',
						$document->documentURI,
						$isClass ? 'file' : 'namespace',
						$isClass ? $autoload->getAttribute('class') : $autoload->getAttribute('namespace'),
						$path
					);
					throw new AgaviParseException($error);
				}
				
				if($isClass) {
					// it's a class
					$classes[$autoload->getAttribute('class')] = $file;
				} else {
					// it's a whole namespace
					// trim backslashes from the namespace and trailing slashes or backslashes from the path
					$namespaces[trim($autoload->getAttribute('namespace'), '\\')] = rtrim($file, '/\\'); 
				}
			}
		}

		$code = array(
			'AgaviAutoloader::addClasses(' . var_export($classes, true) . ');',
			'AgaviAutoloader::addNamespaces(' . var_export($namespaces, true) . ');',
		);

		return $this->generate($code, $document->documentURI);
	}
}

?>
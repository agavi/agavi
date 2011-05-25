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
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.9.0
 *
 * @version    $Id$
 */
class AgaviAutoloadConfigHandler extends AgaviXmlConfigHandler
{
	const XML_NAMESPACE = 'http://agavi.org/agavi/config/parts/autoload/1.0';
	
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
	 * @since      0.9.0
	 */
	public function execute(AgaviXmlConfigDomDocument $document)
	{
		// set up our default namespace
		$document->setDefaultNamespace(self::XML_NAMESPACE, 'autoload');

		$data = array();
		
		foreach($document->getConfigurationElements() as $configuration) {
			if(!$configuration->has('autoloads')) {
				continue;
			}
			
			// let's do our fancy work
			foreach($configuration->get('autoloads') as $autoload) {
				// we can have variables in the filename
				$file = AgaviToolkit::expandDirectives($autoload->getValue());
				// we need the filename w/o app dir prepended since the file could 
				// be placed in the include path
				$originalFile = $file;
				// if the filename is not absolute we assume its relative to the app dir
				$file = self::replacePath($file);

				$class = $autoload->getAttribute('name');

				if(!($fp = @fopen($file, 'r', true))) {
					if($originalFile != $file && ($fpOriginal = @fopen($originalFile, 'r', true))) {
						$file = $originalFile;
						$fp = $fpOriginal;
					} else {
						// the class path doesn't exist
						$error = 'Configuration file "%s" specifies class "%s" with ' .
								 'nonexistent or unreadable file "%s"';
						$error = sprintf($error, $document->documentURI, $class, $file);

						throw new AgaviParseException($error);
					}
				}
				fclose($fp);

				$data[$class] = $file;
			}
		}

		$code = array(
			'return ' . var_export($data, true) . ';',
		);

		return $this->generate($code, $document->documentURI);
	}
}

?>
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
 * AgaviCompileConfigHandler gathers multiple files and puts them into a single
 * file. Upon creation of the new file, all comments and blank lines are removed.
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
class AgaviCompileConfigHandler extends AgaviXmlConfigHandler
{
	const XML_NAMESPACE = 'http://agavi.org/agavi/config/parts/compile/1.0';
	
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
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      0.9.0
	 */
	public function execute(AgaviXmlConfigDomDocument $document)
	{
		// set up our default namespace
		$document->setDefaultNamespace(self::XML_NAMESPACE, 'compile');
		
		$config = $document->documentURI;
		
		$data = array();
		
		// let's do our fancy work
		foreach($document->getConfigurationElements() as $configuration) {
			if(!$configuration->has('compiles')) {
				continue;
			}
			
			foreach($configuration->get('compiles') as $compileFile) {
				$file = trim($compileFile->getValue());
				
				$file = AgaviToolkit::expandDirectives($file);
				$file = self::replacePath($file);
				$file = realpath($file);
				
				if(!is_readable($file)) {
					// file doesn't exist
					$error = 'Configuration file "%s" specifies nonexistent ' . 'or unreadable file "%s"';
					$error = sprintf($error, $config, $compileFile->getValue());
					throw new AgaviParseException($error);
				}
				
				if(AgaviConfig::get('core.debug', false)) {
					// debug mode, just require() the files, makes for nicer stack traces
					$contents = 'require(' . var_export($file, true) . ');';
				} else {
					// no debug mode, so make things fast
					$contents = $this->formatFile(file_get_contents($file));
				}
				
				// append file data
				$data[$file] = $contents;
			}
		}
		
		return $this->generate($data, $config);
	}

	/**
	 * Given some data, remove unnecessary formatting and return the new data
	 *
	 * @param      string Data to format for a compiled file, probably PHP code
	 *
	 * @return     string Data with unnecessary content removed
	 *
	 * @author     Blake Matheny <bmatheny@mobocracy.net>
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	protected function formatFile($data)
	{
		// replace windows and mac format with unix format
		$data = str_replace("\r\n", "\n", $data);
		$data = str_replace("\r", "\n", $data);

		// remove comments and tags with tokenizer

		// I disabled this, it seems broken somehow. doesn't remove all <?php tags. - david

		if(function_exists('token_get_all')) {
			$tokens = token_get_all($data);
			$tokenized = null;
			// has something been written to tokenized? If so, we can optionally append whitespace.
			$appended = false;

			foreach($tokens as $token) {

				if(is_string($token)) {
					$tokenized .= $token;
					$appended = true;
				} else {
					@list($id,$text) = $token;
					switch($id) {
						case T_COMMENT:
						case T_DOC_COMMENT:
						case T_OPEN_TAG:
							$appended = false;
							break;
						case T_CLOSE_TAG:
							$appended = false;
							break;

						case T_WHITESPACE:
							// something was appended, optionally add a newline
							if($appended) {
								$replace = null;
								if(strstr($text, "\n") !== false) {
									$replace = "\n";
								}
								if($replace) {
									$text = preg_replace('/\s+/m', $replace, $text);
								}
								$tokenized .= $text;
							}
							$appended = false;
							break;

						case T_INLINE_HTML:
							// If empty T_INLINE_HTML move on
							if(!preg_match('/[^\s]+/m', $text)) {
								$appended = false;
								break;
							}

						default:
							$tokenized .= $text;
							$appended = true;
							break;
					}
				}
			}
			$data = $tokenized;
		}
		$data = trim($data);
		if(substr($data, 0, 5) == '<?php') {
			$data = substr($data, 5);
		} elseif(substr($data, 0, 2) == '<?') {
			$data = substr($data, 2);
		}
		if(substr($data, -2, 2) == '?>') {
			$data = substr($data, 0, -2);
		}
		$data = preg_replace('/\s*\?>\s*<\?(php)?\s*/', '', $data);

		return $data;
	}

}

?>
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
 * Renderer for Smarty (versions 2 and 3).
 *
 * @package    agavi
 * @subpackage renderer
 *
 * @author     David Zülke <dz@bitxtender.com>
 * @author     TANAKA Koichi <tanaka@ensites.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.11.0
 *
 * @version    $Id$
 */
class AgaviSmartyRenderer extends AgaviRenderer implements AgaviIReusableRenderer
{
	/**
	 * @constant   string The directory inside the cache dir where templates will
	 *                    be stored in compiled form.
	 */
	const COMPILE_DIR = 'templates';
	
	/**
	 * @constant   string The subdirectory inside the compile dir where templates
	 *                    will be stored in compiled form.
	 */
	const COMPILE_SUBDIR = 'smarty';
	
	/**
	 * @constant   string The directory inside the cache dir where cached content
	 *                    will be stored.
	 */
	const CACHE_DIR = 'content';

	/**
	 * @var        Smarty Smarty template engine.
	 */
	protected $smarty = null;

	/**
	 * @var        string A string with the default template file extension,
	 *                    including the dot.
	 */
	protected $defaultExtension = '.tpl';

	/**
	 * @var        bool Internal flag to indicate the Smarty version used.
	 */
	protected $isSmarty2 = true;

	/**
	 * Pre-serialization callback.
	 *
	 * Excludes the Smarty instance to prevent excessive serialization load.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function __sleep()
	{
		$keys = parent::__sleep();
		unset($keys[array_search('smarty', $keys)]);
		return $keys;
	}
	
	/**
	 * Create an instance of Smarty and initialize it correctly.
	 *
	 * @return     Smarty The Smarty instance.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.2
	 */
	protected function createEngineInstance()
	{
		if(!class_exists('Smarty')) {
			if(defined('SMARTY_DIR') ) {
				// if SMARTY_DIR constant is defined, we'll use it
				require(SMARTY_DIR . 'Smarty.class.php');
			} else {
				// otherwise we resort to include_path
				require('Smarty.class.php');
			}
		}
		
		return new Smarty();
	}

	/**
	 * Grab a cleaned up smarty instance.
	 *
	 * @return     Smarty A Smarty instance.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @author     TANAKA Koichi <tanaka@ensites.com>
	 * @since      0.9.0
	 */
	protected function getEngine()
	{
		if($this->smarty) {
			if($this->isSmarty2) {
				$this->smarty->clear_all_assign();
				$this->smarty->clear_config();
			}
			return $this->smarty;
		}

		$this->smarty = $this->createEngineInstance();
		
		$this->isSmarty2 = !defined("Smarty::SMARTY_VERSION") || !preg_match('#^Smarty.?3#', Smarty::SMARTY_VERSION, $matches);
		
		if($this->isSmarty2) {
			$this->smarty->config_dir = AgaviConfig::get('core.config_dir');
		} else {
			$this->smarty->setConfigDir(AgaviConfig::get('core.config_dir'));
		}

		$parentMode = fileperms(AgaviConfig::get('core.cache_dir'));

		$compileDir = AgaviConfig::get('core.cache_dir') . DIRECTORY_SEPARATOR . self::COMPILE_DIR . DIRECTORY_SEPARATOR . self::COMPILE_SUBDIR;
		AgaviToolkit::mkdir($compileDir, $parentMode, true);
		if($this->isSmarty2) {
			$this->smarty->compile_dir = $compileDir;
		} else {
			$this->smarty->setCompileDir($compileDir);
		}

		$cacheDir = AgaviConfig::get('core.cache_dir') . DIRECTORY_SEPARATOR . self::CACHE_DIR;
		AgaviToolkit::mkdir($cacheDir, $parentMode, true);
		if($this->isSmarty2) {
			$this->smarty->cache_dir = $cacheDir;
		} else {
			$this->smarty->setCacheDir($cacheDir);
		}

		if(AgaviConfig::get('core.debug', false)) {
			$this->smarty->debugging = true;
		}

		foreach((array)$this->getParameter('smarty_variables') as $key => $value) {
			$this->smarty->$key = $value;
		}

		return $this->smarty;
	}

	/**
	 * Render the presentation and return the result.
	 *
	 * @param      AgaviTemplateLayer The template layer to render.
	 * @param      array              The template variables.
	 * @param      array              The slots.
	 * @param      array              Associative array of additional assigns.
	 *
	 * @return     string A rendered result.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function render(AgaviTemplateLayer $layer, array &$attributes = array(), array &$slots = array(), array &$moreAssigns = array())
	{
		$engine = $this->getEngine();
		if($this->isSmarty2) {
			$assignFunc = 'assign_by_ref';
			$data = $engine;
		} else {
			$assignFunc = 'assignByRef';
			$data = $engine->createData($engine);
		}
		
		if($this->extractVars) {
			foreach($attributes as $name => &$value) {
				$data->$assignFunc($name, $value);
			}
		} else {
			$data->$assignFunc($this->varName, $attributes);
		}
		
		$data->$assignFunc($this->slotsVarName, $slots);
		
		foreach($this->assigns as $key => $getter) {
			$data->assign($key, $this->context->$getter());
		}
		
		$finalMoreAssigns =& self::buildMoreAssigns($moreAssigns, $this->moreAssignNames);
		foreach($finalMoreAssigns as $key => &$value) {
			$data->$assignFunc($key, $value);
		}
		
		// hack because stupid smarty cannot handle php streams... my god
		$resource = str_replace('://', ':', $layer->getResourceStreamIdentifier());
		return $engine->fetch($resource, $this->isSmarty2 ? null : $data);
	}
}

?>
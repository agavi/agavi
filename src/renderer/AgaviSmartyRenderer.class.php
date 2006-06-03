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
 * A renderer produces the output as defined by a View
 *
 * @package    agavi
 * @subpackage renderer
 *
 * @author     David Zuelke <dz@bitxtender.com>
 * @author     Agavi Project <info@agavi.org>
 * @copyright  (c) Authors
 * @since      0.11.0
 *
 * @version    $Id$
 */
abstract class AgaviSmartyRenderer
{
	const COMPILE_DIR = 'templates';
	const COMPILE_SUBDIR = 'smarty';
	const CACHE_DIR = 'content';

	protected static
		$smarty = null;

	public function getEngine()
	{
		if($this->smarty) {
			return $this->smarty;
		}
		
		if (!class_exists('Smarty')) {

			// if SMARTY_DIR constant is defined, we'll use it
			if ( defined('SMARTY_DIR') ) {
				require(SMARTY_DIR . 'Smarty.class.php');
			}
			// otherwise we resort to include_path
			else {
				require('Smarty.class.php');
			}
		}

		$this->smarty = new Smarty();
		$this->smarty->clear_all_assign();
		$this->smarty->clear_config();
		$this->smarty->config_dir = AgaviConfig::get('core.config_dir');

		$compileDir = AgaviConfig::get('core.cache_dir') . DIRECTORY_SEPARATOR . self::COMPILE_DIR . DIRECTORY_SEPARATOR . self::COMPILE_SUBDIR;
		@mkdir($compileDir, null, true);
		$this->smarty->compile_dir = $compileDir;

		$cacheDir = AgaviConfig::get('core.cache_dir') . DIRECTORY_SEPARATOR . self::CACHE_DIR;
		@mkdir($cacheDir, null, true);
		$this->smarty->cache_dir = $cacheDir;

		$this->smarty->plugins_dir  = array("plugins","plugins_local");
		
		return $this->smarty;
	}

	public function & render()
	{
		$retval = null;

		// execute pre-render check
		$this->preRenderCheck();

		$engine = $this->getEngine();
		$view = $this->getView();

		// get the render mode
		$mode = $this->getContext()->getController()->getRenderMode();

		$engine->template_dir = $this->getDirectory();

		$attribs = $view->getAttributesByRef();

		foreach($attribs as $name => &$value) {
			$engine->assign_by_ref($name, $value);
		}

		if ($mode == AgaviView::RENDER_CLIENT && !$this->isDecorator()) {
			// render directly to the client
			$this->getEngine()->display($this->getTemplate());
		} else if ($mode != AgaviView::RENDER_NONE) {
			// render to variable
			$retval = $this->getEngine()->fetch($this->getTemplate());

			// now render our decorator template, if one exists
			if ($this->isDecorator()) {
				$retval =& $this->decorate($retval);
			}

			if ($mode == AgaviView::RENDER_CLIENT) {
				echo($retval);
				$retval = null;
			}
		}
		return $retval;
	}

	public function & decorate(&$content)
	{
		// call our parent decorate() method
		parent::decorate($content);

		// render the decorator template and return the result
		$decoratorTemplate = $this->getDecoratorDirectory() . '/' . $this->getDecoratorTemplate();

		$retval = $this->getEngine()->fetch($decoratorTemplate);

		return $retval;
	}
}
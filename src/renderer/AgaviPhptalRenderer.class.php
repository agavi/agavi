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
 * @author     Benjamin Muskalla <bm@bmuskalla.de>
 * @author     Agavi Project <info@agavi.org>
 * @copyright  (c) Authors
 * @since      0.11.0
 *
 * @version    $Id$
 */
class AgaviPhptalRenderer extends AgaviRenderer
{
	protected $extension = '.tal';
	
	protected $_phptal = null;
	
	const COMPILE_DIR = 'templates';
	const COMPILE_SUBDIR = 'phptal';

	/**
	 * Retrieve the PHPTAL instance
	 *
	 * @return     PHPTAL A PHPTAL instance.
	 *
	 * @since      0.11.0
	 */
	public function getEngine()
	{
		if($this->_phptal === null) {
			$this->_phptal = new FixedPHPTAL();
		}
		return $this->_phptal;
	}
	
	/**
	 * Render the presentation.
	 *
	 * When the controller render mode is View::RENDER_CLIENT, this method will
	 * render the presentation directly to the client and null will be returned.
	 *
	 * @return     string A string representing the rendered presentation, if
	 *                    the controller render mode is View::RENDER_VAR,
	 *                    otherwise null.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     Benjamin Muskalla <bm@bmuskalla.de>
	 * @since      0.11.0
	 */
	public function &render()
	{
		$retval = null;
		
		$this->preRenderCheck();
		$engine = $this->getEngine();
		$view = $this->getView();
		
		$mode = $view->getContext()->getController()->getRenderMode();
		$engine->setTemplateRepository($view->getDirectory());
		$engine->setTemplate($view->getTemplate() . $this->getExtension());
		if($this->extractVars) {
			foreach($view->getAttributes() as $key => $value) {
				$engine->set($key, $value);
			}
		} else {
			$engine->set($this->varName, $view->getAttributes());
		}
		$engine->set('this', $this);
		
		if($mode == AgaviView::RENDER_CLIENT && !$view->isDecorator()) {
			// render directly to the client
			echo $engine->execute();
		} elseif($mode != AgaviView::RENDER_NONE) {
			// render to variable
			$retval = $engine->execute();
			// now render our decorator template, if one exists
			if($view->isDecorator()) {
				$retval = $this->decorate($retval);
			}
			
			if($mode == AgaviView::RENDER_CLIENT) {
				echo $retval;
				$retval = null;
			}
		}
		
		return $retval;
	}

	/*
	 * @see        View::decorate()
	 */
	public function &decorate(&$content)
	{
		// call our parent decorate() method
		parent::decorate($content);
		$engine = $this->getEngine();
		$view = $this->getView();
		
		// render the decorator template and return the result
		$engine->setTemplateRepository($view->getDecoratorDirectory());
		$engine->setTemplate($view->getDecoratorTemplate() . $this->getExtension());
		
		// set the template resources
		if($this->extractVars) {
			foreach($view->getAttributes() as $key => $value) {
				$engine->set($key, $value);
			}
			foreach($this->output as $key => $value) {
				$engine->set($key, $value);
			}
		} else {
			$engine->set($this->varName, array_merge($view->getAttributes(), $this->output));
		}
		$engine->set('this', $this);
		
		$retval = $engine->execute();
		
		return $retval;
	}
}


// the following lines are a fix until PHPTAL has been changed so setTemplate() resets _prepared, _source and _functionName.
// as soon as this is fixed in PHPTAL SVN, we will remove the stub class and move the define and the require into initialize()
// 2006-06-10: still not fixed in PHPTAL 1.1.5...

if(!defined('PHPTAL_PHP_CODE_DESTINATION')) {
	define('PHPTAL_PHP_CODE_DESTINATION', AgaviConfig::get('core.cache_dir') . DIRECTORY_SEPARATOR . AgaviPhptalRenderer::COMPILE_DIR . DIRECTORY_SEPARATOR . AgaviPhptalRenderer::COMPILE_SUBDIR . DIRECTORY_SEPARATOR);
	@mkdir(PHPTAL_PHP_CODE_DESTINATION, fileperms(AgaviConfig::get('core.cache_dir')), true);
}

require_once('PHPTAL.php');

class FixedPHPTAL extends PHPTAL
{
	public function setTemplate($path)
	{
		$this->_prepared = false;
		$this->_functionName = null;
		$this->_source = null;
		parent::setTemplate($path);
	}
}

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
abstract class AgaviPHPTALRenderer
{
	/**
	 * Retrieve the PHPTAL instance
	 *
	 * @return     null
	 *
	 * @since      0.11.0
	 */
	public function & getEngine ()
	{

		$retval = $this->_phptal;

		return $retval;

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
	public function & render ()
	{
		$retval = null;
		
		$this->preRenderCheck();
		$engine = $this->getEngine();
		$view = $this->getView();
		
		$mode = $this->getContext()->getController()->getRenderMode();
		$engine->setTemplateRepository($view->getDirectory());
		$engine->setTemplate($view->getTemplate());
		$this->updateTemplateAttributes();
		
		if($mode == AgaviView::RENDER_CLIENT && !$view->isDecorator()) {
			// render directly to the client
			echo $engine->execute();
		} else if($mode != AgaviView::RENDER_NONE) {
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
	public function & decorate(&$content)
	{
		// call our parent decorate() method
		parent::decorate($content);
		$engine = $this->getEngine();

		// render the decorator template and return the result
		$decoratorTemplate = $this->getDecoratorDirectory() . '/' . $this->getDecoratorTemplate();

		$engine->setTemplate($decoratorTemplate);
		
		// TODO: fix this crap :)
		/*
		define('PHPTAL_FORCE_REPARSE', true);
		$this->getEngine()->_prepared = false;
		$this->getEngine()->_functionName = 0;	
		*/
		// set the template resources
		$this->updateTemplateAttributes();
	

		$retval = $engine->execute();

		return $retval;
	}	
	
	/**
	 * Updates template attributes
	 *
	 * @author     Benjamin Muskalla <bm@bmuskalla.de>
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	private function updateTemplateAttributes()
	{
		$view = $this->getView();
		$engine = $this->getEngine();
		if($this->extractAttributes()) {
			foreach($view->getAttributes() as $key => $val) {
				$engine->set($key, $val);
			}
		} else {
			$engine->set('template', $view->getAttributes());
		}
		$engine->set('this', $this);
	}
	
}


// the following lines are a fix until PHPTAL has been changed so setTemplate() resets prepared and functionName.
// as soon as this is fixed in PHPTAL SVN, we will remove the stub class and move the define and the require into initialize()

if(!defined('PHPTAL_PHP_CODE_DESTINATION')) {
	@mkdir(AgaviConfig::get('core.cache_dir') . DIRECTORY_SEPARATOR . PHPTALView::CACHE_SUBDIR);
	define('PHPTAL_PHP_CODE_DESTINATION', AgaviConfig::get('core.cache_dir') . DIRECTORY_SEPARATOR . PHPTALView::CACHE_SUBDIR . DIRECTORY_SEPARATOR);
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

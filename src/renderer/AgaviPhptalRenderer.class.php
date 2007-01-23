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
class AgaviPhptalRenderer extends AgaviRenderer implements AgaviIReusableRenderer
{
	/**
	 * @var        string A string with the default template file extension,
	 *                    including the dot.
	 */
	protected $defaultExtension = '.tal';

	/**
	 * @var        PHPTAL PHPTAL template engine.
	 */
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
			if(!defined('PHPTAL_PHP_CODE_DESTINATION')) {
				define('PHPTAL_PHP_CODE_DESTINATION', AgaviConfig::get('core.cache_dir') . DIRECTORY_SEPARATOR . AgaviPhptalRenderer::COMPILE_DIR . DIRECTORY_SEPARATOR . AgaviPhptalRenderer::COMPILE_SUBDIR . DIRECTORY_SEPARATOR);
				AgaviToolkit::mkdir(PHPTAL_PHP_CODE_DESTINATION, fileperms(AgaviConfig::get('core.cache_dir')), true);
			}
			
			if(!class_exists('PHPTAL')) {
				require('PHPTAL.php');
			}

			$this->_phptal = new PHPTAL();
		}
		return $this->_phptal;
	}

	/**
	 * Reset the engine for re-use
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	protected function reset()
	{
	}
	
	/**
	 * Render the presentation to the Response.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @author     Benjamin Muskalla <bm@bmuskalla.de>
	 * @since      0.11.0
	 */
	public function render()
	{
		$retval = null;

		$engine = $this->getEngine();
		$view = $this->getView();

		$mode = $view->getContext()->getController()->getRenderMode();
		$engine->setTemplateRepository($view->getDirectory());

		$engine->setTemplate($this->buildTemplateName($view->getTemplate()));
		if($this->extractVars) {
			foreach($view->getAttributes() as $key => $value) {
				$engine->set($key, $value);
			}
		} else {
			$engine->set($this->varName, $view->getAttributes());
		}

		$collisions = array_intersect(array_keys($this->assigns), $this->view->getAttributeNames());
		if(count($collisions)) {
			throw new AgaviException('Could not import system objects due to variable name collisions ("' . implode('", "', $collisions) . '" already in use).');
		}
		foreach($this->assigns as $key => &$value) {
			$engine->set($key, $value);
		}

		$engine->set('this', $this);

		$retval = $engine->execute();
		
		// now render our decorator template, if one exists
		if($view->isDecorator()) {
			$retval = $this->decorate($retval);
		}
		
		$this->response->setContent($retval);
	}

	/**
	 * @see        AgaviRenderer::decorate()
	 */
	public function decorate($content)
	{
		// call our parent decorate() method
		parent::decorate($content);
		$engine = $this->getEngine();
		$view = $this->getView();

		// render the decorator template and return the result
		$engine->setTemplateRepository($view->getDecoratorDirectory());

		$engine->setTemplate($this->buildTemplateName($view->getDecoratorTemplate()));

		$toSet = array();
		// set the template resources
		if($this->extractVars) {
			foreach($view->getAttributes() as $key => $value) {
				$engine->set($key, $value);
			}
		} else {
			$toSet =& $view->getAttributes();
		}

		if($this->extractSlots === true || ($this->extractVars && $this->extractSlots !== false)) {
			foreach($this->output as $key => &$value) {
				$engine->set($key, $value);
			}
		} else {
			if($this->varName == $this->slotsVarName) {
				$toSet = array_merge($toSet, $this->output);
			} else {
				$engine->set($this->slotsVarName, $this->output);
			}
		}
		$engine->set($this->varName, $toSet);

		$collisions = array_intersect(array_keys($this->assigns), $this->view->getAttributeNames());
		if(count($collisions)) {
			throw new AgaviException('Could not import system objects due to variable name collisions ("' . implode('", "', $collisions) . '" already in use).');
		}
		foreach($this->assigns as $key => $value) {
			$engine->set($key, $value);
		}

		$engine->set('this', $this);

		$retval = $engine->execute();

		return $retval;
	}
}

?>
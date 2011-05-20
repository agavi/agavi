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
 * A renderer produces the output as defined by a View
 *
 * @package    agavi
 * @subpackage renderer
 *
 * @author     Felix Weis <mail@felixweis.com>
 * @author     David Zülke <dz@bitxtender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.11.0
 *
 * @version    $Id$
 */
class AgaviEzctemplateRenderer extends AgaviRenderer implements AgaviIReusableRenderer
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
	const COMPILE_SUBDIR = 'ezctemplate';

	/**
	 * @constant   string The directory inside the cache dir where cached content
	 *                    will be stored.
	 */
	const CACHE_DIR = 'content';

	/**
	 * @var        ezcTemplate The template engine instance.
	 */
	protected $ezcTemplate = null;

	/**
	 * @var        string A string with the default template file extension,
	 *                    including the dot.
	 */
	protected $defaultExtension = '.ezt';

	/**
	 * Pre-serialization callback.
	 *
	 * Excludes the ezcTemplate instance to prevent excessive serialization load.
	 *
	 * @author     Felix Weis <mail@felixweis.com>
	 * @since      0.11.0
	 */
	public function __sleep()
	{
		$keys = parent::__sleep();
		unset($keys[array_search('ezctemplate', $keys)]);
		return $keys;
	}
	
	/**
	 * Create an instance of ezcTemplate and initialize it correctly.
	 * Returns an instance of AgaviEzctemplateTemplate by default.
	 *
	 * @return     ezcTemplate The ezcTemplate instance.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.2
	 */
	protected function createEngineInstance()
	{
		$cls = $this->getParameter('template_class', 'AgaviEzctemplateTemplate');
		
		$ezcTemplate = new $cls();
		if($ezcTemplate instanceof AgaviIEzctemplateTemplate) {
			$ezcTemplate->setContext($this->context);
		}
		
		return $ezcTemplate;
	}

	/**
	 * Grab a cleaned up ezctemplate instance.
	 *
	 * @return     ezcTemplate A ezcTemplate instance.
	 *
	 * @author     Felix Weis <mail@felixweis.com>
	 * @since      0.11.0
	 */
	protected function getEngine()
	{
		// ezcTemplate already initialized, only clear the assigns and retun the engine
		if($this->ezcTemplate) {
			$this->ezcTemplate->send = new ezcTemplateVariableCollection();
			return $this->ezcTemplate;
		}

		$this->ezcTemplate = $this->createEngineInstance();
		// initialize ezcTemplate
		
		$parentMode = fileperms(AgaviConfig::get('core.cache_dir'));

		$compileDir = AgaviConfig::get('core.cache_dir') . DIRECTORY_SEPARATOR . self::COMPILE_DIR . DIRECTORY_SEPARATOR . self::COMPILE_SUBDIR;
		AgaviToolkit::mkdir($compileDir, $parentMode, true);

		// templatePath unnessesary because Agavi will always supply the absolute ressource path
		$config = new ezcTemplateConfiguration();
		$config->templatePath = "";
		$config->compilePath = $compileDir;

		// set the ezcTemplateOutputContext (standard is ezcTemplateXhtmlContext)
		if($this->hasParameter('context')) {
			$contextClass = $this->getParameter('context');
			$config->context = new $contextClass();
		}

		// add some usefull Agavi Functions/Blocks as Extension
		$config->addExtension('AgaviEzctemplateCustomBlocks');
		$config->addExtension('AgaviEzctemplateCustomFunctions');
		
		foreach($this->getParameter('extensions', array()) as $extension) {
			$config->addExtension($extension);
		}
		
		$this->ezcTemplate->configuration = $config;

		return $this->ezcTemplate;
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
	 * @author     Felix Weis <mail@felixweis.com>
	 * @since      0.11.0
	 */
	public function render(AgaviTemplateLayer $layer, array &$attributes = array(), array &$slots = array(), array &$moreAssigns = array())
	{
		$engine = $this->getEngine();

		if($this->extractVars) {
			foreach($attributes as $name => &$value) {
				$engine->send->{$name} = $value;
			}
		} else {
			$engine->send->{$this->varName} = $attributes;
		}

		$key = $this->slotsVarName;
		$engine->send->{$key} = $slots;

		foreach($this->assigns as $key => $getter) {
			$engine->send->{$key} = $this->context->$getter();
		}

		$finalMoreAssigns =& self::buildMoreAssigns($moreAssigns, $this->moreAssignNames);
		foreach($finalMoreAssigns as $key => &$value) {
			$engine->send->{$key} = $value;
		}

		return $engine->process($layer->getResourceStreamIdentifier());
	}
}

?>
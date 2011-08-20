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
 * @author     David Zülke <david.zuelke@bitextender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      1.0.6
 *
 * @version    $Id$
 */
class AgaviTwigRenderer extends AgaviRenderer implements AgaviIReusableRenderer
{
	/**
	 * @var        Twig_Environment The template engine.
	 */
	protected $twig = null;

	/**
	 * @var        string A string with the default template file extension,
	 *                    including the dot.
	 */
	protected $defaultExtension = '.twig';

	/**
	 * Pre-serialization callback.
	 *
	 * Excludes the Twig instance to prevent excessive serialization load.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.6
	 */
	public function __sleep()
	{
		$keys = parent::__sleep();
		unset($keys[array_search('twig', $keys)]);
		return $keys;
	}
	
	/**
	 * Initialize this Renderer.
	 *
	 * @param      AgaviContext The current application context.
	 * @param      array        An associative array of initialization parameters.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function initialize(AgaviContext $context, array $parameters = array())
	{
		parent::initialize($context, $parameters);
		
		$this->setParameter('options', array_merge(
			array(
				'debug' => AgaviConfig::get('core.debug'),
				'cache' => AgaviConfig::get('core.debug') ? false : AgaviConfig::get('core.cache_dir') . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'twig',
			),
			(array)$this->getParameter('options', array())
		));
	}
	
	/**
	 * Load and create an instance of Twig.
	 *
	 * @return     Twig_Environment
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.6
	 */
	protected function createEngineInstance()
	{
		if(!class_exists('Twig_Environment')) {
			if(!class_exists('Twig_Autoloader')) {
				require('Twig/Autoloader.php');
			}
			Twig_Autoloader::register();
		}
		
		// loader is set in render()
		return new Twig_Environment(null, (array)$this->getParameter('options', array()));
	}

	/**
	 * Grab an initialized Twig instance.
	 *
	 * @return     Twig_Environment
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.6
	 */
	protected function getEngine()
	{
		if(!$this->twig) {
			$this->twig = $this->createEngineInstance();
			
			// assigns can be set as globals
			foreach($this->assigns as $key => $getter) {
				$this->twig->addGlobal($key, $this->context->$getter());
			}
		}
		
		return $this->twig;
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
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.6
	 */
	public function render(AgaviTemplateLayer $layer, array &$attributes = array(), array &$slots = array(), array &$moreAssigns = array())
	{
		$twig = $this->getEngine();
		
		$path = $layer->getResourceStreamIdentifier();
		if($layer instanceof AgaviFileTemplateLayer) {
			$pathinfo = pathinfo($path);
			// set the directory the template is in as the first path to load from, and the directory set on the layer second
			// that way, including another template inside this template will look at e.g. a locale subdirectory first before falling back to the originally defined folder
			$paths = array(
				$pathinfo['dirname'],
				$layer->getParameter('directory'),
			);
			// also allow loading from the main template dir by default, and any other directories the user has set through configuration
			foreach((array)$this->getParameter('template_dirs', array(AgaviConfig::get('core.template_dir'))) as $dir) {
				$paths[] = $dir;
			}
			$twig->setLoader(new Twig_Loader_Filesystem($paths));
			$source = $pathinfo['basename'];
		} else {
			// a stream template or whatever; either way, it's something Twig can't load directly :S
			$twig->setLoader(new Twig_Loader_String());
			$source = file_get_contents($path);
		}
		$template = $twig->loadTemplate($source);
		
		$data = array();
		
		// template vars
		if($this->extractVars) {
			foreach($attributes as $name => $value) {
				$data[$name] = $value;
			}
		} else {
			$data[$this->varName] = $attributes;
		}
		
		// slots
		$data[$this->slotsVarName] = $slots;
		
		// dynamic assigns (global ones were set in getEngine())
		$finalMoreAssigns = self::buildMoreAssigns($moreAssigns, $this->moreAssignNames);
		foreach($finalMoreAssigns as $key => $value) {
			$data[$key] = $value;
		}
		
		return $template->render($data);
	}
}

?>
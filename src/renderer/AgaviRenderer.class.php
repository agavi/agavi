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
abstract class AgaviRenderer
{
	/**
	 * @var        AgaviContext An AgaviContext instance.
	 */
	protected $context = null;
	
	/**
	 * @var        string A string with the default template file extension,
	 *                    including the dot.
	 */
	protected $defaultExtension = '';
	
	/**
	 * @var        string The name of the array that contains the template vars.
	 */
	protected $varName = 'template';
	
	/**
	 * @var        string The name of the array that contains the slot output.
	 *                    Defaults to null, which means it'll be the identical to
	 *                    the varName setting.
	 *
	 * @see        AgaviRenderer::$varName
	 */
	protected $slotsVarName = null;
	
	/**
	 * @var        bool Whether or not the template vars should be extracted.
	 */
	protected $extractVars = false;
	
	/**
	 * @var        bool Whether or not the slot output vars should be extracted.
	 *                  Defaults to null, which means it behaves according to the
	 *                  extractVars setting.
	 *
	 * @see        AgaviRenderer::$extractVars
	 */
	protected $extractSlots = null;
	
	/**
	 * @var        array An array of objects to be exported for use in templates.
	 */
	protected $assigns = array();
	
	/**
	 * Initialize this Renderer.
	 *
	 * @param      AgaviContext The current application context.
	 * @param      array        An associative array of initialization parameters.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function initialize(AgaviContext $context, array $parameters = array())
	{
		$this->context = $context;
		if(isset($parameters['var_name'])) {
			$this->varName = $parameters['var_name'];
		}
		if(isset($parameters['slots_var_name'])) {
			$this->slotsVarName = $parameters['slots_var_name'];
		}
		if(isset($parameters['extract_vars'])) {
			$this->extractVars = $parameters['extract_vars'];
		}
		if(isset($parameters['extract_slots'])) {
			$this->extractSlots = $parameters['extract_slots'];
		}
		if($this->slotsVarName === null) {
			$this->slotsVarName = $this->varName;
		}
		if(isset($parameters['assigns'])) {
			foreach($parameters['assigns'] as $factory => $var) {
				$getter = 'get' . str_replace('_', '', $factory);
				$this->assigns[$var] = $this->context->$getter();
			}
		}
	}
	
	/**
	 * Retrieve the current application context.
	 *
	 * @return     AgaviContext The current AgaviContext instance.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public final function getContext()
	{
		return $this->context;
	}
	
	/**
	 * Get the template file extension
	 *
	 * @return     string The extension, including a leading dot.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getDefaultExtension()
	{
		return $this->defaultExtension;
	}
	
	/**
	 * Render the presentation to the Response.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	abstract public function render(AgaviTemplateLayer $layer, array &$attributes, array &$slots = array());
}

?>
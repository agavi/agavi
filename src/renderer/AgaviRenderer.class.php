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
 * @author     David Zülke <dz@bitxtender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.11.0
 *
 * @version    $Id$
 */
abstract class AgaviRenderer extends AgaviParameterHolder
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
	 * @var        string The name of the array that contains the slots output.
	 */
	protected $slotsVarName = 'slots';
	
	/**
	 * @var        bool Whether or not the template vars should be extracted.
	 */
	protected $extractVars = false;
	
	/**
	 * @var        array An array of objects to be exported for use in templates.
	 */
	protected $assigns = array();
	
	/**
	 * @var        array An array of names for the "more" assigns.
	 */
	protected $moreAssignNames = array();
	
	/**
	 * Pre-serialization callback.
	 *
	 * Will set the name of the context and exclude the instance from serializing.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function __sleep()
	{
		$this->contextName = $this->context->getName();
		$arr = get_object_vars($this);
		unset($arr['context']);
		return array_keys($arr);
	}
	
	/**
	 * Post-unserialization callback.
	 *
	 * Will restore the context based on the names set by __sleep.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function __wakeup()
	{
		$this->context = AgaviContext::getInstance($this->contextName);
		unset($this->contextName);
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
		$this->context = $context;
		
		$this->setParameters($parameters);
		
		$this->varName = $this->getParameter('var_name', $this->varName);
		$this->slotsVarName = $this->getParameter('slots_var_name', $this->slotsVarName);
		$this->extractVars = $this->getParameter('extract_vars', $this->extractVars);
		
		$this->defaultExtension = $this->getParameter('default_extension', $this->defaultExtension);
		
		if(!$this->extractVars && $this->varName == $this->slotsVarName) {
			throw new AgaviException('Template and Slots container variable names cannot be identical.');
		}
		
		foreach($this->getParameter('assigns', array()) as $item => $var) {
			$getter = 'get' . str_replace('_', '', $item);
			if(is_callable(array($this->context, $getter))) {
				if($var === null) {
					// the name is null, which means this one should not be assigned
					// we do this in here, not for the moreAssignNames, since those are checked later in the renderer
					continue;
				}
				$this->assigns[$var] = $getter;
			} else {
				$this->moreAssignNames[$item] = $var;
			}
		}
	}
	
	/**
	 * Retrieve the current application context.
	 *
	 * @return     AgaviContext The current AgaviContext instance.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
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
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getDefaultExtension()
	{
		return $this->defaultExtension;
	}
	
	/**
	 * Build an array of "more" assigns.
	 *
	 * @param      array The values to be assigned.
	 * @param      array Assigns name map.
	 *
	 * @return     array The data.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.0
	 */
	protected static function &buildMoreAssigns(&$moreAssigns, $moreAssignNames)
	{
		$retval = array();
		
		foreach($moreAssigns as $name => &$value) {
			if(isset($moreAssignNames[$name])) {
				$name = $moreAssignNames[$name];
			} elseif(array_key_exists($name, $moreAssignNames)) {
				// the name is null, which means this one should not be assigned
				continue;
			}
			$retval[$name] =& $value;
		}
		
		return $retval;
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
	abstract public function render(AgaviTemplateLayer $layer, array &$attributes = array(), array &$slots = array(), array &$moreAssigns = array());
}

?>
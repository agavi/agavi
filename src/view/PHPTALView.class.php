<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2003-2006 the Agavi Project.                                |
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
 * A view that uses PHPTAL to render templates.
 *
 * @package    agavi
 * @subpackage view
 *
 * @author     Benjamin Muskalla <bm@bmuskalla.de>
 * @author     Agavi Project <info@agavi.org>
 * @copyright  (c) Authors
 * @since      0.11.0
 *
 * @version    $Id$
 */
abstract class PHPTALView extends View
{
	private
		$_phptal = null;
	private
		$attributes = array();
			
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
	 * Initialize this view.
	 *
	 * @param      Context The current application context.
	 *
	 * @return     bool true, if initialization completes successfully,
	 *                  otherwise false.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     Benjamin Muskalla <bm@bmuskalla.de>
	 * @since      0.11.0
	 */
	public function initialize ($context)
	{
		
		$this->_phptal = new FixedPHPTAL();

		return(parent::initialize($context));
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
		
		$mode = $this->getContext()->getController()->getRenderMode();
		$this->getEngine()->setTemplateRepository($this->getDirectory());
		$this->getEngine()->setTemplate($this->getTemplate());
		$this->updateTemplateAttributes();
		
		if ($mode == View::RENDER_CLIENT && !$this->isDecorator()) {
			// render directly to the client
			echo $this->getEngine()->execute();
		} else if ($mode != View::RENDER_NONE) {
			// render to variable
			$retval = $this->getEngine()->execute();
			// now render our decorator template, if one exists
			if ($this->isDecorator()) {
				$retval = $this->decorate($retval);
			}

			if ($mode == View::RENDER_CLIENT) {
				echo($retval);
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

		// render the decorator template and return the result
		$decoratorTemplate = $this->getDecoratorDirectory() . '/' . $this->getDecoratorTemplate();

		$this->getEngine()->setTemplate($decoratorTemplate);
		
		// TODO: fix this crap :)
		/*
		define('PHPTAL_FORCE_REPARSE', true);
		$this->getEngine()->_prepared = false;
		$this->getEngine()->_functionName = 0;	
		*/
		// set the template resources
		$this->updateTemplateAttributes();
	

		$retval = $this->getEngine()->execute();

		return $retval;
	}	
	
	/**
	 * Updates template attributes
	 *
	 * @return     void
	 *
	 * @author     Benjamin Muskalla <bm@bmuskalla.de>
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	private function updateTemplateAttributes()
	{
		if($this->extractAttributes()) {
			foreach($this->attributes as $key => $val) {
				$this->getEngine()->set($key, $val);
			}
		} else {
			$this->getEngine()->set('template', $this->attributes);
		}
		$this->getEngine()->set('this', $this);
	}
	
	/**
	 * Function to set if the template variables should be extracted
	 * 
	 * If it set to true (default), you can use attributes/slot/etc like this:
	 * <foo tal:content="content"></foo>
	 * You can overwrite it in your Views and set it to false. Then you have to use this syntax:
	 * <foo tal:content="template/content"></foo>
	 * Keep in mind that there are certain variables names you cannot use with this setting enabled, such as "this" and "repeat".
	 *
	 * @return     boolean
	 *
	 * @author     Benjamin Muskalla <bm@bmuskalla.de>
	 * @since      0.11.0
	 */
	public function extractAttributes()
	{
		 return true;
	}
	 
	/**
	 * Clear all attributes associated with this view.
	 *
	 * @return     void
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.11.0
	 */
	public function clearAttributes ()
	{

		$this->attributes = null;
		$this->attributes = array();

	}
	
	/**
	 * Indicates whether or not an attribute exists.
	 *
	 * @param      string An attribute name.
	 *
	 * @return     bool true, if the attribute exists, otherwise false.
	 *
	 * @author     Bob Zoller <bob@agavi.org>
	 * @since      0.11.0
	 */
	public function hasAttribute ($name)
	{
		return isset($this->attributes[$name]);
	}
	
	/**
	 * Retrieve an attribute.
	 *
	 * @param      string An attribute name.
	 * @param      mixed A default attribute value.
	 *
	 * @return     mixed An attribute value, if the attribute exists, otherwise
	 *                   null.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     Bob Zoller <bob@agavi.org>
	 * @since      0.11.0
	 */
	public function & getAttribute ($name, $default=null)
	{

		$retval =& $default;

		if (isset($this->attributes[$name])) {
			$retval =& $this->attributes[$name];
		}
		return $retval;

	}
	
	/**
	 * Retrieve an array of attribute names.
	 *
	 * @return     array An indexed array of attribute names.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.11.0
	 */
	public function getAttributeNames ()
	{

		return array_keys($this->attributes);

	}
	
	/**
	 * Remove an attribute.
	 *
	 * @param      string An attribute name.
	 *
	 * @return     mixed An attribute value, if the attribute was removed,
	 *                   otherwise null.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.11.0
	 */
	public function & removeAttribute ($name)
	{

		$retval = null;

		if (isset($this->attributes[$name]))
		{

			$retval =& $this->attributes[$name];

			unset($this->attributes[$name]);

		}

		return $retval;

	}
	
	/**
	 * Set an attribute.
	 *
	 * If an attribute with the name already exists the value will be
	 * overridden.
	 *
	 * @param      string An attribute name.
	 * @param      mixed  An attribute value.
	 *
	 * @return     void
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.11.0
	 */
	public function setAttribute($name, $value)
	{

		$this->attributes[$name] = $value;

	}

	/**
	 * Append an attribute.
	 *
	 * If this attribute is already set, convert it to an array and append the
	 * new value.  If not, set the new value like normal.
	 *
	 * @param      string An attribute name.
	 * @param      mixed  An attribute value.
	 *
	 * @return     void
	 *
	 * @author     Bob Zoller <bob@agavi.org>
	 * @since      0.11.0
	 */
	public function appendAttribute($name, $value)
	{

		if (!isset($this->attributes[$name]) || !is_array($this->attributes[$name])) {
			settype($this->attributes[$name], 'array');
		}
		$this->attributes[$name][] = $value;

	}

	/**
	 * Set an attribute by reference.
	 *
	 * If an attribute with the name already exists the value will be
	 * overridden.
	 *
	 * @param      string An attribute name.
	 * @param      mixed  A reference to an attribute value.
	 *
	 * @return     void
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.11.0
	 */
	public function setAttributeByRef($name, &$value)
	{

		$this->attributes[$name] =& $value;

	}

	/**
	 * Append an attribute by reference.
	 * 
	 * If this attribute is already set, convert it to an array and append the
	 * reference to the new value.  If not, set the new value like normal.
	 *
	 * @param      string An attribute name.
	 * @param      mixed  A reference to an attribute value.
	 *
	 * @return     void
	 *
	 * @author     Bob Zoller <bob@agavi.org>
	 * @since      0.11.0
	 */
	public function appendAttributeByRef($name, &$value)
	{

		if (!isset($this->attributes[$name]) || !is_array($this->attributes[$name])) {
			settype($this->attributes[$name], 'array');
		}
		$this->attributes[$name][] =& $value;

	}

	/**
	 * Set an array of attributes.
	 *
	 * If an existing attribute name matches any of the keys in the supplied
	 * array, the associated value will be overridden.
	 *
	 * @param      array An associative array of attributes and their associated
	 *                   values.
	 *
	 * @return     void
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.11.0
	 */
	public function setAttributes ($attributes)
	{

		$this->attributes = array_merge($this->attributes, $attributes);

	}

	/**
	 * Set an array of attributes by reference.
	 *
	 * If an existing attribute name matches any of the keys in the supplied
	 * array, the associated value will be overridden.
	 *
	 * @param      array An associative array of attributes and references to
	 *                   their associated values.
	 *
	 * @return     void
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.11.0
	 */
	public function setAttributesByRef (&$attributes)
	{

		foreach ($attributes as $key => &$value)
		{

			$this->attributes[$key] =& $value;

		}

	}
}


// the following lines are a fix until PHPTAL has been changed so setTemplate() resets prepared and functionName.
// as soon as this is fixed in PHPTAL SVN, we will remove the stub class and move the define and the require into initialize()

if(!defined('PHPTAL_PHP_CODE_DESTINATION')) {
	define('PHPTAL_PHP_CODE_DESTINATION', AG_CACHE_DIR);
}

require_once('PHPTAL.php');

class FixedPHPTAL extends PHPTAL
{
	public function setTemplate($path)
	{
		parent::setTemplate($path);
		$this->_prepared = false;
		$this->_functionName = 0;
	}
}
?>
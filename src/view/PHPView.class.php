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
 * A view that uses PHP to render templates.
 *
 * @package    agavi
 * @subpackage view
 *
 * @author     Sean Kerr <skerr@mojavi.org>
 * @author     Agavi Project <info@agavi.org>
 * @copyright  (c) Authors
 * @since      0.9.0
 *
 * @version    $Id$
 */
abstract class AgaviPHPView extends AgaviView
{

	private
		$attributes = array();

	/**
	 * Clear all attributes associated with this view.
	 *
	 * @return     void
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function clearAttributes ()
	{

		$this->attributes = null;
		$this->attributes = array();

	}

	/**
	 * Loop through all template slots and fill them in with the results of
	 * presentation data.
	 *
	 * @param      string A chunk of decorator content.
	 *
	 * @return     string A decorated template.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function & decorate (&$content)
	{

		// call our parent decorate() method
		parent::decorate($content);

		// alias the attributes array so it's directly accessible to the
		// template
		$template =& $this->attributes;

		// render the decorator template and return the result
		$decoratorTemplate = $this->getDecoratorDirectory() . '/' .
						     $this->getDecoratorTemplate();

		ob_start();

		require($decoratorTemplate);

		$retval = ob_get_contents();

		ob_end_clean();

		return $retval;

	}

	/**
	 * Indicates whether or not an attribute exists.
	 *
	 * @param      string An attribute name.
	 *
	 * @return     bool true, if the attribute exists, otherwise false.
	 *
	 * @author     Bob Zoller <bob@agavi.org>
	 * @since      0.10.0
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
	 * @since      0.9.0
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
	 * @since      0.9.0
	 */
	public function getAttributeNames ()
	{

		return array_keys($this->attributes);

	}

	/**
	 * Retrieve the template engine associated with this view.
	 *
	 * Note: This will return null because PHP itself has no engine reference.
	 *
	 * @return     null
	 */
	public function & getEngine ()
	{

		$retval = null;

		return $retval;

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
	 * @since      0.9.0
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
	 * @since      0.9.0
	 */
	public function & render ()
	{

		$retval = null;

		// execute pre-render check
		$this->preRenderCheck();

		// get the render mode
		$mode = $this->getContext()->getController()->getRenderMode();

		// alias the attributes array so it's directly accessible to the
		// template
		$template =& $this->attributes;

		if ($mode == AgaviView::RENDER_CLIENT && !$this->isDecorator())
		{

			// render directly to the client
			require($this->getDirectory() . '/' . $this->getTemplate());

		} else if ($mode != AgaviView::RENDER_NONE)
		{

			// render to variable
			ob_start();

			require($this->getDirectory() . '/' . $this->getTemplate());

			$retval = ob_get_contents();

			ob_end_clean();

			// now render our decorator template, if one exists
			if ($this->isDecorator())
			{

				$retval =& $this->decorate($retval);

			}

			if ($mode == AgaviView::RENDER_CLIENT)
			{

				echo $retval;

				$retval = null;

			}

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
	 * @since      0.9.0
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
	 * @since      0.10.0
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
	 * @since      0.9.0
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
	 * @since      0.10.0
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
	 * @since      0.9.0
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
	 * @since      0.9.0
	 */
	public function setAttributesByRef (&$attributes)
	{

		foreach ($attributes as $key => &$value)
		{

			$this->attributes[$key] =& $value;

		}

	}

}

?>
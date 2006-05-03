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
 * A view represents the presentation layer of an action. Output can be
 * customized by supplying attributes, which a template can manipulate and
 * display.
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
abstract class AgaviView extends AgaviAttributeHolder
{

	/**
	 * Render the presentation to the client.
	 *
	 * @since      0.9.0
	 */
	const RENDER_CLIENT = 2;

	/**
	 * Do not render the presentation.
	 *
	 * @since      0.9.0
	 */
	const RENDER_NONE = 1;

	/**
	 * Render the presentation to a variable.
	 *
	 * @since      0.9.0
	 */
	const RENDER_VAR = 4;

	private
		$context            = null,
		$decorator          = false,
		$decoratorDirectory = null,
		$decoratorTemplate  = null,
		$directory          = null,
		$slots              = array(),
		$template           = null;


	/**
	 * Execute any presentation logic and set template attributes.
	 *
	 * @return     void
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	abstract function execute ();

	/**
	 * Retrieve the current application context.
	 *
	 * @return     AgaviContext The current Context instance.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public final function getContext ()
	{

		return $this->context;

	}

	/**
	 * Retrieve this views decorator template directory.
	 *
	 * @return     string An absolute filesystem path to this views decorator
	 *                    template directory.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function getDecoratorDirectory ()
	{

		return $this->decoratorDirectory;

	}

	/**
	 * Retrieve this views decorator template.
	 *
	 * @return     string A template filename, if a template has been set,
	 *                    otherwise null.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function getDecoratorTemplate ()
	{

		return $this->decoratorTemplate;

	}

	/**
	 * Retrieve this views template directory.
	 *
	 * @return     string An absolute filesystem path to this views template
	 *                    directory.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function getDirectory ()
	{

		return $this->directory;

	}

	/**
	 * Retrieve an array of specified slots for the decorator template.
	 *
	 * @return     array An associative array of decorator slots.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function getSlots ()
	{

		return $this->slots;

	}

	/**
	 * Retrieve this views template.
	 *
	 * @return     string A template filename, if a template has been set,
	 *                    otherwise null.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function getTemplate ()
	{

		return $this->template;

	}

	/**
	 * Import parameter values and error messages from the request directly as
	 * view attributes.
	 *
	 * @param      array An indexed array of file/parameter names.
	 * @param      bool  Is this a list of files?
	 * @param      bool  Import error messages too?
	 * @param      bool  Run strip_tags() on attribute value?
	 * @param      bool  Run htmlspecialchars() on attribute value?
	 *
	 * @return     void
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function importAttributes ($names, $files = false, $errors = true,
						              $stripTags = true, $specialChars = true)
	{

		// alias $request to keep the code clean
		$request = $this->context->getRequest();

		// get our array
		if ($files)
		{

			// file names
			$array =& $request->getFiles();

		} else
		{

			// parameter names
			$array =& $request->getParameters();

		}

		// loop through our parameter names and import them
		foreach ($names as &$name)
		{

			if (preg_match('/^([a-z0-9\-_]+)\{([a-z0-9\s\-_]+)\}$/i',
						   $name, $match))
			{

				// we have a parent
				$parent  = $match[1];
				$subname = $match[2];

				// load the file/parameter value for this attribute if one
				// exists
				if (isset($array[$parent]) && isset($array[$parent][$subname]))
				{

				    $value = $array[$parent][$subname];

				    if ($stripTags)
				    {

						$value = strip_tags($value);

				    }

				    if ($specialChars)
				    {

						$value = htmlspecialchars($value);

				    }

				    $this->setAttribute($name, $value);

				} else
				{

				    // set an empty value
				    $this->setAttribute($name, '');

				}

			} else
			{

				// load the file/parameter value for this attribute if one
				// exists
				if (isset($array[$name]))
				{

				    $value = $array[$name];

				    if ($stripTags)
				    {

						$value = strip_tags($value);

				    }

				    if ($specialChars)
				    {

						$value = htmlspecialchars($value);

				    }

				    $this->setAttribute($name, $value);

				} else
				{

				    // set an empty value
				    $this->setAttribute($name, '');

				}

			}

			if ($errors)
			{

				if ($request->hasError($name))
				{

				    $this->setAttribute($name . '_error',
						                $request->getError($name));

				} else
				{

				    // set empty error
				    $this->setAttribute($name . '_error', '');

				}

			}

		}

	}

	/**
	 * Initialize this view.
	 *
	 * @param      AgaviContext The current application context.
	 *
	 * @return     bool true, if initialization completes successfully,
	 *                  otherwise false.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function initialize ($context)
	{

		$this->context = $context;

		// set the currently executing module's template directory as the
		// default template directory
		$this->decoratorDirectory = $context->getModuleDirectory() .'/templates';

		$this->directory          = $this->decoratorDirectory;

		return true;

	}

	/**
	 * Indicates that this view is a decorating view.
	 *
	 * @return     bool true, if this view is a decorating view, otherwise 
	 *                  false.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function isDecorator ()
	{

		return $this->decorator;

	}

	/**
	 * Set the decorator template directory for this view.
	 *
	 * @param      string An absolute filesystem path to a template directory.
	 *
	 * @return     void
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function setDecoratorDirectory ($directory)
	{

		$this->decoratorDirectory = $directory;

	}

	/**
	 * Set the decorator template for this view.
	 *
	 * If the template path is relative, it will be based on the currently
	 * executing module's template sub-directory.
	 *
	 * @param      string An absolute or relative filesystem path to a template.
	 *
	 * @return     void
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function setDecoratorTemplate ($template)
	{

		if (AgaviToolkit::isPathAbsolute($template))
		{

			$this->decoratorDirectory = dirname($template);
			$this->decoratorTemplate  = basename($template);

		} else
		{

			$this->decoratorTemplate = $template;

		}

		// set decorator status
		$this->decorator = true;

	}

	/**
	 * Clears out a previously assigned decorator template and directory
	 *
	 * @return     void
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.10.0
	 */
	public function clearDecorator()
	{
		$this->decoratorDirectory = null;
		$this->decoratorTemplate  = null;
		$this->decorator = false;
	}

	/**
	 * Set the template directory for this view.
	 *
	 * @param      string An absolute filesystem path to a template directory.
	 *
	 * @return     void
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function setDirectory ($directory)
	{

		$this->directory = $directory;

	}

	/**
	 * Set the module and action to be executed in place of a particular
	 * template attribute.
	 *
	 * If a slot with the name already exists, it will be overridden.
	 *
	 * @param      string A template attribute name.
	 * @param      string A module name.
	 * @param      string An action name.
	 *
	 * @return     void
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function setSlot ($attributeName, $moduleName, $actionName)
	{

		$this->slots[$attributeName]                = array();
		$this->slots[$attributeName]['module_name'] = $moduleName;
		$this->slots[$attributeName]['action_name'] = $actionName;

	}

	/**
	 * Set an array of slots
	 *
	 * @see        AgaviView::setSlot()
	 * @param      array An array of slots
	 *
	 * @return     void
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.10.0
	 */
	public function setSlots($slots)
	{
		$this->slots = $slots;
	}

	/**
	 * Empties the slots array, clearing all previously registered slots
	 *
	 * @return     void
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.10.0
	 */
	public function clearSlots()
	{
		$this->slots = array();
	}

	/**
	 * Set the template for this view.
	 *
	 * If the template path is relative, it will be based on the currently
	 * executing module's template sub-directory.
	 *
	 * @param      string An absolute or relative filesystem path to a template.
	 *
	 * @return     void
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function setTemplate ($template)
	{

		if (AgaviToolkit::isPathAbsolute($template))
		{

			$this->directory = dirname($template);
			$this->template  = basename($template);

		} else
		{

			$this->template = $template;

		}

	}

}

?>
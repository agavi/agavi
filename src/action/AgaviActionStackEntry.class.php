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
 * ActionStackEntry represents information relating to a single Action request
 * during a single HTTP request.
 *
 * @package    agavi
 * @subpackage action
 *
 * @author     Sean Kerr <skerr@mojavi.org>
 * @copyright  (c) Authors
 * @since      0.9.0
 *
 * @version    $Id$
 */
class AgaviActionStackEntry
{
	/**
	 * @var        AgaviAction The Action instance that belongs to this entry.
	 */
	private $actionInstance = null;
	
	/**
	 * @var        string The name of the Action.
	 */
	private $actionName     = null;
	
	/**
	 * @var        float The microtime at which this entry was created.
	 */
	private $microtime      = null;
	
	/**
	 * @var        string The name of the Action's Module.
	 */
	private $moduleName     = null;
	
	/**
	 * @var        AgaviParameterHolder A ParameterHoler instance containing the
	 *                                  request parameters for this action.
	 */
	private $parameters     = null;
	
	/**
	 * @var        AgaviResponse A response instance holding the Action's output.
	 */
	private $presentation   = null;
	
	/**
	 * @var        array Information about the next Action to be executed, if any.
	 */
	private $next           = null;
	
	/**
	 * @var        string The name of the View returned by the Action.
	 */
	private $viewName       = null;
	
	/**
	 * @var        string Name of the module of the View returned by the Action.
	 */
	private $viewModuleName = null;
	
	/**
	 * Class constructor.
	 *
	 * @param      string               A module name.
	 * @param      string               An action name.
	 * @param      AgaviAction          An action implementation instance.
	 * @param      AgaviParameterHolder A ParameterHoler instance containing the
	 *                                  request parameters for this action.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function __construct($moduleName, $actionName, AgaviAction $actionInstance, AgaviParameterHolder $parameters)
	{
		
		$this->actionName = $actionName;
		$this->actionInstance = $actionInstance;
		$this->microtime = microtime(true);
		$this->moduleName = $moduleName;
		$this->parameters = $parameters;
		
	}
	
	/**
	 * Retrieve this entry's action name.
	 *
	 * @return     string An action name.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function getActionName()
	{
		return $this->actionName;
	}
	
	/**
	 * Retrieve this entry's action instance.
	 *
	 * @return     AgaviAction An action implementation instance.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function getActionInstance()
	{
		return $this->actionInstance;
	}
	
	/**
	 * Retrieve this entry's microtime.
	 *
	 * @return     string A string representing the microtime this entry was
	 *                    created.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function getMicrotime()
	{
		return $this->microtime;
	}
	
	/**
	 * Retrieve this entry's module name.
	 *
	 * @return     string A module name.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function getModuleName()
	{
		return $this->moduleName;
	}
	
	/**
	 * Retrieve this entry's module name.
	 *
	 * @return     string A module name.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getViewName()
	{
		return $this->viewName;
	}
	
	/**
	 * Retrieve this entry's module name.
	 *
	 * @return     string A module name.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getViewModuleName()
	{
		return $this->viewModuleName;
	}
	
	/**
	 * Retrieve this entry's module name.
	 *
	 * @return     string A module name.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function setViewName($viewName)
	{
		$this->viewName = $viewName;
	}
	
	/**
	 * Retrieve this entry's module name.
	 *
	 * @return     string A module name.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function setViewModuleName($viewModuleName)
	{
		$this->viewModuleName = $viewModuleName;
	}
	
	/**
	 * Retrieve the request parameters for this Action.
	 *
	 * @return     AgaviParameterHolder An AgaviParameterHolder of request parameters for this Action.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getParameters()
	{
		return $this->parameters;
	}
	
	/**
	 * Set the request parameters for this Action.
	 *
	 * @param      AgaviParameterHolder The request parameters for this Action.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function setParameters(AgaviParameterHolder $parameters)
	{
		$this->parameters = $parameters;
	}
	
	/**
	 * Retrieve this entry's rendered view presentation.
	 *
	 * This will only exist if the view has processed and the render mode
	 * is set to AgaviView::RENDER_VAR.
	 *
	 * @return     AgaviResponse The Response instance for this action.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.9.0
	 */
	public function getPresentation()
	{
		return $this->presentation;
	}
	
	/**
	 * Set the rendered presentation for this action.
	 *
	 * @param      AgaviResponse A response holding the rendered presentation.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function setPresentation(AgaviResponse $presentation)
	{
		$this->presentation = $presentation;
	}
	
	/**
	 * Set the next entry that will be run after this Action finished.
	 *
	 * @param      string The Module name of the Action to execute next.
	 * @param      string The name of the Action to execute next.
	 * @param      mixed  An AgaviParameterHolder instance or an array holding
	 *                    request parameters to pass to that Action.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function setNext($moduleName, $actionName, array $parameters = array())
	{
		$this->next = array('moduleName' => $moduleName, 'actionName' => $actionName, 'parameters' => $parameters);
	}
	
	/**
	 * Check if this Action or a View specified another Action to run next.
	 *
	 * @return     bool Whether or not a next Action has been set.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function hasNext()
	{
		return is_array($this->next);
	}
	
	/**
	 * Get the Action that should be run after this one finished execution.
	 *
	 * @return     array An associative array of information on the next Action.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function getNext()
	{
		return $this->next;
	}
}

?>
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
 * Proxies a project from an external build file.
 *
 * @package    agavi
 * @subpackage build
 *
 * @author     Noah Fontes <noah.fontes@bitextender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      1.0.0
 *
 * @version    $Id$
 */
class AgaviProxyProject extends Project
{
	/**
	 * @var        Project The Phing Project instance to be proxied.
	 */
	protected $proxied = null;
	
	/**
	 * @var        bool Whether the proxied configuration has been copied.
	 */
	protected $copied = false;
	
	/**
	 * @var        array A List of properties to set on the proxy.
	 */
	protected static $protectedProperties = array(
		'phing.file',
		'basedir',
		'project.basedir'
	);
	
	/**
	 * Creates a new proxied project.
	 *
	 * @param      Project The project to proxy.
	 *
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 * @since      1.0.0
	 */
	public function __construct(Project $proxied)
	{
		$this->proxied = $proxied;
		
		parent::__construct();
	}
	
	/**
	 * Initializes this project.
	 *
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 * @since      1.0.0
	 */
	public function init()
	{
	}

	/**
	 * Performs initialization associated with a parsing configurator starting.
	 *
	 * @param      ProjectConfigurator The new configurator.
	 *
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 * @since      1.0.4
	 */
	public function fireConfigureStarted($configurator)
	{
		if(!$this->copied) {
			$this->copy();
			$this->copied = true;
		}
	}

	/**
	 * Performs any cleanup associated with the current configurator finishing.
	 *
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 * @since      1.0.4
	 */
	public function fireConfigureFinished()
	{
	}
	
	/**
	 * Determines whether a given property is protected (that is, should not be
	 * copied from one project to the other).
	 *
	 * @param      string The name of the property.
	 *
	 * @return     bool True if the property is protected, false otherwise.
	 *
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 * @since      1.0.0
	 */
	public static function isPropertyProtected($property)
	{
		return in_array($property, self::$protectedProperties);
	}
	
	/**
	 * Sets a property. Proxies the request to the underlying project.
	 *
	 * @param      string The name of the property.
	 * @param      mixed The value of the property.
	 *
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 * @since      1.0.0
	 */
	public function setProperty($name, $value)
	{
		if(!self::isPropertyProtected($name)) {
			$this->proxied->setProperty($name, $value);
		}
		parent::setProperty($name, $value);
	}
	
	/**
	 * Sets a new property. Proxies the request to the underlying project.
	 *
	 * @param      string The name of the property.
	 * @param      mixed The value of the property.
	 */
	public function setNewProperty($name, $value)
	{
		if(!self::isPropertyProtected($name)) {
			$this->proxied->setNewProperty($name, $value);
		}
		parent::setNewProperty($name, $value);
	}
	
	/**
	 * Sets a user property. Proxies the request to the underlying project.
	 *
	 * @param      string The name of the property.
	 * @param      mixed The value of the property.
	 *
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 * @since      1.0.0
	 */
	public function setUserProperty($name, $value)
	{
		if(!self::isPropertyProtected($name)) {
			$this->proxied->setUserProperty($name, $value);
		}
		parent::setUserProperty($name, $value);
	}
	
	/**
	 * Sets an inherited property. Proxies the request to the underlying project.
	 *
	 * @param      string The name of the property.
	 * @param      mixed The value of the property.
	 *
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 * @since      1.0.0
	 */
	public function setInheritedProperty($name, $value)
	{
		if(!self::isPropertyProtected($name)) {
			$this->proxied->setInheritedProperty($name, $value);
		}
		parent::setInheritedProperty($name, $value);
	}
	
	/**
	 * Adds a new task to the project. Proxies the request to the underlying
	 * project.
	 *
	 * @param      string The name of the task.
	 * @param      string The name of the class.
	 * @param      string The classpath to use when resolving the class.
	 *
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 * @since      1.0.0
	 */
	public function addTaskDefinition($name, $class, $classpath = null)
	{
		$this->proxied->addTaskDefinition($name, $class, $classpath);
		parent::addTaskDefinition($name, $class, $classpath);
	}
	
	/**
	 * Adds a new datatype to the project. Proxies the request to the underlying
	 * project.
	 *
	 * @param      string The name of the task.
	 * @param      string The name of the class.
	 * @param      string The classpath to use when resolving the class.
	 *
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 * @since      1.0.0
	 */
	public function addDataTypeDefinition($name, $class, $classpath = null)
	{
		$this->proxied->addDataTypeDefinition($name, $class, $classpath);
		parent::addDataTypeDefinition($name, $class, $classpath);
	}
	
	/**
	 * Adds a new build listener to the project. Proxies the request to the
	 * underlying project.
	 *
	 * @param      BuildListener The build listener to add.
	 *
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 * @since      1.0.0
	 */
	public function addBuildListener(BuildListener $listener)
	{
		$this->proxied->addBuildListener($listener);
		parent::addBuildListener($listener);
	}

	/**
	 * Copies the configuration from the proxied project into this project.
	 *
	 * @author     Noah Fontes <noah.fontes@bitextender.com>
	 * @since      1.0.4
	 */
	protected function copy()
	{
		foreach($this->proxied->getBuildListeners() as $listener) {
			parent::addBuildListener($listener);
		}
		$this->setInputHandler($this->proxied->getInputHandler());
		foreach($this->proxied->getTaskDefinitions() as $name => $class) {
			parent::addTaskDefinition($name, $class);
		}
		foreach($this->proxied->getDataTypeDefinitions() as $name => $class) {
			parent::addDataTypeDefinition($name, $class);
		}
		
		/* Assign properties for consistency. */
		$this->proxied->copyUserProperties($this);
		$this->proxied->copyInheritedProperties($this);
		foreach($this->proxied->getProperties() as $name => $property) {
			if(!AgaviProxyProject::isPropertyProtected($name) && $this->getProperty($name) === null) {
				parent::setNewProperty($name, $property);
			}
		}
		
		/* Add proxy targets to the new project. */
		foreach($this->proxied->getTargets() as $name => $target) {
			$proxy = new AgaviProxyTarget();
			$proxy->setName($name);
			$proxy->setDescription($target->getDescription());
			$proxy->setTarget($target);
			parent::addTarget($name, $proxy);
		}
		
		parent::setUserProperty('phing.version', $this->proxied->getProperty('phing.version'));
		$this->setSystemProperties();
	}
}

?>
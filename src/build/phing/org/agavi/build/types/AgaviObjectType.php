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

require_once(dirname(__FILE__) . '/AgaviType.php');

/**
 * Represents an object that persists across the build session.
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
class AgaviObjectType extends AgaviType
{
	protected $classname = null;
	protected $classpath = null;
	protected $instance = null;
	
	/**
	 * Sets the name of the class.
	 *
	 * @param      string The name of the class.
	 */
	public function setClassname($classname)
	{
		$this->classname = $classname;
	}
	
	/**
	 * Gets the name of the class.
	 *
	 * @return     string The name of the class.
	 */
	public function getClassname()
	{
		return $this->classname;
	}
	
	/**
	 * Sets the classpath for resolving the class.
	 *
	 * @param      Path The classpath.
	 */
	public function setClasspath(Path $classpath)
	{
		if($this->classpath === null) {
			$this->classpath = $classpath;
		} else {
			$this->classpath->append($classpath);
		}
	}
	
	/**
	 * Gets the classpath.
	 *
	 * @return     Path The classpath.
	 */
	public function getClasspath()
	{
		return $this->classpath;
	}
	
	/**
	 * Creates a classpath to be used for resolving the class.
	 */
	public function createClasspath()
	{
		if($this->classpath === null) {
			$this->classpath = new Path($this->project);
		}
		return $this->classpath->createPath();
	}
	
	/**
	 * Sets a reference to a classpath for resolving the class.
	 *
	 * @param     Reference A reference to a classpath.
	 */
	public function setClasspathref(Reference $reference)
	{
		$this->createClasspath()->setRefid($reference);
	}
	
	/**
	 * Gets an instance of the object.
	 *
	 * @return     object An instance of the object associated with this type.
	 */
	public function getInstance()
	{
		if($this->classname === null) {
			throw new BuildException('The classname attribute must be specified');
		}
		
		if($this->instance === null) {
			try {
				Phing::import($this->classname, $this->classpath);
			}
			catch (BuildException $be) {
				/* Cannot import the file. */
				throw new BuildException(sprintf('Object from class %s cannot be instantiated', $this->classname), $be);
			}
			
			$class = $this->classname;
			$this->instance = new $class();
		}
		return $this->instance;
	}
	
	/**
	 * Returns the referenced object type.
	 *
	 * @return     AgaviObjectType The object type.
	 */
	public function getRef(Project $project)
	{
		if(!$this->checked) {
			$stack = array($this);
			$this->dieOnCircularReference($stack, $project);
		}
		
		$object = $this->ref->getReferencedObject($project);
		if(!$object instanceof AgaviObjectType) {
			throw new BuildException(sprintf('%s is not an instance of %s', $this->ref->getRefId(), get_class()));
		}
		
		return $object;
	}
}

?>
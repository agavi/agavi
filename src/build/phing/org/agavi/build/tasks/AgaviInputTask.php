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

require_once(dirname(__FILE__) . '/AgaviTask.php');
require_once('phing/input/InputRequest.php');

/**
 * Requests an input value from the user.
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
class AgaviInputTask extends AgaviTask
{
	protected $property = null;
	protected $message = '';
	protected $default = null;
	protected $promptCharacter = '?';
	protected $ignoreIfSet = false;
	protected $failIfEmpty = false;
	protected $useExistingAsDefault = true;
	
	/**
	 * Sets the property to which this task will write.
	 *
	 * @param      string The name of the property.
	 */
	public function setProperty($property)
	{
		$this->property = $property;
	}
	
	/**
	 * Sets the message that is shown to the user.
	 *
	 * @param      string The message to show to the user.
	 */
	public function setMessage($message)
	{
		$this->message = $message;
	}
	
	/**
	 * Appends to the message that is shown to the user.
	 *
	 * @param      string The text to append.
	 */
	public function addText($text)
	{
		$this->message .= $this->project->replaceProperties($text);
	}
	
	/**
	 * Sets the default value for the property.
	 *
	 * @param      string The default property value.
	 */
	public function setDefault($default)
	{
		$this->default = $default;
	}
	
	/**
	 * Sets the prompt character.
	 *
	 * @param      string The prompt character.
	 */
	public function setPromptCharacter($promptCharacter)
	{
		$this->promptCharacter = $promptCharacter;
	}
	
	/**
	 * Sets whether to ignore this prompt if the property is already set.
	 *
	 * @param      bool Whether to bypass the prompt if the property is
	 *                  set.
	 */
	public function setIgnoreIfSet($ignoreIfSet)
	{
		$this->ignoreIfSet = StringHelper::booleanValue($ignoreIfSet);
	}
	
	/**
	 * Sets whether to fail if the property is empty by the end of the
	 * task's execution.
	 *
	 * @param      bool Whether to fail if the property is empty.
	 */
	public function setFailIfEmpty($failIfEmpty)
	{
		$this->failIfEmpty = StringHelper::booleanValue($failIfEmpty);
	}
	
	/**
	 * Sets whether to use the existing value for the property as the default
	 * value for the prompt.
	 *
	 * @param      bool Whether to use the existing property value as the
	 *                  default.
	 */
	public function setUseExistingAsDefault($useExistingAsDefault)
	{
		$this->useExistingAsDefault = StringHelper::booleanValue($useExistingAsDefault);
	}

	/**
	 * Executes this task.
	 */
	public function main()
	{
		if($this->property === null) {
			throw new BuildException('The property attribute must be specified');
		}
		if($this->message === '') {
			throw new BuildException('The message attribute must be specified or the element must contain a message');
		}
		
		if($this->ignoreIfSet && $this->project->getProperty($this->property) !== null) {
			if($this->failIfEmpty) {
				if($this->project->getProperty($this->property) != '') {
					return;
				}
			} else {
				return;
			}
		}
		
		$request = new InputRequest($this->message);
		$request->setPromptChar($this->promptCharacter);
		
		if($this->useExistingAsDefault === true) {
			$request->setDefaultValue($this->project->getProperty($this->property));
		}
		if($this->default !== null) {
			$request->setDefaultValue($this->default);
		}
		
		$this->project->getInputHandler()->handleInput($request);
		
		$result = $request->getInput();
		
		if($this->failIfEmpty && $result == '') {
			throw new BuildException('Input value cannot be empty');
		}
		
		$this->project->setUserProperty($this->property, $result);
	}
}

?>
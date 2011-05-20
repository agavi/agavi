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
require_once(dirname(__FILE__) . '/AgaviTransformstringtoidentifierTask.php');

/**
 * Transforms a string into an identifier suitable for use in PHP in the same
 * way as <code>AgaviTransformstringtoidentifierTask</code>, but allows for
 * ASCII character 0x2E as a namespace separator.
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
class AgaviTransformnamespacestringtoidentifierTask extends AgaviTransformstringtoidentifierTask
{
	/**
	 * Executes the task.
	 */
	public function main()
	{
		if($this->property === null) {
			throw new BuildException('The property attribute must be specified');
		}
		if($this->string === null || strlen($this->string) === 0) {
			throw new BuildException('The string attribute must be specified and must be non-empty');
		}

		$transformables = explode('.', $this->string);
		foreach($transformables as &$transformable) {
			$transform = new AgaviIdentifierTransform();
			$transform->setInput($transformable);

			$transformable = $transform->transform();
		}

		$this->project->setUserProperty($this->property, implode('.', $transformables));
	}
}

?>
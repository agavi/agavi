<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2005-2008 the Agavi Project.                                |
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
 * AgaviViewTestCase is the base class for all view testcases and provides
 * the necessary assertions
 * 
 * 
 * @package    agavi
 * @subpackage testing
 *
 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
 * @copyright  The Agavi Project
 *
 * @since      1.0.0
 *
 * @version    $Id$
 */
abstract class AgaviViewTestCase extends AgaviFragmentTestCase
{
	protected $viewResult;
	
	protected function createViewInstance()
	{
		$this->getContext()->getController()->initializeModule($this->moduleName);
		$viewName = $this->normalizeViewName($this->viewName);
		$viewInstance = $this->getContext()->getController()->createViewInstance($this->moduleName, $viewName);
		$viewInstance->initialize($this->container);
		return $viewInstance;
	}
	
	protected function runView($otName = null)
	{
		$this->container->setOutputType($this->getContext()->getController()->getOutputType($otName));
		$this->container->setViewInstance($this->createViewInstance());
		$executionFilter = $this->createExecutionFilter();
		$this->viewResult = $executionFilter->executeView($this->container);
	}
	
	/**
	 * assert that the view handles the given output type
	 * 
	 * @param      string  the output type name
	 * @param      boolean true if the generic 'execute' method should be accepted as handled
	 * @param      string  an optional message to display if the test fails
	 * 
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 * @since      1.0.0
	 */
	protected function assertHandlesOutputType($method, $acceptGeneric = false, $message = '')
	{
		$viewInstance = $this->createViewInstance();
		$constraint = new AgaviConstraintViewHandlesOutputType($viewInstance, $acceptGeneric);
		
		self::assertThat($method, $constraint, $message);
	}
	
	/**
	 * assert that the view does not handle the given output type
	 * 
	 * @param      string  the output type name
	 * @param      boolean true if the generic 'execute' method should be accepted as handled
	 * @param      string  an optional message to display if the test fails
	 * 
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 * @since      1.0.0
	 */
	protected function assertNotHandlesOutputType($method, $acceptGeneric = false, $message = '')
	{
		$viewInstance = $this->createViewInstance();
		$constraint = self::logicalNot(new AgaviConstraintViewHandlesOutputType($viewInstance, $acceptGeneric));
		
		self::assertThat($method, $constraint, $message);
	}
	
	
	protected function assertResponseHasRedirect($expected, $message = '')
	{
		
	}
	
	protected function assertResponseHasContent($expected, $message = '')
	{
		
	}
	
	protected function assertForwards($expectedModule, $expectedAction, $message = '')
	{
		if (!($this->viewResult instanceof AgaviExecutionContainer))
		{
			$this->fail('Failed asserting that the view result is a forward');
		}
	}
	
	protected function assertHasLayer($expectedLayer, $message = '')
	{
		$viewInstance = $this->container->getViewInstance();
		$layer = $viewInstance->getLayer($expectedLayer);
		
		if (null == $layer)
		{
			$this->fail('Failed asserting that the view contains the layer');
		}
	}
	
}

?>
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
 * AgaviActionTestCase is the base class for all action testcases and provides
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
abstract class AgaviActionTestCase extends AgaviFragmentTestCase
{
	/**
	 * @var        string the name of the action to test
	 */
	protected $actionName;
	
	/**
	 * @var        string the name of the module 
	 */
	protected $moduleName;
	
	/**
	 * @var        string the name of the resulting view
	 */
	protected $viewName;
	
	/**
	 * @var        string the name of the resulting view's module
	 */
	protected $viewModuleName;
	
	/**
	 * @var        bool   the result of the validation process
	 */
	protected $validationSuccess;
	
	/**
	 * @var        AgaviExecutionContainer the container to run the action in
	 */
	protected $container;
	
	/**
	 * creates a new AgaviExecutionContainer for each test
	 * 
	 * @return void
	 * 
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 * @since      1.0.0
	 */
	public function setUp()
	{
		$this->container = $this->createExecutionContainer();
	}
	
	
	/**
	 * unsets the AgaviExecutionContainer after each test
	 * 
	 * @return void
	 * 
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 * @since      1.0.0
	 */
	public function tearDown()
	{
		$this->container = null;
	}
	
	/**
	 * creates an Action instance and initializes it with this testcases
	 * container
	 * 
	 * @return     AgaviAction
	 * 
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 * @since      1.0.0
	 */
	protected function createActionInstance()
	{
		$actionInstance = $this->getContext()->getController()->createActionInstance($this->moduleName, $this->actionName);
		$actionInstance->initialize($this->container);
		return $actionInstance;
	}
	
	/**
	 * run the action for this testcase
	 *  
	 * @return     void
	 * 
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 * @since      1.0.0
	 */ 
	protected function runAction()
	{
		$this->container->setActionInstance($this->createActionInstance());
		$executionFilter = $this->createExecutionFilter();
		list($this->viewModuleName, $this->viewName) = $executionFilter->runAction($this->container);
	}
	
	/**
	 * register the validators for this testcase
	 *  
	 * @return     void
	 * 
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 * @since      1.0.0
	 */ 
	protected function performValidation()
	{
		$this->container->setActionInstance($this->createActionInstance());
		$this->validationSuccess = $this->container->performValidation($this->container);
	}
	
	
	/**
	 * create a requestDataHolder with the given arguments and type
	 * 
	 * arguments need to be passed in the way {@see AgaviRequestDataHolder} accepts them
	 * 
	 * array(AgaviRequestDataHolder::SOURCE_PARAMETERS => array('foo' => 'bar'))
	 * 
	 * if no type is passed, the default for the configured request class will be used
	 * 
	 * @param      array   a two-dimensional array with the arguments
	 * @param      string  the subclass of AgaviRequestDataHolder to create
	 * 
	 * @return     AgaviRequestDataHolder
	 * 
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 * @since      1.0.0
	 */
	protected function createRequestDataHolder(array $arguments = array(), $type = null)
	{
		if(null === $type) {
			$type = $this->getContext()->getRequest()->getParameter('request_data_holder_class', 'AgaviRequestDataHolder');
		}
		
		$class = new $type($arguments);
		return $class;
	}
	
	/**
	 * asserts that the viewName is the expected value after runAction was called
	 * 
	 * @param      string the expected viewname in short form ('Success' etc)
	 * @param      string an optional message to display if the test fails
	 * 
	 * @return     void
	 * 
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 * @since      1.0.0 
	 */
	protected function assertViewNameEquals($expected, $message = '')
	{
		if($expected != AgaviView::NONE) {
			$expected = AgaviToolkit::expandVariables(
				AgaviToolkit::expandDirectives(
					AgaviConfig::get(
						sprintf('modules.%s.agavi.view.name', strtolower($this->moduleName)),
						'${actionName}${viewName}'
					)
				),
				array(
					'actionName' => $this->actionName,
					'viewName' => $expected,
				)	
			);	
		}
		$this->assertEquals($expected, $this->viewName, $message);
	}
	
	/**
	 * asserts that the view's modulename is the expected value after runAction was called
	 * 
	 * @param      string the expected moduleName 
	 * @param      string an optional message to display if the test fails
	 * 
	 * @return     void
	 * 
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 * @since      1.0.0 
	 */
	protected function assertViewModuleNameEquals($expected, $message = '')
	{
		$this->assertEquals($expected, $this->viewModuleName, $message);
	}
	
	/**
	 * asserts that the DefaultView is the expected 
	 * 
	 * @param     mixed A string containing the view name associated with the
	 *                   action.
	 *                   Or an array with the following indices:
	 *                   - The parent module of the view that will be executed.
	 *                   - The view that will be executed.
	 *
	 * @param      string an optional message to display if the test fails
	 * 
	 * @return     void
	 * 
	 * @see        AgaviAction::getDefaultViewName()
	 * 
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 * @since      1.0.0 
	 */
	protected function assertDefaultView($expected, $message = '')
	{
		$actionInstance = $this->createActionInstance();
		$this->assertEquals($expected, $actionInstance->getDefaultViewName(), $message);
	}
	
	/**
	 * assert that the exectionContainer has a given attribute with the expected value
	 * 
	 * @param      mixed   the expected attribute value
	 * @param      string  the attribute name
	 * @param      string  the attribute namespace
	 * @param      string  an optional message to display if the test fails
     * @param      float   $delta
     * @param      integer $maxDepth
     * @param      boolean $canonicalizeEol
     * 
     * @see        PHPUnit_Framework_Assert::assertEquals()
	 * 
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 * @since      1.0.0
	 */
	protected function assertContainerAttributeEquals($expected, $attributeName, $namespace = null, $message = '', $delta = 0, $maxDepth = 10, $canonicalizeEol = false)
	{
		$this->assertEquals($expected, $this->container->getAttribute($attributeName, $namespace), $message, $delta, $maxDepth, $canonicalizeEol);
	}
	
	/**
	 * assert that the exectionContainer has a given attribute 
	 * 
	 * @param      string  the attribute name
	 * @param      string  the attribute namespace
	 * @param      string  an optional message to display if the test fails
     * 
     * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 * @since      1.0.0
	 */
	protected function assertContainerAttributeExists($attributeName, $namespace = null, $message = '')
	{
		$this->assertTrue($this->container->hasAttribute($attributeName, $namespace), $message);
	}
	
	/**
	 * assert that the action handles the given request method
	 * 
	 * @param      string  the method name
	 * @param      boolean true if the generic 'execute' method should be accepted as handled
	 * @param      string  an optional message to display if the test fails
	 * 
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 * @since      1.0.0
	 */
	protected function assertHandlesMethod($method, $acceptGeneric = true, $message = '')
	{
		$actionInstance = $this->createActionInstance();
		$constraint = new AgaviConstraintActionHandlesMethod($actionInstance, $acceptGeneric);
		
		self::assertThat($method, $constraint, $message);
	}
	
	/**
	 * assert that the action does not handle the given request method
	 * 
	 * @param      string  the method name
	 * @param      boolean true if the generic 'execute' method should be accepted as handled
	 * @param      string  an optional message to display if the test fails
	 * 
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 * @since      1.0.0
	 */
	protected function assertNotHandlesMethod($method, $acceptGeneric = true, $message = '')
	{
		$actionInstance = $this->createActionInstance();
		$constraint = self::logicalNot(new AgaviConstraintActionHandlesMethod($actionInstance, $acceptGeneric));
		
		self::assertThat($method, $constraint, $message);
	}
	
	/**
	 * assert that the action is simple
	 * 
	 * @param      string  an optional message to display if the test fails
	 * 
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 * @since      1.0.0
	 */
	protected function assertIsSimple($message = '')
	{
		$actionInstance = $this->createActionInstance();
		$this->assertTrue($actionInstance->isSimple(), $message);
	}
	
	/**
	 * assert that the action is not simple
	 * 
	 * @param      string  an optional message to display if the test fails
	 * 
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 * @since      1.0.0
	 */
	protected function assertIsNotSimple($message = '')
	{
		$actionInstance = $this->createActionInstance();
		$this->assertFalse($actionInstance->isSimple(), $message);
	}

	protected function assertValidatesArgument($argumentName, $type = null, $validationParameters = null, $source = AgaviRequestDataHolder::SOURCE_PARAMETERS)
	{
		$found = false;
		foreach ($this->container->getValidationManager()->getChilds() as $child)
		{
			if (in_array($argumentName, $child->getArguments()))
			{
				$found = true;
			}
		}
		
		if (!$found)
		{
			$this->fail('does not validate argument');
		}
	}
	
	/**
	 * create an executionfilter for the test
	 * 
	 * the configured executionfilter class will be wrapped in a testing
	 * extension to provide advanced capabilities required for testing 
	 * only
	 * 
	 * @return     AgaviExecutionFilter 
	 * 
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 * @since      1.0.0
	 */
	protected function createExecutionFilter()
	{
		$effi = $this->getContext()->getFactoryInfo('execution_filter');
		
		$wrapper_class = $effi['class'].'UnitTesting';
		
		//extend the original class to overwrite runAction, so that the containers request data is cloned
		if(!class_exists($wrapper_class)) {
			$code = sprintf('
class %1$s extends %2$s
{
	protected $validationResult = null;
	
	public function performValidation(AgaviExecutionContainer $container)
	{	
		return  parent::performValidation($container);
	}
	
	public function runAction(AgaviExecutionContainer $container)
	{
		$container->cloneArgumentsToRequestData();
		return parent::runAction($container);
	}
}',
			$wrapper_class,
			$effi['class']);
		
			eval($code);
		}
		
		// create a new execution container with the wrapped class
		$filter = new $wrapper_class();
		$filter->initialize($this->getContext(), $effi['parameters']);
		return $filter;
	}
	
	/**
	 * create an AgaviExecutionContainer for the test
	 * 
	 * the configured AgaviExecutionContainer class will be wrapped in a testing
	 * extension to provide advanced capabilities required for testing 
	 * only
	 * 
	 * @return     AgaviExecutionContainer 
	 * 
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 * @since      1.0.0
	 */
	protected function createExecutionContainer()
	{
		$context = $this->getContext();
		
		$ecfi = $context->getFactoryInfo('execution_container');
		$wrapper_class = $ecfi['class'].'UnitTesting';
		
		//extend the original class to add a setter for the action instance
		if(!class_exists($wrapper_class)) {
			$code = sprintf('
class %1$s extends %2$s
{
	protected $validationResult = null;
	
	public function performValidation()
	{	
		if(null === $this->validationResult) {
			$this->cloneArgumentsToRequestData();
			$this->validationResult = parent::performValidation();
		}
		return $this->validationResult;
	}
	
	public function cloneArgumentsToRequestData()
	{
		$this->requestData = clone $this->arguments;
	}
	
	public function setActionInstance(AgaviAction $action)
	{
		$this->actionInstance = $action;
	}
}',
			$wrapper_class,
			$ecfi['class']);
			
			eval($code);
		}
		
		// create a new execution container with the wrapped class
		$container = new $wrapper_class();
		$container->initialize($context, $ecfi['parameters']);
		$container->setModuleName($this->moduleName);
		$container->setActionName($this->actionName);
		$container->setArguments($this->createRequestDataHolder(array()));
		
		return $container;
	}
	
	/* --- container delegates --- */
	
	/**
	 * @see        AgaviExcutionContainer::setOutputType()
	 *
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 * @since      1.0.0
	 */
	protected function setOutputType(AgaviOutputType $outputType)
	{
		$this->container->setOutputType($outputType);
	}
	
	/**
	 * @see        AgaviExcutionContainer::setArguments()
	 *
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 * @since      1.0.0
	 */
	protected function setArguments(AgaviRequestDataHolder $rd)
	{
		$this->container->setArguments($rd);
	}
	
	/**
	 * @see        AgaviExcutionContainer::setRequestMethod()
	 *
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 * @since      1.0.0
	 */
	protected function setRequestMethod($method)
	{
		$this->container->setRequestMethod($method);
	}
	
	/**
	 * @see        AgaviAttributeHolder::clearAttributes()
	 *
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 * @since      1.0.0
	 */
	protected function clearAttributes()
	{
		$this->container->clearAttributes();
	}

	/**
	 * @see        AgaviAttributeHolder::getAttribute()
	 *
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 * @since      1.0.0
	 */
	protected function &getAttribute($name, $default = null)
	{
		return $this->container->getAttribute($name, null, $default);
	}

	/**
	 * @see        AgaviAttributeHolder::getAttributeNames()
	 *
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 * @since      1.0.0
	 */
	protected function getAttributeNames()
	{
		return $this->container->getAttributeNames();
	}

	/**
	 * @see        AgaviAttributeHolder::getAttributes()
	 *
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 * @since      1.0.0
	 */
	protected function &getAttributes()
	{
		return $this->container->getAttributes();
	}

	/**
	 * @see        AgaviAttributeHolder::hasAttribute()
	 *
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 * @since      1.0.0
	 */
	protected function hasAttribute($name)
	{
		return $this->container->hasAttribute($name);
	}

	/**
	 * @see        AgaviAttributeHolder::removeAttribute()
	 *
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 * @since      1.0.0
	 */
	protected function &removeAttribute($name)
	{
		return $this->container->removeAttribute($name);
	}

	/**
	 * @see        AgaviAttributeHolder::setAttribute()
	 *
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 * @since      1.0.0
	 */
	protected function setAttribute($name, $value)
	{
		$this->container->setAttribute($name, $value);
	}

	/**
	 * @see        AgaviAttributeHolder::appendAttribute()
	 *
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 * @since      1.0.0
	 */
	protected function appendAttribute($name, $value)
	{
		$this->container->appendAttribute($name, $value);
	}

	/**
	 * @see        AgaviAttributeHolder::setAttributesByRef()
	 *
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 * @since      1.0.0
	 */
	protected function setAttributeByRef($name, &$value)
	{
		$this->container->setAttributeByRef($name, $value);
	}

	/**
	 * @see        AgaviAttributeHolder::appendAttributeByRef()
	 *
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 * @since      1.0.0
	 */
	protected function appendAttributeByRef($name, &$value)
	{
		$this->container->appendAttributeByRef($name, $value);
	}

	/**
	 * @see        AgaviAttributeHolder::setAttributes()
	 *
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 * @since      1.0.0
	 */
	protected function setAttributes(array $attributes)
	{
		$this->container->setAttributes($attributes);
	}

	/**
	 * @see        AgaviAttributeHolder::setAttributesByRef()
	 *
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 * @since      1.0.0
	 */
	protected function setAttributesByRef(array &$attributes)
	{
		$this->container->setAttributesByRef($attributes);
	}
}

?>
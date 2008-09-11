<?php

abstract class AgaviActionTestCase extends AgaviFragmentTestCase
{
	protected $actionName;
	protected $moduleName;
	
	protected $viewName;
	protected $viewModuleName;
	
	protected $container;
	
	public function setUp()
	{
		$this->container = $this->createExecutionContainer();
	}
	
	public function tearDown()
	{
		$this->container = null;
	}
	
	protected function createActionInstance()
	{
		$actionInstance = $this->getContext()->getController()->createActionInstance($this->moduleName, $this->actionName);
		return $actionInstance;
	}
	
	protected function runAction()
	{
		$this->container->setActionInstance($this->createActionInstance());
		$executionFilter = $this->createExecutionFilter();
		list($this->viewModuleName, $this->viewName) = $executionFilter->runAction($this->container);
	}
	
	protected function createRequestDataHolder(array $arguments = array(), $type = 'AgaviRequestDataHolder')
	{
		$class = new $type($arguments);
		return $class;
	}
	

	protected function assertViewEquals($expected, $message = '', $delta = 0, $maxDepth = 10, $canonicalizeEol = FALSE)
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
		$this->assertEquals($expected, $this->viewName, $message = '', $delta = 0, $maxDepth = 10, $canonicalizeEol = FALSE);
	}
	
	protected function assertContainerAttributeEquals($expected, $attributeName, $value, $namespace = null, $message = '', $delta = 0, $maxDepth = 10, $canonicalizeEol = FALSE)
	{
		$this->assertEquals($expected, $this->container->getAttribute($attributeName, $value, $namespace), $message = '', $delta = 0, $maxDepth = 10, $canonicalizeEol = FALSE);
	}
	
	protected function assertHandlesMethod($method, $acceptGeneric = true, $message = '')
	{
		$actionInstance = $this->createActionInstance();
        $constraint = new AgaviConstraintActionHandlesMethod($actionInstance, $acceptGeneric);

        self::assertThat($method, $constraint, $message);
	}
	
	protected function assertNotHandlesMethod($method, $acceptGeneric = true, $message = '')
	{
		$actionInstance = $this->createActionInstance();
        $constraint = self::logicalNot(new AgaviConstraintActionHandlesMethod($actionInstance, $acceptGeneric));

        self::assertThat($method, $constraint, $message);
	}
	
	protected function assertIsSimple($message = '')
	{
		$actionInstance = $this->createActionInstance();
		$this->assertTrue($actionInstance->isSimple(), $message);
	}
	
	protected function assertIsNotSimple($message = '')
	{
		$actionInstance = $this->createActionInstance();
		$this->assertFalse($actionInstance->isSimple(), $message);
	}
	
	/**
	 * TODO: Maybe getDefaultViewName() can return an array, check
	 */
	protected function assertDefaultViewName($expected, $message = '', $delta = 0, $maxDepth = 10, $canonicalizeEol = FALSE)
	{
		$actionInstance = $this->createActionInstance();
		$this->assertEquals($expected, $actionInstance->getDefaultViewName(), $message = '', $delta = 0, $maxDepth = 10, $canonicalizeEol = FALSE);
	}

	protected function createExecutionFilter()
	{
		$effi = $this->getContext()->getFactoryInfo('execution_filter');

		$wrapper_class = $effi['class'].'UnitTesting';

		//extend the original class to add a setter for the action instance
		if (!class_exists($wrapper_class))
		{
			$code = sprintf('
class %1$s extends %2$s
{
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
	
	protected function createExecutionContainer()
	{
		$context = $this->getContext();
		
		$ecfi = $context->getFactoryInfo('execution_container');
		$wrapper_class = $ecfi['class'].'UnitTesting';
		
		//extend the original class to add a setter for the action instance
		if (!class_exists($wrapper_class))
		{
			$code = sprintf('
class %1$s extends %2$s
{
	/**
	 * @var        AgaviRequestDataHolder A request data holder with request info.
	 */
	protected $requestData = null;
	
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
	
	protected function setOutputType(AgaviOutputType $outputType)
	{
		$this->container->setOutputType($outputType);
	}
	
	protected function setArguments(AgaviRequestDataHolder $rd)
	{
		$this->container->setArguments($rd);
	}
	
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
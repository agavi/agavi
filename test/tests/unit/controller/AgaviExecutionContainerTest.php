<?php

class AgaviExecutionContainerTest extends AgaviUnitTestCase
{
	
	public function testSimpleActionWithoutArguments()
	{
		$container = $this->getContext()->getController()->createExecutionContainer('ControllerTests', 'SimpleAction');
		$response = $container->execute();
		
	}
}
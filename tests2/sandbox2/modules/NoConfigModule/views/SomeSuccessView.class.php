<?

class NoConfigModule_SomeSuccessView extends AgaviView
{
	public function execute(AgaviParameterHolder $parameters)
	{
		$this->setTemplate('some');
	}
}

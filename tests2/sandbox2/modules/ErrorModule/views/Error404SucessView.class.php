<?

class ErrorModule_Error404SuccessView extends AgaviView
{
	public function execute(AgaviParameterHolder $parameters)
	{
		$this->setTemplate('404');
	}
}

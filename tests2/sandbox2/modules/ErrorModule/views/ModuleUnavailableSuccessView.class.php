<?

class ModuleUnavailableSuccessView extends AgaviView
{
	public function execute(AgaviParameterHolder $parameters)
	{
		$this->setTemplate('notavailable');
	}
}

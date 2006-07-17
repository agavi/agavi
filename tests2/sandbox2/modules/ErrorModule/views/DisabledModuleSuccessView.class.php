<?

class DisabledModuleSuccessView extends AgaviView
{
	public function execute(AgaviParameterHolder $parameters)
	{
		$this->setTemplate('disabled');
	}
}

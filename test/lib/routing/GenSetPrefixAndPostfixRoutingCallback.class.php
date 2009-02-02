<?php

class GenSetPrefixAndPostfixRoutingCallback extends AgaviRoutingCallback
{
	public function onGenerate(array $defaultParameters, array &$userParameters, array &$userOptions)
	{
		$userParameters['number'] = $this->context->getRouting()->createValue('value')->setPrefix('prefix-')->setPostfix('-postfix');
		return true;
	}
}

?>
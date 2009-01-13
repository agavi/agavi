<?php

class GenSetPrefixAndPostfixRoutingCallback extends AgaviRoutingCallback
{
	public function onGenerate(array $defaultParameters, array &$userParameters, array &$userOptions)
	{
		$userParameters['number'] = array('pre' => 'prefix-', 'val' => 'value', 'post' => '-postfix');
		return true;
	}
}

?>
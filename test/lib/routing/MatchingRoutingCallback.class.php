<?php

class MatchingRoutingCallback extends AgaviRoutingCallback
{
	public function onMatched(array &$parameters, AgaviExecutionContainer $container)
	{
		$paramters['callback'] = 'set';
		return true;
	}
}

?>
<?php

class MatchingRoutingCallback extends AgaviRoutingCallback
{
	public function onMatched(array &$parameters, AgaviExecutionContainer $container)
	{
		$parameters['callback'] = 'set';
		return true;
	}
}

?>
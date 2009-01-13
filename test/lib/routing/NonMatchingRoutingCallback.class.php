<?php

class NonMatchingRoutingCallback extends AgaviRoutingCallback
{
	public function onMatched(array &$parameters, AgaviExecutionContainer $container)
	{
		return false;
	}
}

?>
<?php

class GenUnsetExtraParamRoutingCallback extends AgaviRoutingCallback
{
	/**
	 * Gets executed when the route of this callback is about to be reverse 
	 * generated into an URL.
	 *
	 * @param      array The default parameters stored in the route.
	 * @param      array The parameters the user supplied to AgaviRouting::gen().
	 * @param      array The options the user supplied to AgaviRouting::gen().
	 *
	 * @return     bool  Whether this route part should be generated.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function onGenerate(array $defaultParameters, array &$userParameters, array &$userOptions)
	{
		// unsetting a value in callback is supposed to have no impact
		unset($userParameters['extra']);
		return true;
	}
}

?>
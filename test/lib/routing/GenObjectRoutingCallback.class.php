<?php

class GenObjectRoutingCallback extends AgaviRoutingCallback
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
		if(isset($userParameters['value']) && $userParameters['value'] instanceof AgaviIRoutingValue) {
			if($this->getParameter('set_as_string', false)) {
				$userParameters['value'] = $userParameters['value']->getValue()->getPath();
			} else {
				$userParameters['value']->setValue($userParameters['value']->getValue()->getPath());
			}
		}
		
		return true;
	}
}

?>
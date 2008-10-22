<?php

class TestResponse extends AgaviResponse
{
	public function send(AgaviOutputType $ot = null)
	{
		$this->sendContent();
	}
	
	/**
	 * Clear all reponse data.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function clear()
	{
		$this->clearContent();
	}
	
	public function setRedirect($to)
	{
	}
	
	public function getRedirect()
	{
	}
	
	public function hasRedirect()
	{
	}
	
	public function clearRedirect()
	{
	}
	
	public function merge(AgaviResponse $other)
	{
	}
}

?>
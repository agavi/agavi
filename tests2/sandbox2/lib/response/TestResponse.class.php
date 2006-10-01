<?php

class TestResponse extends AgaviResponse
{
	public function send()
	{
		$this->sendContent();
	}
	
	/**
	 * Clear all reponse data.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function clear()
	{
		if(!$this->locked) {
			$this->clearContent();
		}
	}
}

?>
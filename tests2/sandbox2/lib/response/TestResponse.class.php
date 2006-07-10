<?php

class TestResponse extends AgaviResponse
{
	public function send()
	{
		if($this->dirty) {
			$this->sendContent();
		
			$this->dirty = false;
		}
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
			$this->dirty = false;
		}
	}
}

?>
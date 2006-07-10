<?php
class DummyValidator extends AgaviValidator
{
	public $cleared = false;
	public $val_result = true;
	public $validated = false;
	public $shutdown = false;
	
	protected function validate()
	{
		$this->validated = true;
		if($this->val_result == false) {
			$this->throwError();
		}
		return $this->val_result;
	}
	public function clear() { $this->cleared = true; $this->validated = false; $this->shutdown = false;}
	public function shutdown() { $this->shutdown = true; }
}
?>
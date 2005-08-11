<?php

class SingletonSampleModel extends SingletonModel
{
	public $foo = null;
	public function setFoo($value)
	{
		$this->foo = $value;
	}
	public function getFoo()
	{
		return $this->foo;
	}
}

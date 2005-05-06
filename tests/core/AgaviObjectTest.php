<?php
require_once('tests/include.php');
require_once('core/AgaviObject.class.php');

class SampleAgaviObject extends AgaviObject
{
	private $_something;
	public $_somethingelse;
	public function __construct()
	{
		$this->_something = 'a value';
		$this->_somethingelse = array('another', 'value');
	}
}

class AgaviObjectTest extends PHPUnit2_Framework_TestCase
{
	public function testtoString()
	{
		$ao = new SampleAgaviObject();
		self::assertEquals('_something: a value, _somethingelse: Array', $ao->toString());
	}
}

?>

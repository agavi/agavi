<?php
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

class AgaviObjectTest extends UnitTestCase
{
	public function testtoString()
	{
		$ao = new SampleAgaviObject();
		$this->assertEqual('_something: a value, _somethingelse: Array', $ao->toString());
	}
}

?>

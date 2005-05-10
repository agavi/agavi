<?php
require_once('core/AgaviObject.class.php');
require_once('util/ParameterHolder.class.php');

class SampleParameterHolder extends ParameterHolder {}

class ParameterHolderTest extends UnitTestCase
{
	private $_ph = null;

	public function setUp()
	{
		$this->_ph = new SampleParameterHolder();
	}

	public function testclearParameters()
	{
		$this->_ph->setParameter('name1', 'value1');
		$this->assertTrue($this->_ph->getParameters() != array());
		$this->_ph->clearParameters();
		$this->assertEqual(array(), $this->_ph->getParameters());
	}

	public function testgetParameter()
	{
		$this->_ph->setParameter('name1', 'value1');
		$this->assertEqual('value1', $this->_ph->getParameter('name1'));
	}

	public function testgetParameterNames()
	{
		$this->_ph->setParameters(array(
			'name1'=>'value1',
			'name2'=>'value2'
		));
		$this->assertEqual(array('name1', 'name2'), $this->_ph->getParameterNames());
	}

	public function testgetParameters()
	{
		$params = array('name1'=>'value1','name2'=>'value2');
		$this->_ph->setParameters($params);
		$this->assertEqual($params, $this->_ph->getParameters());
	}

	public function testhasParameter()
	{
		$this->assertFalse($this->_ph->hasParameter('name1'));
		$this->_ph->setParameter('name1', 'value1');
		$this->assertTrue($this->_ph->hasParameter('name1'));
	}

	public function testremoveParameter()
	{
		$this->assertNull($this->_ph->removeParameter('name1'));
		$this->_ph->setParameter('name1', 'value1');
		$this->assertEqual('value1', $this->_ph->removeParameter('name1'));
	}

	public function testsetParameter()
	{
		$this->_ph->setParameter('name1', 'value1');
		$this->assertEqual('value1', $this->_ph->getParameter('name1'));
	}

	public function testsetParameterByRef()
	{
		$val = 'value1';
		$this->_ph->setParameterByRef('name1', $val);
		$this->assertReference($val, $this->_ph->getParameter('name1'));
	}

	public function testsetParameters()
	{
		$params = array(
			'name1'=>'value1',
			'name2'=>'value2'
		);
		$this->_ph->setParameters($params);
		$this->assertEqual($params, $this->_ph->getParameters());
	}

	public function testsetParametersByRef()
	{
		$val1 = 'value1';
		$val2 = 'value2';
		$params = array(
			'name1'=>&$val1,
			'name2'=>&$val2
		);
		$this->_ph->setParametersByRef($params);
		$this->assertReference($val1, $this->_ph->getParameter('name1'));
		$this->assertReference($val2, $this->_ph->getParameter('name2'));
	}

}

?>

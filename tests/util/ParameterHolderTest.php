<?php
require_once('tests/include.php');
require_once('core/AgaviObject.class.php');
require_once('util/ParameterHolder.class.php');

class SampleParameterHolder extends ParameterHolder {}

class ParameterHolderTest extends PHPUnit2_Framework_TestCase
{
	private $_ph = null;

	public function setUp()
	{
		$this->_ph = new SampleParameterHolder();
	}

	public function testclearParameters()
	{
		$this->_ph->setParameter('name1', 'value1');
		self::assertTrue($this->_ph->getParameters() != array());
		$this->_ph->clearParameters();
		self::assertEquals(array(), $this->_ph->getParameters());
	}

	public function testgetParameter()
	{
		$this->_ph->setParameter('name1', 'value1');
		self::assertEquals('value1', $this->_ph->getParameter('name1'));
	}

	public function testgetParameterNames()
	{
		$this->_ph->setParameters(array(
			'name1'=>'value1',
			'name2'=>'value2'
		));
		self::assertEquals(array('name1', 'name2'), $this->_ph->getParameterNames());
	}

	public function testgetParameters()
	{
		$params = array('name1'=>'value1','name2'=>'value2');
		$this->_ph->setParameters($params);
		self::assertEquals($params, $this->_ph->getParameters());
	}

	public function testhasParameter()
	{
		self::assertFalse($this->_ph->hasParameter('name1'));
		$this->_ph->setParameter('name1', 'value1');
		self::assertTrue($this->_ph->hasParameter('name1'));
	}

	public function testremoveParameter()
	{
		self::assertNull($this->_ph->removeParameter('name1'));
		$this->_ph->setParameter('name1', 'value1');
		self::assertEquals('value1', $this->_ph->removeParameter('name1'));
	}

	public function testsetParameter()
	{
		$this->_ph->setParameter('name1', 'value1');
		self::assertEquals('value1', $this->_ph->getParameter('name1'));
	}

	public function testsetParameterByRef()
	{
		$val = 'value1';
		$this->_ph->setParameterByRef('name1', $val);
		self::assertEquals($val, $this->_ph->getParameter('name1'));
		$val = 'newvalue1';
		self::assertEquals($val, $this->_ph->getParameter('name1'));
	}

	public function testsetParameters()
	{
		$params = array(
			'name1'=>'value1',
			'name2'=>'value2'
		);
		$this->_ph->setParameters($params);
		self::assertEquals($params, $this->_ph->getParameters());
	}

	public function testsetParametersByRef()
	{
		$params = array(
			'name1'=>'value1',
			'name2'=>'value2'
		);
		$this->_ph->setParametersByRef($params);
		self::assertEquals($params, $this->_ph->getParameters());
		$params['name1'] = 'newvalue1';
		self::assertEquals($params, $this->_ph->getParameters());
	}

}

?>

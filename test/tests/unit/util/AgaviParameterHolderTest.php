<?php

if(!class_exists('AgaviParameterHolder')) {
	include(__DIR__ . '/../../../../src/util/AgaviParameterHolder.class.php');
}

if(!class_exists('AgaviArrayPathDefinition')) {
	include(__DIR__ . '/../../../../src/util/AgaviArrayPathDefinition.class.php');
}

//class AgaviParameterHolderTest extends AgaviUnitTestCase
class AgaviParameterHolderTest extends PHPUnit_Framework_TestCase
{
	
	public function __construct($name = NULL, array $data = array(), $dataName = '')
	{
		parent::__construct($name, $data, $dataName);
		// $this->setRunTestInSeparateProcess(true);
	}

	public function testConstructAndGetParameters()
	{
		$data = array('foo' => 'bar', 'bar' => 'baz', 'baz' => 'qux');
		$p = new AgaviParameterHolder($data);
		$p2 = new AgaviParameterHolder(array('bla'));
		$p3 = new AgaviParameterHolder();
		$this->assertEquals(array(), $p3->getParameters());
		$this->assertEquals($data, $p->getParameters());
		$this->assertEquals(array('bla'), $p2->getParameters());
		$this->assertEquals($data, $p->getParameters());
		$p->clearParameters();
		$this->assertEquals(array(), $p->getParameters());
	}

	public function testGetParametersIntegerIndex()
	{
		$data2 = array('a' => '11', 'b' => '22', 3 => '33', '44');
		$p = new AgaviParameterHolder($data2);
		$this->assertEquals(array('a' => '11', 'b' => '22', 3 => '33', '44'), $p->getParameters());
		$p->clearParameters();
		$this->assertEquals(array(), $p->getParameters());
	}

	public function testGetParameterNames()
	{
		$data2 = array('a' => '11', 'b' => '22', 'c' => '33');
		$p = new AgaviParameterHolder($data2);
		$this->assertEquals(array('a', 'b', 'c'), $p->getParameterNames());
		$p->clearParameters();
		$this->assertEquals(array(), $p->getParameters());
	}

	public function testGetParameterNamesIntegerIndex()
	{
		$data2 = array('a' => '11', 'b' => '22', 3 => '33', '44');
		$p = new AgaviParameterHolder($data2);
		$this->assertEquals(array('a', 'b', 3, 4), $p->getParameterNames());
		$p->clearParameters();
		$this->assertEquals(array(), $p->getParameters());
	}

	public function testGetFlatParameterNames()
	{
		$data2 = array('a' => '11', 'b' => '22', 'c' => '33');
		$p = new AgaviParameterHolder($data2);
		$this->assertEquals(array('a', 'b', 'c'), $p->getFlatParameterNames());
		$p->clearParameters();
		$this->assertEquals(array(), $p->getParameters());
	}

	public function testGetFlatParameterIntegerIndex()
	{
		$data2 = array('a' => '11', 'b' => '22', 3 => '33', '44');
		$p = new AgaviParameterHolder($data2);
		$this->assertEquals(array('a', 'b', 3, 4), $p->getFlatParameterNames());
		$p->clearParameters();
		$this->assertEquals(array(), $p->getParameters());
	}
  
	public function testGetParameter()
	{
		$data = array('stefy' => 'ecuador', 'amy' => 'florida', 'stasy' => 'ukraine', 'lalala');
		$p = new AgaviParameterHolder($data);
		$this->assertEquals('florida', $p->getParameter('amy'));
		$this->assertEquals('', $p->getParameter('kiki'));
		$this->assertEquals('', $p->getParameter('lalala'));
		$p->clearParameters();
		$this->assertEquals(array(), $p->getParameters());
	}

	public function testGetParameterIntegerIndex()
	{
		$data = array('stefy' => 'ecuador', 0 => 'florida', 'lalala');
		$p = new AgaviParameterHolder($data);
		$this->assertEquals('florida', $p->getParameter(0));
		$this->assertEquals('lalala', $p->getParameter(1));
		$p->clearParameters();
		$this->assertEquals(array(), $p->getParameters());
	}

	public function testHasParameter()
	{
		$data = array('stefy' => 'ecuador', 'amy' => 'florida', 'stasy' => 'ukraine', 'lalala');
		$p = new AgaviParameterHolder($data);
		$this->assertTrue($p->hasParameter('stefy'));
		$this->assertFalse($p->hasParameter('kiki'));
		$this->assertFalse($p->hasParameter('lalala'));
		$p->clearParameters();
		$this->assertEquals(array(), $p->getParameters());
	}

	public function testHasParameterIntegerIndex()
	{
		$data = array(1 => '111');
		$p = new AgaviParameterHolder($data);
		$this->assertTrue($p->hasParameter(1));
		$this->assertFalse($p->hasParameter(0));
		$p->clearParameters();
		$this->assertEquals(array(), $p->getParameters());
	}

	public function testRemoveParameter()
	{
		$data = array('stef' => 'ecuador', 'amy' => 'florida', 'stasy' => 'ukraine', 'lalala');
		$p = new AgaviParameterHolder($data);
		$this->assertEquals('ecuador', $p->removeParameter('stef'));
		$this->assertEquals(NULL, $p->removeParameter('kiki'));
		$this->assertEquals('lalala', $p->removeParameter(0));
		$this->assertEquals(array('amy', 'stasy'), $p->getParameterNames());
		$this->assertEquals(array('amy' => 'florida', 'stasy' => 'ukraine'), $p->getParameters());
		$p->clearParameters();
		$this->assertEquals(array(), $p->getParameters());
	}

	public function testRemoveParameterIntegerIndex()
	{
		$data = array(2 => '222', 1 => '111');
		$p = new AgaviParameterHolder($data);
		$this->assertEquals('222', $p->removeParameter(2));
		$this->assertEquals(NULL, $p->removeParameter(0));
		$this->assertEquals(array(1 => '111'), $p->getParameters());
		$p->clearParameters();
		$this->assertEquals(array(), $p->getParameters());
	}

	public function testSetParameter()
	{
		$data = array('stefy' => 'ecuador', 'amy' => 'florida', 'stasy' => 'ukraine', 'lalala');
		$p = new AgaviParameterHolder($data);
		$p->setParameter('kiki', 'bulgaria');
		$p->setParameter('stefy', 'germany');
		$p->setParameter(0, 'ohh');
		$this->assertEquals(array('stefy' => 'germany', 'amy' => 'florida', 'stasy' => 'ukraine', 'ohh', 'kiki' => 'bulgaria'), $p->getParameters());
		$p->clearParameters();
		$this->assertEquals(array(), $p->getParameters());
	}

	public function testSetParameterIntegerIndex()
	{
		$data = array(0 => 'ecuador', 'lalala');
		$p = new AgaviParameterHolder($data);
		$p->setParameter(0, 'bulgaria');
		$p->setParameter(1, 'germany');
		$this->assertEquals(array('bulgaria', 'germany'), $p->getParameters());
		$p->clearParameters();
		$this->assertEquals(array(), $p->getParameters());
	}

	public function testSetParameterByRef()
	{
		$data = array('stefy' => 'ecuador', 'stasy' => 'ukraine');
		$p = new AgaviParameterHolder($data);
		$amy = 'florida';
		$p->setParameterByRef('amy', $amy);
		// amy moves
		$amy = 'new york';
		$this->assertEquals('new york', $p->getParameter('amy'));
	}

	public function testSetGetParameterAsArray()
	{
		$p = new AgaviParameterHolder();
		$p->setParameter('foo', array('bar' => 'baz'));
		$this->assertEquals('baz', $p->getParameter('foo[bar]'));
	}

	public function testAppendParameter()
	{
		$data = array('stefy' => 'ecuador', 'amy' => 'florida', 'stasy' => 'ukraine', 'lalala');
		$p = new AgaviParameterHolder($data);
		$kiki = 'bulgaria';
		$p->appendParameter('kiki', $kiki);
		$kiki = 'munich';
		$p->appendParameter('stefy', 'germany');
		$p->appendParameter(0, 'ohh');
		$this->assertEquals(array('stefy' => array('ecuador', 'germany'), 'amy' => 'florida', 'stasy' => 'ukraine', array('lalala', 'ohh'), 'kiki' => array('bulgaria')), $p->getParameters());
		$p->appendParameter('stefy', 'sanni');
		$this->assertEquals(array('stefy' => array('ecuador', 'germany', 'sanni'), 'amy' => 'florida', 'stasy' => 'ukraine', array('lalala', 'ohh'), 'kiki' => array('bulgaria')), $p->getParameters());
		$p->clearParameters();
		$this->assertEquals(array(), $p->getParameters());
	}

	public function testAppendParameterIntegerIndex()
	{
		$data = array(0 => 'ecuador', 'lalala', 3);
		$p = new AgaviParameterHolder($data);
		$p->appendParameter(0, 'ohh');
		$this->assertEquals(array(0 => array('ecuador', 'ohh'), 1 => 'lalala', 2 => 3), $p->getParameters());
		$p->clearParameters();
		$this->assertEquals(array(), $p->getParameters());
	}

	public function testAppendParameterByRef()
	{
		$data = array('stefy' => 'ecuador', 'amy' => 'florida', 'stasy' => 'ukraine');
		$p = new AgaviParameterHolder($data);
		$bg = 'bulgaria';
		$stefy = 'peru';
		$la = 'lalala';
		$p->appendParameterByRef('kiki', $bg);
		$p->appendParameterByRef('stefy', $stefy);
		$stefy = 'germany';
		$p->appendParameterByRef(0, $la);
		$la = 'ohh';
		$this->assertEquals(array('stefy' => array('ecuador', 'germany'), 'amy' => 'florida', 'stasy' => 'ukraine', 'kiki' => array('bulgaria'), array('ohh')), $p->getParameters());
		$p->clearParameters();
		$this->assertEquals(array(), $p->getParameters());
	}

	public function testSetParameters()
	{
		$data = array('stefy' => 'ecuador', 'amy' => 'florida', 'stasy' => 'ukraine', 'lalala');
		$p = new AgaviParameterHolder($data);
		$p->setParameters(array('kiki' => 'bulgaria', 'stefy' => 'germany', 'ohh'));
		$this->assertEquals(array('stefy' => 'germany', 'amy' => 'florida', 'stasy' => 'ukraine', 'kiki' => 'bulgaria', 'ohh'), $p->getParameters());
		$p->clearParameters();
		$this->assertEquals(array(), $p->getParameters());
	}

	public function testSetParametersIntegerIndex()
	{
		$data = array(1 => 'ukraine', 'lalala');
		$p = new AgaviParameterHolder($data);
		$p->setParameters(array('ohh', 1 => 'london'));
		// fails in php
		$this->assertEquals(array('ohh', 'london', 'lalala'), $p->getParameters());
		$p->clearParameters();
		$this->assertEquals(array(), $p->getParameters());
	}

	public function testSetParametersByRef()
	{
		$data = array('stefy' => 'ecuador', 'amy' => 'florida', 'stasy' => 'ukraine');
		$p = new AgaviParameterHolder($data);
		$kiki = 'bulgaria';
		$newparameters = array('kiki' => &$kiki, 'stefy' => 'germany');
		$p->setParametersByRef($newparameters);
		$kiki = 'munich';
		$this->assertEquals(array('stefy' => 'germany', 'amy' => 'florida', 'stasy' => 'ukraine', 'kiki' => 'munich'), $p->getParameters());
		$p->clearParameters();
		$this->assertEquals(array(), $p->getParameters());
	}

	public function testClear()
	{
		$data3 = array('a' => '11', 'b' => '22', 'c' => '33', '44');
		$p = new AgaviParameterHolder($data3);
		$p->clearParameters();
		$this->assertEquals(array(), $p->getParameters());
	}

	public function testGetSetStringInteger() {
		$p = new AgaviParameterHolder();
		$p->setParameter('10', 'ten');
		$this->assertEquals('ten', $p->getParameter(10));
		$p->setParameter(21, 'twentyone');
		$this->assertEquals('twentyone', $p->getParameter('21'));
		$p->setParameters(array(1 => 'one'));
		$this->assertEquals('one', $p->getParameter('1'));
		$this->assertEquals(array(1 => 'one', 10 => 'ten', 21 => 'twentyone'), $p->getParameters());
	}

	public function testRemoveInvalidKeyCausesNoNotice()
	{
		$ph = new AgaviParameterHolder();
		$zomg =& $ph->removeParameter('[]foo[]');
		$this->assertNull($zomg);
	}
}

?>
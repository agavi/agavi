<?php

if(!class_exists('AgaviParameterHolder')) {
	include(dirname(__FILE__) . '../../../../src/util/AgaviParameterHolder.class.php');
}

if(!class_exists('AgaviArrayPathDefinition')) {
	include(dirname(__FILE__) . '../../../../src/util/AgaviArrayPathDefinition.class.php');
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
		$this->assertEquals($data, $p->getParameters());
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

	public function testGetFlatParameterNames()
	{
		$data2 = array('a' => '11', 'b' => '22', 'c' => '33');
		$p = new AgaviParameterHolder($data2);
		$this->assertEquals(array('a', 'b', 'c'), $p->getFlatParameterNames());
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

	public function testRemoveParameter()
	{
		$data = array('stefy' => 'ecuador', 'amy' => 'florida', 'stasy' => 'ukraine', 'lalala');
		$p = new AgaviParameterHolder($data);
		$this->assertEquals('ecuador', $p->removeParameter('stefy'));
		$this->assertEquals(NULL, $p->removeParameter('kiki'));
		$this->assertEquals('lalala', $p->removeParameter(0));
		$this->assertEquals(array('amy', 'stasy'), $p->getParameterNames());
		$this->assertEquals(array('amy' => 'florida', 'stasy' => 'ukraine'), $p->getParameters());
		$p->clearParameters();
		$this->assertEquals(array(), $p->getParameters());
	}

	public function testSetParameter()
	{
		$data = array('stefy' => 'ecuador', 'amy' => 'florida', 'stasy' => 'ukraine', 'lalala');
		$p = new AgaviParameterHolder($data);
		$p->SetParameter('kiki', 'bulgaria');
		$p->SetParameter('stefy', 'germany');
		$p->SetParameter(0, 'ohh');
		$this->assertEquals(array('stefy' => 'germany', 'amy' => 'florida', 'stasy' => 'ukraine', 'ohh', 'kiki' => 'bulgaria'), $p->getParameters());
		$p->clearParameters();
		$this->assertEquals(array(), $p->getParameters());
	}

	public function testSetParameterByRef()
	{
		$data = array('stefy' => 'ecuador', 'amy' => 'florida', 'stasy' => 'ukraine', 'lalala');
		$p = new AgaviParameterHolder($data);
		$bg = 'bulgaria';
		$p->SetParameterByRef('kiki', &$bg);
		$de = 'germany';
		$p->SetParameterByRef('stefy', &$de);
		$la = 'ohh';
		$p->SetParameterByRef(0, &$la);
		$this->assertEquals(array('stefy' => 'germany', 'amy' => 'florida', 'stasy' => 'ukraine', 'ohh', 'kiki' => 'bulgaria'), $p->getParameters());
		$p->clearParameters();
		$this->assertEquals(array(), $p->getParameters());
	}

	public function testAppendParameter()
	{
		$data = array('stefy' => 'ecuador', 'amy' => 'florida', 'stasy' => 'ukraine', 'lalala');
		$p = new AgaviParameterHolder($data);
		$p->AppendParameter('kiki', 'bulgaria');
		$p->AppendParameter('stefy', 'germany');
		$p->AppendParameter(0, 'ohh');
		$this->assertEquals(array('stefy' => array('ecuador', 'germany'), 'amy' => 'florida', 'stasy' => 'ukraine', array('lalala', 'ohh'), 'kiki' => array('bulgaria')), $p->getParameters());
		$p->clearParameters();
		$this->assertEquals(array(), $p->getParameters());
	}

	public function testAppendParameterByRef()
	{
		$data = array('stefy' => 'ecuador', 'amy' => 'florida', 'stasy' => 'ukraine', 'lalala');
		$p = new AgaviParameterHolder($data);
		$bg = 'bulgaria';
		$de = 'germany';
		$la = 'ohh';
		$p->AppendParameterByRef('kiki', &$bg);
		$p->AppendParameterByRef('stefy', &$de);
		$p->AppendParameterByRef(0, &$la);
		$this->assertEquals(array('stefy' => array('ecuador', 'germany'), 'amy' => 'florida', 'stasy' => 'ukraine', array('lalala', 'ohh'), 'kiki' => array('bulgaria')), $p->getParameters());
		$p->clearParameters();
		$this->assertEquals(array(), $p->getParameters());
	}

	public function testSetParameters()
	{
		$data = array('stefy' => 'ecuador', 'amy' => 'florida', 'stasy' => 'ukraine', 'lalala');
		$p = new AgaviParameterHolder($data);
		$newparameters = array('kiki' => 'bulgaria', 'stefy' => 'germany', 'ohh');
		$p->SetParameters($newparameters);
		$this->assertEquals(array('stefy' => 'germany', 'amy' => 'florida', 'stasy' => 'ukraine', 'lalala', 'kiki' => 'bulgaria', 'ohh'), $p->getParameters());
		$p->clearParameters();
		$this->assertEquals(array(), $p->getParameters());
	}

	public function testSetParametersByRef()
	{
		$data = array('stefy' => 'ecuador', 'amy' => 'florida', 'stasy' => 'ukraine', 'lalala');
		$p = new AgaviParameterHolder($data);
		$newparameters = array('kiki' => 'bulgaria', 'stefy' => 'germany', 'ohh');
		$p->SetParametersByRef(&$newparameters);
		$this->assertEquals(array('stefy' => 'germany', 'amy' => 'florida', 'stasy' => 'ukraine', 'ohh', 'kiki' => 'bulgaria'), $p->getParameters());
		$p->clearParameters();
		$this->assertEquals(array(), $p->getParameters());
	}

	public function testClear()
	{
		$data3 = array('a' => '11', 'b' => '22', 'c' => '33');
		$p = new AgaviParameterHolder($data3);
		$p->clearParameters();
		$this->assertEquals(array(), $p->getParameters());
	}
}

?>
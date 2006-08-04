<?php

class SampleResponse extends AgaviResponse
{
	public function clear()
	{
	}

	public function send()
	{
	}
}

class ResponseTest extends AgaviTestCase
{
	private $_r = null;

	public function setUp()
	{
		$this->_r = new SampleResponse();
		$this->_r->initialize(AgaviContext::getInstance('test'));
	}

	public function testGetContext()
	{
		$ctx = AgaviContext::getInstance('test');
		$ctx_test = $this->_r->getContext();
		$this->assertReference($ctx, $ctx_test);
	}

	public function testSetGetContent()
	{
		$r = $this->_r;
		$this->assertEquals('', $r->getContent());
		$this->assertTrue($r->setContent('test1'));
		$this->assertEquals('test1', $r->getContent());
		$this->assertFalse($r->setContent('test1'));
		$r->lock();
		$this->assertFalse($r->setContent('test2'));
		$this->assertEquals('test1', $r->getContent());
	}

	public function testImportExport()
	{
		$r = $this->_r;

		$r->setContent('test1');
		$this->assertSame(array('content' => 'test1', 'locked' => false), $r->export());

		$r->import(array('content' => 'test2'));
		$this->assertSame(array('content' => 'test2', 'locked' => false), $r->export());


		$r->import(array('content' => 'test3', 'locked' => true));
		$this->assertSame(array('content' => 'test3', 'locked' => true), $r->export());
	}

	public function testExportInfo()
	{
		$r = $this->_r;

		$this->assertSame(array('locked' => false), $r->exportInfo());

		$r->lock();
		$this->assertSame(array('locked' => true), $r->exportInfo());
	}

	public function testMerge()
	{
		$r = $this->_r;

		$r->setContent('content a');
		$this->assertTrue($r->merge(array('content' => 'content b', 'locked' => true)));
		$this->assertFalse($r->isLocked());
		$this->assertEquals('content a' . 'content b', $r->getContent());

		$r->lock();
		$this->assertFalse($r->merge(array('content' => 'content c', 'locked' => false)));
		$this->assertTrue($r->isLocked());
		$this->assertEquals('content a' . 'content b', $r->getContent());
	}

	public function testAppend()
	{
		$r = $this->_r;

		$r->setContent('content a');
		$this->assertTrue($r->append(array('content' => 'content b', 'locked' => true)));
		$this->assertFalse($r->isLocked());
		$this->assertEquals('content a' . 'content b', $r->getContent());

		$r->lock();
		$this->assertFalse($r->append(array('content' => 'content c', 'locked' => false)));
		$this->assertTrue($r->isLocked());
		$this->assertEquals('content a' . 'content b', $r->getContent());
	}

	public function testIsDirty()
	{
		$r = $this->_r;

		$this->assertFalse($r->isDirty());
		$r->setContent('content');
		$this->assertTrue($r->isDirty());
	}

	public function testLocked()
	{
		$r = $this->_r;

		$this->assertFalse($r->isLocked());
		$r->lock();
		$this->assertTrue($r->isLocked());
	}

	public function testPrependContent()
	{
		$r = $this->_r;

		$r->setContent('content a');
		$this->assertTrue($r->prependContent('content b'));
		$this->assertEquals('content b' . 'content a', $r->getContent());

		$r->lock();
		$this->assertFalse($r->prependContent('content c'));
		$this->assertEquals('content b' . 'content a', $r->getContent());
	}

	public function testAppendContent()
	{
		$r = $this->_r;

		$r->setContent('content a');
		$this->assertTrue($r->appendContent('content b'));
		$this->assertEquals('content a' . 'content b', $r->getContent());

		$r->lock();
		$this->assertFalse($r->appendContent('content c'));
		$this->assertEquals('content a' . 'content b', $r->getContent());
	}

	public function testSendContent()
	{
		$r = $this->_r;

		ob_start();
		$r->setContent('content');
		$r->sendContent();

		$content = ob_get_contents();
		ob_end_clean();

		$this->assertEquals('content', $content);
	}
}

?>
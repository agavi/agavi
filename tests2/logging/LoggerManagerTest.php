<?php

class LoggerManagerTest extends AgaviTestCase
{
	private
		$_context = null,
		$_lm = null,
		$_logfile = '',
		$_logfile2 = '',
		$_pl = null,
		$_fa = null,
		$_fa2 = null,
		$_l = null,
		$_l2 = null;

	public function setUp()
	{
		$this->_context = AgaviContext::getInstance('test');
		$this->_lm = $this->_context->getLoggerManager();
		$this->_logfile = tempnam('','logtest');
		$this->_logfile2 = tempnam('', 'logtest2');
		@unlink($this->_logfile);
		@unlink($this->_logfile2);
		$this->_pl = new AgaviPassthruLoggerLayout;
		$this->_fa = new AgaviFileLoggerAppender;
		$this->_fa->initialize($this->_context, array('file' => $this->_logfile));
		$this->_fa->setLayout($this->_pl);
		$this->_fa2 = new AgaviFileLoggerAppender;
		$this->_fa2->initialize($this->_context, array('file' => $this->_logfile2));
		$this->_fa2->setLayout($this->_pl);
		$this->_l = new AgaviLogger;
		$this->_l->setLevel(AgaviLogger::INFO);
		$this->_l->setAppender('fa', $this->_fa);
		$this->_l2 = new AgaviLogger;
		$this->_l2->setLevel(AgaviLogger::DEBUG | AgaviLogger::INFO);
		$this->_l2->setAppender('fa2', $this->_fa2);
	}

	public function tearDown()
	{
		$this->_lm->shutdown();
		@unlink($this->_logfile);
		@unlink($this->_logfile2);
		$this->_lm = null;
		$this->_context = null;
	}

	public function testgetLoggerNames()
	{
		$this->assertEquals(array(), $this->_lm->getLoggerNames());
		$this->_lm->setLogger('logfile', $this->_l);
		$this->assertEquals(array('logfile'), $this->_lm->getLoggerNames());
		$this->_lm->setLogger('logfile2', $this->_l2);
		$this->assertEquals(array('logfile', 'logfile2'), $this->_lm->getLoggerNames());
	}

	public function testgetLogger()
	{
		$this->_lm->setLogger($this->_lm->getDefaultLoggerName(), $this->_l);
		$this->assertEquals($this->_l, $this->_lm->getLogger());
		$this->_lm->setLogger('logfile2', $this->_l2);
		$this->assertEquals($this->_l, $this->_lm->getLogger('default'));
		$this->assertEquals($this->_l2, $this->_lm->getLogger('logfile2'));
	}

	public function testLog()
	{
		$this->_lm->setLogger('logfile', $this->_l);
		$this->_lm->setLogger('logfile2', $this->_l2);
		$this->assertFalse(file_exists($this->_logfile));
		$this->assertFalse(file_exists($this->_logfile2));

		//this should be logged by both
		$this->_lm->log(new AgaviLoggerMessage('simple info message', AgaviLogger::INFO));
		$this->assertRegexp('/simple info message/', file_get_contents($this->_logfile));
		$this->assertRegexp('/simple info message/', file_get_contents($this->_logfile2));

		//this should be logged only by l2
		$this->_lm->log(new AgaviLoggerMessage('simple debug message', AgaviLogger::DEBUG));
		$this->assertNotRegexp('/simple debug message/', file_get_contents($this->_logfile));
		$this->assertRegexp('/simple debug message/', file_get_contents($this->_logfile2));

		//this should be logged only by l2
		$this->_lm->log('simple debug message two', AgaviLogger::DEBUG);
		$this->assertNotRegexp('/simple debug message two/', file_get_contents($this->_logfile));
		$this->assertRegexp('/simple debug message two/', file_get_contents($this->_logfile2));

		//this should be logged only by l
		$this->_lm->log('simple debug message three', $this->_l);
		$this->assertRegexp('/simple debug message three/', file_get_contents($this->_logfile));
		$this->assertNotRegexp('/simple debug message three/', file_get_contents($this->_logfile2));

		//this should be logged only by l
		$this->_lm->log(new AgaviLoggerMessage('simple info message four', AgaviLogger::INFO), $this->_l);
		$this->assertRegexp('/simple info message four/', file_get_contents($this->_logfile));
		$this->assertNotRegexp('/simple info message four/', file_get_contents($this->_logfile2));
	}

}

?>
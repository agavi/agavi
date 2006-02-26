<?php
require_once dirname(__FILE__) . '/../test_environment.php';

class LoggerManagerTest extends UnitTestCase
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
		$this->_context = Context::getInstance();
		$this->_lm = $this->_context->getLoggerManager();
		$this->_logfile = tempnam('','logtest');
		$this->_logfile2 = tempnam('', 'logtest2');
		@unlink($this->_logfile);
		@unlink($this->_logfile2);
		$this->_pl = new PassthruLayout;
		$this->_fa = new FileAppender;
		$this->_fa->initialize(array('file' => $this->_logfile));
		$this->_fa->setLayout($this->_pl);
		$this->_fa2 = new FileAppender;
		$this->_fa2->initialize(array('file' => $this->_logfile2));
		$this->_fa2->setLayout($this->_pl);
		$this->_l = new Logger;
		$this->_l->setPriority(Logger::INFO);
		$this->_l->setAppender('fa', $this->_fa);
		$this->_l2 = new Logger;
		$this->_l2->setPriority(Logger::DEBUG);
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
		$this->assertEqual(array(), $this->_lm->getLoggerNames());
		$this->_lm->setLogger('logfile', $this->_l);
		$this->assertEqual(array('logfile'), $this->_lm->getLoggerNames());
		$this->_lm->setLogger('logfile2', $this->_l2);
		$this->assertEqual(array('logfile', 'logfile2'), $this->_lm->getLoggerNames());
	}

	public function testgetLogger()
	{
		$this->_lm->setLogger('default', $this->_l);
		$this->assertIdentical($this->_l, $this->_lm->getLogger());
		$this->_lm->setLogger('logfile2', $this->_l2);
		$this->assertIdentical($this->_l, $this->_lm->getLogger('default'));
		$this->assertIdentical($this->_l2, $this->_lm->getLogger('logfile2'));
	}

	public function testLog()
	{
		$this->_lm->setLogger('logfile', $this->_l);
		$this->_lm->setLogger('logfile2', $this->_l2);
		$this->assertFalse(file_exists($this->_logfile));
		$this->assertFalse(file_exists($this->_logfile2));
		$this->_lm->log(new Message('simple info message', Logger::INFO));
		$this->assertWantedPattern('/simple info message/', file_get_contents($this->_logfile));
		$this->assertWantedPattern('/simple info message/', file_get_contents($this->_logfile2));
		$this->_lm->log(new Message('simple debug message', Logger::DEBUG));
		$this->assertNoUnwantedPattern('/simple debug message/', file_get_contents($this->_logfile));
		$this->assertWantedPattern('/simple debug message/', file_get_contents($this->_logfile2));
	}

}

?>
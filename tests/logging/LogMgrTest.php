<?php
require_once dirname(__FILE__) . '/../mockContext.php';

class LogMgr 
{
	const FATAL		= 1,
				ERROR		=	2,
				WARNING	= 3,
				INFO 		= 4,
				DEBUG		= 999;

	private static 
			$loggers = array(),
			$context;

	private function __construct(){}
	
	public static function initialize ($context)
	{
		self::$context = $context;
		// add configured loggers
		// self::addLogger(....);
		require_once(ConfigCache::checkConfig('config/logging.ini'));
	}
	
	public static function addLogger($loggerclass, $params = null)
	{
		if (!class_exists($loggerclass)) {
			throw new LoggingException("$loggerclass doesnt exist.");
		} 
		self::$loggers[] = new $loggerclass(self::$context, $params);
		return count(self::$loggers);
	}

	public static function logEvent($message, $priority = Logger::INFO)
	{
		if (count(self::$loggers)) {
			foreach (self::$loggers as $logger) {
				$logger->log($message, $priority);
			}
		} else {
			throw new LoggingException('No Loggers Established');
		}
	}

	public static function shutdown ()
	{
		foreach (self::$loggers as $logger)	{
			$logger->shutdown();
		}
	}

}

class FileLogger
{
	private $context,
					$filename;
	
	public function __construct($context, $params)
	{
		$this->context = $context;
		$this->filename = isset($params['filename']) ? $params['filename'] : '/dev/null';
	}

	public function log($message, $priority)
	{
		file_put_contents($this->filename, "[$priority] $message\n");
	}
}

class LogMgrTest extends UnitTestCase
{
	public function testLogMgr()
	{
		$logfile = '/tmp/logtest';
		$logfile2 = '/tmp/logtest2';
		@unlink($logfile);
		@unlink($logfile2);
		//$LM = new LogMgr();
		//$this->assertIsA($LM, 'LogMgr');
		
		$this->assertEqual(1,LogMgr::addLogger('FileLogger', array('filename' => $logfile)));
		$this->assertEqual(2,LogMgr::addLogger('FileLogger', array('filename' => $logfile2)));
		$this->assertFalse(file_exists($logfile));
		$this->assertFalse(file_exists($logfile2));
		LogMgr::logEvent('Something Happened');
		$this->assertTrue(file_exists($logfile));
		$this->assertTrue(file_exists($logfile2));
		$this->AssertWantedPattern('/Something Happened/i', file_get_contents($logfile));
		$this->AssertWantedPattern('/Something Happened/i', file_get_contents($logfile2));
	}
}

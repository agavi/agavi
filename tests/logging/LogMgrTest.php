<?php
require_once dirname(__FILE__) . '/../mockContext.php';

class AgaviLogMgr 
{
	const FATAL		= 10,
				ERROR		=	20,
				WARNING	= 30,
				INFO 		= 40,
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

	public static function log($message, $priority = Logger::INFO)
	{
		if (count(self::$loggers)) {
			foreach (self::$loggers as $logger) {
				$logger->log($message, $priority);
			}
		} else {
			// throw new LoggingException('No Loggers Established');
		}
	}

	public static function shutdown ()
	{
		foreach (self::$loggers as $logger)	{
			$logger->shutdown();
		}
	}

}

class AgaviFileLogger
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

	public function shutdown()
	{
		file_put_contents($this->filename, '[shutdown] Closing log.' . date('Y/m/d h:i')."\n");
	}
}

class LogMgrTest extends UnitTestCase
{
	private $file1, $file2;
	
	public function setUp()
	{
		$this->file1 = '/tmp/logtest1';
		$this->file2 = '/tmp/logtest2';
		@unlink($this->file1);
		@unlink($this->file2);
	}
	
	public function testLogMgr()
	{
		$this->assertEqual(1,AgaviLogMgr::addLogger('AgaviFileLogger', array('filename' => $this->file1)));
		$this->assertEqual(2,AgaviLogMgr::addLogger('AgaviFileLogger', array('filename' => $this->file2)));
		$this->assertFalse(file_exists($this->file1));
		$this->assertFalse(file_exists($this->file2));
		AgaviLogMgr::log('Something Happened');
		$this->assertTrue(file_exists($this->file1));
		$this->assertTrue(file_exists($this->file2));
		$this->AssertWantedPattern('/Something Happened/i', file_get_contents($this->file1));
		$this->AssertWantedPattern('/Something Happened/i', file_get_contents($this->file2));
	}
}

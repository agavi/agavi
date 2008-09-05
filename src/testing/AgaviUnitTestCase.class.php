<?php

abstract class AgaviUnitTestCase extends PHPUnit_Framework_TestCase implements AgaviIUnitTestCase
{

    /**
     * Constructs a test case with the given name.
     *
     * @param  string $name
     * @param  array  $data
     * @param  string $dataName
     */
    public function __construct($name = NULL, array $data = array(), $dataName = '')
    {
		parent::__construct($name, $data, $dataName);
		$this->setRunTestInSeparateProcess(true);
	}
	
	public function getContext()
	{
		/**
		 * @TODO change this implementation, it's only here for dev purposes
		 */
		return AgaviContext::getInstance('web');
	}
}

?>
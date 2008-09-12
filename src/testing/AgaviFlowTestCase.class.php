<?php

abstract class AgaviFlowTestCase extends PHPUnit_Framework_TestCase implements AgaviIFlowTestCase
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

}

?>
<?php

abstract class AgaviUnitTestCase extends PHPUnit_Framework_TestCase implements AgaviIUnitTestCase
{
	/**
	 * @var        bool Indicates to PHPUnit whether or not each test case in this suite should run in isolation. In this case, we want all methods of the test case to run in the same process, but the entire test case run in the suite should be isolated from others, so this must be set to true.
	 */
	protected $runInSeparateProcess = true;
	
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
		$this->methodTemplateName = sprintf(
            '%1$s%2$stemplates%2$sAgaviTestMethod.tpl',

            dirname(__FILE__),
            DIRECTORY_SEPARATOR
          );
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
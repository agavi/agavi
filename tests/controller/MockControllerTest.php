<?php
require_once dirname(__FILE__) . '/../mockContext.php';

class MockControllerTest extends UnitTestCase
{
	private $mc;
	
	
	function assertNotReference(&$first, &$second, $message = "%s") 
	{
    $dumper = &new SimpleDumper();
    $message = sprintf(
            $message,
            "[" . $dumper->describeValue($first) .
                    "] and [" . $dumper->describeValue($second) .
                    "] should reference the same object");
    return $this->assertFalse(
            SimpleTestCompatibility::isReference($first, $second),
            $message);
	}
	

	public function testEnvironment()
	{
		$this->mc = new MockController($this);
		$this->mc->dispatch(); // calls initialize, sets up context, etc.
		$Context = $this->mc->getContext();
		$this->assertIsA($Context,'MockContext');
		$myContext = MockContext::getInstance($this->mc);
		$this->assertReference($myContext, $Context);
	
		$mc2 = $this->mc->getContext()->getController();
		$this->assertReference($mc2, $this->mc);
		
		$as = $this->mc->getActionStack();
		$as2 = $this->mc->getContext()->getActionStack();
		$this->assertReference($as, $as2);
		$this->assertIsA($as, 'ActionStack');
		$real = new ActionStack();
		$this->assertTrue($this->mc->getContext()->replaceObj('actionStack', $real));
		$as3 = $this->mc->getActionStack();
		$as4 = $this->mc->getContext()->getActionStack();
		$this->assertReference($as3, $as4);
		$this->assertReference($real, $as4);
		$this->assertNotReference($as, $as4);

	}

	public function testCleanSlate()
	{
		//$this->
	}
}

<?php

interface AgaviIFlowTestCase extends AgaviITestCase
{
	public function dispatch(AgaviITestCall $call);
	
	public function assertValidationFailed();
}

?>
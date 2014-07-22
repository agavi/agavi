<?php

if(version_compare(PHP_VERSION, '5.4', '<')) {
	class SandboxTestingChildClass extends SandboxTestingParentClass implements SandboxITestingChild
	{
	}
} else {
	class SandboxTestingChildClass extends SandboxTestingParentClass implements SandboxITestingChild
	{
		use SandboxTestingTrait;
	}
}

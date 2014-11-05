<?php

if(version_compare(PHP_VERSION, '5.4', '<')) {
	require_once(__DIR__ . '/SandboxTestingChildClassPhp53.class.php');
} else {
	require_once(__DIR__ . '/SandboxTestingChildClassPhp54.class.php');
}

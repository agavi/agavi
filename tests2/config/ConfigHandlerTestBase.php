<?php

abstract class ConfigHandlerTestBase extends AgaviTestCase
{
	protected function getIncludeFile($code)
	{
		$file = tempnam(AgaviConfig::get('core.cache_dir'), 'cht');
		file_put_contents($file, $code);
		return $file;
	}

	protected function includeCode($code)
	{
		$file = $this->getIncludeFile($code);
		$ret = include($file);
		unlink($file);
		return $ret;
	}
}

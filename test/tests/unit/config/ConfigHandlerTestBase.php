<?php

abstract class ConfigHandlerTestBase extends AgaviUnitTestCase
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
	
	protected function parseConfiguration($configFile, $xslFile = null, $environment = null) {
		return AgaviXmlConfigParser::run(
			$configFile,
			$environment ? $environment : AgaviConfig::get('core.environment'),
			'',
			array(
				AgaviXmlConfigParser::STAGE_SINGLE => $xslFile ? array($xslFile) : array(),
				AgaviXmlConfigParser::STAGE_COMPILATION => array(),
			),
			array(
				AgaviXmlConfigParser::STAGE_SINGLE => array(
					AgaviXmlConfigParser::STEP_TRANSFORMATIONS_BEFORE => array(),
					AgaviXmlConfigParser::STEP_TRANSFORMATIONS_AFTER => array(),
				),
				AgaviXmlConfigParser::STAGE_COMPILATION => array(
					AgaviXmlConfigParser::STEP_TRANSFORMATIONS_BEFORE => array(),
					AgaviXmlConfigParser::STEP_TRANSFORMATIONS_AFTER => array()
				),
			)
		);
		
	}
}

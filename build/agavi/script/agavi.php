#! /usr/bin/env php
<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2005-2008 the Agavi Project.                                |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE   |
// | file that was distributed with this source code. You can also view the    |
// | LICENSE file online at http://www.agavi.org/LICENSE.txt                   |
// |   vi: set noexpandtab:                                                    |
// |   Local Variables:                                                        |
// |   indent-tabs-mode: t                                                     |
// |   End:                                                                    |
// +---------------------------------------------------------------------------+

require('phing/Phing.php');

require(dirname(__FILE__) . '/../build.php');
AgaviBuild::bootstrap();

require(dirname(__FILE__) . '/AgaviOptionParser.class.php');

define('AGAVI_DIRECTORY', realpath(dirname(__FILE__) . '/../../..'));
define('BUILD_DIRECTORY', realpath(dirname(__FILE__) . '/../..'));
define('START_DIRECTORY', getcwd());

$GLOBALS['OUTPUT'] = new OutputStream(fopen('php://stdout', 'w'));
$GLOBALS['ERROR'] = new OutputStream(fopen('php://stderr', 'w'));
$GLOBALS['INPUT'] = new InputStream(fopen('php://stdin', 'r'));

/* Initialize Phing. */
try {
	Phing::startup();
	
	Phing::setProperty('phing.home', getenv('PHING_HOME'));
}
catch(Exception $e) {
	$GLOBALS['OUTPUT']->write($e->getMessage() . PHP_EOL);
	exit(1);
}

$GLOBALS['PROPERTIES'] = array();
$GLOBALS['SHOW_LIST'] = false;
$GLOBALS['BUILD'] = new PhingFile(BUILD_DIRECTORY . '/build.xml');

/* Define parser callbacks. */
function input_help_display()
{
	$GLOBALS['OUTPUT']->write(sprintf('Usage: %s [options] [target...]', basename($_SERVER['argv'][0])) . PHP_EOL);
	$GLOBALS['OUTPUT']->write('Options:' . PHP_EOL);
	$GLOBALS['OUTPUT']->write('  -h -? --help                    Displays the help for this utility' . PHP_EOL);
	$GLOBALS['OUTPUT']->write('  -D --define <property> <value>  Defines a build property' . PHP_EOL);
	$GLOBALS['OUTPUT']->write('  -v --version                    Displays relevant version information' . PHP_EOL);
	$GLOBALS['OUTPUT']->write('  -l --list --targets             Displays the list of available targets' . PHP_EOL);
}

function input_help(AgaviOptionParser $parser, $name, $arguments, $scriptArguments)
{
	input_help_display();
	exit(0);
}

function input_define(AgaviOptionParser $parser, $name, $arguments, $scriptArguments)
{
	$name = $arguments[0];
	$value = $arguments[1];
	
	$GLOBALS['PROPERTIES'][$name] = $value;
}

function input_version(AgaviOptionParser $parser, $name, $arguments, $scriptArguments)
{
	$GLOBALS['OUTPUT']->write('Agavi build system, script version $Id$' . PHP_EOL);
	$GLOBALS['OUTPUT']->write(Phing::getPhingVersion() . PHP_EOL);
	exit(0);
}

function input_list(AgaviOptionParser $parser, $name, $arguments, $scriptArguments)
{
	$GLOBALS['SHOW_LIST'] = true;
}

/* Parse incoming arguments. */
$parser = new AgaviOptionParser(array_slice($_SERVER['argv'], 1));
$parser->addOption('help', array('h', '?'), array('help'), 'input_help');
$parser->addOption('define', array('D'), array('define'), 'input_define', 2);
$parser->addOption('version', array('v'), array('version'), 'input_version');
$parser->addOption('list', array('l'), array('list', 'targets'), 'input_list');

try {
	$parser->parse();
}
catch(AgaviOptionException $aae) {
	$GLOBALS['ERROR']->write('Error: ' . $aae->getMessage() . PHP_EOL);
	$GLOBALS['ERROR']->write(PHP_EOL);
	input_help_display();
	exit(1);
}

$GLOBALS['TARGETS'] = $parser->getPassedArguments();

$GLOBALS['PROPERTIES']['agavi.directory'] = AGAVI_DIRECTORY;

$GLOBALS['PROJECT_DIRECTORY'] = null;

try {
	$project = new Project();
	$project->setBasedir(BUILD_DIRECTORY);
	
	foreach($GLOBALS['PROPERTIES'] as $name => $value) {
		$project->setUserProperty($name, $value);
	}
	
	$project->init();
	
	$project->addTaskDefinition('agavi.import', 'org.agavi.build.tasks.AgaviImportTask', 'phing');
	$project->addTaskDefinition('agavi.locate-project', 'org.agavi.build.tasks.AgaviLocateprojectTask', 'phing');
	$project->addTaskDefinition('agavi.check-project', 'org.agavi.build.tasks.AgaviCheckprojectTask', 'phing');
	
	Phing::setCurrentProject($project);
	
	try {
		$project->fireBuildStarted();
		
		$task = $project->createTask('agavi.import');
		$task->setFile(new PhingFile($GLOBALS['BUILD']->getAbsolutePath()));
		$task->init();
		$task->perform();
		
		$project->executeTarget('initialize');
		
		$task = $project->createTask('agavi.locate-project');
		$task->setProperty('project.directory');
		$task->setPath(new PhingFile($project->getProperty('application.startdir')));
		$task->setIgnoreIfSet(true);
		$task->init();
		$task->perform();
		
		if($project->getProperty('project.directory') !== null) {
			$task = $project->createTask('agavi.check-project');
			$task->setProperty('project.available');
			$task->setPath(new PhingFile($project->getProperty('project.directory')));
			$task->init();
			$task->perform();
		}
		else {
			/* The script might be a symlink. */
			$task = $project->createTask('agavi.locate-project');
			$task->setProperty('project.directory');
			
			$path = new PhingFile(dirname($_SERVER['argv'][0]));
			$path = $path->isAbsolute() ? $path : new PhingFile(START_DIRECTORY, (string)$path);
			
			$task->setPath($path);
			$task->setIgnoreIfSet(true);
			$task->init();
			$task->perform();
			
			if($project->getProperty('project.directory') !== null) {
				$task = $project->createTask('agavi.check-project');
				$task->setProperty('project.available');
				$task->setPath(new PhingFile($project->getProperty('project.directory')));
				$task->init();
				$task->perform();
			}
		}
	}
	catch(BuildException $be) {
		$project->fireBuildFinished($be);
		throw $be;
	}
	$project->fireBuildFinished(null);
	
	if($project->getProperty('project.available')) {
		$GLOBALS['PROJECT_DIRECTORY'] = $project->getProperty('project.directory');
	}
}
catch(Exception $e) {
	/* This failed. Can't figure out project directory. Forget it. */
}

/* Switch to whichever project directory the script determined. */
$GLOBALS['PROPERTIES']['project.directory'] = $GLOBALS['PROJECT_DIRECTORY'];

/* Execute Phing. */
try {
	$project = new Project();
	
	$logger = new AgaviProxyBuildLogger();
	$logger->setMessageOutputLevel(Project::MSG_INFO);
	$logger->setOutputStream($GLOBALS['OUTPUT']);
	$logger->setErrorStream($GLOBALS['ERROR']);
	
	$project->addBuildListener($logger);
	$project->setInputHandler(new DefaultInputHandler());
	
	$project->setUserProperty('phing.file', $GLOBALS['BUILD']->getAbsolutePath());
	$project->setUserProperty('phing.version', Phing::getPhingVersion());
	
	/* Phing fucks with the cwd. Really, brilliant. */
	$project->setUserProperty('application.startdir', START_DIRECTORY);
	
	foreach($GLOBALS['PROPERTIES'] as $name => $value) {
		$project->setUserProperty($name, $value);
	}
	
	$project->init();
	ProjectConfigurator::configureProject($project, $GLOBALS['BUILD']);
	
	Phing::setCurrentProject($project);
	
	if($GLOBALS['SHOW_LIST'] === true) {
		input_help_display();
		$GLOBALS['OUTPUT']->write(PHP_EOL);
		$GLOBALS['OUTPUT']->write('Targets:' . PHP_EOL);
		
		$size = 0;
		$targets = array();
		foreach($project->getTargets() as $target) {
			$name = $target->getName();
			$nameSize = strlen($name);
			$description = $target->getDescription();
			if($description !== null) {
				$size = $nameSize > $size ? $nameSize : $size;
				$targets[$name] = $description;
			}
		}
		
		$formatter = '  %-' . $size . 's  %s';
		
		foreach($targets as $name => $description) {
			$GLOBALS['OUTPUT']->write(sprintf($formatter, $name, $description) . PHP_EOL);
		}
		
		$defaultTarget = $project->getDefaultTarget();
		if($defaultTarget !== null && $defaultTarget !== '') {
			$GLOBALS['OUTPUT']->write(PHP_EOL);
			$GLOBALS['OUTPUT']->write('Default target: ' . $defaultTarget . PHP_EOL);
		}
		
		exit(0);
	}
	
	try {
		$project->fireBuildStarted();
		
		$GLOBALS['TARGETS'] = count($GLOBALS['TARGETS']) === 0
			? array($project->getDefaultTarget())
			: $GLOBALS['TARGETS'];
		
		$project->executeTargets($GLOBALS['TARGETS']);
	}
	catch(Exception $e) {
		$project->fireBuildFinished($e);
		throw $e;
	}
	$project->fireBuildFinished(null);
}
catch(Exception $e) {
	$GLOBALS['ERROR']->write(PHP_EOL);
	$GLOBALS['ERROR']->write(sprintf('%s:%d: %s', $e->getFile(), $e->getLine(), $e->getMessage()) . PHP_EOL);
	exit(1);
}

exit(0);

?>

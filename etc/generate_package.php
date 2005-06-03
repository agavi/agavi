<?php
if (!file_exists('src')) {
	die("must be run from the root of an agavi SVN checkout\n");
}
$tmpdir = tempnam('/tmp', 'AGAVIPEAR');
unlink($tmpdir);
mkdir($tmpdir);
exec("cd src && cp -Rp * {$tmpdir}");
exec("cp CHANGELOG {$tmpdir}");
exec("cp LICENSE {$tmpdir}");
exec("mkdir {$tmpdir}/scripts");
exec("cp etc/agavi-dist {$tmpdir}/scripts");
exec("cp etc/agavi.bat-dist {$tmpdir}/scripts");

/*
//  the following packages are presumed to be present in the base install:

PACKAGE        VERSION STATE
Archive_Tar    1.1     stable
Console_Getopt 1.2     stable
DB             1.6.1   stable
Mail           1.1.2   stable
Net_SMTP       1.2.5   stable
Net_Socket     1.0.1   stable
PEAR           1.3.1   stable
PHPUnit        0.6.2   stable
XML_Parser     1.0.1   stable
XML_RPC        1.1.0   stable
*/

set_time_limit(0);
require_once 'PEAR/PackageFileManager.php';

// Modify short description. Try to keep under 80 chars width
$shortDesc = <<<EOD
PHP5 MVC Application Framework
EOD;

// Modify long description. Try to keep under 80 chars width
$longDesc = <<<EOD
Agavi is a fork of the Mojavi project.  It aims to provide an MVC
application framework for PHP5.
EOD;

$packagexml = new PEAR_PackageFileManager;
$e = $packagexml->setOptions(array(
	'baseinstalldir' => 'agavi',
	'version' => '0.9.0',
	'license' => 'LGPL',
	'packagedirectory' => $tmpdir,
	'state' => 'beta',
	'package' => 'agavi',
	'simpleoutput' => true,
	'summary' => $shortDesc,
	'description' => $longDesc,
	'filelistgenerator' => 'file', // generate from cvs, use file for directory
	'notes' => 'See the CHANGELOG for full list of changes',
	'dir_roles' => array(
				'scripts' => 'script'
	),
	'ignore' => array(
		'.svn/',
	), 
	'roles' => array(
		'*' => 'php',
	),
	'exceptions' => array(
		'CHANGELOG' => 'doc',
		'LICENSE' => 'doc',
	),
	'installas' => array(
		'scripts/agavi-dist' => 'agavi',
		'scripts/agavi.bat-dist' => 'agavi.bat'
	),
	'replacements' => array(
		'scripts/agavi-dist' => array(
			array(
				'type' => 'pear-config',
				'from' => '@PEAR-DIR@',
				'to' => 'php_dir'
			)
		),
		'scripts/agavi.bat-dist' => array(
			array(
				'type' => 'pear-config',
				'from' => '@PEAR-DIR@',
				'to' => 'php_dir'
			)
		)
	),
	'platformexceptions' => array(
		'scripts/agavi-dist' => '(*ix|*ux|darwin*|SunOS*)',
		'scripts/agavi.bat-dist' => 'windows'
	),
	'installexceptions' => array(
		'scripts/agavi-dist' => '/',
		'scripts/agavi.bat-dist' => '/'
	)
));
if (is_a($e, 'PEAR_Error')) {
	echo $e->getMessage();
	die();
}

$e = $packagexml->addDependency('phing', '2.1.0');
if (is_a($e, 'PEAR_Error')) {
	echo $e->getMessage();
	exit;
}

$e = $packagexml->addMaintainer('bob', 'lead', 'Bob Zoller', 'bob@agavi.org');
if (is_a($e, 'PEAR_Error')) {
	echo $e->getMessage();
	exit;
}

$e = $packagexml->addMaintainer('mike', 'lead', 'Mike Vincent', 'mike@agavi.org');
if (is_a($e, 'PEAR_Error')) {
	echo $e->getMessage();
	exit;
}

// note use of {@link debugPackageFile()} - this is VERY important
if ((isset($_SERVER['argv'][1]) && $_SERVER['argv'][1] == 'make')) {
	$e = $packagexml->writePackageFile();
	echo("contents left in {$tmpdir}\n");
	echo("you probably want to go run 'pear package' in there if there were no errors.\n");
} else {
	$e = $packagexml->debugPackageFile();
	exec("rm -rf {$tmpdir}");
}
if (is_a($e, 'PEAR_Error')) {
	echo $e->getMessage();
	die();
}
?>

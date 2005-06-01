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

set_time_limit(0);
require_once 'PEAR/PackageFileManager2.php';

// Modify short description. Try to keep under 80 chars width
$shortDesc = <<<EOD
PHP5 MVC Application Framework
EOD;

// Modify long description. Try to keep under 80 chars width
$longDesc = <<<EOD
Agavi is a fork of the Mojavi project.  It aims to provide an MVC
application framework for PHP5.
EOD;

$p2 = new PEAR_PackageFileManager2;
$p2->setOptions(array(
	'filelistgenerator' => 'file',
	'packagedirectory' => $tmpdir,
	'baseinstalldir' => 'agavi',
	'simpleoutput' => true,
	'dir_roles' => array(
		'scripts' => 'script'
	),
	'ignore' => array(
		'.svn/'
	), 
	'exceptions' => array(
		'CHANGELOG' => 'doc',
		'LICENSE' => 'doc'
	),
	'installexceptions' => array(
		'scripts/agavi-dist' => '/',
		'scripts/agavi.bat-dist' => '/'
	)
));
$p2->setPackageType('php');
$p2->setPackage('agavi');
$p2->setChannel('pear.agavi.org');
$p2->setReleaseVersion('0.9.0');
$p2->setAPIVersion('0.9.0');
$p2->setReleaseStability('alpha');
$p2->setAPIStability('alpha');
$p2->setSummary($shortDesc);
$p2->setDescription($longDesc);
$p2->setNotes('See the CHANGELOG for full list of changes');
$p2->addRelease();
$p2->setOSInstallCondition('windows');
$p2->addInstallAs('scripts/agavi.bat-dist', 'agavi.bat');
$p2->addIgnore('scripts/agavi-dist');
$p2->addRelease();
$p2->setOSInstallCondition('(*ix|*ux|darwin*|SunOS*)');
$p2->addInstallAs('scripts/agavi-dist', 'agavi');
$p2->addIgnore('scripts/agavi.bat-dist');
$p2->addRole('*', 'php');
$p2->setPhpDep('5.0.0');
$p2->setPearinstallerDep('1.4.0a12');
$p2->addMaintainer('lead', 'bob', 'Bob Zoller', 'bob@agavi.org');
$p2->addMaintainer('lead', 'mike', 'Mike Vincent', 'mike@agavi.org');
$p2->setLicense('LGPL', 'http://www.gnu.org/copyleft/lesser.html');
$p2->generateContents();
$p2->addReplacement('scripts/agavi-dist', 'pear-config', '@PEAR-DIR@', 'php_dir');
$p2->addReplacement('scripts/agavi.bat-dist', 'pear-config', '@PEAR-DIR@', 'php_dir');

$pkg = &$p2->exportCompatiblePackageFile1();

if ((isset($_SERVER['argv'][1]) && $_SERVER['argv'][1] == 'make')) {
	$pkg->writePackageFile();
	$p2->writePackageFile();
	echo("contents left in {$tmpdir}\n");
	echo("you probably want to go run 'pear package' in there if there were no errors.\n");
} else {
	$pkg->debugPackageFile();
	$p2->debugPackageFile();
	exec("rm -rf {$tmpdir}");
}
?>

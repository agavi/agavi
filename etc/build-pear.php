#!/usr/bin/php
<?php

$r_version = (empty($argv[1]) ? '0.9.1' : $argv[1]);
$r_date = date('Y-m-d');
$r_time = date('h:i:s');

$tmpdir = tempnam('/tmp', 'AGAVI');
unlink($tmpdir);
define('TMPDIR', "{$tmpdir}/");
define('SRCDIR', TMPDIR."agavi-{$r_version}/");
mkdir(TMPDIR);
mkdir(SRCDIR);
mkdir(SRCDIR.'agavi');

$xml = '<?xml version="1.0"?>
<package version="2.0" packagerversion="1.4.0a9" xmlns="http://pear.php.net/dtd/package-2.0" xmlns:tasks="http://pear.php.net/dtd/tasks-1.0" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://pear.php.net/dtd/tasks-1.0 http://pear.php.net/dtd/tasks-1.0.xsd http://pear.php.net/dtd/package-2.0 http://pear.php.net/dtd/package-2.0.xsd">
  <name>agavi</name>
  <channel>pear.agavi.org</channel>
  <summary>PHP5 MVC framework</summary>
  <description>Agavi is a PHP5 based MVC framework based on Mojavi</description>
  <lead>
    <name>Bob Zoller</name>
    <user>bzoller</user>
    <email>bob@agavi.org</email>
    <active>yes</active>
  </lead>
  <lead>
    <name>Mike Vincent</name>
    <user>mvincent</user>
    <email>mike@agavi.org</email>
		<active>yes</active>
  </lead>
  <date>'.$r_date.'</date>
  <time>'.$r_time.'</time>
  <version>
    <release>'.$r_version.'</release>
    <api>'.$r_version.'</api>
    </version>
  <stability>
    <release>beta</release>
    <api>beta</api>
  </stability>
  <license uri="http://www.gnu.org/copyleft/lesser.html">LGPL</license>
  <notes>This is the initial release.</notes>
  <contents>
    <dir name="/">
      <dir name="agavi">
';

function processdir($dir) {
	$retval = '';
	foreach (glob("{$dir}*") as $node) {
		if ($node == '.svn' || $node == '.' || $node == '..') continue;
		if (is_dir($node)) {
			mkdir(SRCDIR.'agavi/'.$node);
			$retval .= "\n<dir name=\"".basename($node)."\">\n";
			$retval .= processdir("{$node}/");
			$retval .= "\n</dir>";
		} else {
			copy($node, SRCDIR.'agavi/'.$node);
			$retval .= '<file baseinstalldir="" md5sum="'. md5_file($node).'" name="'.basename($node).'" role="php"/>'."\n";
		}
	}
	return $retval;
}

chdir('src') || die('must be called from agavi root');
$xml .= processdir('');
chdir('..');
$xml .= "\n</dir>\n";

mkdir(SRCDIR.'pear');
$xml .= "\n<dir name=\"pear\">\n";
copy('etc/agavi-dist', SRCDIR.'pear/pear-agavi');
copy('etc/agavi.bat-dist', SRCDIR.'pear/pear-agavi.bat');
$xml .= '
    <file role="script" baseinstalldir="" platform="(*ix|*ux|darwin*|SunOS*)" install-as="agavi" name="pear-agavi">
      <tasks:replace type="pear-config" from="@PHP-BIN@" to="php_bin"/>
      <tasks:replace type="pear-config" from="@BIN-DIR@" to="bin_dir"/>
      <tasks:replace type="pear-config" from="@PEAR-DIR@" to="php_dir"/>
      <tasks:replace type="pear-config" from="@DATA-DIR@" to="data_dir"/>
    </file>
    <file role="script" baseinstalldir="" platform="windows" install-as="agavi.bat" name="pear-agavi.bat">
      <tasks:replace type="pear-config" from="@PHP-BIN@" to="php_bin"/>
      <tasks:replace type="pear-config" from="@BIN-DIR@" to="bin_dir"/>
      <tasks:replace type="pear-config" from="@PEAR-DIR@" to="php_dir"/>
      <tasks:replace type="pear-config" from="@DATA-DIR@" to="data_dir"/>
    </file>
  </dir>
  </dir>
  </contents>
  <dependencies>
    <required>
      <php>
        <min>5.0.0</min>
      </php>
      <pearinstaller>
        <min>1.4.0a1</min>
      </pearinstaller>
   <!--   <package>
        <name>phing</name>
				<channel>phing.info/pear/phing-current.tgz</channel>
        <min>2.0.0</min>
      </package> -->
    </required>
  </dependencies>
  <phprelease />
  <changelog>
    <release>
      <version>
        <release>'.$r_version.'</release>
        <api>'.$r_version.'</api>
      </version>
      <stability>
        <release>beta</release>
        <api>beta</api>
      </stability>
      <license uri="http://www.gnu.org/copyleft/lesser.html">LGPL</license>
      <notes>This is the initial release.</notes>
    </release>
  </changelog>
</package>
';

file_put_contents(TMPDIR.'package2.xml', $xml);
exec('cd '.TMPDIR.' && tar czf agavi-'.$r_version.'.tgz *');
echo($xml);
echo(TMPDIR."agavi-{$r_version}.tgz");
?>

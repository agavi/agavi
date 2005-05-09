#!/bin/sh
IFS="
"
cd src || exit "you must be in agavi's root dir"
tmpdir="/var/tmp/$$.tmp"
r_version="0.9a"
r_date="2005-05-09"
srcdir="${tmpdir}/agavi-${r_version}"
mkdir "${tmpdir}"

cat >> "${tmpdir}/package.xml" << BLAH
<?xml version="1.0" encoding="ISO-8859-1" ?>
<!DOCTYPE package SYSTEM "http://pear.php.net/dtd/package-1.0">
<package version="1.0" packagerversion="1.4.0a9">
 <name>agavi</name>
 <summary>PHP5 MVC framework based on Mojavi</summary>
 <description>Agavi is a PHP5 based MVC framework.</description>
 <maintainers>
  <maintainer>
   <user>bzoller</user>
   <name>Bob</name>
	 <email>bob@agavi.org</email>
   <role>lead</role>
  </maintainer>
  <maintainer>
   <user>mvincent</user>
   <name>Mike</name>
	 <email>mike@agavi.org</email>
   <role>lead</role>
  </maintainer>
  </maintainers>
 <release>
  <version>${r_version}</version>
  <date>${r_date}</date>
  <license>LGPL</license>
  <state>beta</state>
  <notes>This is the initial release.</notes>
  <deps>
   <dep type="php" rel="ge" version="5.0.0"/>
   <dep type="pkg" rel="ge" version="2.0.0">phing</dep>
  </deps>
  <filelist>
BLAH

mkdir "${srcdir}"
for i in `find . -type f \! -path '*.svn*'|sed 's/^\.\///'`; do
	echo "    <file role=\"php\" baseinstalldir=\"agavi\" name=\"${i}\"/>" >> "${tmpdir}/package.xml"
	mkdir -p `dirname "${srcdir}/${i}"`
	cp "${i}" "${srcdir}/${i}"
done
cd ..

mkdir "${srcdir}/pear"
cp "etc/agavi-dist" "${srcdir}/pear/pear-agavi"
cp "etc/agavi.bat-dist" "${srcdir}/pear/pear-agavi.bat"
cat >> "${tmpdir}/package.xml" << BLAH
	 <file role="script" baseinstalldir="/" platform="(*ix|*ux|darwin*|SunOS*)" install-as="agavi" name="pear/pear-agavi">
    <replace type="pear-config" from="@PHP-BIN@" to="php_bin"/>
    <replace type="pear-config" from="@BIN-DIR@" to="bin_dir"/>
    <replace type="pear-config" from="@PEAR-DIR@" to="php_dir"/>
    <replace type="pear-config" from="@DATA-DIR@" to="data_dir"/>
   </file>
   <file role="script" baseinstalldir="/" platform="windows" install-as="agavi.bat" name="pear/pear-agavi.bat">
    <replace type="pear-config" from="@PHP-BIN@" to="php_bin"/>
    <replace type="pear-config" from="@BIN-DIR@" to="bin_dir"/>
    <replace type="pear-config" from="@PEAR-DIR@" to="php_dir"/>
    <replace type="pear-config" from="@DATA-DIR@" to="data_dir"/>
   </file>
  </filelist>
 </release>
 <changelog>
   <release>
    <version>${r_version}</version>
    <date>${r_date}</date>
    <license>LGPL</license>
    <state>beta</state>
    <notes>This is the initial release.</notes>
   </release>
 </changelog>
</package>
BLAH

cd "$tmpdir" && tar czf agavi-${r_version}.tgz *
echo "${tmpdir}/agavi-${r_version}.tgz"
exit

<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2003-2006 the Agavi Project.                                |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE   |
// | file that was distributed with this source code. You can also view the    |
// | LICENSE file online at http://www.agavi.org/LICENSE.txt                   |
// |   vi: set noexpandtab:                                                    |
// |   Local Variables:                                                        |
// |   indent-tabs-mode: t                                                     |
// |   End:                                                                    |
// +---------------------------------------------------------------------------+

class AgaviPackageTask extends Task {
	private $dir = '';

	public function setDir($dir)
	{
		$this->dir = (string) $dir;
	}

	public function main()
	{
		if (!@require_once('PEAR/PackageFileManager2.php')) {
			throw new BuildException('Requires PEAR_PackageFileManager >=1.6.0a1');
		}
		require_once('PEAR/Exception.php');
		PEAR::setErrorHandling(PEAR_ERROR_CALLBACK,'PEAR_ErrorToPEAR_Exception');
		if (!$this->dir) {
			throw new BuildException('dir attribute is required!');
		}
		exec("rm -rf {$this->dir}");
		if (!@mkdir($this->dir)) {
			throw new BuildException("Could not make build directory: {$this->dir}");
		}

		$this->log("Building package contents in: {$this->dir}", PROJECT_MSG_INFO);

		exec("cp -Rp src/* {$this->dir}");
		exec('find '.$this->dir.' -name ".svn" -type d -exec rm -rf {} \; 2>&1 >/dev/null');
		copy('CHANGELOG', "{$this->dir}/CHANGELOG");
		copy('RELEASE_NOTES', "{$this->dir}/RELEASE_NOTES");
		copy('INSTALL', "{$this->dir}/INSTALL");
		copy('LICENSE', "{$this->dir}/LICENSE");
		mkdir("{$this->dir}/scripts");
		copy('etc/agavi-dist', "{$this->dir}/scripts/agavi-dist");
		copy('etc/agavi.bat-dist', "{$this->dir}/scripts/agavi.bat-dist");
		
		set_time_limit(0);
		
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
			'packagedirectory' => $this->dir,
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
				'LICENSE' => 'doc',
				'INSTALL' => 'doc',
				'RELEASE_NOTES' => 'doc'
			),
			'installexceptions' => array(
				'scripts/agavi-dist' => '/',
				'scripts/agavi.bat-dist' => '/'
			)
		));
		$p2->setPackageType('php');
		$p2->setPackage('agavi');
		$p2->addMaintainer('developer', 'bob', 'Bob Zoller', 'bob@agavi.org');
		$p2->addMaintainer('developer', 'mike', 'Mike Vincent', 'mike@agavi.org');
		$p2->addMaintainer('developer', 'david', 'David Zuelke', 'dz@bitxtender.com');
		$p2->addMaintainer('developer', 'v-dogg', 'Veikko Makinen', 'mail@veikkomakinen.com');
		$p2->setChannel('pear.agavi.org');
		$p2->setReleaseVersion('0.10.1');
		$p2->setAPIVersion('0.10.1');
		$p2->setReleaseStability('beta');
		$p2->setAPIStability('beta');
		$p2->setSummary($shortDesc);
		$p2->setDescription($longDesc);
		$p2->setNotes('See the CHANGELOG for full list of changes');
		$p2->addRelease();
		$p2->setOSInstallCondition('windows');
		$p2->addInstallAs('scripts/agavi.bat-dist', 'agavi.bat');
		$p2->addIgnore('scripts/agavi-dist');
		$p2->addRelease();
		$p2->setOSInstallCondition('(*BSD|*ix|*ux|darwin*|SunOS*)');
		$p2->addInstallAs('scripts/agavi-dist', 'agavi');
		$p2->addIgnore('scripts/agavi.bat-dist');
		$p2->addRole('*', 'php');
		$p2->setPhpDep('5.0.0');
		$p2->setPearinstallerDep('1.4.0a12');
		//$p2->addPackageDepWithUri('required', 'phing', 'http://phing.info/pear/phing-current.tgz');
		$p2->setLicense('LGPL', 'http://www.gnu.org/copyleft/lesser.html');
		$p2->generateContents();
		$p2->addReplacement('scripts/agavi-dist', 'pear-config', '@PEAR-DIR@', 'php_dir');
		$p2->addReplacement('scripts/agavi.bat-dist', 'pear-config', '@PEAR-DIR@', 'php_dir');
		
		$pkg = &$p2->exportCompatiblePackageFile1();
		
		try {
			$pkg->writePackageFile();
		//	$p2->writePackageFile();
		} catch (PEAR_Exception $e) {
			$this->log("Oops!  Caught PEAR Exception: ".$e->getMessage());
		}
	}
}

function PEAR_ErrorToPEAR_Exception($err)
{
    if ($err->getCode()) {
        throw new PEAR_Exception($err->getMessage(),
            $err->getCode());
    }
    throw new PEAR_Exception($err->getMessage());
}
?>
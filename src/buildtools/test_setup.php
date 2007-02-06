<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2003-2007 the Agavi Project.                                |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE   |
// | file that was distributed with this source code. You can also view the    |
// | LICENSE file online at http://www.agavi.org/LICENSE.txt                   |
// |   vi: set noexpandtab:                                                    |
// |   Local Variables:                                                        |
// |   indent-tabs-mode: t                                                     |
// |   End:                                                                    |
// +---------------------------------------------------------------------------+

/**
 * @package    agavi
 * @subpackage buildtools
 *
 * @author     Mike Vincent <mike@agavi.org>
 * @copyright  Authors
 * @copyright  The Agavi Project
 * @since      0.9.0
 *
 * @version    $Id$
 */

// simpletest should be in your path
#require_once('simpletest/unit_tester.php');
#require_once('simpletest/reporter.php');
#require_once('simpletest/mock_objects.php');

AgaviConfig::set('tests.cache_dir', false, false);

// the agavi script will have defined core.agavi_dir, else we should attempt to find it. 
if (!AgaviConfig::has('core.agavi_dir') && isset($_ENV['AGAVI_INSTALLATION'])) {
	AgaviConfig::set('core.agavi_dir', $_ENV['AGAVI_INSTALLATION']);
} else if (file_exists('src/agavi.php')) {
	// looks like we're here, then.
	AgaviConfig::set('core.agavi_dir', realpath('./src'));
} else if (!AgaviConfig::has('core.agavi_dir')) {
	die ('core.agavi_dir undefined. Try using the agavi helper script.');
}

// Assume this is an agavi project if there's a app subdir, we'll look for classes in there too.
if (file_exists($_SERVER['PWD_PATH'].'/app')) {
	AgaviConfig::set('core.app_dir', $_SERVER['PWD_PATH'] . '/app');
}

ini_set('unserialize_callback_func', 'test__autoload');

spl_autoload_register('test__autoload');

function locateClasses($path, $prefix=true)
{
	$i = new ClassFinder($path);
	$classes = 	array();
	while ( $i->valid() ) {
		if ( $i->isDir() && !$i->isDot() && !$i->isHidden() ) {
			if ( $i->hasChildren() ) {
				$classes = array_merge( $classes, locateClasses( $i->getPathname() ) );
			}
		} else if ( $i->isClass() ) { 
			$classes[$i->className()] = $i->getPathname();
			if ($prefix && ($pname = $i->prefixedClassName())) {
				$classes[$pname] = $i->getPathname();
			}
		}
		$i->next();
	}
	return $classes;
}


function test__autoload($class)
{
	if(substr($class, 0, 5) == 'Agavi' && $class != 'AgaviException') {
		$class = substr($class, 5);
	}

	$datefmt = 'c';
	$cachedir = AgaviConfig::get('tests.cache_dir');
	$cache = $cachedir . '/classcache.inc';
	static $classes;
	
	if (!is_array($classes) || !isset($classes[$class])) {
		if ($cachedir && file_exists($cache)) {
			include($cache); 
			if (isset($classes[$class])) {
				require_once($classes[$class]);
				return;
			}
		}
		$classes = locateClasses(AgaviConfig::get('core.agavi_dir'));
		if(AgaviConfig::has('core.app_dir')) { 
			$classes = array_merge((array) $classes, (array) locateClasses(AgaviConfig::get('core.app_dir'), true));
		}
		if ($cachedir && is_writable($cachedir)) {
			$contents = "<?php\n// --Automagically created ".date($datefmt)."\n//" .
									(AgaviConfig::has('core.app_dir') ? " includes classes located in {$_SERVER['CWD_NAME']}/app, too.\n" : "no app classes included.\n") .
									'$classes = ' .var_export($classes, true)."\n?>";
			file_put_contents($cache, $contents);
		}
	}
	if (isset($classes[$class])) {
		require_once($classes[$class]);
	}
}

class ClassFinder extends RecursiveDirectoryIterator
{
	protected $_classes,
						$_path;
	
	public function __construct($path)
	{
		$this->_path = $path;
		parent::__construct($path);
	}
	
	public function isHidden()
	{
		$name = $this->getFilename();
		return ($name{0} == ".");
	}

	public function isClass()
	{
		return ($this->isFile() && preg_match("/(.*)\.class\.php$/", $this->getFilename()));
	}

	public function className()
	{
		return str_replace ('.class.php', '', $this->getFilename());
	}

	public function prefixedClassName()
	{
		$class = $this->className();
		// /var/www/sites/project/app/modules/Default/actions/IndexAction.class.php, 
		// the class might (_should_) be called Default_IndexAction, so we set an entry for that case too. 
		$path = explode('/', $this->getPath());
		$c = count($path);
		if ($path[($c >= 3 ? $c-3 : 0)] == 'modules') {
			$module = $path[($c-2)];
			return ($module . '_' . $class);
		}
	}

}
?>
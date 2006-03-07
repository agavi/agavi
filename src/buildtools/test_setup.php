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

/**
 * @package    agavi
 * @subpackage buildtools
 *
 * @author     Mike Vincent <mike@agavi.org>
 * @copyright  (c) Authors
 * @since      0.9.0
 *
 * @version    $Id$
 */

// simpletest should be in your path
#require_once('simpletest/unit_tester.php');
#require_once('simpletest/reporter.php');
#require_once('simpletest/mock_objects.php');
if (!defined('AG_TEST_CACHE_DIR')) {
	define('AG_TEST_CACHE_DIR', false); // set to a path where you want to write the cache to, to enable caching the class locations
}

// the agavi script will have defined the AG_APP_DIR, else we should attempt to find it. 
if (!defined('AG_APP_DIR') && isset($_ENV['AGAVI_INSTALLATION'])) {
	define('AG_APP_DIR', $_ENV['AGAVI_INSTALLATION']);
} else if (file_exists('src/agavi.php')) {
	// looks like we're here, then.
	define('AG_APP_DIR', realpath('./src'));
} else if (!defined('AG_APP_DIR')) {
	die ('AG_APP_DIR undefined. Try using the agavi helper script.');
}

// Assume this is an agavi project if there's a webapp subdir, we'll look for classes in there too.
if (file_exists($_SERVER['PWD_PATH'].'/webapp')) {
	define('PROJECT_APP_DIR', $_SERVER['PWD_PATH'] . '/webapp');
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
	$cachedir = AG_TEST_CACHE_DIR;
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
		$classes = locateClasses(AG_APP_DIR);
		if (defined('PROJECT_APP_DIR')) { 
			$classes = array_merge((array) $classes, (array) locateClasses(PROJECT_APP_DIR, true));
		}
		if ($cachedir && is_writable($cachedir)) {
			$contents = "<?php\n// --Automagicly created ".date($datefmt)."\n//" .
									(defined('PROJECT_APP_DIR') ? " includes classes located in {$_SERVER['CWD_NAME']}/webapp, too.\n" : "no webapp classes included.\n") .
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
		// /var/www/sites/project/webapp/modules/Default/actions/IndexAction.class.php, 
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
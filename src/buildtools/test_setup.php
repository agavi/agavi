<?php
// simpletest should be in your path
require_once('simpletest/unit_tester.php');
require_once('simpletest/reporter.php');
require_once('simpletest/mock_objects.php');

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

ini_set('unserialize_callback_func', '__autoload');

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
				echo "pname: $pname\n";
				$classes[$pname] = $i->getPathname();
			}
		}
		$i->next();
	}
	return $classes;
}


function __autoload($class)
{
	$datefmt = 'c';
	$cachedir = dirname(__FILE__);
	$cache = $cachedir . '/classcache.inc';
	static $classes;
	
	if (!is_array($classes) || !array_key_exists($class, $classes)) {
		if (file_exists($cache)) {
			include($cache); 
			if (array_key_exists($class, $classes)) {
				require_once($classes[$class]);
				return;
			}
		}
		$classes = locateClasses(AG_APP_DIR);
		if (defined('PROJECT_APP_DIR')) { 
			$classes = array_merge((array) $classes, (array) locateClasses(PROJECT_APP_DIR, true));
		}
		if (is_writable($cachedir)) {
			$contents = "<?php\n//--Automagicly created ".date($datefmt)."\n//" .
									(defined('PROJECT_APP_DIR') ? "includes {$_SERVER['CWD_NAME']} webapp classes.\n" : "no webapp classes included.\n") .
									'$classes = ' .var_export($classes, true)."\n?>";
			file_put_contents($cache, $contents);
		}
	}
	if (array_key_exists($class, $classes)) {
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

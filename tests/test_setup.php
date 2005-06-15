<?php
// simpletest should be in your path
require_once("simpletest/unit_tester.php");
require_once("simpletest/reporter.php");
require_once("simpletest/mock_objects.php");

$testing_app = file_exists('webapp') ? true : false;

if (!defined('AG_APP_DIR') && $_ENV('AGAVI_INSTALLATION')) {
	define('AG_APP_DIR', $_ENV['AGAVI_INSTALLATION']);
} // eh.. maybe you have everything in your include path or helper aliens to fix things or sompn. *shrug* 


function locateClasses($path, $prefix=false)
{
	$iterator = new RecursiveDirectoryIterator($path);
	$classes = 	array();
	while ($iterator->valid()) {
		if ($iterator->isDir() && !$iterator->isDot() && $iterator->getFilename() != '.svn') {
			if ($iterator->hasChildren() ) {
				$classes += locateClasses($iterator->getPathname());
			}
		} else if ($iterator->isFile() && strrpos($iterator->getFilename(), ".class.php")) { 
			$class = str_replace ('.class.php', '', $iterator->getFilename());
			$classes[$class] = $iterator->getPathname();
			if ($prefix) {
				// add a 'prefixed' entry as well to catch possible cases where the name of the class is prefixed by the name of the module
				// the module name should be the name of the parent's parent directory
				// eg: /project/webapp/modules/Default/actions/IndexAction.class.php, the class name -might- be Default_IndexAction
				$path = explode(PATH_SEPARATOR, $iterator->getPath());
				print_r($path);
				$module = 'bleh';
				$classes[$module.'_'.$class] =& $classes[$class];
			}
		}
		$iterator->next();
	}
	return $classes;
}


function __autoload($class)
{
	static $classes;
	$classcache = '/tmp/classcache.php'; // where we will maintain a cached copy of the class matchings
	
	if (!is_array($classes)) {
		if (!file_exists($classcache)) {
			$classes = locateClasses(AG_APP_DIR);
			if (file_exists('webapp')) { 
				$classes = array_merge((array) $classes, (array) locateClasses(AG_WEBAPP_DIR, true));
			}
	  	file_put_contents('<?php $classes = '. var_export($classcache) . ';?>', $classes);
		} else {
			require_once($classcache);
		}
	}

	if (array_key_exists($class, $classes)) {
		require_once($classes[$class]);
	}
}

class AbstractedUnitTestCase extends UnitTestCase
{
}


?>

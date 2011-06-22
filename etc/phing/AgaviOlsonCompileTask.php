<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2005-2011 the Agavi Project.                                |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE   |
// | file that was distributed with this source code. You can also view the    |
// | LICENSE file online at http://www.agavi.org/LICENSE.txt                   |
// |   vi: set noexpandtab:                                                    |
// |   Local Variables:                                                        |
// |   indent-tabs-mode: t                                                     |
// |   End:                                                                    |
// +---------------------------------------------------------------------------+

class AgaviOlsonCompileTask extends Task
{
	private $olsonDir = '';
	private $outputDir = '';

	public function setOlsonDir($olsonDir)
	{
		$this->olsonDir = (string) $olsonDir;
	}

	public function setOutputDir($outputDir)
	{
		$this->outputDir = (string) $outputDir;
	}

	public function main()
	{
		set_time_limit(0);

		require_once('src/agavi.php');

		$this->olsonDir = realpath($this->olsonDir);
		$this->outputDir = realpath($this->outputDir);

		AgaviConfig::set('olson.dir', $this->olsonDir);

		AgaviConfig::set('core.app_dir', getcwd() . '/etc/olson/agavi/app');
		Agavi::bootstrap('');

		$context = AgaviContext::getInstance('');

		if(!$this->olsonDir || !file_exists($this->olsonDir)) {
			throw new BuildException('Olson data directory is not defined or does not exist.');
		}

		if(!$this->outputDir || !file_exists($this->outputDir)) {
			throw new BuildException('Timezone data output directory is not defined or does not exist.');
		}

		$this->log("Building compiling olson files in {$this->olsonDir} to {$this->outputDir}", PROJECT_MSG_INFO);

		$links = array();
		$zones = array();

		$di = new DirectoryIterator($this->olsonDir);
		foreach($di as $file) {
			if($file->isFile()) {
				// the file doesn't contain an extension so we parse it
				// and we don't want the factory time zone
				if(strpos($file->getFilename(), '.') === false && $file->getFilename() != 'factory') {
					$this->log(sprintf('compiling %s', $file->getPathname()), PROJECT_MSG_INFO);
					$parser = new AgaviTimeZoneDataParser();
					$parser->initialize(AgaviContext::getInstance($context));
					$rules = $parser->parse($file->getPathname());
					$zones = $rules['zones'] + $zones;
					$links = $rules['links'] + $links;
				}
			}
		}

		$baseCode = '<?php

/**
 * %s
 * %s
 *
 * @package    agavi
 * @subpackage translation
 *
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.11.0
 *
 * @version    $Id' . '$
 */

return %s;

?>';

		$zoneList = array();

		foreach($zones as $name => $zone) {
			$fname = preg_replace('#([^a-z0-9_])#ie', "'_'.ord('\\1').'_'", $name) . '.php';
			$pathname = $this->outputDir . '/' . $fname;
			$zone['name'] = $name;

			$zoneList[$name] = array('type' => 'zone', 'filename' => $fname);
			$this->log('Writing zone ' . $name . ' to: ' . $pathname);
			file_put_contents(
				$pathname,
				sprintf(
					$baseCode,
					sprintf(
						'Data file for timezone "%s".',
						$name
					),
					sprintf(
						'Compiled from olson file "%s", version %s.',
						$zone['source'],
						$zone['version']
					),
					var_export($zone, true)
				)
			);
		}

		foreach($links as $from => $to) {
			$zoneList[$from] = array('type' => 'link', 'to' => $to);
		}

		$this->log('Writing zone listing to: ' . $this->outputDir . '/zonelist.php');
		file_put_contents(
			$this->outputDir . '/zonelist.php',
			sprintf(
				$baseCode,
				'Zone list file.',
				sprintf('Generated on %s.', gmdate('c')),
				var_export($zoneList, true)
			)
		);
	}
}

?>
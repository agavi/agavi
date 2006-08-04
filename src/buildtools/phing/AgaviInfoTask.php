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
 * @author     Veikko Makinen <mail@veikkomakinen.com>
 * @copyright  (c) Authors
 * @since      0.11.0
 *
 * @version    $Id$
 */
class AgaviInfoTask extends Task {

	protected $agaviDir;

	protected $phing;


	public function setAgavidir($d) {
		$this->agaviDir = $d;
	}

	public function setPhing($p) {
		$this->phing = $p;
	}

	public function main() {

		include($this->agaviDir . '/config/AgaviConfig.class.php');
		include($this->agaviDir . '/version.php');

		echo "\nUsage: agavi [target]\n\n";
		echo "Targets: \n\n";

		//project
		echo "  project    Creates a new Agavi project (basic directory layout, modules, actions, views and test stubs).\n";
		echo "             Names for default actions are asked at the end of creation.\n";
		echo "  module     Creates a new module for an existing Agavi project.\n";
		echo "  action     Creates a new action for an existing Agavi project.\n";
		echo "             Action must be created for an existing module.\n";
		echo "             If called inside a module directory that module will be used as a default value.\n";
		echo "  test       Run projects unit tests\n";
		echo "  help       Show this help & info.\n";

		echo "\nInfo:\n\n";

		// agavi info
		$msg .= "  Agavi:     %s.\n";
		$msg .= "  Phing:     %s.\n";
		$msg .= "  Agavi dir: %s.\n";
		printf($msg, AgaviConfig::get('agavi.version'), $this->phing, $this->agaviDir);

		$msg = "\n  Thank you for using Agavi.\n  %s\n\n";
		printf($msg, AgaviConfig::get('agavi.url'));

	}
}
?>
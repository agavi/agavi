<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2003-2006 the Agavi Project.                                |
// | Based on the Mojavi3 MVC Framework, Copyright (c) 2003-2005 Sean Kerr.    |
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
 * AgaviLoggingConfigHandler allows you to register loggers with the system.
 *
 * @package    agavi
 * @subpackage config
 *
 * @author     Sean Kerr <skerr@mojavi.org>
 * @author     Bob Zoller <bob@agavi.org>
 * @copyright  (c) Authors
 * @since      0.10.0
 *
 * @version    $Id$
 */
class AgaviLoggingConfigHandler extends AgaviIniConfigHandler
{

	/**
	 * Execute this configuration handler.
	 *
	 * @param      string An absolute filesystem path to a configuration file.
	 *
	 * @return     string Data to be written to a cache file.
	 *
	 * @throws     <b>AgaviUnreadableException</b> If a requested configuration file
	 *                                             does not exist or is not readable.
	 * @throws     <b>AgaviParseException</b> If a requested configuration file is
	 *                                        improperly formatted.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function execute($config, $context = null)
	{
		$configurations = $this->orderConfigurations(AgaviConfigCache::parseConfig($config, false)->configurations, AgaviConfig::get('core.environment'), $context);

		// init our data, includes, methods, appenders and appenders arrays
		$data      = array();
		$loggers   = array();
		$appenders = array();
		$layouts   = array();

		foreach($configurations as $cfg) {
			if(isset($cfg->loggers)) {
				foreach($cfg->loggers as $logger) {
					$name = $logger->getAttribute('name');
					$loggers[$name]['class'] = isset($logger->class) ? $logger->class->getValue() : null;
					$loggers[$name]['priority'] = isset($logger->priority) ? $logger->priority->getValue() : null;
					if(isset($logger->appenders)) {
						foreach($logger->appenders as $appender) {
							$loggers[$name]['appenders'][] = $appender->getValue();
						}
					}
					if(!isset($loggers[$name]['params'])) {
						$loggers[$name]['params'] = array();
					}
					$loggers[$name]['params'] = $this->getItemParameters($logger, $loggers[$name]['params']);
				}
			}

			if(isset($cfg->appenders)) {
				foreach($cfg->appenders as $appender) {
					$name = $appender->getAttribute('name');
					$appenders[$name]['class'] = isset($appender->class) ? $appender->class->getValue() : null;
					$appenders[$name]['layout'] = isset($appender->layout) ? $appender->layout->getValue() : null;

					if(!isset($appenders[$name]['params'])) {
						$appenders[$name]['params'] = array();
					}
					$appenders[$name]['params'] = $this->getItemParameters($appender, $appenders[$name]['params']);
				}
			}

			if(isset($cfg->layouts)) {
				foreach($cfg->layouts as $layout) {
					$name = $layout->getAttribute('name');
					$layouts[$name]['class'] = isset($layout->class) ? $layout->class->getValue() : null;

					if(!isset($layouts[$name]['params'])) {
						$layouts[$name]['params'] = array();
					}
					$layouts[$name]['params'] = $this->getItemParameters($layout, $layouts[$name]['params']);
				}
			}
		}

		if(count($loggers) > 0) {
			foreach($layouts as $name => $layout) {
				$data[] = sprintf('$%s = new %s();', $name, $layout['class']);
				if(count($layout['params']) > 0) {
					$data[] = sprintf('$%s->initialize(%s);', $name, var_export($layout['params'], true));
				}
			}

			foreach($appenders as $name => $appender) {
				$data[] = sprintf('$%s = new %s();', $name, $appender['class']);
				if(count($appender['params']) > 0) {
					$data[] = sprintf('$%s->initialize(%s);', $name, var_export($appender['params'], true));
				}
				$data[] = sprintf('$%s->setLayout($%s);', $name, $appender['layout']);
			}

			foreach($loggers as $name => $logger) {
				$data[] = sprintf('$%s = new %s();', $name, $logger['class']);
				foreach($logger['appenders'] as $appender) {
					$data[] = sprintf('$%s->setAppender("%s", $%s);', $name, $appender, $appender);
				}
				if($logger['priority'] !== null) {
					$data[] = sprintf('$%s->setPriority(%s);', $name, $logger['priority']);
				}
				$data[] = sprintf('LoggerManager::setLogger("%s", $%s);', $name, $name);
			}
		}

		// compile data
		$retval = "<?php\n" .
				  "// auto-generated by LoggingConfigHandler\n" .
				  "// date: %s\n%s\n?>";
		$retval = sprintf($retval, date('m/d/Y H:i:s'),
						  implode("\n", $data));

		return $retval;

	}

}

?>
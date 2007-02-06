<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2003-2007 the Agavi Project.                                |
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
 * @copyright  Authors
 * @copyright  The Agavi Project
 * @since      0.10.0
 *
 * @version    $Id$
 */
class AgaviLoggingConfigHandler extends AgaviConfigHandler
{

	/**
	 * Execute this configuration handler.
	 *
	 * @param      string An absolute filesystem path to a configuration file.
	 * @param      string An optional context in which we are currently running.
	 *
	 * @return     string Data to be written to a cache file.
	 *
	 * @throws     <b>AgaviUnreadableException</b> If a requested configuration
	 *                                             file does not exist or is not
	 *                                             readable.
	 * @throws     <b>AgaviParseException</b> If a requested configuration file is
	 *                                        improperly formatted.
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function execute($config, $context = null)
	{
		$configurations = $this->orderConfigurations(AgaviConfigCache::parseConfig($config, false, $this->getValidationFile(), $this->parser)->configurations, AgaviConfig::get('core.environment'), $context);

		// init our data, includes, methods, appenders and appenders arrays
		$data      = array();
		$loggers   = array();
		$appenders = array();
		$layouts   = array();

		foreach($configurations as $cfg) {
			if(isset($cfg->loggers)) {
				foreach($cfg->loggers as $logger) {
					$name = $logger->getAttribute('name');
					if(!isset($loggers[$name])) {
						$loggers[$name] = array('class' => null, 'level' => null, 'appenders' => array(), 'params' => array());
					}
					$loggers[$name]['class'] = $logger->hasAttribute('class') ? $logger->getAttribute('class') : $loggers[$name]['class'];
					$loggers[$name]['level'] = $logger->hasAttribute('level') ? $logger->getAttribute('level') : $loggers[$name]['level'];
					if(isset($logger->appenders)) {
						foreach($logger->appenders as $appender) {
							$loggers[$name]['appenders'][] = $appender->getValue();
						}
					}
					$loggers[$name]['params'] = $this->getItemParameters($logger, $loggers[$name]['params']);
				}
			}

			if(isset($cfg->appenders)) {
				foreach($cfg->appenders as $appender) {
					$name = $appender->getAttribute('name');
					if(!isset($appenders[$name])) {
						$appenders[$name] = array('class' => null, 'layout' => null, 'params' => array());
					}
					$appenders[$name]['class'] = $appender->hasAttribute('class') ? $appender->getAttribute('class') : $appenders[$name]['class'];
					$appenders[$name]['layout'] = $appender->hasAttribute('layout') ? $appender->getAttribute('layout') : $appenders[$name]['layout'];

					$appenders[$name]['params'] = $this->getItemParameters($appender, $appenders[$name]['params']);
				}
			}

			if(isset($cfg->layouts)) {
				foreach($cfg->layouts as $layout) {
					$name = $layout->getAttribute('name');
					if(!isset($layouts[$name])) {
						$layouts[$name] = array('class' => null, 'params' => array());
					}

					$layouts[$name]['class'] = $layout->hasAttribute('class') ? $layout->getAttribute('class') : $layouts[$name]['class'];
					$layouts[$name]['params'] = $this->getItemParameters($layout, $layouts[$name]['params']);
				}
			}

			if(isset($cfg->loggers)) {
				$defaultLogger = $cfg->loggers->getAttribute('default');
				if(!isset($loggers[$defaultLogger])) {
					throw new AgaviConfigurationException(sprintf('Logger "%s" is configured as default, but does not exist.', $defaultLogger));
				}
			}
		}

		if(count($loggers) > 0) {
			foreach($layouts as $name => $layout) {
				$data[] = sprintf('$%s = new %s();', $name, $layout['class']);
				$data[] = sprintf('$%s->initialize($this->context, %s);', $name, var_export($layout['params'], true));
			}

			foreach($appenders as $name => $appender) {
				$data[] = sprintf('$%s = new %s();', $name, $appender['class']);
				$data[] = sprintf('$%s->initialize($this->context, %s);', $name, var_export($appender['params'], true));
				$data[] = sprintf('$%s->setLayout($%s);', $name, $appender['layout']);
			}

			foreach($loggers as $name => $logger) {
				$data[] = sprintf('$%s = new %s();', $name, $logger['class']);
				foreach($logger['appenders'] as $appender) {
					$data[] = sprintf('$%s->setAppender("%s", $%s);', $name, $appender, $appender);
				}
				if($logger['level'] !== null) {
					$data[] = sprintf('$%s->setLevel(%s);', $name, $logger['level']);
				}
				$data[] = sprintf('$this->context->getLoggerManager()->setLogger("%s", $%s);', $name, $name);
			}
			$data[] = sprintf('$this->context->getLoggerManager()->setDefaultLoggerName(%s);', var_export($defaultLogger, true));
		}

		// compile data
		$retval = "<?php\n" .
				  "// auto-generated by ".__CLASS__."\n" .
				  "// date: %s GMT\n%s\n?>";
		$retval = sprintf($retval, gmdate('m/d/Y H:i:s'), implode("\n", $data));

		return $retval;

	}

}

?>
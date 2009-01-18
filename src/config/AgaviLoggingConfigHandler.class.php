<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2005-2009 the Agavi Project.                                |
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
 * @author     Dominik del Bondio <ddb@bitxtender.com>
 * @author     Bob Zoller <bob@agavi.org>
 * @author     Sean Kerr <skerr@mojavi.org>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
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
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     Bob Zoller <bob@agavi.org>
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @since      0.9.0
	 */
	public function execute($config, $context = null)
	{
		$configurations = $this->orderConfigurations(AgaviConfigCache::parseConfig($config, false, $this->getValidationFile(), $this->parser)->configurations, AgaviConfig::get('core.environment'), $context);

		// init our data, includes, methods, appenders and appenders arrays
		$code      = array();
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
				$code[] = sprintf('${%s} = new %s();', var_export('_layout_' . $name, true), $layout['class']);
				$code[] = sprintf('${%s}->initialize($this->context, %s);', var_export('_layout_' . $name, true), var_export($layout['params'], true));
			}

			foreach($appenders as $name => $appender) {
				$code[] = sprintf('${%s} = new %s();', var_export('_appender_' . $name, true), $appender['class']);
				$code[] = sprintf('${%s}->initialize($this->context, %s);', var_export('_appender_' . $name, true), var_export($appender['params'], true));
				$code[] = sprintf('${%s}->setLayout(${%s});', var_export('_appender_' . $name, true), var_export('_layout_' . $appender['layout'], true));
			}

			foreach($loggers as $name => $logger) {
				$code[] = sprintf('${%s} = new %s();', var_export('_logger_' . $name, true), $logger['class']);
				foreach($logger['appenders'] as $appender) {
					$code[] = sprintf('${%s}->setAppender(%s, ${%s});', var_export('_logger_' . $name, true), var_export($appender, true), var_export('_appender_' . $appender, true));
				}
				if($logger['level'] !== null) {
					$code[] = sprintf('${%s}->setLevel(%s);', var_export('_logger_' . $name, true), $logger['level']);
				}
				$code[] = sprintf('$this->setLogger(%s, ${%s});', var_export($name, true), var_export('_logger_' . $name, true));
			}
			$code[] = sprintf('$this->setDefaultLoggerName(%s);', var_export($defaultLogger, true));
		}

		return $this->generate($code);
	}
}

?>
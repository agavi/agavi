<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2005-2011 the Agavi Project.                                |
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
 * AgaviException is the base class for all Agavi related exceptions and
 * provides an additional method for printing up a detailed view of an
 * exception.
 *
 * @package    agavi
 * @subpackage exception
 *
 * @author     David Zülke <dz@bitxtender.com>
 * @author     Sean Kerr <skerr@mojavi.org>
 * @author     Bob Zoller <bob@agavi.org>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.9.0
 *
 * @version    $Id$
 */
class AgaviException extends Exception
{
	/**
	 * Print the stack trace for this exception.
	 *
	 * @param      Exception     The original exception.
	 * @param      AgaviContext  The context instance.
	 * @param      AgaviResponse The response instance.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.9.0
	 *
	 * @deprecated Superseded by AgaviException::render()
	 */
	public static function printStackTrace(Exception $e, AgaviContext $context = null, AgaviExecutionContainer $container = null)
	{
		return self::render($e, $context, $container);
	}
	
	/**
	 * Returns a fixed stack trace in case the original one from the exception
	 * does not contain the origin as the first entry in the trace array, which
	 * appears to happen from time to time or with certain PHP/XDebug versions.
	 *
	 * @param      Exception The exception to pull the trace from.
	 * @param      Exception Optionally, the next exception to display (pulled
	 *                       from Exception::getPrevious() and displayed in
	 *                       reverse order), which will then result in identical
	 *                       parts of the stack trace not being returned.
	 *
	 * @return     array The trace containing the exception origin as first item.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.3
	 */
	public static function getFixedTrace(Exception $e, Exception $next = null)
	{
		// fix stack trace in case it doesn't contain the exception origin as the first entry
		$fixedTrace = $e->getTrace();
		
		if(isset($fixedTrace[0]['file']) && !($fixedTrace[0]['file'] == $e->getFile() && $fixedTrace[0]['line'] == $e->getLine())) {
			$fixedTrace = array_merge(array(array('file' => $e->getFile(), 'line' => $e->getLine())), $fixedTrace);
		}
		
		if($next) {
			$nextTrace = self::getFixedTrace($next);
			foreach($fixedTrace as $i => $fixedTraceItem) {
				if($fixedTraceItem == $nextTrace[1]) {
					$fixedTrace = array_slice($fixedTrace, 0, $i);
					break;
				}
			}
		}
		
		return $fixedTrace;
	}
	
	/**
	 * Build a list of parameters passed to a method. Example:
	 * array([object AgaviFilter], 'baz' => array(1, 2), 'log' => [resource stream])
	 *
	 * @param      array An (associative) array of variables.
	 * @param      bool  Whether or not to style and encode for HTML output.
	 *
	 * @return     string A string, possibly formatted using HTML "em" tags.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public static function buildParamList($params, $html = true, $level = 1)
	{
		if($html) {
			$oem = '<em>';
			$cem = '</em>';
		} else {
			$oem = '';
			$cem = '';
		}
		
		$retval = array();
		foreach($params as $key => $param) {
			if(is_string($key)) {
				if(preg_match('/^(.{5}).{2,}(.{5})$/us', $key, $matches)) {
					$key = $matches[1] . '…' . $matches[2];
				}
				$key = var_export($key, true) . ' => ';
				if($html) {
					$key = htmlspecialchars($key);
				}
			} else {
				$key = '';
			}
			switch(gettype($param)) {
				case 'array':
					$retval[] = $key . 'array(' . ($level < 2 ? self::buildParamList($param, $html, ++$level) : (count($param) ? '…' : '')) . ')';
					break;
				case 'object':
					if($html) {
						$retval[] = $key . '[object <em>' . get_class($param) . '</em>]';
					} else {
						$retval[] = $key . '[object ' . get_class($param) . ']';
					}
					break;
				case 'resource':
					if($html) {
						$retval[] = $key . '[resource <em>' . htmlspecialchars(get_resource_type($param)) . '</em>]';
					} else {
						$retval[] = $key . '[resource ' . get_resource_type($param) . ']';
					}
					break;
				case 'string':
					$val = $param;
					if(preg_match('/^(.{20}).{3,}(.{20})$/us', $val, $matches)) {
						$val = $matches[1] . ' … ' . $matches[2];
					}
					$val = var_export($val, true);
					if($html) {
						$retval[] = $key . htmlspecialchars($val);
					} else {
						$retval[] = $key . $val;
					}
					break;
				default:
					if($html) {
						$retval[] = $key . htmlspecialchars(var_export($param, true));
					} else {
						$retval[] = $key . var_export($param, true);
					}
			}
		}
		return implode(', ', $retval);
	}
	
	/**
	 * Perform PHP syntax highlighting on the given file.
	 *
	 * @param      string The path of the file to highlight.
	 *
	 * @return     array An 0-indexed array of HTML-highlighted code lines.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.3
	 */
	public static function highlightFile($filepath)
	{
		return self::highlightString(file_get_contents($filepath));
	}
	
	/**
	 * Perform PHP syntax highlighting on the given code string.
	 *
	 * @param      string The PHP code to highlight.
	 *
	 * @return     array An 0-indexed array of HTML-highlighted code lines.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.3
	 */
	public static function highlightString($code)
	{
		$code = highlight_string(str_replace('	', '  ', $code), true);
		// time to cleanup this highlighted string
		// first, drop all newlines (we'll explode by "<br />")
		$code = str_replace(array("\r\n", "\n", "\r"), array('', '', ''), $code);
		// second, remove start and end wrappers and replace &nbsp; with numeric entity
		$code = str_replace(array(sprintf('<code><span style="color: %s">', ini_get('highlight.html')), '</span></code>', '&nbsp;'), array('', '', '&#160;'), $code);
		// make an array of lines
		$code = explode('<br />', $code);
		// iterate and cleanup each line
		$remember = null;
		foreach($code as &$line) {
			// we need at least an nbsp for empty lines
			if($line == '') {
				$line = '&#160;';
			}
			
			// drop leading </span>
			if(strpos($line, '</span>') === 0) {
				$line = substr($line, 7);
				// no style to carry over from previous line(s)
				$remember = null;
			}
			
			// prepend style from previous line if we have one
			if($remember) {
				$line = sprintf('<span style="color: %s">%s', $remember, $line);
			}
			
			$openingSpanPos = strpos($line, '<span');
			$openingSpanRPos = strrpos($line, '<span');
			$closingSpanPos = strpos($line, '</span>');
			$closingSpanRPos = strrpos($line, '</span>');
			
			$balanced = (($openingSpanCount = preg_match_all('#<span#', $line, $matches)) == ($closingSpanCount = preg_match_all('#</span>#', $line, $matches)));
			if($balanced && $openingSpanPos !== false && $openingSpanPos < $closingSpanPos) {
				// already balanced, no further cleanup necessary
				$remember = null;
				continue;
			}
			
			if(substr($line, -7) == '</span>') {
				// discard previous style if style terminates in this line
				$remember = null;
			} else {
				// otherwise, remember last style from this line if there is one
				if($openingSpanRPos !== false) {
					// must remember previous color; 20 is the length of '<span style="color: '
					// we're using strpos since someone could set the colors to "#333" or "red" through php.ini, so we don't know the length
					$remember = substr($line, $openingSpanRPos + 20, strpos($line, '"', $openingSpanRPos + 20) - ($openingSpanRPos + 20));
				}
				// append closing tag
				$line .= '</span>';
				$closingSpanCount++;
			}
			
			// in case things still are not right...
			// can happen for instance when the first line of the file is HTML and we drop the first span, since that is a wrapper for everything
			if($openingSpanCount < $closingSpanCount) {
				$line = sprintf('%1$s%2$s', str_repeat('<span color="%3s">', $closingSpanCount - $openingSpanCount), $line, ini_get('highlight.html'));
			}
			if($closingSpanCount < $openingSpanCount) {
				$line = sprintf('%s%s', $line, str_repeat('</span>', $openingSpanCount - $closingSpanCount), $line);
			}
		}
		
		return $code;
	}
	
	/**
	 * Pretty-print this exception using a template.
	 *
	 * @param      Exception     The original exception.
	 * @param      AgaviContext  The context instance.
	 * @param      AgaviResponse The response instance.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      1.0.0
	 */
	public static function render(Exception $e, AgaviContext $context = null, AgaviExecutionContainer $container = null)
	{
		// exit code is 70, EX_SOFTWARE, according to /usr/include/sysexits.h: http://cvs.opensolaris.org/source/xref/on/usr/src/head/sysexits.h
		// nice touch: an exception template can change this value :)
		$exitCode = 70;
		
		$exceptions = array();
		if(version_compare(PHP_VERSION, '5.3', 'ge')) {
			// reverse order of exceptions
			$ce = $e;
			while($ce) {
				array_unshift($exceptions, $ce);
				$ce = $ce->getPrevious();
			}
		} else {
			$exceptions[] = $e;
		}
		
		// discard any previous output waiting in the buffer
		while(@ob_end_clean());
		
		if($container !== null && $container->getOutputType() !== null && $container->getOutputType()->getExceptionTemplate() !== null) { 
			// an exception template was defined for the container's output type
			include($container->getOutputType()->getExceptionTemplate()); 
			exit($exitCode);
		}
		
		if($context !== null && $context->getController() !== null) {
			try {
				// check if an exception template was defined for the default output type
				if($context->getController()->getOutputType()->getExceptionTemplate() !== null) {
					include($context->getController()->getOutputType()->getExceptionTemplate());
					exit($exitCode);
				}
			} catch(Exception $e2) {
				unset($e2);
			}
		}
		
		if($context !== null && AgaviConfig::get('exception.templates.' . $context->getName()) !== null) {
			// a template was set for this context
			include(AgaviConfig::get('exception.templates.' . $context->getName()));
			exit($exitCode);
		}
		
		// include default exception template
		include(AgaviConfig::get('exception.default_template'));
		
		// bail out
		exit($exitCode);
	}
}

?>
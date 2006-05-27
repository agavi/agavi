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
 * AgaviException is the base class for all Agavi related exceptions and
 * provides an additional method for printing up a detailed view of an
 * exception.
 *
 * @package    agavi
 * @subpackage exception
 *
 * @author     Sean Kerr <skerr@mojavi.org>
 * @author     Bob Zoller <bob@agavi.org>
 * @copyright  (c) Authors
 * @since      0.9.0
 *
 * @version    $Id$
 */
class AgaviException extends Exception
{
	/**
	 * Print the stack trace for this exception.
	 *
	 * @param      string The format you wish to use for printing. Options
	 *                    include:
	 *                    - html
	 *                    - plain
	 *
	 * @author     Sean Kerr <skerr@mojavi.org>
	 * @author     Bob Zoller <bob@agavi.org>
	 * @since      0.9.0
	 */
	public static function printStackTrace(Exception $e, AgaviContext $context = null)
	{
		// exception related properties
		$class     = ($e->getFile() != null)
				     ? AgaviToolkit::extractClassName($e->getFile()) : 'N/A';

		$class     = ($class != '')
				     ? $class : 'N/A';

		$code      = ($e->getCode() > 0)
				     ? $e->getCode() : 'N/A';

		$file      = ($e->getFile() != null)
				     ? $e->getFile() : 'N/A';

		$line      = ($e->getLine() != null)
				     ? $e->getLine() : 'N/A';

		$message   = ($e->getMessage() != null)
				     ? $e->getMessage() : 'N/A';

		$name      = get_class($e);

		$traceData = $e->getTrace();
		$trace     = array();
		
		$format = 'html';

		// lower-case the format to avoid sensitivity issues
		$format = strtolower($format);

		if ($trace !== null && count($traceData) > 0)
		{

			// format the stack trace
			for ($i = 0, $z = count($traceData); $i < $z; $i++)
			{

				if (!isset($traceData[$i]['file']))
				{

				    // no file key exists, skip this index
				    continue;

				}

				// grab the class name from the file
				// (this only works with properly named classes)
				$tClass = AgaviToolkit::extractClassName($traceData[$i]['file']);
				$tType      = isset($traceData[$i]['type']) ? $traceData[$i]['type'] : '::';

				$tFile      = $traceData[$i]['file'];
				$tFunction  = $traceData[$i]['function'];
				$tLine      = $traceData[$i]['line'];

				if ($tClass != null)
				{

				    $tFunction = $tClass . $tType . $tFunction . '()';

				} else
				{

				    $tFunction = $tFunction . '()';

				}

				if ($format == 'html')
				{

				    $tFunction = '<strong>' . $tFunction . '</strong>';

				}

				$data = 'at %s in [%s:%s]';
				$data = sprintf($data, $tFunction, $tFile, $tLine);

				$trace[] = $data;

			}

		}

		switch ($format)
		{

			case 'html':

				// print the exception info
				echo '<!DOCTYPE html
				      PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
				      "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
				      <html xmlns="http://www.w3.org/1999/xhtml"
						    xml:lang="en" lang="en">
				      <head>
				      <meta http-equiv="Content-Type"
						    content="text/html; charset=iso-8859-1"/>
				      <title>Agavi Exception</title>
				      <style type="text/css">

				      #exception {
						  background-color: #EEEEEE;
						  border:           solid 1px #750000;
						  font-family:      verdana, helvetica, sans-serif;
						  font-size:        76%;
						  font-style:       normal;
						  font-weight:      normal;
						  margin:           10px;
				      }

				      #help {
						  color:     #750000;
						  font-size: 0.9em;
				      }

				      .message {
						  color:       #FF0000;
						  font-weight: bold;
				      }

				      .title {
						  font-size:   1.1em;
						  font-weight: bold;
				      }

				      td {
						  background-color: #EEEEEE;
						  padding:          5px;
				      }

				      th {
						  background-color: #750000;
						  color:            #FFFFFF;
						  font-size:        1.2em;
						  font-weight:      bold;
						  padding:          5px;
						  text-align:       left;
				      }

				      </style>
				      </head>
				      <body>

				      <table id="exception" cellpadding="0" cellspacing="0">
						  <tr>
						      <th colspan="2">' . $name . '</th>
						  </tr>
						  <tr>
						      <td class="title">Message:</td>
						      <td class="message">' . $message . '</td>
						  </tr>
						  <tr>
						      <td class="title">Code:</td>
						      <td>' . $code . '</td>
						  </tr>
						  <tr>
						      <td class="title">Class:</td>
						      <td>' . $class . '</td>
						  </tr>
						  <tr>
						      <td class="title">File:</td>
						      <td>' . $file . '</td>
						  </tr>
						  <tr>
						      <td class="title">Line:</td>
						      <td>' . $line . '</td>
						  </tr>';

				if (count($trace) > 0)
				{

				    echo '<tr>
						      <th colspan="2">Stack Trace</th>
						  </tr>';

				    foreach ($trace as $line)
				    {

						echo '<tr>
						          <td colspan="2">' . $line . '</td>
						      </tr>';

				    }

				}

				echo     '<tr>
						      <th colspan="2">Info</th>
						  </tr>
						  <tr>
						      <td class="title">Agavi Version:</td>
						      <td>' . AgaviConfig::get('agavi.version') . '</td>
						  </tr>
						  <tr>
						      <td class="title">PHP Version:</td>
						      <td>' . PHP_VERSION . '</td>
						  </tr>
						  <tr id="help">
						      <td colspan="2">
						          For help resolving this issue, please visit
						          <a href="http://www.agavi.org">www.agavi.org</a>.
						      </td>
						  </tr>
				      </table>

				      </body>
				      </html>';

				break;

			case 'plain':
			default:

				// print the exception info
				echo $name . "\n\tMessage: " . $message . "\n\tCode: " . $code . "\n\tClass: " . $class . "\n\tFile: " . $file . "\n\tLine: " . $line . "\n";

				if (count($trace) > 0)
				{

					echo "Stack Trace:\n";

					foreach ($trace as $line)
					{

						echo "\t$line\n";

					}

				}

				echo 'Agavi Version: ' . AgaviConfig::get('agavi.version') . "\nPHP Version: " . PHP_VERSION . "\n";

				break;

		}

		exit;

	}
}

?>
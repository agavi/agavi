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
 * AgaviGettextMoReader reads a .mo file into an array.
 * 
 * @package    agavi
 * @subpackage translation
 * 
 * @since      0.11.0 
 * @author     Dominik del Bondio <ddb@bitxtender.com>
 * @copyright  (c) Authors
 *
 * @version    $Id$
 */
final class AgaviGettextMoReader
{

	/**
	 * Parses a .mo file and returns the data as an array.
	 * For the format see the gettext manual
	 */
	public static function readFile($filePath)
	{
		$content = file_get_contents($filePath);

		// WTF! php 5.1.2 (at least on my ubuntu box) returns 950412de0 (i have NO
		// clue where the trailing 0 comes from, so cut it out again
		$fileId = substr(array_pop(unpack('H*', substr($content, 0, 4))), 0, 8);

		// little endian: V   big endian: N
		if($fileId == 'de120495') {
			// the file is in little endian format
			$longPackChar = 'V';
		} elseif($fileId == '950412de') {
			// big endian
			$longPackChar = 'N';
		} else {
			throw new AgaviException('Unknown .mo file header. Was: ' . $fileId);
		}

		$fileHeader = unpack($longPackChar . '*', substr($content, 4, 24));

		$rev = $fileHeader[1];
		$numStrings = $fileHeader[2];
		$originalOffset = $fileHeader[3];
		$translatedOffset = $fileHeader[4];
		// we don't need the hashing table

		$strings = array();

		$originalOffsetPos = $originalOffset;
		$translatedOffsetPos = $translatedOffset;
		$i = 0;

		for($i = 0; $i < $numStrings; ++$i) {
			$strings[self::getString($content, $originalOffsetPos, $longPackChar)] = self::getString($content, $translatedOffsetPos, $longPackChar);

			$originalOffsetPos += 8;
			$translatedOffsetPos += 8;
		}

		return $strings;
	}

	protected static function getString($content, $infoOffset, $unpackCharLong)
	{
			$len = unpack($unpackCharLong, substr($content, $infoOffset, 4));
			$offset = unpack($unpackCharLong, substr($content, $infoOffset + 4, 4));
			return substr($content, $offset[1], $len[1]);
	}
}

?>
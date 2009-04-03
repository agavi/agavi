<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2005-2009 the Agavi Project.                                |
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
 * AgaviInflector allows you to singularize or pluralize an English word
 *
 * @package    agavi
 * @subpackage util
 *
 * @author     Dominik del Bondio <ddb@bitxtender.com>
 * @author     Thomas Bachem <mail@thomasbachem.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.11.0
 *
 * @version    $Id$
 */
final class AgaviInflector
{
	/**
	 * @var        array singular => plural mapping
	 */
	protected static $singularMatches = array(
		'/move$/i' => 'moves',
		'/sex$/i' => 'sexes',
		'/child$/i' => 'children',
		'/man$/i' => 'men',
		'/person$/i' => 'people',
		'/(quiz)$/i' => '$1zes',
		'/^(ox)$/i' => '$1en',
		'/(m|l)ouse$/i' => '$1ice',
		'/(matr|vert|ind)ix|ex$/i' => '$1ices',
		'/(x|ch|ss|sh)$/i' => '$1es',
		'/([^aeiouy]|qu)ies$/i' => '$1y',
		'/([^aeiouy]|qu)y$/i' => '$1ies',
		'/(hive)$/i' => '$1s',
		'/(?:([^f])fe|([lr])f)$/i' => '$1$2ves',
		'/sis$/i' => 'ses',
		'/([ti])um$/i' => '$1a',
		'/(buffal|tomat)o$/i' => '$1oes',
		'/(bu)s$/i' => '$1ses',
		'/(alias|status)$/i' => '$1es',
		'/(octop|vir)us$/i' => '$1i',
		'/(ax|test)is$/i' => '$1es',
		'/s$/i' => 's',
		'/$/' => 's',
	);

	/**
	 * @var        array plural => singular mapping
	 */
	protected static $pluralMatches = array(
		'/cookies$/i' => 'cookie',
		'/moves$/i' => 'move',
		'/sexes$/i' => 'sex',
		'/children$/i' => 'child',
		'/men$/i' => 'man',
		'/people$/i' => 'person',
		'/databases$/i'=> 'database',
		'/caches$/i'=> 'cache',
		'/(quiz)zes$/i' => '\1',
		'/(matr)ices$/i' => '\1ix',
		'/(vert|ind)ices$/i' => '\1ex',
		'/^(ox)en/i' => '\1',
		'/(alias|status)es$/i' => '\1',
		'/([octop|vir])i$/i' => '\1us',
		'/(cris|ax|test)es$/i' => '\1is',
		'/(shoe)s$/i' => '\1',
		'/(o)es$/i' => '\1',
		'/(bus)es$/i' => '\1',
		'/([m|l])ice$/i' => '\1ouse',
		'/(x|ch|ss|sh)es$/i' => '\1',
		'/(m)ovies$/i' => '\1ovie',
		'/(s)eries$/i' => '\1eries',
		'/([^aeiouy]|qu)ies$/i' => '\1y',
		'/([lr])ves$/i' => '\1f',
		'/(tive)s$/i' => '\1',
		'/(hive)s$/i' => '\1',
		'/([^f])ves$/i' => '\1fe',
		'/(^analy)ses$/i' => '\1sis',
		'/((a)naly|(b)a|(d)iagno|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$/i' => '\1\2sis',
		'/([ti])a$/i' => '\1um',
		'/(n)ews$/i' => '\1ews',
		'/s$/i' => '',
	);

	/**
	 * @var        array An array of uncountable nouns as keys
	 */
	protected static $uncountables = array(
		'equipment' => true,
		'information' => true,
		'rice' => true,
		'money' => true,
		'species' => true,
		'series' => true,
		'fish' => true,
		'sheep' => true,
	);
	
	/**
	 * @var        array An array remembering the results of singularize()
	 */
	protected static $singularizeCache = array();
	
	/**
	 * @var        array An array remembering the results of pluralize()
	 */
	protected static $pluralizeCache = array();

	/**
	 * Translates a noun from its plural form in its singular form
	 *
	 * @param      string Word to singularize
	 *
	 * @return     string The singular form of the word
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     Thomas Bachem <mail@thomasbachem.com>
	 * @since      0.11.0
	 */
	public static function singularize($word)
	{
		if(isset(self::$singularizeCache[$word])) {
			return self::$singularizeCache[$word];
		}
		
		if(isset(self::$uncountables[$word])) {
			return $word;
		}

		$count = 0;
		$singularizedWord = $word;
		foreach(self::$pluralMatches as $regexp => $replacement) {
			$singularizedWord = preg_replace($regexp, $replacement, $word, 1, $count);
			if($count) {
				break;
			}
		}
		
		self::$singularizeCache[$word] = $singularizedWord;
		
		return $singularizedWord;
	}

	/**
	 * Translates a noun from its singular form in its plural form
	 *
	 * @param      string Word to pluralize
	 *
	 * @return     string The plural form of the word
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     Thomas Bachem <mail@thomasbachem.com>
	 * @since      0.11.0
	 */
	public static function pluralize($word)
	{
		if(isset(self::$pluralizeCache[$word])) {
			return self::$pluralizeCache[$word];
		}
		
		if(isset(self::$uncountables[$word])) {
			return $word;
		}

		$count = 0;
		$pluralizedWord = $word;
		foreach(self::$singularMatches as $regexp => $replacement) {
			$pluralizedWord = preg_replace($regexp, $replacement, $pluralizedWord, 1, $count);
			if($count) {
				break;
			}
		}
		
		self::$pluralizeCache[$word] = $pluralizedWord;
		
		return $pluralizedWord;
	}
}

?>
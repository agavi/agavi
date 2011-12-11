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
	 * @const      string
	 */
		const UNCOUNTABLE_REGEX = '/(
			advice|
			equipment|
			information|
			(?<![a-z0-9])rice| # after a boundary, or else "price" is treated the same; cannot use \b because that includes the underscore
			sugar|
			water|
			electricity|
			gas|
			power|
			money|
			music|
			love|
			furniture|
			(lug|bag)gage|
			species|
			series|
			bison|
			deer|
			fish|
			moose|
			sheep|
			jeans
		)$/ix';
	
	/**
	 * @var        array singular => plural mapping
	 */
	protected static $singularMatches = array(
		self::UNCOUNTABLE_REGEX => '$1',
		'/child$/i' => 'children',
		'/man$/i' => 'men',
		'/person$/i' => 'people',
		'/(quiz)$/i' => '$1zes',
		'/(?<![a-z0-9])(ox)(en)?$/i' => '$1en',
		'/(?<![a-z0-9])tooth$/i' => 'teeth', // cannot use \b because that includes the underscore
		'/(?<![a-z0-9])foot$/i' => 'feet', // cannot use \b because that includes the underscore
		'/(?<![a-z0-9])goose$/i' => 'geese', // cannot use \b because that includes the underscore
		'/(m|l)ice$/i' => '$1ice',
		'/(m|l)ouse$/i' => '$1ice',
		'/(matr|vert|ind)(?:ix|ex)$/i' => '$1ices',
		'/(x|ch|ss|sh)$/i' => '$1es',
		'/([^aeiouy]|qu)y$/i' => '$1ies',
		'/(hive)$/i' => '$1s',
		'/(?:([^f])fe|([lr])f)$/i' => '$1$2ves',
		'/sis$/i' => 'ses',
		'/criterion$/i' => 'criteria',
		'/([ti])a$/i' => '$1a',
		'/([ti])um$/i' => '$1a',
		'/(buffal|tomat)o$/i' => '$1oes',
		'/(bu)s$/i' => '$1ses',
		'/(alias|status)$/i' => '$1es',
		'/(octop|vir)i$/i' => '$1i',
		'/(octop|vir)us$/i' => '$1i',
		'/(ax|test)is$/i' => '$1es',
		'/s$/i' => 's',
		'/$/' => 's',
	);

	/**
	 * @var        array plural => singular mapping
	 */
	protected static $pluralMatches = array(
		self::UNCOUNTABLE_REGEX => '\1',
		'/children$/i' => 'child',
		'/men$/i' => 'man',
		'/people$/i' => 'person',
		'/(database)s$/i' => '\1',
		'/(quiz)zes$/i' => '\1',
		'/(matr)ices$/i' => '\1ix',
		'/(vert|ind)ices$/i' => '\1ex',
		'/(?<![a-z0-9])(ox)en/i' => '\1',
		'/(alias|status)es$/i' => '\1',
		'/(octop|vir)i$/i' => '\1us',
		'/(cris|ax|test)es$/i' => '\1is',
		'/(cook|zomb|mov)ies$/i' => '\1ie',
		'/(sho|cach|mov)es$/i' => '\1e',
		'/(o)es$/i' => '\1',
		'/(bus)es$/i' => '\1',
		'/teeth$/i' => 'tooth',
		'/feet$/i' => 'foot',
		'/geese$/i' => 'goose',
		'/(m|l)ice$/i' => '\1ouse',
		'/(x|ch|ss|sh)es$/i' => '\1',
		'/(s)eries$/i' => '\1eries',
		'/([^aeiouy]|qu)ies$/i' => '\1y',
		'/([lr])ves$/i' => '\1f',
		'/(tive)s$/i' => '\1',
		'/(hive)s$/i' => '\1',
		'/([^f])ves$/i' => '\1fe',
		'/(^analy)ses$/i' => '\1sis',
		'/((a)naly|(b)a|(d)iagno|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$/i' => '\1\2sis',
		'/criteria$/i' => 'criterion',
		'/([ti])a$/i' => '\1um',
		'/(n)ews$/i' => '\1ews',
		'/s$/i' => '',
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
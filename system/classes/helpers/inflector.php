<?php
/**
 * Inflector helper class.
 *
 * @version		$Id: inflector.php 242 2010-02-10 23:06:09Z Shaun $
 *
 * @package		System
 * @subpackage	Helpers
 * @author		enormego
 * @copyright	(c) 2009-2010 enormego
 * @license		http://license.eightphp.com
 */
class inflector_Core {

	// Cached inflections
	protected static $cache = array();

	// Uncountable and irregular words
	protected static $uncountable;
	protected static $irregular;

	/**
	 * Checks if a word is defined as uncountable.
	 *
	 * @param   string   word to check
	 * @return  boolean
	 */
	public static function uncountable($str) {
		if(self::$uncountable === nil) {
			// Cache uncountables
			self::$uncountable = Eight::config('inflector.uncountable');

			// Make uncountables mirroed
			self::$uncountable = array_combine(self::$uncountable, self::$uncountable);
		}

		return isset(self::$uncountable[strtolower($str)]);
	}

	/**
	 * Makes a plural word singular.
	 *
	 * @param   string   word to singularize
	 * @param   integer  number of things
	 * @return  string
	 */
	public static function singular($str, $count = nil) {
		// Remove garbage
		$str = strtolower(trim($str));

		if(is_string($count)) {
			// Convert to integer when using a digit string
			$count = (int) $count;
		}

		// Do nothing with a single count
		if($count === 0 OR $count > 1)
			return $str;

		// Cache key name
		$key = 'singular_'.$str.$count;

		if(isset(self::$cache[$key]))
			return self::$cache[$key];

		if(inflector::uncountable($str))
			return self::$cache[$key] = $str;

		if(empty(self::$irregular)) {
			// Cache irregular words
			self::$irregular = Eight::config('inflector.irregular');
		}

		if($irregular = array_search($str, self::$irregular)) {
			$str = $irregular;
		} elseif(preg_match('/[sxz]es$/', $str) OR preg_match('/[^aeioudgkprt]hes$/', $str)) {
			// Remove "es"
			$str = substr($str, 0, -2);
		} elseif(preg_match('/[^aeiou]ies$/', $str)) {
			$str = substr($str, 0, -3).'y';
		} elseif(substr($str, -1) === 's' and substr($str, -2) !== 'ss') {
			$str = substr($str, 0, -1);
		}

		return self::$cache[$key] = $str;
	}

	/**
	 * Makes a singular word plural.
	 *
	 * @param   string  word to pluralize
	 * @return  string
	 */
	public static function plural($str, $count = nil) {
		// Remove garbage
		$str = strtolower(trim($str));

		if(is_string($count)) {
			// Convert to integer when using a digit string
			$count = (int) $count;
		}

		// Do nothing with singular
		if($count === 1)
			return $str;

		// Cache key name
		$key = 'plural_'.$str.$count;

		if(isset(self::$cache[$key]))
			return self::$cache[$key];

		if(inflector::uncountable($str))
			return self::$cache[$key] = $str;

		if(empty(self::$irregular)) {
			// Cache irregular words
			self::$irregular = Eight::config('inflector.irregular');
		}

		if(isset(self::$irregular[$str])) {
			$str = self::$irregular[$str];
		} elseif(preg_match('/[sxz]$/', $str) OR preg_match('/[^aeioudgkprt]h$/', $str)) {
			$str .= 'es';
		} elseif(preg_match('/[^aeiou]y$/', $str)) {
			// Change "y" to "ies"
			$str = substr_replace($str, 'ies', -1);
		} else {
			$str .= 's';
		}

		// Set the cache and return
		return self::$cache[$key] = $str;
	}

	/**
	 * Makes a phrase camel case.
	 *
	 * @param   string  phrase to camelize
	 * @return  string
	 */
	public static function camelize($str) {
		$str = 'x'.strtolower(trim($str));
		$str = ucwords(preg_replace('/[\s_]+/', ' ', $str));

		return substr(str_replace(' ', '', $str), 1);
	}

	/**
	 * Makes a phrase underscored instead of spaced.
	 *
	 * @param   string  phrase to underscore
	 * @return  string
	 */
	public static function underscore($str) {
		return preg_replace('/\s+/', '_', trim($str));
	}

	/**
	 * Makes an underscored or dashed phrase human-reable.
	 *
	 * @param   string  phrase to make human-reable
	 * @return  string
	 */
	public static function humanize($str) {
		return preg_replace('/[_-]+/', ' ', trim($str));
	}
	
	/**
	 * Returns a URL Safe Title Slug, limited to the specified characters
	 * 
	 * @param	string	title
	 * @param	int		limit (default 25)
	 * @return	string	url safe slug
	 */
	public static function title_slug($title, $limit=25) {
		return preg_replace("/([_+]|_)$/", "", str::limit_chars(inflector::underscore(str::strip_all_but(strtolower($title), "a-z0-9 \_")), $limit, ""));
	}
} // End inflector
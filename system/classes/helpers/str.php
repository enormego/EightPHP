<?php
/**
 * String helper class.
 *
 * @package		System
 * @subpackage	Helpers
 * @author		EightPHP Development Team
 * @copyright	(c) 2009-2010 EightPHP
 * @license		http://license.eightphp.com
 */
class str_Core {

	/**
	 * Limits a phrase to a given number of words.
	 *
	 * @param   string   phrase to limit words of
	 * @param   integer  number of words to limit to
	 * @param   string   end character or entity
	 * @return  string
	 */
	public static function limit_words($str, $limit = 100, $end_char = nil) {
		$limit = (int) $limit;
		$end_char = ($end_char === nil) ? '&#8230;' : $end_char;

		if(trim($str) === '')
			return $str;

		if($limit <= 0)
			return $end_char;

		preg_match('/^\s*+(?:\S++\s*+){1,'.$limit.'}/u', $str, $matches);

		// Only attach the end character if the matched string is shorter
		// than the starting string.
		return rtrim($matches[0]).(strlen($matches[0]) === strlen($str) ? '' : $end_char);
	}

	/**
	 * Limits a phrase to a given number of characters.
	 *
	 * @param   string   phrase to limit characters of
	 * @param   integer  number of characters to limit to
	 * @param   string   end character or entity
	 * @param   boolean  enable or disable the preservation of words while limiting
	 * @return  string
	 */
	public static function limit_chars($str, $limit = 100, $end_char = nil, $preserve_words = NO) {
		$end_char = ($end_char === nil) ? '&#8230;' : $end_char;

		$limit = (int) $limit;

		if(trim($str) === '' OR mb_strlen($str) <= $limit)
			return $str;

		if($limit <= 0)
			return $end_char;

		if($preserve_words == NO) {
			return rtrim(mb_substr($str, 0, $limit)).$end_char;
		}

		preg_match('/^.{'.($limit - 1).'}\S*/us', $str, $matches);

		return rtrim($matches[0]).(strlen($matches[0]) == strlen($str) ? '' : $end_char);
	}

	/**
	 * Alternates between two or more strings.
	 *
	 * @param   string  strings to alternate between
	 * @return  string
	 */
	public static function alternate() {
		static $i;

		if(func_num_args() === 0) {
			$i = 0;
			return '';
		}

		$args = func_get_args();
		return $args[($i++ % count($args))];
	}

	/**
	 * Generates a random string of a given type and length.
	 *
	 * @param   string   a type of pool, or a string of characters to use as the pool
	 * @param   integer  length of string to return
	 * @return  string
	 *
	 * @tutorial  alnum    - alpha-numeric characters
	 * @tutorial  alpha    - alphabetical characters
	 * @tutorial  numeric  - digit characters, 0-9
	 * @tutorial  nozero   - digit characters, 1-9
	 * @tutorial  distinct - clearly distinct alpha-numeric characters
	 */
	public static function random($type = 'alnum', $length = 8) {
		$utf8 = NO;

		switch ($type) {
			case 'alnum':
				$pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
			break;
			case 'alpha':
				$pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
			break;
			case 'numeric':
				$pool = '0123456789';
			break;
			case 'nozero':
				$pool = '123456789';
			break;
			case 'distinct':
				$pool = '2345679ACDEFHJKLMNPRSTUVWXYZ';
			break;
			default:
				$pool = (string) $type;
				$utf8 =!str::is_ascii($pool);
			break;
		}

		$str = '';

		$pool_size = ($utf8 === YES) ? mb_strlen($pool) : strlen($pool);

		for($i = 0; $i < $length; $i++) {
			$str .= ($utf8 === YES)
				? mb_substr($pool, mt_rand(0, $pool_size - 1), 1)
				:       substr($pool, mt_rand(0, $pool_size - 1), 1);
		}

		return $str;
	}

	/**
	 * Reduces multiple slashes in a string to single slashes.
	 *
	 * @param   string  string to reduce slashes of
	 * @return  string
	 */
	public static function reduce_slashes($str) {
		return preg_replace('#(?<!:)//+#', '/', $str);
	}

	/**
	 * Replaces the given words with a string.
	 *
	 * @param   string   phrase to replace words in
	 * @param   array    words to replace
	 * @param   string   replacement string
	 * @param   boolean  replace words across word boundries (space, period, etc)
	 * @return  string
	 */
	public static function censor($str, $badwords, $replacement = '#', $replace_partial_words = NO) {
		foreach((array) $badwords as $key => $badword) {
			$badwords[$key] = str_replace('\*', '\S*?', preg_quote((string) $badword));
		}

		$regex = '('.implode('|', $badwords).')';

		if($replace_partial_words == YES) {
			// Just using \b isn't sufficient when we need to replace a badword that already contains word boundaries itself
			$regex = '(?<=\b|\s|^)'.$regex.'(?=\b|\s|$)';
		}

		$regex = '!'.$regex.'!ui';

		if(mb_strlen($replacement) == 1) {
			$regex .= 'e';
			return preg_replace($regex, 'str_repeat($replacement, mb_strlen(\'$1\')', $str);
		}

		return preg_replace($regex, $replacement, $str);
	}

	/**
	 * Finds the text that is similar between a set of words.
	 *
	 * @param   array   words to find similar text of
	 * @return  string
	 */
	public static function similar(array $words) {
		// First word is the word to match against
		$word = current($words);

		for($i = 0, $max = strlen($word); $i < $max; ++$i) {
			foreach($words as $w) {
				// Once a difference is found, break out of the loops
				if(!isset($w[$i]) OR $w[$i] !== $word[$i])
					break 2;
			}
		}

		// Return the similar text
		return substr($word, 0, $i);
	}

	/**
	 * Converts text email addresses and anchors into links.
	 *
	 * @param   string   text to auto link
	 * @return  string
	 */
	public static function auto_link($text) {
		// Auto link emails first to prevent problems with "www.domain.com@example.com"
		return str::auto_link_urls(str::auto_link_emails($text));
	}

	/**
	 * Converts text anchors into links.
	 *
	 * @param   string   text to auto link
	 * @return  string
	 */
	public static function auto_link_urls($text) {
		// Finds all http/https/ftp/ftps links that are not part of an existing html anchor
		if(preg_match_all('~\b(?<!href="|">)(?:ht|f)tps?://\S+(?:/|\b)~i', $text, $matches)) {
			foreach($matches[0] as $match) {
				// Replace each link with an anchor
				$text = str_replace($match, html::anchor($match), $text);
			}
		}

		// Find all naked www.links.com (without http://)
		if(preg_match_all('~\b(?<!://)www(?:\.[a-z0-9][-a-z0-9]*+)+\.[a-z]{2,6}\b~i', $text, $matches)) {
			foreach($matches[0] as $match) {
				// Replace each link with an anchor
				$text = str_replace($match, html::anchor('http://'.$match, $match), $text);
			}
		}

		return $text;
	}

	/**
	 * Converts text email addresses into links.
	 *
	 * @param   string   text to auto link
	 * @return  string
	 */
	public static function auto_link_emails($text) {
		// Finds all email addresses that are not part of an existing html mailto anchor
		// Note: The "58;" negative lookbehind prevents matching of existing encoded html mailto anchors
		//       The html entity for a colon (:) is &#58; or &#058; or &#0058; etc.
		if(preg_match_all('~\b(?<!href="mailto:|">|58;)(?!\.)[-+_a-z0-9.]++(?<!\.)@(?![-.])[-a-z0-9.]+(?<!\.)\.[a-z]{2,6}\b~i', $text, $matches)) {
			foreach($matches[0] as $match) {
				// Replace each email with an encoded mailto
				$text = str_replace($match, html::mailto($match), $text);
			}
		}

		return $text;
	}

	/**
	 * Automatically applies <p> and <br /> markup to text. Basically nl2br() on steroids.
	 *
	 * @param   string   subject
	 * @return  string
	 */
	public static function auto_p($str) {
		// Trim whitespace
		if(($str = trim($str)) === '')
			return '';

		// Standardize newlines
		$str = str_replace(array("\r\n", "\r"), "\n", $str);

		// Trim whitespace on each line
		$str = preg_replace('~^[ \t]+~m', '', $str);
		$str = preg_replace('~[ \t]+$~m', '', $str);

		// The following regexes only need to be executed if the string contains html
		if($html_found = (strpos($str, '<') !== NO)) {
			// Elements that should not be surrounded by p tags
			$no_p = '(?:p|div|h[1-6r]|ul|ol|li|blockquote|d[dlt]|pre|t[dhr]|t(?:able|body|foot|head)|c(?:aption|olgroup)|form|s(?:elect|tyle)|a(?:ddress|rea)|ma(?:p|th))';

			// Put at least two linebreaks before and after $no_p elements
			$str = preg_replace('~^<'.$no_p.'[^>]*+>~im', "\n$0", $str);
			$str = preg_replace('~</'.$no_p.'\s*+>$~im', "$0\n", $str);
		}

		// Do the <p> magic!
		$str = '<p>'.trim($str).'</p>';
		$str = preg_replace('~\n{2,}~', "</p>\n\n<p>", $str);

		// The following regexes only need to be executed if the string contains html
		if($html_found !== NO) {
			// Remove p tags around $no_p elements
			$str = preg_replace('~<p>(?=</?'.$no_p.'[^>]*+>)~i', '', $str);
			$str = preg_replace('~(</?'.$no_p.'[^>]*+>)</p>~i', '$1', $str);
		}

		// Convert single linebreaks to <br />
		$str = preg_replace('~(?<!\n)\n(?!\n)~', "<br />\n", $str);

		return $str;
	}

	/**
	 * Returns human readable sizes.
	 * @see  Based on original functions written by:
	 * @see  Aidan Lister: http://aidanlister.com/repos/v/function.size_readable.php
	 * @see  Quentin Zervaas: http://www.phpriot.com/d/code/strings/filesize-format/
	 *
	 * @param   integer  size in bytes
	 * @param   string   a definitive unit
	 * @param   string   the return string format
	 * @param   boolean  whether to use SI prefixes or IEC
	 * @return  string
	 */
	public static function bytes($bytes, $force_unit = nil, $format = nil, $si = YES) {
		// Format string
		$format = ($format === nil) ? '%01.2f %s' : (string) $format;

		// IEC prefixes (binary)
		if($si == NO OR strpos($force_unit, 'i') !== NO) {
			$units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
			$mod   = 1024;
		}
		// SI prefixes (decimal)
		else {
			$units = array('b', 'kb', 'mb', 'gb', 'tb', 'pb');
			$mod   = 1000;
		}

		// Determine unit to use
		if(($power = array_search((string) $force_unit, $units)) === NO) {
			$power = ($bytes > 0) ? floor(log($bytes, $mod)) : 0;
		}

		return sprintf($format, $bytes / pow($mod, $power), $units[$power]);
	}

	/**
	 * Prevents widow words by inserting a non-breaking space between the last two words.
	 * @see  http://www.shauninman.com/archive/2006/08/22/widont_wordpress_plugin
	 *
	 * @param   string  string to remove widows from
	 * @return  string
	 */
	public static function widont($str) {
		$str = rtrim($str);
		$space = strrpos($str, ' ');

		if($space !== NO) {
			$str = substr($str, 0, $space).'&nbsp;'.substr($str, $space + 1);
		}

		return $str;
	}
	
	/**
	 * Allows you to use the "empty" feature of PHP in a more flexible manner
	 * @see		http://php.net/manual/en/function.empty.php
	 *
	 * @param   string  string to check if empty
	 * @return  string
	 */
	public static function e($var) {
		$v = $var;
		return empty($v);
	}
	
	/**
	 * Check if a string starts with either a string or an array of strings
	 *
	 * @param   string  haystack, string to check against
	 * @param	mixed	needle(s), a single string or an array of strings
	 * @param	bool	case sensitive, default is false
	 * @return  string
	 */
	public static function starts_with($str, $arr, $case=false) {
		if(!is_array($arr)) {
			$arr = array($arr);
		}
		
		$str = $case ? $str : strtolower($str);
		$slen = strlen($str);
		
		foreach($arr as $cmp) {
			$cmp = $case ? $cmp : strtolower($cmp);
			$clen = strlen($cmp);
			if($clen > $slen) continue;
			
			if(substr($str, 0, $clen) == $cmp) return true;
		}
		
		return false;
	}
	
	/**
	 * Check if a string end with either a string or an array of strings
	 *
	 * @param   string  haystack, string to check against
	 * @param	mixed	needle(s), a single string or an array of strings
	 * @param	bool	case sensitive, default is false
	 * @return  string
	 */
	public static function ends_with($str, $arr, $case=false) {
		if(!is_array($arr))
			$arr = array($arr);
		
		$str = $case ? $str : strtolower($str);
		$slen = strlen($str);
		
		foreach($arr as $cmp) {
			$cmp = $case ? $cmp : strtolower($cmp);
			$clen = strlen($cmp);
			if($clen > $slen) continue;
			
			if(substr($str, $clen*-1) == $cmp) return true;
		}
		
		return false;
	}
	
	/**
	 * Check if a string contains either a string or an array of strings
	 *
	 * @param   string  haystack, string to check against
	 * @param	mixed	needle(s), a single string or an array of strings
	 * @param	bool	case sensitive, default is false
	 * @return  string
	 */
	public static function contains($str, $arr, $case=false) {
		!is_array($arr) && $arr = array($arr);

		$func = $case ? "strstr" : "stristr";

		foreach($arr as $cmp)
			if($func($str, $cmp)) return true;
		
		return false;
	}
	
	/**
	 * Check if a string is a path, a valid URL is condsidered a path.
	 *
	 * @param   string  string to check if is path/url
	 * @return  bool
	 */
	public static function is_path($path) {
		if(self::is_url($path)) {
			return true;
		}
		
		if(self::contains($path, array("\n", "\r", "\t"))) {
			return false;
		}
		
		if(file_exists($path) && is_file($path)) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Check if a string is a URL
	 *
	 * @param   string  string to check if is url
	 * @return  bool
	 */
	public static function is_url($path) {
		if(self::starts_with($path, array("http://", "https://", "ftp://", "feed://"))) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Get an element in a delimeter separated string
	 *
	 * @param   string  delimeter
	 * @param   string  string with elements separated by delimeter
	 * @param   integer	index of element to get
	 * @return  bool
	 */
	public static function get_at($delimeter, $haystack, $get=0) {
		$var = explode($delimeter,$haystack);
		return $var[$get];
	}

	/**
	 * Get last element in a delimeter separated string
	 *
	 * @param   string  delimeter
	 * @param   string  string with elements separated by delimeter
	 * @return  bool
	 */
	public static function get_last($delimeter, $haystack) {
		$var = explode($delimeter,$haystack);
		return end($var);
	}
	
	public static function contains_only(&$str, $pattern, $trim=false) {
		$trim && $str = trim($str);
		return strlen(preg_replace("/[".$pattern."]/", "", $str)) == 0;
	}
	
	/**
	 * Strip everything in a string except what matches the regex pattern
	 *
	 * @param   string  string to strip
	 * @param   string  regex string to preserve
	 * @return  string
	 */
	public static function strip_all_but($str, $pattern) {
		return preg_replace("/([^".$pattern."]*)/", "", $str);
	}

	/**
	 * Turn a string into "yes" or "no"
	 * If a string equals 1 or string, or starts with "y/Y" or "t/T" it will return "yes", otherwise it will return "no"
	 *
	 * @param   string
	 * @return  string	"yes" or "no"
	 */
	public static function yesno($str) {
		if($str == 1 OR $str == true OR (strlen($str) > 0 AND (strtolower($str{0}) == 'y' OR strtolower($str{0}) == 't'))) {
			return "yes";
		} else {
			return "no";
		}
	}

	/**
	 * Toggle wildcards in a string between asterisks and SQL compatible percentage signs
	 *
	 * @param   string
	 * @return  string
	 */
	public static function wildcard($str, $back=false) {
		if($back)	return str_replace("%", "*", $str);
		else		return str_replace("*", "%", $str);
	}

	public static function between($str, $start, $end) {
		$str = explode($start, $str);
		$str = $str[1];
		$str = explode($end, $str);
		return $str[0];
	}
	
	/**
	 * Checks if $count and returns either a plural or singular string of the given string
	 *
	 * @param   string	initial word, should be singular
	 * @param   integer	item count
	 * @return  string	singular or plural version of initial word
	 */
	public static function plural($var, $count) {
		if($count == 1) return inflector::singular($var);
		else return inflector::plural($var);
	}
	
	/**
	 * Replaces BR tags in a string with newlines.
	 *
	 * @param   string	string containing br tags
	 * @return  string
	 */
	public static function br2nl($var) {
		return preg_replace('/\<br(\s*)?\/?\>/i', "\n", $string);
	}
	
	/**
	 * Better version of PHP's gzuncompress
	 * @see http://www.php.net/manual/en/function.gzuncompress.php 
	 *
	 * @param   data	gzipped data
	 * @return  string
	 */
	public static function gzuncompress($data) {
		$f = tempnam('/tmp', 'gz_fix');
		file_put_contents($f, $data);
		return file_get_contents('compress.zlib://'.$f);
	}
	
	/**
	 * Hard wraps a string after a number of characters pass
	 *
	 * @param   string	long string
	 * @param   integer	length of each string
	 * @return  string
	 */
	public static function hard_wrap($str, $to_width=100) {
		$lines = array();

		while(strlen($str) > $to_width) {
			$lines[] = substr($str, 0, $to_width);
			$str = substr($str, $to_width);
		}

		$lines[] = $str;

		return implode($lines, "\n");
	}

	/**
	 * Tests whether a string contains only 7bit ASCII bytes. This is used to
	 * determine when to use native functions or UTF-8 functions.
	 *
	 * ##### Example
	 *
	 *     $str = 'abcd';
	 *     var_export(str::is_ascii($str));
	 *
	 *     // Output:
	 *     true
	 *
	 * @see http://sourceforge.net/projects/phputf8/
	 * @copyright  (c) 2005 Harry Fuecks
	 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt
	 *
	 * @param   string  $str  String to check
	 * @return  bool
	 */
	public static function is_ascii($str) {
		return is_string($str) AND ! preg_match('/[^\x00-\x7F]/S', $str);
	}
	
	/**
	 * Strips out device control codes in the ASCII range.
	 *
	 * ##### Example
	 *
	 *     $result = str::strip_ascii_ctrl($str_containing_control_codes);
	 *
	 * @link http://sourceforge.net/projects/phputf8/
	 * @copyright  (c) 2005 Harry Fuecks
	 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt
	 *
	 * @param   string  $str  String to clean
	 * @return  string
	 */
	public static function strip_ascii_ctrl($str) {
		return preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S', '', $str);
	}

	/**
	 * Strips out all non-7bit ASCII bytes.
	 *
	 * For a description of the difference between 7bit and
	 * extended (8bit) ASCII:
	 *
	 * @link http://en.wikipedia.org/wiki/ASCII
	 *
	 * ##### Example
	 *
	 *     $result = str::strip_non_ascii($str_with_8bit_ascii);
	 *
	 * @link http://sourceforge.net/projects/phputf8/
	 * @copyright  (c) 2005 Harry Fuecks
	 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt
	 *
	 * @param   string  $str  String to clean
	 * @return  string
	 */
	public static function strip_non_ascii($str) {
		return preg_replace('/[^\x00-\x7F]+/S', '', $str);
	}

	/**
	 * Replaces special/accented UTF-8 characters by ASCII-7 'equivalents'.
	 *
	 * ##### Example
	 *
	 *     echo str::transliterate_to_ascii("Útgarðar");
	 *
	 *     // Output:
	 *     Utgardhar
	 *
	 * @author  Andreas Gohr <andi@splitbrain.org>
	 * @link http://sourceforge.net/projects/phputf8/
	 * @copyright  (c) 2005 Harry Fuecks
	 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt
	 *
	 * @param   string   $str    String to transliterate
	 * @param   integer  $case   -1 lowercase only, +1 uppercase only, 0 both cases
	 * @return  string
	 */
	public static function transliterate_to_ascii($str, $case = 0) {
		static $UTF8_LOWER_ACCENTS = NULL;
		static $UTF8_UPPER_ACCENTS = NULL;

		if ($case <= 0) {
			if ($UTF8_LOWER_ACCENTS === NULL) {
				$UTF8_LOWER_ACCENTS = array(
					'à' => 'a',  'ô' => 'o',  'ď' => 'd',  'ḟ' => 'f',  'ë' => 'e',  'š' => 's',  'ơ' => 'o',
					'ß' => 'ss', 'ă' => 'a',  'ř' => 'r',  'ț' => 't',  'ň' => 'n',  'ā' => 'a',  'ķ' => 'k',
					'ŝ' => 's',  'ỳ' => 'y',  'ņ' => 'n',  'ĺ' => 'l',  'ħ' => 'h',  'ṗ' => 'p',  'ó' => 'o',
					'ú' => 'u',  'ě' => 'e',  'é' => 'e',  'ç' => 'c',  'ẁ' => 'w',  'ċ' => 'c',  'õ' => 'o',
					'ṡ' => 's',  'ø' => 'o',  'ģ' => 'g',  'ŧ' => 't',  'ș' => 's',  'ė' => 'e',  'ĉ' => 'c',
					'ś' => 's',  'î' => 'i',  'ű' => 'u',  'ć' => 'c',  'ę' => 'e',  'ŵ' => 'w',  'ṫ' => 't',
					'ū' => 'u',  'č' => 'c',  'ö' => 'o',  'è' => 'e',  'ŷ' => 'y',  'ą' => 'a',  'ł' => 'l',
					'ų' => 'u',  'ů' => 'u',  'ş' => 's',  'ğ' => 'g',  'ļ' => 'l',  'ƒ' => 'f',  'ž' => 'z',
					'ẃ' => 'w',  'ḃ' => 'b',  'å' => 'a',  'ì' => 'i',  'ï' => 'i',  'ḋ' => 'd',  'ť' => 't',
					'ŗ' => 'r',  'ä' => 'a',  'í' => 'i',  'ŕ' => 'r',  'ê' => 'e',  'ü' => 'u',  'ò' => 'o',
					'ē' => 'e',  'ñ' => 'n',  'ń' => 'n',  'ĥ' => 'h',  'ĝ' => 'g',  'đ' => 'd',  'ĵ' => 'j',
					'ÿ' => 'y',  'ũ' => 'u',  'ŭ' => 'u',  'ư' => 'u',  'ţ' => 't',  'ý' => 'y',  'ő' => 'o',
					'â' => 'a',  'ľ' => 'l',  'ẅ' => 'w',  'ż' => 'z',  'ī' => 'i',  'ã' => 'a',  'ġ' => 'g',
					'ṁ' => 'm',  'ō' => 'o',  'ĩ' => 'i',  'ù' => 'u',  'į' => 'i',  'ź' => 'z',  'á' => 'a',
					'û' => 'u',  'þ' => 'th', 'ð' => 'dh', 'æ' => 'ae', 'µ' => 'u',  'ĕ' => 'e',  'ı' => 'i',
				);
			}

			$str = str_replace(
				array_keys($UTF8_LOWER_ACCENTS),
				array_values($UTF8_LOWER_ACCENTS),
				$str
			);
		}

		if ($case >= 0) {
			if ($UTF8_UPPER_ACCENTS === NULL) {
				$UTF8_UPPER_ACCENTS = array(
					'À' => 'A',  'Ô' => 'O',  'Ď' => 'D',  'Ḟ' => 'F',  'Ë' => 'E',  'Š' => 'S',  'Ơ' => 'O',
					'Ă' => 'A',  'Ř' => 'R',  'Ț' => 'T',  'Ň' => 'N',  'Ā' => 'A',  'Ķ' => 'K',  'Ĕ' => 'E',
					'Ŝ' => 'S',  'Ỳ' => 'Y',  'Ņ' => 'N',  'Ĺ' => 'L',  'Ħ' => 'H',  'Ṗ' => 'P',  'Ó' => 'O',
					'Ú' => 'U',  'Ě' => 'E',  'É' => 'E',  'Ç' => 'C',  'Ẁ' => 'W',  'Ċ' => 'C',  'Õ' => 'O',
					'Ṡ' => 'S',  'Ø' => 'O',  'Ģ' => 'G',  'Ŧ' => 'T',  'Ș' => 'S',  'Ė' => 'E',  'Ĉ' => 'C',
					'Ś' => 'S',  'Î' => 'I',  'Ű' => 'U',  'Ć' => 'C',  'Ę' => 'E',  'Ŵ' => 'W',  'Ṫ' => 'T',
					'Ū' => 'U',  'Č' => 'C',  'Ö' => 'O',  'È' => 'E',  'Ŷ' => 'Y',  'Ą' => 'A',  'Ł' => 'L',
					'Ų' => 'U',  'Ů' => 'U',  'Ş' => 'S',  'Ğ' => 'G',  'Ļ' => 'L',  'Ƒ' => 'F',  'Ž' => 'Z',
					'Ẃ' => 'W',  'Ḃ' => 'B',  'Å' => 'A',  'Ì' => 'I',  'Ï' => 'I',  'Ḋ' => 'D',  'Ť' => 'T',
					'Ŗ' => 'R',  'Ä' => 'A',  'Í' => 'I',  'Ŕ' => 'R',  'Ê' => 'E',  'Ü' => 'U',  'Ò' => 'O',
					'Ē' => 'E',  'Ñ' => 'N',  'Ń' => 'N',  'Ĥ' => 'H',  'Ĝ' => 'G',  'Đ' => 'D',  'Ĵ' => 'J',
					'Ÿ' => 'Y',  'Ũ' => 'U',  'Ŭ' => 'U',  'Ư' => 'U',  'Ţ' => 'T',  'Ý' => 'Y',  'Ő' => 'O',
					'Â' => 'A',  'Ľ' => 'L',  'Ẅ' => 'W',  'Ż' => 'Z',  'Ī' => 'I',  'Ã' => 'A',  'Ġ' => 'G',
					'Ṁ' => 'M',  'Ō' => 'O',  'Ĩ' => 'I',  'Ù' => 'U',  'Į' => 'I',  'Ź' => 'Z',  'Á' => 'A',
					'Û' => 'U',  'Þ' => 'Th', 'Ð' => 'Dh', 'Æ' => 'Ae', 'İ' => 'I',
				);
			}

			$str = str_replace(
				array_keys($UTF8_UPPER_ACCENTS),
				array_values($UTF8_UPPER_ACCENTS),
				$str
			);
		}

		return $str;
	}

} // End str
<?php
/**
 * Format helper class.
 *
 * @package		System
 * @subpackage	Helpers
 * @author		EightPHP Development Team
 * @copyright	(c) 2009-2010 EightPHP
 * @license		http://license.eightphp.com
 */
class format_Core {

	/**
	 * Formats a phone number according to the specified format.
	 *
	 * @param   string  phone number
	 * @param   string  format string
	 * @return  string
	 */
	public static function phone($number, $format = '3-3-4') {
		// Get rid of all non-digit characters in number string
		$number_clean = preg_replace('/\D+/', '', (string) $number);

		// Array of digits we need for a valid format
		$format_parts = preg_split('/[^1-9][^0-9]*/', $format, -1, PREG_SPLIT_NO_EMPTY);

		// Number must match digit count of a valid format
		if(strlen($number_clean) !== array_sum($format_parts))
			return $number;

		// Build regex
		$regex = '(\d{'.implode('})(\d{', $format_parts).'})';

		// Build replace string
		for($i = 1, $c = count($format_parts); $i <= $c; $i++) {
			$format = preg_replace('/(?<!\$)[1-9][0-9]*/', '\$'.$i, $format, 1);
		}

		// Hocus pocus!
		return preg_replace('/^'.$regex.'$/', $format, $number_clean);
	}

	/**
	 * Formats a URL to contain a protocol at the beginning.
	 *
	 * @param   string  possibly incomplete URL
	 * @return  string
	 */
	public static function url($str = '') {
		// Clear protocol-only strings like "http://"
		if($str === '' OR substr($str, -3) === '://')
			return '';

		// If no protocol given, prepend "http://" by default
		if(strpos($str, '://') === NO)
			return 'http://'.$str;

		// Return the original URL
		return $str;
	}
	
	/**
	 * Formats a second into hours, minutes, seconds.
	 * Maxes out at one day.
	 * 
	 * Example:
	 * format::seconds(65) returns "1 minute, 5 seconds"
	 * 
	 * @param	int	number of seconds
	 * @return	string	formatted seconds
	 */
	public static function seconds($seconds) {
		list($hours, $minutes, $seconds) = explode(":", gmdate("G:i:s", $seconds));
		$parts = array();
		if($hours > 0) {
			$parts[] = $hours . " " . str::plural("hour", $hours);
		}
		
		if($minutes > 0) {
			$parts[] = sprintf("%d", $minutes) . " " . str::plural("minute", $minutes);
		}
		
		if($seconds > 0) {
			$parts[] = sprintf("%d", $seconds) . " " . str::plural("second", $seconds);
		}
		
		return implode(", ", $parts);
	}

} // End format
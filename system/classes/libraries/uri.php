<?php
/**
 * URI library.
 *
 * @version		$Id: uri.php 244 2010-02-11 17:14:39Z shaun $
 *
 * @package		System
 * @subpackage	Libraries
 * @author		enormego
 * @copyright	(c) 2009-2010 enormego
 * @license		http://license.eightphp.com
 */
class URI_Core {

	// URI singleton
	protected static $instance;

	// Array with all URI segments
	protected static $segments;

	/**
	 * Retrieves a singleton instance of URI.
	 *
	 * @return  object
	 */
	public static function instance() {
		return (self::$instance === nil) ? new URI : self::$instance;
	}

	/**
	 * Constructor.
	 *
	 * @return  void
	 */
	public function __construct() {
		// Only run the constructor once
		if(self::$instance !== nil)
			return;

		// Create segment array from the URI
		self::$segments = explode('/', Router::$current_uri);

		// Create a singleton
		self::$instance = $this;

		Eight::log('debug', 'URI Library initialized');
	}

	/**
	 * Retrieves a specific URI segment.
	 *
	 * @param   integer|string  segment number or label
	 * @param   mixed           default value returned if segment does not exist
	 * @return  mixed
	 */
	public function segment($index, $default = NO) {
		// Segment label
		if(is_string($index)) {
			if(($index = array_search($index, self::$segments)) === NO)
				return $default;

			$index += 2;
		}

		// Numeric segment index
		$index = (int) $index - 1;

		return isset(self::$segments[$index]) ? self::$segments[$index] : $default;
	}

	/**
	 * Returns an array containing specific URI segments.
	 *
	 * @param   integer  segment offset
	 * @param   boolean  return an associative array
	 * @return  array
	 */
	public function segments($offset = 0, $assoc = NO) {
		// Index the first URI segment at 1 instead of 0
		$array = self::$segments;
		array_unshift($array, 0);

		// Slice the array, preserving the keys
		$array = array_slice($array, $offset + 1, count($array), YES);

		if($assoc === NO)
			return $array;

		// Build an associative array
		$assoc = array();

		foreach(array_chunk($array, 2) as $pair) {
			// Add the key/value pair to the associative array
			$assoc[$pair[0]] = isset($pair[1]) ? $pair[1] : '';
		}

		return $assoc;
	}

	/**
	 * Returns the last URI segment.
	 *
	 * @param   mixed  default value returned if current URI is empty
	 * @return  mixed
	 */
	public function last_segment($default = NO) {
		if(($end = count(self::$segments)) < 1)
			return $default;

		return self::$segments[$end - 1];
	}

} // End URI Class
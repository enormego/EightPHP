<?php
/**
 * Simple benchmarking class for use within the Eight framework.
 *
 * @package		System
 * @subpackage	Core
 * @author		EightPHP Development Team
 * @copyright	(c) 2009-2010 EightPHP
 * @license		http://license.eightphp.com
 */
final class Benchmark {

	// Benchmark timestamps
	private static $marks;

	/**
	 * Set a benchmark start point.
	 *
	 * @param   string  benchmark name
	 * @return  void
	 */
	public static function start($name) {
		if(!isset(self::$marks[$name])) {
			self::$marks[$name] = array
			(
				'start'        => microtime(YES),
				'stop'         => NO,
				'memory_start' => function_exists('memory_get_usage') ? memory_get_usage() : 0,
				'memory_stop'  => NO
			);
		}
	}

	/**
	 * Set a benchmark stop point.
	 *
	 * @param   string  benchmark name
	 * @return  void
	 */
	public static function stop($name) {
		if(isset(self::$marks[$name]) and self::$marks[$name]['stop'] === NO) {
			self::$marks[$name]['stop'] = microtime(YES);
			self::$marks[$name]['memory_stop'] = function_exists('memory_get_usage') ? memory_get_usage() : 0;
		}
	}

	/**
	 * Get the elapsed time between a start and stop.
	 *
	 * @param   string   benchmark name, YES for all
	 * @param   integer  number of decimal places to count to
	 * @return  array
	 */
	public static function get($name, $decimals = 4) {
		if($name === YES) {
			$times = array();
			$names = array_keys(self::$marks);

			foreach($names as $name) {
				// Get each mark recursively
				$times[$name] = self::get($name, $decimals);
			}

			// Return the array
			return $times;
		}

		if(!isset(self::$marks[$name]))
			return NO;

		if(self::$marks[$name]['stop'] === NO) {
			// Stop the benchmark to prevent mis-matched results
			self::stop($name);
		}

		// Return a string version of the time between the start and stop points
		// Properly reading a float requires using number_format or sprintf
		return array
		(
			'time'   => number_format(self::$marks[$name]['stop'] - self::$marks[$name]['start'], $decimals),
			'memory' => (self::$marks[$name]['memory_stop'] - self::$marks[$name]['memory_start'])
		);
	}

} // End Benchmark

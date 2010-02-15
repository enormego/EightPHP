<?php
/**
 * Boolean helper class.
 *
 * @version		$Id: bool.php 242 2010-02-10 23:06:09Z Shaun $
 *
 * @package		System
 * @subpackage	Helpers
 * @author		enormego
 * @copyright	(c) 2009-2010 enormego
 * @license		http://license.eightphp.com
 */
class bool_Core {
	
	public static function to_string($boolean) {
		return $boolean === FALSE ? 'false' : 'true';
	}
	
	public static function to_int($boolean) {
		return $boolean === FALSE ? 0 : 1;
	}
	
} // End Boolean Helper Class
<?php
/**
 * Boolean helper class.
 *
 * @package		System
 * @subpackage	Helpers
 * @author		EightPHP Development Team
 * @copyright	(c) 2009-2010 EightPHP
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
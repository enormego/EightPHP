<?php
/**
 * Browser helper class.
 *
 * @package		System
 * @subpackage	Helpers
 * @author		EightPHP Development Team
 * @copyright	(c) 2009-2010 EightPHP
 * @license		http://license.eightphp.com
 */

class browser_Core {

	public static function is($browser) {
		$func = "is_" . $browser;
		return self::$f();
	}
	
	public static function is_safari() {
		return !stristr($_SERVER['HTTP_USER_AGENT'], 'webkit') ? false : true;
	}
	
	public static function is_ie($ver="") {
		return !stristr($_SERVER['HTTP_USER_AGENT'], 'msie'.(!empty($ver)?' '.$ver:'')) ? false : true;
	}
	
	public static function is_gecko() {
		return !stristr($_SERVER['HTTP_USER_AGENT'], 'gecko') ? false : true;
	}
	
	public static function is_iphone($strict=FALSE) {
		$iphone = !stristr($_SERVER['HTTP_USER_AGENT'], 'iphone') ? false : true;
		if(!$strict) {
			return ($iphone || self::is_ipod()) ? TRUE : FALSE;
		} else {
			return $iphone;
		}
	}
	
	public static function is_ipod() {
		return !stristr($_SERVER['HTTP_USER_AGENT'], 'ipod') ? false : true;
	}
	
	public static function supports_ellipsis($word=NO) {
		return self::is_safari() || self::is_ie(7) || self::is_ie(8) || self::is_ie(9);
	}

}

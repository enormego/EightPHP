<?php
/**
 * Notice helper class.
 *
 * @package		System
 * @subpackage	Helpers
 * @author		EightPHP Development Team
 * @copyright	(c) 2009-2010 EightPHP
 * @license		http://license.eightphp.com
 */

class notice_Core {
    private static $counter;
	
	public static function error($message, $title="An error has occured.") {
		return self::notice('error', $title, $message);
	}
	
	public static function success($message, $title="Success!") {
		return self::notice('success', $title, $message);
	}
	
	public static function general($message, $title="General Notice") {
		return self::notice('general', $title, $message);
	}
	
	public static function last_id() {
		return self::$counter;
	}
	
	public static function notice($kind, $title, $message) {
		$data = array(
						'notice_title'		=>	$title,
						'title'				=>	$title,
						'notice_message'	=>	$message,
						'message'			=>	$message,
						'id' => ++self::$counter,
					);
					
		return View::factory('notice/'.$kind, $data);
	}
}
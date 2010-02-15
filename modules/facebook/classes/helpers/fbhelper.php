<?php

/**
 * Helper Class for Facebook Connect Stuff
 *
 * @package     Modules
 * @subpackage	Facebook
 * @author		enormego
 * @copyright	(c) 2009-2010 enormego
 * @license		http://license.eightphp.com
 */

class fbhelper_Core {
	
	public static function logged_in() {
		if(str::e($_COOKIE[Eight::config('facebook.api_key').'_user'])) {
			return FALSE;
		} else {
			if(intval(Eight::instance()->fb->user) > 0) {
				return TRUE;
			} else {
				return FALSE;
			}
			
		}
	}

}
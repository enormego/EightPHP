<?php

/**
 * Eight compatible library wrapped around the Facebook library
 *
 * @package		Modules
 * @subpackage	Facebook
 * @author		EightPHP Development Team
 * @copyright	(c) 2009-2010 EightPHP
 * @license		http://license.eightphp.com
 */

// Include Facebook Vendor Files
include Eight::find_file('vendor/facebook', 'facebook', TRUE);

class Fb_Core extends Facebook {
	
	public function __construct() {
		parent::__construct(Eight::config('facebook.api_key'), Eight::config('facebook.secret'));
	}
	
	/**
	 * Method: user
	 * 		Provides an easy way to get information about the current user.
	 * 		A single field can be passed as a string or multiple fields via an array 
	 */
	public function user($info=array()) {
		if(!is_array($info)) {
			$info = array($info);
		}
		
		// Use the API client to fetch info about the current user
		return $this->api_client->users_getInfo($this->user, $info);
	}
}
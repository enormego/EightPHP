<?php

/**
 * @package		Modules
 * @subpackage	Authentication
 * @author		EightPHP Development Team
 * @copyright	(c) 2009-2010 EightPHP
 * @license		http://license.eightphp.com
 */

class Model_AuthUserToken extends Modeler {
	
	// Database table name
	protected $table_name = 'user_tokens';

	// Table primary key
	protected $primary_key = 'user_token_token';

	// Column prefix
	protected $column_prefix = 'user_token_';
 
 	// Run all queries on master db
	protected $use_master = YES;

	// Database fields and default values
	protected $data = array(
								'user_token_id'			=>	'',
								'user_token_user_id'	=>	'',
	                        	'user_token_token'		=>	'',
								'user_token_user_agent'	=>	'',
	                        	'user_token_created'	=>	'',
								'user_token_expires'	=>	'',
							);
	
	public function __construct($id = NULL, $create_token = TRUE) {
		parent::__construct($id);
		
		// Current time
		$this->now = time();
		
		// Don't run this stuff if we're only looking for an empty shell
		if($create_token === TRUE) {
			
			// Should we handle the expired ones?
			if(mt_rand(1, 100) === 1) {
				// Do garbage collection
				$this->delete_expired();
			}
		
			// Did the token expire?
			if($this->expires < $this->now) {
				// This object has expired
				$this->delete();
			}
		
			// No ID? Create a new token.
			if(is_null($id)) {
				$this->token = $this->create_token();
			}
		}
	}
	
	/**
	 * Saves the token value.
	 *
	 * @return  void
	 */
	public function save() {
		// Reset primary key so we don't break Modeler
		$this->primary_key = 'user_token_id';
			
		// Add the user_agent
		if(str::e($this->user_agent)) {
			$this->user_agent = sha1(Eight::$user_agent);
		}
		
		// Add the created time
		if(str::e($this->created)) {
			$this->created = time();
		}
		
		self::db()->use_master(YES);
		return parent::save();
	}
	
	/**
	 * Deletes all expired tokens.
	 *
	 * @return  void
	 */
	public function delete_expired() {
		// Delete all expired tokens
		self::db()->use_master(YES);
		self::db()->where('user_token_expires <', $this->now)->delete($this->table_name);
		return $this;
	}
	
	/**
	 * Determines whether or not the current token is valid
	 */
	public function is_valid() {
		if($this->expires > time()) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	/**
	 * Finds a new unique token, using a loop to make sure that the token does
	 * not already exist in the database. This could potentially become an
	 * infinite loop, but the chances of that happening are very unlikely.
	 *
	 * @return  string
	 */
	protected function create_token() {
		while(true) {
			// Create a random token
			$token = str::random('alnum', 32);

			// Make sure the token does not already exist
			self::db()->use_master(YES);
			if (self::db()->select('user_token_id')->where('user_token_token', $token)->get($this->table_name)->count() === 0) {
				// A unique token has been found
				return $token;
			}
		}
	}

	/**
	 * Search for the provided token
	 */
	public static function find_token($token) {
		if(empty($token)) {
			return FALSE;
		}
		
		$data = self::db()->use_master(TRUE)->where('user_token_token', $token)->get('user_tokens')->row_array();
		if($data === FALSE) {
			return FALSE;
		} else {
			$token = new Model_UserToken(NULL, TRUE);
			$token->set($data);
			return $token;
		}
	}
		
	/**
	 * Finds a token for the given user
	 * 
	 * 		Accepts a user ID or user object
	 */
	public static function find_token_for_user($user) {
		if(is_null($user) OR str::e($user)) {
			return FALSE;
		}
		
		if(!is_object($user)) {
			$user = new Model_User($user);
		}
		
		$data = self::db()->where('user_token_user_id', $user->id)->get('user_tokens')->row_array();
		if($data === FALSE) {
			return FALSE;
		} else {
			$token = new Model_UserToken(NULL, FALSE);
			$token->set($data);
			return $token;
		}
	}
	
}  // End Auth User Token Model
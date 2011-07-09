<?php

/**
 * @package		Modules
 * @subpackage	Authentication
 * @author		EightPHP Development Team
 * @copyright	(c) 2009-2010 EightPHP
 * @license		http://license.eightphp.com
 */

class Model_AuthUser_Core extends Modeler {

	// Database table name
	protected $table_name = 'users';

	// Table primary key
	public $primary_key = 'user_id';

	// Column prefix
	public $column_prefix = 'user_';
 
 	// Run all queries on master db
	protected $use_master = YES;

	// Database fields and default values
	public $data = array(
								'user_id'				=>	'',
								'user_email'			=>	'',
	                        	'user_password'			=>	'',
								'user_logins'			=>	'',
								'user_last_login'		=>	'',
								'user_created'			=>	'',
							);
	
	public $username_field = 'user_email';
	
	// Internal vars
	public $roles = false;
	
	public function __construct($data=null) {
		if(is_string($data) && !ctype_digit($data)) {
			$this->primary_key = 'user_email';
		} else {
			$this->primary_key = 'user_id';
		}
		
		return parent::__construct($data);
	}
	
	/**
	 * Intercepts the set function for the password
	 *
	 * @param   string		variable to set
	 * @param	mixed		value to set the var to
	 * @return  boolean
	 */
	public function set_user_password($value) {
		// This method is designed for EXPLICIT var setting, not Modeler loads or anything like that.
		if(!$this->ran_set) {
			$this->data['user_password'] = Auth::instance()->hash($value);
		} else {
			$this->data['user_password'] = $value;
		}
	}
	
	/**
	 * Saves the current user
	 *
	 * @return  boolean		success? OR (on insert) the insert id
	 */
	public function save() {
		// If created isn't in here...
		if(empty($this->created)) {
			$this->created = time();
		}
		
		return parent::save();
	}
	
	/**
	 * Checks for the specified role
	 *
	 * @param   string		role to check for
	 * @return  boolean		does the user have this role?
	 */
	public function has_role($role) {
		if(!$this->loaded) {
			return false;
		}

		if(!is_array($this->roles) OR $this->roles === NULL) {
			self::db()->use_master(YES);
			$rs = self::db()->join('user_roles AS ur', 'ur.user_role_role_id = r.role_id', 'LEFT')->where('ur.user_role_user_id', $this->id)->get('roles AS r')->result_array();
			foreach(arr::c($rs) as $r) {
				$this->roles[$r['role_id']] = $r['role_name'];
			}
			unset($r,$rs);
		}
		
		// After we loaded them...if we still have no roles
		if(!is_array($this->roles) or count($this->roles) == 0) {
			return false;
		}
		
		if(is_string($role) AND !ctype_digit($role)) {
			return in_array($role, $this->roles);
		} else {
			return isset($this->roles[$role]);
		}
	}

	/**
	 * Tests if a username exists in the database.
	 *
	 * @param   string		user to check
	 * @return  boolean
	 */
	public function user_exists($user) {
		self::db()->use_master(YES);
		return (bool) $this->db()->where($this->username_field, $user)->count_records($this->table_name);
	}
	
	/**
	* Returns all users with the specified role
	*
	* @param   string              role to check
	* @return  boolean
	*/
	public function fetch_all_with_role($role) {
		self::db()->use_master(YES);
		return self::db()->join('user_roles AS ur', 'u.user_id = ur.user_role_user_id', 'LEFT')->join('roles AS r', 'r.role_id = ur.user_role_role_id', 'LEFT')->where('r.role_name', $role)->get('users AS u')->result_array();
	}
	
} // End Auth User Model
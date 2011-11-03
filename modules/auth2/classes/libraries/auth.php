<?php
/**
 * User authorization library. Handles user login and logout, as well as secure
 * password hashing.
 *
 * @package		Modules
 * @subpackage	Auth2
 * @author		EightPHP Development Team
 * @copyright	(c) 2009-2010 EightPHP
 * @license		http://license.eightphp.com
 */
class Auth_Core {

	// Session instance
	protected $session;

	// Configuration
	protected $config;
	
	// Bcrypt
	protected $bcrypt;
	
	/**
	 * Create an instance of Auth.
	 *
	 * @return  object
	 */
	public static function factory($config = array()) {
		return new Auth($config);
	}

	/**
	 * Return a static instance of Auth.
	 *
	 * @return  object
	 */
	public static function instance($config = array()) {
		static $instance;
		
		// Load the Auth instance
		empty($instance) && $instance = new Auth($config);

		return $instance;
	}

	/**
	 * Loads Session and configuration options.
	 *
	 * @return  void
	 */
	public function __construct($config = array()) {
		// Load Session
		$this->session = Session::instance();
		
		// Append default auth configuration
		$config += Eight::config('auth');

		// Save the config in the object
		$this->config = $config;
		
		// Init Bcrypt if we're using it
		if ($this->config['hash_method'] == 'bcrypt') {
			$this->bcrypt = new Bcrypt(12);
		}
		
		// Set the driver class name
		$driver = 'Auth_Driver_'.$config['driver'];

		if (!Eight::auto_load($driver)) {
			throw new Eight_Exception('core.driver_not_found', $config['driver'], get_class($this));
		}

		// Load the driver
		$driver = new $driver($config);

		if (!($driver instanceof Auth_Driver)) {
			throw new Eight_Exception('core.driver_implements', $config['driver'], get_class($this), 'Auth_Driver');
		}

		// Load the driver for access
		$this->driver = $driver;
		
		Eight::log('debug', 'Auth Library loaded');
	}

	/**
	 * Check if there is an active session. Optionally allows checking for a
	 * specific role.
	 *
	 * @param   string   role name
	 * @return  boolean
	 */
	public function logged_in($role = NULL) {
		// See if they're logged in.
		$logged_in = $this->driver->logged_in($role);

		// Attempt an auto-login if they're not logged in.
		if ($logged_in !== TRUE && $this->auto_login() === TRUE) {
			return TRUE;
		}
		
		return $logged_in;
	}

	/**
	 * Attempt to log in a user
	 *
	 * @param   string   username to log in
	 * @param   string   password to check against
	 * @param   boolean  enable auto-login
	 * @return  boolean
	 */
	public function login($username, $password, $remember = FALSE) {
		if (empty($password)) {
			return FALSE;
		}
		
		return $this->driver->login($username, $password, $remember);
	}
	
	/**
	 * Logged in user
	 */
	public function user() {
		return $this->driver->user();
	}
	
	/**
	 * Attempt to automatically log a user in.
	 *
	 * @return  boolean
	 */
	public function auto_login() {
		return $this->driver->auto_login();
	}

	/**
	 * Force a login for a specific username.
	 *
	 * @param   mixed    username
	 * @return  boolean
	 */
	public function force_login($username, $remember) {
		return $this->driver->force_login($username, $remember);
	}

	/**
	 * Log out a user by removing the related session variables.
	 *
	 * @param   boolean   completely destroy the session
	 * @return  boolean
	 */
	public function logout($destroy = FALSE) {
		return $this->driver->logout($destroy);
	}
	
	/**
	 * Use HMAC to create a hash using the hash method and salt in the config.
	 * 
	 * @param	string	A string to create a hash of.
	 * @return	string
	 */
	public function hash($string) {
		if($this->config['hash_method'] != 'bcrypt') {
			return hash_hmac($this->config['hash_method'], $string, $this->config['hash_key']);
		} else {
			return $this->bcrypt->hash($string);
		}
	}
	
	/**
	 * Verifies the given password against the known hash that's stored.
	 * 
	 * @param	string	Password in plaintext
	 * @param	string	Hashed password
	 * @return	boolean
	 */
	public function verify($password, $hash) {
		if(str::e($password)) return FALSE;
		if(str::e($hash)) return FALSE;
		
		$valid = FALSE;
		
		// Check password depending on hash_method
		if ($this->config['hash_method'] == 'bcrypt') {
			$valid = ($this->bcrypt->verify($password, $hash) === TRUE);
		} else {
			$valid = ($this->hash($password) === $hash);
		}
		
		return $valid;
	}

} // End Auth
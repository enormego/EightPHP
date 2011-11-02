<?php
/**
 * Database Auth driver.
 *
 * @package		Modules
 * @subpackage	Authentication
 * @author		EightPHP Development Team
 * @copyright	(c) 2009-2010 EightPHP
 * @license		http://license.eightphp.com
 */
class Auth_Driver_Database_Core implements Auth_Driver {

	protected $config;

	// Session library
	protected $session;

	/**
	 * Constructor. Loads the Session instance.
	 *
	 * @return  void
	 */
	public function __construct(array $config) {
		// Load config
		$this->config = $config;

		// Load libraries
		$this->session = Session::instance();
	}
	
	/**
	 * Checks if the current session is "logged in" and if that 
	 * logged in user has access to the given role
	 * 
	 * @param	string	User Role
	 * @return	boolean
	 */
	public function logged_in($role) {
		$status = FALSE;
		
		// Get user from session
		$user = $this->session->get($this->config['session_key']);
		
		// Checks if a user is logged in and valid
		if(!empty($user) && is_object($user) && ($user instanceof Model_AuthUser) && $user->id > 0) {
			// Everything is okay so far
			$status = TRUE;
			
			if(!empty($role)) {
				// Check that the user has the given role
				$status = $user->has_role($role);
			}
		}

		return $status;
	}
	
	/**
	 * Logs a user in via their user and password
	 * 
	 * @param	string		Username
	 * @param	string		Password
	 * @param	boolean		Whether to "remember" this user via a cookie.
	 * @return	boolean
	 */
	public function login($user, $password, $remember) {
		if (!is_object($user)) {
			// Load the user
			$user = new Model_User($user);
		}
		
		// If the passwords match, perform a login
		if (Auth::instance()->verify($password, $user->password) === TRUE) {
			if ($remember === TRUE) {
				$this->_remember_user($user);
			}

			// Finish the login
			$this->complete_login($user);
			
			return TRUE;
		}

		// Login failed
		return FALSE;
	}
	
	/**
	 * Forces a login just with the username given. This should be
	 * used to force logins when a password isn't given.
	 * 
	 * @param	string		Username
	 * @return	void
	 */
	public function force_login($user, $remember) {
		if (!is_object($user)) {
			// Load the user
			$user = new Model_User($user);
		}
		
		if ($remember === TRUE) {
			$this->_remember_user($user);
		}
		
		// Mark the session as forced, to prevent users from changing account information
		$this->session->set('auth_forced', TRUE);
		
		// Run the standard completion
		$this->complete_login($user);
	}
	
	/**
	 * Tries to "auto-login" the user by searching for the user's
	 * auto login cookie in their browser.
	 * 
	 * @return boolean
	 */
	public function auto_login() {
		if($token = cookie::get($this->config['auto_login_cookie'])) {
			// Load the token and user
			$token = new Model_UserToken($token);

			if($token->id > 0 && $token->user_id > 0) {
				if ($token->user_agent === sha1(Eight::$user_agent)) {
					// Save user id
					$user_id = $token->user_id;
					
					// Delete old token
					$token->delete();

					// Create a new user
					$user = new Model_User($token->user_id);
					
					// Remember User
					$this->_remember_user($user);
					
					// Complete the login
					$this->complete_login($user);

					// Automatic login was successful
					return TRUE;
				} else {
					// Token is invalid
					$token->delete();
				}
			}
		}

		return FALSE;
	}
	
	/**
	 * Logs the current user out and cleans up after itself
	 * 
	 * @param	boolean		Whether or not to destory the session
	 * @return	boolean		TRUE if logout went Ok.
	 */
	public function logout($destroy) {
		// Delete the auto login cookie if it exists
		cookie::get($this->config['auto_login_cookie']) && cookie::delete($this->config['auto_login_cookie']);

		if($destroy === TRUE) {
			// Destroy the session completely
			$this->session->destroy();
		} else {
			// Remove the user object from the session
			$this->session->del($this->config['session_key']);

			// Regenerate session_id
			$this->session->regenerate();
		}

		// Double check
		return !$this->session->get($this->config['session_key']);
	}
	
	/**
	 * Returns the currently logged in user
	 * 
	 * @return	Model_AuthUser
	 */
	public function user() {
		return $this->session->get($this->config['session_key']);
	}

	/**
	 * Returns the password for the given user
	 * 
	 * @param	mixed		User ID or User Object
	 * @return	string		User's hashed password
	 */
	public function password($user) {
		if(!is_object($user)) {
			// Load the user
			$user = new Model_User($user);
		}

		return $user->password;
	}

	/**
	 * Complete the login for a user by incrementing the logins and setting
	 * session data: user_id, username, roles
	 *
	 * @param   object   user model object
	 * @return  void
	 */
	protected function complete_login(Model_User $user) {
		// Update the number of logins
		$user->logins += 1;

		// Set the last login date
		$user->last_login = time();

		// Save the user
		$user->save();

		// Store session data
		$this->session->set($this->config['session_key'], $user);
	}
	
	/**
	 * Stores a cookie to remember the specified user
	 */
	private function _remember_user(Model_User $user) {
		// Create a new autologin token
		$token = new Model_UserToken;

		// Set token data
		$token->user_id = $user->id;
		$token->expires = time() + $this->config['lifetime'];
		$token->save();

		// Set the auto login cookie
		cookie::set($this->config['auto_login_cookie'], $token->token, $this->config['lifetime']);
		
		return $token;
	}

} // End Auth_Driver_Database Class
<?php
/**
 * Database Auth driver.
 *
 * @version		$Id: database.php 244 2010-02-11 17:14:39Z shaun $
 *
 * @package		Modules
 * @subpackage	Authentication
 * @author		enormego
 * @copyright	(c) 2009-2010 enormego
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

	public function logged_in($role) {
		$status = false;
		
		// Checks if a user is logged in and valid
		if(!empty($_SESSION['auth_user']) AND is_object($_SESSION['auth_user']) AND ($_SESSION['auth_user'] instanceof Model_AuthUser) AND $_SESSION['auth_user']->id > 0) {
			// Everything is okay so far
			$status = true;
			
			if(!empty($role)) {
				// Check that the user has the given role
				$status = $_SESSION['auth_user']->has_role($role);
			}
		}

		return $status;
	}

	public function login($user, $password, $remember) {
		if(!is_object($user)) {
			// Load the user
			$user = new Model_User($user);
		}

		// If the passwords match, perform a login
		if($user->password === $password) {
			if($remember === true) {
				// Create a new autologin token
				$token = new Model_UserToken;

				// Set token data
				$token->user_id = $user->id;
				$token->expires = time() + $this->config['lifetime'];
				$token->save();

				// Set the autologin cookie
				cookie::set('authautologin', $token->token, $this->config['lifetime']);
			}

			// Finish the login
			$this->complete_login($user);
			return true;
		}

		// Login failed
		return false;
	}

	public function force_login($user) {
		if (!is_object($user)) {
			// Load the user
			$user = new Model_User($user);
		}

		// Mark the session as forced, to prevent users from changing account information
		$_SESSION['auth_forced'] = true;

		// Run the standard completion
		$this->complete_login($user);
	}

	public function auto_login() {
		if($token = cookie::get('authautologin')) {
			// Load the token and user
			$token = new Model_UserToken($token);

			if ($token->id > 0 AND $token->user_id > 0) {
				if ($token->user_agent === sha1(Eight::$user_agent)) {
					// Save the token to create a new unique token
					$token->save();

					// Set the new token
					cookie::set('authautologin', $token->token, $token->expires - time());

					// Complete the login with the found data
					$user = new Model_User($token->user_id);
					$this->complete_login($user);

					// Automatic login was successful
					return true;
				}

				// Token is invalid
				$token->delete();
			}
		}

		return false;
	}

	public function logout($destroy) {
		// Delete the autologin cookie if it exists
		cookie::get('authautologin') and cookie::delete('authautologin');

		if ($destroy === true) {
			// Destroy the session completely
			$this->session->destroy();
		} else {
			// Remove the user object from the session
			unset($_SESSION['auth_user']);

			// Regenerate session_id
			$this->session->regenerate();
		}

		// Double check
		return !isset($_SESSION['auth_user']);
	}

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
		
		// Save the desired_url if it's there
		$desired_url = $_SESSION['desired_url'];
		
		// Regenerate session_id
		$this->session->regenerate();

		// Store session data
		$_SESSION['auth_user'] = $user;
		$_SESSION['desired_url'] = $desired_url;
		
	}

} // End Auth_Driver_Database Class
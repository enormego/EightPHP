<?php

/**
 * Handles all the session related functions and also works with the drivers.
 *
 * @package		System
 * @subpackage	Libraries.Sessions
 * @author		EightPHP Development Team
 * @copyright	(c) 2009-2010 EightPHP
 * @license		http://license.eightphp.com
 */

class Session_Core {

	// Session singleton
	private static $instance;

	// Protected key names (cannot be set by the user)
	protected static $protect = array('session_id', 'user_agent', 'last_activity', 'ip_address', 'total_hits', '_kf_flash_');

	// Configuration and driver
	protected static $config;
	protected static $driver;

	// Flash variables
	protected static $flash;

	// Input library
	protected $input;

	/**
	 * Singleton instance of Session.
	 */
	public static function instance() {
		// Create the instance if it does not exist
		empty(self::$instance) and new Session;

		return self::$instance;
	}

	/**
	 * Constructor: __construct
	 *  On first session instance creation, sets up the driver and creates session.
	 */
	public function __construct() {
		$this->input = new Input;

		// This part only needs to be run once
		if (self::$instance === NULL) {
			// Load config
			self::$config = Eight::config('session');

			// Makes a mirrored array, eg: foo=foo
			self::$protect = array_combine(self::$protect, self::$protect);

			if (self::$config['driver'] != 'native') {
				// Set driver name
				$driver = 'Session_Driver_'.ucfirst(self::$config['driver']);

				// Load the driver
				if ( ! Eight::auto_load($driver))
					throw new Eight_Exception('session.driver_not_supported', self::$config['driver']);

				// Initialize the driver
				self::$driver = new $driver();

				// Validate the driver
				if ( ! (self::$driver instanceof Session_Driver))
					throw new Eight_Exception('session.driver_must_implement_interface');
			}

			// Create a new session
			$this->create();

			// Regenerate session id
			if (self::$config['regenerate'] > 0 AND ($_SESSION['total_hits'] % self::$config['regenerate']) === 0) {
				$this->regenerate();
			}

			// Close the session just before sending the headers, so that
			// the session cookie can be written
			Event::add('system.post_controller', 'session_write_close');

			// Singleton instance
			self::$instance = $this;
		}

		Eight::log('debug', 'Session Library initialized');
	}

	/**
	 * Method: id
	 *  Get the session id.
	 *
	 * Returns:
	 *  Session id
	 */
	public function id() {
		return $_SESSION['session_id'];
	}

	/**
	 * Method: create
	 *  Create a new session.
	 */
	public function create($vars = NULL) {
		// Destroy the session
		$this->destroy();

		// Set the session name after having checked it
		if ( ! ctype_alnum(self::$config['name']) OR ctype_digit(self::$config['name']))
			throw new Eight_Exception('session.invalid_session_name', self::$config['name']);

		session_name(self::$config['name']);

		// Set garbage collection probability
		ini_set('session.gc_probability', (int) self::$config['gc_probability']);
		ini_set('session.gc_divisor', 100);

		// Set expiration time for native sessions
		if (self::$config['driver'] == 'native') {
			ini_set('session.gc_maxlifetime', (self::$config['expiration'] == 0) ? 86400 : self::$config['expiration']);
		}

		// Set the session cookie parameters
		// Note: the httponly parameter was added in PHP 5.2.0
		if (version_compare(PHP_VERSION, '5.2', '>=')) {
			session_set_cookie_params
			(
				self::$config['expiration'],
				Eight::config('cookie.path'),
				Eight::config('cookie.domain'),
				Eight::config('cookie.secure'),
				Eight::config('cookie.httponly')
			);
		} else {
			session_set_cookie_params
			(
				self::$config['expiration'],
				Eight::config('cookie.path'),
				Eight::config('cookie.domain'),
				Eight::config('cookie.secure')
			);
		}

		// Register non-native driver as the session handler
		if (self::$config['driver'] != 'native') {
			session_set_save_handler
			(
				array(self::$driver, 'open'),
				array(self::$driver, 'close'),
				array(self::$driver, 'read'),
				array(self::$driver, 'write'),
				array(self::$driver, 'destroy'),
				array(self::$driver, 'gc')
			);
		}

		// Start the session!
		session_start();

		// Put session_id in the session variable
		$_SESSION['session_id'] = self::$driver->identify();

		// Set defaults
		if ( ! isset($_SESSION['_kf_flash_'])) {
			$_SESSION['user_agent'] = Eight::$user_agent;
			$_SESSION['ip_address'] = $this->input->ip_address();
			$_SESSION['_kf_flash_'] = array();
			$_SESSION['total_hits'] = 0;
		}

		// Set up flash variables
		self::$flash =& $_SESSION['_kf_flash_'];

		// Update constant session variables
		$_SESSION['last_activity'] = time();
		$_SESSION['total_hits']   += 1;

		// Validate data only on hits after one
		if ($_SESSION['total_hits'] > 1) {
			// Validate the session
			foreach((array) self::$config['validate'] as $valid) {
				switch($valid) {
					case 'user_agent':
						if ($_SESSION[$valid] !== Eight::$user_agent)
							return $this->create();
					break;
					case 'ip_address':
						if ($_SESSION[$valid] !== $this->input->$valid())
							return $this->create();
					break;
				}
			}

			// Remove old flash data
			if ( ! empty(self::$flash)) {
				foreach(self::$flash as $key => $state) {
					if ($state == 'old') {
						self::del($key);
						unset(self::$flash[$key]);
					} else {
						self::$flash[$key] = 'old';
					}
				}
			}
		}

		// Set the new data
		self::set($vars);
	}

	/**
	 * Method: regenerate
	 *  Regenerates the global session id.
	 */
	public function regenerate($delete_old_session=true) {
		if (self::$config['driver'] == 'native') {
			// Thank god for small gifts
			session_regenerate_id($delete_old_session);

			// Update session with new id
			$_SESSION['session_id'] = session_id();
		} else {
			// Pass the regenerating off to the driver in case it wants to do anything special
			$_SESSION['session_id'] = self::$driver->regenerate($delete_old_session);
		}
	}

	/**
	 * Method: destroy
	 *  Destroys the current session.
	 *
	 * Returns:
	 *  TRUE or FALSE (or NULL if called before session_start())
	 */
	public function destroy() {
		if (isset($_SESSION)) {
			// Remove all session data
			session_unset();

			// Delete the session cookie
			cookie::delete(session_name());

			// Destroy the session
			return session_destroy();
		}
	}

	/**
	 * Method: set
	 *  Set a session variable.
	 *
	 * Parameters:
	 *  keys - array of values, or key
	 *  val  - value (if keys is not an array)
	 */
	public function set($keys, $val = FALSE) {
		if (empty($keys))
			return FALSE;

		if ( ! is_array($keys)) {
			$keys = array($keys => $val);
		}

		foreach($keys as $key => $val) {
			if (isset(self::$protect[$key]))
				continue;

			// Set the key
			$_SESSION[$key] = $val;
		}
	}

	/**
	 * Method: set_flash
	 *  Set a flash variable.
	 *
	 * Parameters:
	 *  keys - array of values, or key
	 *  val  - value (if keys is not an array)
	 */
	public function set_flash($keys, $val = FALSE) {
		if (empty($keys))
			return FALSE;

		if ( ! is_array($keys)) {
			$keys = array($keys => $val);
		}

		foreach ($keys as $key => $val) {
			if ($key == FALSE)
				continue;

			self::$flash[$key] = 'new';
			$this->set($key, $val);
		}
	}

	/**
	 * Method: keep_flash
	 *  Freshen a flash variable.
	 *
	 * Parameters:
	 *  key - variable key
	 */
	public function keep_flash($key) {
		if (isset(self::$flash[$key])) {
			self::$flash[$key] = 'new';
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Method: get
	 *  Get a variable. Access to sub-arrays is supported with key.subkey.
	 *
	 * Parameters:
	 *  key     - variable key (optional)
	 *  default - default value returned if variable does not exist
	 *
	 * Returns:
	 *   Variable data if key specified, otherwise array containing all session data
	 */
	public function get($key = FALSE, $default = FALSE) {
		if (empty($key)) {
			return $_SESSION;
		}
		
		$result = (isset($_SESSION[$key])) ? $_SESSION[$key] : Eight::key_string($_SESSION, $key);
		return ($result === NULL) ? $default : $result;
	}

	/**
	 * Method: get_once
	 *  Get a variable, and delete it.
	 *
	 * Parameters:
	 *  key - variable key (optional)
	 *
	 * Returns:
	 *   Variable data if key specified, otherwise array containing all session data
	 */
	public function get_once($key) {
		$return = $this->get($key);
		$this->del($key);
		return $return;
	}

	/**
	 * Method: del
	 *  Delete one or more variables.
	 *
	 * Parameters:
	 *  key - variable key(s)
	 */
	public function del($keys) {
		if (empty($keys))
			return FALSE;

		if (func_num_args() > 1) {
			$keys = func_get_args();
		}

		foreach((array) $keys as $key) {
			if (isset(self::$protect[$key]))
				continue;

			// Unset the key
			unset($_SESSION[$key]);
		}
	}

} // End Session Class
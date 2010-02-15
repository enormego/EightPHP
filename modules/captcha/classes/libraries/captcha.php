<?php
/**
 * Captcha library.
 *
 * @package		Modules
 * @subpackage	Captcha
 * @author		EightPHP Development Team
 * @copyright	(c) 2009-2010 EightPHP
 * @license		http://license.eightphp.com
 */
class Captcha_Core {

	// Captcha singleton
	protected static $instances = array();

	// Style-dependent Captcha driver
	protected $driver;

	// Config values
	public static $config = array
	(
		'style'      => 'basic',
		'width'      => 150,
		'height'     => 50,
		'complexity' => 4,
		'background' => '',
		'fontpath'   => '',
		'fonts'      => array(),
		'promote'    => NO,
	);

	/**
	 * Singleton instance of Captcha.
	 *
	 * @return  object
	 */
	public static function instance($group = "default") {
		// Create the instance if it does not exist
		empty(self::$instances[$group]) and new Captcha($group);

		return self::$instances[$group];
	}

	/**
	 * Constructs and returns a new Captcha object.
	 *
	 * @param   string  config group name
	 * @return  object
	 */
	public function factory($group = nil) {
		return new Captcha($group);
	}

	/**
	 * Constructs a new Captcha object.
	 *
	 * @throws  Eight_Exception
	 * @param   string  config group name
	 * @return  void
	 */
	public function __construct($group = nil) {
		// Create a singleton instance once
		empty(self::$instances[$group]) and self::$instances[$group] = $this;

		// No config group name given
		if(!is_string($group)) {
			$group = 'default';
		}

		// Load and validate config group
		if(!is_array($config = Eight::config('captcha.'.$group)))
			throw new Eight_Exception('captcha.undefined_group', $group);

		// All captcha config groups inherit default config group
		if($group !== 'default') {
			// Load and validate default config group
			if(!is_array($default = Eight::config('captcha.default')))
				throw new Eight_Exception('captcha.undefined_group', 'default');

			// Merge config group with default config group
			$config += $default;
		}

		// Assign config values to the object
		foreach($config as $key => $value) {
			if(array_key_exists($key, self::$config)) {
				self::$config[$key] = $value;
			}
		}

		// Store the config group name as well, so the drivers can access it
		self::$config['group'] = $group;

		// If using a background image, check if it exists
		if(!empty($config['background'])) {
			self::$config['background'] = str_replace('\\', '/', realpath($config['background']));

			if(!is_file(self::$config['background']))
				throw new Eight_Exception('captcha.file_not_found', self::$config['background']);
		}

		// If using any fonts, check if they exist
		if(!empty($config['fonts'])) {
			self::$config['fontpath'] = str_replace('\\', '/', realpath($config['fontpath'])).'/';

			foreach($config['fonts'] as $font) {
				if(!is_file(self::$config['fontpath'].$font))
					throw new Eight_Exception('captcha.file_not_found', self::$config['fontpath'].$font);
			}
		}

		// Set driver name
		$driver = 'Captcha_Driver_'.ucfirst($config['style']);

		// Load the driver
		if(!Eight::auto_load($driver))
			throw new Eight_Exception('core.driver_not_found', $config['style'], get_class($this));

		// Initialize the driver
		$this->driver = new $driver;

		// Validate the driver
		if(!($this->driver instanceof Captcha_Driver))
			throw new Eight_Exception('core.driver_implements', $config['style'], get_class($this), 'Captcha_Driver');

		Eight::log('debug', 'Captcha Library initialized');
	}

	/**
	 * Validates a Captcha response and updates response counter.
	 *
	 * @param   string   captcha response
	 * @return  boolean
	 */
	public static function valid($response, $group="default") {
		// Maximum one count per page load
		static $counted;

		// User has been promoted, always YES and don't count anymore
		if(self::instance($group)->promoted())
			return YES;

		// Challenge result
		$result = (bool) self::instance($group)->driver->valid($response);

		// Increment response counter
		if($counted !== YES) {
			$counted = YES;

			// Valid response
			if($result === YES) {
				self::instance($group)->valid_count(Session::instance()->get('captcha_valid_count') + 1);
			}
			// Invalid response
			else {
				self::instance($group)->invalid_count(Session::instance()->get('captcha_invalid_count') + 1);
			}
		}

		return $result;
	}

	/**
	 * Gets or sets the number of valid Captcha responses for this session.
	 *
	 * @param   integer  new counter value
	 * @param   boolean  trigger invalid counter (for internal use only)
	 * @return  integer  counter value
	 */
	public function valid_count($new_count = nil, $invalid = NO) {
		// Pick the right session to use
		$session = ($invalid === YES) ? 'captcha_invalid_count' : 'captcha_valid_count';

		// Update counter
		if($new_count !== nil) {
			$new_count = (int) $new_count;

			// Reset counter = delete session
			if($new_count < 1) {
				Session::instance()->delete($session);
			}
			// Set counter to new value
			else {
				Session::instance()->set($session, (int) $new_count);
			}

			// Return new count
			return (int) $new_count;
		}

		// Return current count
		return (int) Session::instance()->get($session);
	}

	/**
	 * Gets or sets the number of invalid Captcha responses for this session.
	 *
	 * @param   integer  new counter value
	 * @return  integer  counter value
	 */
	public function invalid_count($new_count = nil) {
		return $this->valid_count($new_count, YES);
	}

	/**
	 * Resets the Captcha response counters and removes the count sessions.
	 *
	 * @return  void
	 */
	public function reset_count() {
		$this->valid_count(0);
		$this->valid_count(0, YES);
	}

	/**
	 * Checks whether user has been promoted after having given enough valid responses.
	 *
	 * @param   integer  valid response count threshold
	 * @return  boolean
	 */
	public function promoted($threshold = nil) {
		// Promotion has been disabled
		if(self::$config['promote'] === NO)
			return NO;

		// Use the config threshold
		if($threshold === nil) {
			$threshold = self::$config['promote'];
		}

		// Compare the valid response count to the threshold
		return ($this->valid_count() >= $threshold);
	}

	/**
	 * Returns or outputs the Captcha challenge.
	 *
	 * @param   boolean  YES to output html, e.g. <img src="#" />
	 * @return  mixed    html string or void
	 */
	public function render($html = YES) {
		$this->driver->update_challange();
		return $this->driver->render($html);
	}

	/**
	 * Magically outputs the Captcha challenge.
	 *
	 * @return  mixed
	 */
	public function __toString() {
		return $this->render();
	}

} // End Captcha Class
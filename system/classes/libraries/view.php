<?php
/**
 * Loads and displays Eight view files. Can also handle output of some binary
 * files, such as image, Javascript, and CSS files.
 *
 * @package		System
 * @subpackage	Libraries
 * @author		EightPHP Development Team
 * @copyright	(c) 2009-2010 EightPHP
 * @license		http://license.eightphp.com
 */
class View_Core {

	// The view file name and type
	protected $eight_filename = NO;
	protected $eight_filetype = NO;

	// View variable storage
	protected $eight_local_data = array();
	protected static $eight_global_data = array();

	/**
	 * Creates a new View using the given parameters.
	 *
	 * @param   string  view name
	 * @param   array   pre-load data
	 * @param   string  type of file: html, css, js, etc.
	 * @return  object
	 */
	public static function factory($name = nil, $data = nil, $type = nil) {
		return new View($name, $data, $type);
	}

	/**
	 * Attempts to load a view and pre-load view data.
	 *
	 * @throws  Eight_Exception  if the requested view cannot be found
	 * @param   string  view name
	 * @param   array   pre-load data
	 * @param   string  type of file: html, css, js, etc.
	 * @return  void
	 */
	public function __construct($name = nil, $data = nil, $type = nil) {
		if(is_string($name) and $name !== '') {
			// Set the filename
			$this->set_filename($name, $type);
		}

		if(is_array($data) and!empty($data)) {
			// Preload data using array_merge, to allow user extensions
			$this->eight_local_data = array_merge($this->eight_local_data, $data);
		}
	}

	/**
	 * Sets the view filename.
	 *
	 * @chainable
	 * @param   string  view filename
	 * @param   string  view file type
	 * @return  object
	 */
	public function set_filename($name, $type = nil) {
		if($type === nil) {
			// Load the filename and set the content type
			$this->eight_filename = Eight::find_file('views', $name, YES);
			$this->eight_filetype = EXT;
		} else {
			// Load the filename and set the content type
			$this->eight_filename = Eight::find_file('views', $name, YES, $type);
			$this->eight_filetype = Eight::config('mimes.'.$type);

			if($this->eight_filetype === nil) {
				// Use the specified type
				$this->eight_filetype = $type;
			}
		}

		return $this;
	}

	/**
	 * Sets a view variable.
	 *
	 * @param   string|array  name of variable or an array of variables
	 * @param   mixed         value when using a named variable
	 * @return  object
	 */
	public function set($name, $value = nil) {
		if(is_array($name)) {
			foreach($name as $key => $value) {
				$this->__set($key, $value);
			}
		} else {
			$this->__set($name, $value);
		}

		return $this;
	}

	/**
	 * Sets a bound variable by reference.
	 *
	 * @param   string   name of variable
	 * @param   mixed    variable to assign by reference
	 * @return  object
	 */
	public function bind($name, & $var) {
		$this->eight_local_data[$name] =& $var;

		return $this;
	}

	/**
	 * Sets a view global variable.
	 *
	 * @param   string|array  name of variable or an array of variables
	 * @param   mixed         value when using a named variable
	 * @return  object
	 */
	public function set_global($name, $value = nil) {
		if(is_array($name)) {
			foreach($name as $key => $value) {
				self::$eight_global_data[$key] = $value;
			}
		} else {
			self::$eight_global_data[$name] = $value;
		}

		return $this;
	}

	/**
	 * Magically sets a view variable.
	 *
	 * @param   string   variable key
	 * @param   string   variable value
	 * @return  void
	 */
	public function __set($key, $value) {
		if(!isset($this->$key)) {
			$this->eight_local_data[$key] = $value;
		}
	}

	/**
	 * Magically gets a view variable.
	 *
	 * @param  string  variable key
	 * @return mixed   variable value if the key is found
	 * @return void    if the key is not found
	 */
	public function __get($key) {
		if(isset($this->eight_local_data[$key]))
			return $this->eight_local_data[$key];

		if(isset(self::$eight_global_data[$key]))
			return self::$eight_global_data[$key];

		if(isset($this->$key))
			return $this->$key;
	}

	/**
	 * Magically converts view object to string.
	 *
	 * @return  string
	 */
	public function __toString() {
		return $this->render();
	}

	/**
	 * Renders a view.
	 *
	 * @param   boolean   set to YES to echo the output instead of returning it
	 * @param   callback  special renderer to pass the output through
	 * @return  string    if print is NO
	 * @return  void      if print is YES
	 */
	public function render($print = NO, $renderer = NO) {
		if(empty($this->eight_filename)) {
			throw new Eight_Exception('core.view_set_filename');
		}
		
		if(is_string($this->eight_filetype)) {
			// Merge global and local data, local overrides global with the same name
			$data = array_merge(self::$eight_global_data, $this->eight_local_data);

			// Load the view in the controller for access to $this
			$output = Eight::$instance->_eight_load_view($this->eight_filename, $data);

			if($renderer !== NO and is_callable($renderer, YES)) {
				// Pass the output through the user defined renderer
				$output = call_user_func($renderer, $output);
			}

			if($print === YES) {
				// Display the output
				echo $output;
				return;
			}
		} else {
			// Set the content type and size
			header('Content-Type: '.$this->eight_filetype[0]);

			if($print === YES) {
				if($file = fopen($this->eight_filename, 'rb')) {
					// Display the output
					fpassthru($file);
					fclose($file);
				}
				return;
			}

			// Fetch the file contents
			$output = file_get_contents($this->eight_filename);
		}

		return $output;
	}

} // End View
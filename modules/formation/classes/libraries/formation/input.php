<?php
/**
 * Formation base input library.
 *
 * @package		Modules
 * @subpackage	Formation
 * @author		EightPHP Development Team
 * @copyright	(c) 2009-2010 EightPHP
 * @license		http://license.eightphp.com
 */
class Formation_Input_Core {

	// Input method
	public $method;

	// Element data
	protected $data = array(
		'type'    => 'text',
		'class'   => 'textbox',
		'value'   => ''
	);

	// Protected data keys
	protected $protect = array();

	// Validation rules, matches, and callbacks
	protected $rules = array();
	protected $matches = array();
	protected $callbacks = array();

	// Validation check
	protected $is_valid;

	// Errors
	protected $errors = array();
	protected $error_messages = array();
	
	// Formation
	protected $formation = nil;

	/**
	 * Sets the input element name.
	 */
	public function __construct($name, $formation) {
		$this->data['name'] = $name;
		$this->formation = $formation;
	}

	/**
	 * Sets form attributes, or return rules.
	 */
	public function __call($method, $args) {
		if ($method == 'rules') {
			if (empty($args))
				return $this->rules;

			// Set rules and action
			$rules  = $args[0];
			$action = substr($rules, 0, 1);

			if (in_array($action, array('-', '+', '='))) {
				// Remove the action from the rules
				$rules = substr($rules, 1);
			} else {
				// Default action is append
				$action = '';
			}

			$this->add_rules(explode('|', $rules), $action);
		} elseif ($method == 'name') {
			// Do nothing. The name should stay static once it is set.
		} else {
			$this->data[$method] = $args[0];
		}

		return $this;
	}
	
	/**
	 * Accessor to the form this is attached to
	 */
	public function form() {
		return $this->formation;
	}

	/**
	 * Returns form attributes.
	 *
	 * @param   string  attribute name
	 * @return  string
	 */
	public function __get($key) {
		if(array_key_exists($key, $this->data)) {
			return $this->data[$key];
		}
	}

	/**
	 * Sets form attributes.
	 *
	 * @param   string  attribute name
	 * @param   string  attribute value
	 * @return  object	current instance
	 */
	public function __set($key, $value) {
		$this->data[$key] = $value;
	}

	/**
	 * Sets a form element that this element must match the value of.
	 *
	 * @chainable
	 * @param   object  another Formation input
	 * @return  object
	 */
	public function matches($input) {
		if ( ! in_array($input, $this->matches, YES)) {
			$this->matches[] = $input;
		}

		return $this;
	}

	/**
	 * Sets a callback method as a rule for this input.
	 *
	 * @chainable
	 * @param   callback
	 * @return  object
	 */
	public function callback($callback) {
		if ( ! in_array($callback, $this->callbacks, YES)) {
			$this->callbacks[] = $callback;
		}

		return $this;
	}

	/**
	 * Sets or returns the input label.
	 *
	 * @chainable
	 * @param   string   label to set
	 * @return  string|object
	 */
	public function label($val = nil) {
		if ($val === nil) {
			if (isset($this->data['name']) AND isset($this->data['label'])) {
				if($this->has_rule('required')) {
					$extra_text .= '<span class="required">*</span>';
				}
				return form::label($this->data['name'], $this->data['label'].$extra_text);
			}
			return NO;
		} else {
			$this->data['label'] = ($val === YES) ? utf8::ucwords(inflector::humanize($this->name)) : $val;
			return $this;
		}
	}

	/**
	 * Set or return the error message.
	 *
	 * @chainable
	 * @param   string  error message
	 * @return  strong|object
	 */
	public function message($val = nil) {
		if ($val === nil) {
			if (isset($this->data['message']))
				return $this->data['message'];
		} else {
			$this->data['message'] = $val;
			return $this;
		}
	}
	
	
	/**
	 * Runs validation and returns the element HTML.
	 *
	 * @return  string
	 */
	public function render() {
		// Make sure validation runs
		$this->validate();

		return $this->html_element();
	}

	/**
	 * Returns the form input HTML.
	 *
	 * @return  string
	 */
	protected function html_element() {
		$data = $this->data;

		unset($data['label']);
		unset($data['message']);

		return form::input($data);
	}

	/**
	 * Replace, remove, or append rules.
	 *
	 * @param   array   rules to change
	 * @param   string  action to use: replace, remove, append
	 */
	protected function add_rules( array $rules, $action) {
		if ($action === '=') {
			// Just replace the rules
			$this->rules = $rules;
			return;
		}

		foreach($rules as $rule) {
			if ($action === '-') {
				if (($key = array_search($rule, $this->rules)) !== NO) {
					// Remove the rule
					unset($this->rules[$key]);
				}
			} else {
				if ( ! in_array($rule, $this->rules)) {
					if ($action == '+') {
						array_unshift($this->rules, $rule);
					} else {
						$this->rules[] = $rule;
					}
				}
			}
		}
	}

	/**
	 * Add an error to the input.
	 *
	 * @chainable
	 * @return object
	 */
	public function add_error($key, $val) {
		if ( ! isset($this->errors[$key])) {
			$this->errors[$key] = $val;
		}

		return $this;
	}

	/**
	 * Set or return the error messages.
	 *
	 * @chainable
	 * @param   string|array  failed validation function, or an array of messages
	 * @param   string        error message
	 * @return  object|array
	 */
	public function error_messages($func = nil, $message = nil) {
		// Set custom error messages
		if ( ! empty($func)) {
			if (is_array($func)) {
				// Replace all
				$this->error_messages = $func;
			} else {
				if (empty($message)) {
					// Single error, replaces all others
					$this->error_messages = $func;
				} else {
					// Add custom error
					$this->error_messages[$func] = $message;
				}
			}
			return $this;
		}
		
		if(!$this->formation->submitted()) return array();

		// Make sure validation runs
		is_null($this->is_valid) and $this->validate();

		// Return single error
		if ( ! is_array($this->error_messages) AND ! empty($this->errors))
			return array($this->error_messages);

		$messages = array();
		foreach($this->errors as $func => $args) {
			if (is_string($args)) {
				$error = $args;
			} else {
				// Force args to be an array
				$args = is_array($args) ? $args : array();

				// Add the label or name to the beginning of the args
				array_unshift($args, $this->label ? utf8::strtolower($this->label) : $this->name);

				if (isset($this->error_messages[$func])) {
					// Use custom error message
					$error = vsprintf($this->error_messages[$func], $args);
				} else {
					// Get the proper i18n entry, very hacky but it works
					switch($func) {
						case 'valid_url':
						case 'valid_email':
						case 'valid_ip':
							// Fetch an i18n error message
							$error = Eight::lang('validation.'.$func, $args);
							break;
						case substr($func, 0, 6) === 'valid_':
							// Strip 'valid_' from func name
							$func = (substr($func, 0, 6) === 'valid_') ? substr($func, 6) : $func;
						case 'alpha':
						case 'alpha_dash':
						case 'digit':
						case 'numeric':
							// i18n strings have to be inserted into valid_type
							$args[] = Eight::lang('validation.'.$func);
							$error = Eight::lang('validation.valid_type', $args);
							break;
						default:
							$error = Eight::lang('validation.'.$func, $args);
					}
				}
			}

			// Add error to list
			$messages[] = $error;
		}

		return $messages;
	}
	
	/**
	 * Sets or Returns the HTML class name.
	 *
	 * @return  self or HTML class name
	 */
	 
	 public function class_value($val = nil) {
	 	if($val == nil) {
	 		return $this->data['class'];
	 	} else {
	 		$this->data['class'] = $val;
	 		return $this;
	 	}
	 }
	 
	/**
	 * Sets or Returns the HTML style.
	 *
	 * @return  self or HTML style
	 */
	 
	 public function style_value($val = nil) {
	 	if($val == nil) {
	 		return $this->data['style'];
	 	} else {
	 		$this->data['style'] = $val;
	 		return $this;
	 	}
	 }

	/**
	 * Get the global input value.
	 *
	 * @return  string|bool
	 */
	protected function input_value($name = nil) {
		if(!$this->formation->submitted())
			return NO;
		
		if($name == nil)
			$name = $this->name;

		// Fetch the method for this object
		$method = $this->method;
		
		return (func_num_args() > 0) ? Input::instance()->$method(func_get_arg(0)) : Input::instance()->$method();
	}

	/**
	 * Load the value of the input, if form data is present.
	 *
	 * @return  void
	 */
	protected function load_value() {
		if (is_bool($this->is_valid))
			return;

		if ($name = $this->name) {
			// Load POSTed value, but only for named inputs
			$this->data['value'] = $this->input_value($name);
		}

		if (is_string($this->data['value'])) {
			// Trim string values
			$this->data['value'] = trim($this->data['value']);
		}
	}

	/**
	 * Validate this input based on the set rules.
	 *
	 * @return  bool
	 */
	public function validate() {
		// Validation has already run
		if (is_bool($this->is_valid))
			return $this->is_valid;

		// No data to validate
		if ($this->input_value() == NO) {
			return $this->is_valid = NO;
		}

		// Load the submitted value
		$this->load_value();

		// No rules to validate
		if (count($this->rules) == 0 AND count($this->matches) == 0 AND count($this->callbacks) == 0)
			return $this->is_valid = YES;

		if ( ! empty($this->rules)) {
			foreach($this->rules as $rule) {
				if(str::e($rule)) continue;
				
				if (($offset = strpos($rule, '[')) !== NO) {
					// Get the args
					$args = preg_split('/, ?/', trim(substr($rule, $offset), '[]'));

					// Remove the args from the rule
					$rule = substr($rule, 0, $offset);
				}
				
				if(is_callable($rule)) {
					$this->value = $rule($this->value);
				} elseif (substr($rule, 0, 6) === 'valid_' AND method_exists('valid', substr($rule, 6))) {
					$func = substr($rule, 6);

					if ($this->value AND ! valid::$func($this->value)) {
						$this->errors[$rule] = YES;
					}
				} elseif (str::starts_with($rule, "callback_")) {
					if(method_exists(Eight::instance(), substr($rule, 9))) {
						if(is_array($args)) {
							$new_args = array_merge(array(&$this), $args);	
						} else {
							$new_args = array(&$this);
						}
						
						call_user_func_array(array(Eight::instance(), substr($rule, 9)), $new_args);
					} else {
						throw new Eight_Exception('validation.invalid_callback', substr($rule, 9));
					}
				} elseif (method_exists($this, 'rule_'.$rule)) {
					// The rule function is always prefixed with rule_
					$rule = 'rule_'.$rule;

					if (isset($args)) {
						// Manually call up to 2 args for speed
						switch(count($args)) {
							case 1:
								$this->$rule($args[0]);
							break;
							case 2:
								$this->$rule($args[0], $args[1]);
							break;
							default:
								call_user_func_array(array($this, $rule), $args);
							break;
						}
					} else {
						// Just call the rule
						$this->$rule();
					}

					// Prevent args from being re-used
					unset($args);
				} else {
					throw new Eight_Exception('validation.invalid_rule', $rule);
				}

				// Stop when an error occurs
				if ( ! empty($this->errors))
					break;
			}
		}

		if ( ! empty($this->matches)) {
			foreach($this->matches as $input) {
				if ($this->value != $input->value) {
					// Field does not match
					$this->errors['matches'] = array($input->label ? utf8::strtolower($input->label) : $input->name);
					break;
				}
			}
		}

		if (!empty($this->callbacks)) {
			foreach($this->callbacks as $callback) {
				call_user_func($callback, $this);
				// Stop when an error occurs
				if(!empty($this->errors)) {
					break;
				}
			}
		}

		// If there are errors, validation failed
		return $this->is_valid = empty($this->errors);
	}
	
	/**
	 * Checks for the given rule in the rules array stack
	 * 
	 * @param	string	rule as defined in the formation object creation
	 * @return	bool	whether or not the object has this rule
	 */
	public function has_rule($rule) {
		foreach($this->rules as $r) {
			if($r == $rule) {
				return TRUE;
			}
		}
	}
	
	/**
	 * Validate required.
	 */
	protected function rule_required() {
		if ($this->value === '' OR $this->value === nil) {
			$this->errors['required'] = YES;
		}
	}
	
	/**
	 *	Validate required_if
	 */
	protected function rule_required_if($condition) {
		list($key, $val) = explode('=', $condition);
		$key = trim($key);
		$val = trim($val);
		
		if(request::$input[$key] == $val) {
			$this->rule_required();
		}
	}

	/**
	 * Validate length.
	 */
	protected function rule_length($min, $max = nil) {
		// Get the length, return if zero
		if (($length = mb_strlen($this->value)) === 0)
			return;

		if ($max == nil) {
			if ($length != $min) {
				$this->errors['exact_length'] = array($min);
			}
		} else {
			if ($length < $min) {
				$this->errors['min_length'] = array($min);
			} elseif($length > $max) {
				$this->errors['max_length'] = array($max);
			}
		}
	}
	
	/**
	 * Method: rule_xss_clean
	 *  XSS clean string.
	 *
	 * Parameters:
	 *  str - string
	 */
	protected function rule_xss_clean() {
		$this->value = Eight::instance()->input->xss_clean($this->value);
	}
	
} // End Formation Input
<?php
/**
 * Eight Exceptions
 *
 * @package		System
 * @subpackage	Exceptions
 * @author		EightPHP Development Team
 * @copyright	(c) 2009-2010 EightPHP
 * @license		http://license.eightphp.com
 */

class Eight_Exception_Core extends Exception {

	public static $enabled = FALSE;

	// Template file
	public static $template = 'eight/error';

	// Show stack traces in errors
	public static $trace_output = TRUE;

	// Show source code in errors
	public static $source_output = TRUE;

	// To hold unique identifier to distinguish error output
	protected $instance_identifier;

	// Error code
	protected $code = E_EIGHT;

	/**
	 * Creates a new translated exception.
	 *
	 * @param string error message
	 * @param array translation variables
	 * @return void
	 */
	public function __construct($message, $variables = NULL, $code = 0) {
		$this->instance_identifier = uniqid();
		
		if(!is_array($variables)) {
			$variables = array($variables);
		}

		// Translate the error message
		$message = strval(Eight::lang($message, $variables));

		$code = intval($code);

		// Sets $this->message the proper way
		parent::__construct($message, $code);
	}

	/**
	 * Enable Eight exception handling.
	 *
	 * @uses    Eight_Exception::$template
	 * @return  void
	 */
	public static function enable() {
		if(!Eight_Exception::$enabled) {
			set_exception_handler(array('Eight_Exception', 'handle'));

			Eight_Exception::$enabled = TRUE;
		}
	}

	/**
	 * Disable Eight exception handling.
	 *
	 * @return  void
	 */
	public static function disable() {
		if(Eight_Exception::$enabled) {
			restore_exception_handler();

			Eight_Exception::$enabled = FALSE;
		}
	}

	/**
	 * Get a single line of text representing the exception:
	 *
	 * Error [ Code ]: Message ~ File [ Line ]
	 *
	 * @param   object  Exception
	 * @return  string
	 */
	public static function text($e, $full_args = FALSE) {
		// Should we use the full argument length or truncate?
		$arg_char_limit = $full_args ? 2500 : 50;
		
		// Clean up the message a bit
		$message = str_replace(array("<br>", "<br/>", "<br />", "\r\n", "\n", "\r"), '; ', strip_tags($e->getMessage()));
		
		// How was the request made
		$called = 'Request:'."\n";
		$method = strtoupper(request::method());
		if($method == 'CLI') {
			$called .= 'CLI - '.cli::launch_cmd();
		} else {
			$called .= '['.$method.'] '.request::ip().' - '.request::protocol().'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
			if(is_array($_POST) && count($_POST) > 0) {
				$called .= "\n\nBody:\n";
				$called .= var_export($_POST, TRUE);
			}
		}
		
		return sprintf('%s [ %s ]: %s ~ %s [ %d ]'."\n\n".'%s'."\n\n".'%s'."\n",
			get_class($e), $e->getCode(), $message, Eight_Exception::debug_path($e->getFile()), $e->getLine(), $called, Eight_Exception::trace_string($e->getTrace(), $arg_char_limit));
	}

	/**
	 * exception handler, displays the error message, source of the
	 * exception, and the stack trace of the error.
	 *
	 * @uses    Eight::lang()
	 * @uses    Eight_Exception::text()
	 * @param   object   exception object
	 * @return  void
	 */
	public static function handle(Exception $e) {
		try {
			// Get the exception information
			$type    = get_class($e);
			$code    = $e->getCode();
			$message = $e->getMessage();

			// Create a text version of the exception
			$error = Eight_Exception::text($e, TRUE);

			// Add this exception to the log
			Eight::log('error', $error);

			// Manually save logs after exceptions
			Eight::log_save();

			if(Eight::config('core.display_errors') === FALSE && Eight::$force_show_errors !== YES) {
				// Do not show the details
				$file = $line = NULL;
				$trace = array();

				$template = '_disabled';
			} else {
				$file = $e->getFile();
				$line = $e->getLine();
				$trace = $e->getTrace();

				$template = Eight::$server_api == 'cli' ? '_cli' : '';
			}
			
			if(!headers_sent() && Eight::$server_api != 'cli') {
				header("Content-Type: text/html;charset=utf-8");
			}

			if($e instanceof Eight_Exception) {
				$template = $e->getTemplate().$template;

				if(!headers_sent()) {
					$e->sendHeaders();
				}

				// Use the human-readable error name
				$code = Eight::lang('4'.$code);
			} else {
				$template = Eight_Exception::$template.$template;

				if(!headers_sent()) {
					header('HTTP/1.1 500 Internal Server Error');
				}

				if($e instanceof ErrorException) {
					// Use the human-readable error name
					$code = Eight::lang('4'.$e->getSeverity());

					if(version_compare(PHP_VERSION, '5.3', '<')) {
						// Workaround for a bug in ErrorException::getTrace() that exists in
						// all PHP 5.2 versions. @see http://bugs.php.net/45895
						for ($i = count($trace) - 1; $i > 0; --$i) {
							if(isset($trace[$i - 1]['args'])) {
								// Re-position the arguments
								$trace[$i]['args'] = $trace[$i - 1]['args'];

								unset($trace[$i - 1]['args']);
							}
						}
					}
				}
			}

			// Clean the output buffer if one exists
			ob_get_level() and ob_end_clean();
			
			if($template = Eight::find_file('views', $template)) {
				include $template;
			}
		} catch (Exception $e) {
			// Clean the output buffer if one exists
			ob_get_level() and ob_clean();
			
			// Display the exception text
			echo Eight_Exception::text($e, TRUE), "\n";
		}
		
		// Exit with an error code
		exit(1);
	}

	/**
	 * Returns the template for this exception.
	 *
	 * @uses    Eight_Exception::$template
	 * @return  string
	 */
	public function getTemplate() {
		return Eight_Exception::$template;
	}

	/**
	 * Sends an Internal Server Error header.
	 *
	 * @return  void
	 */
	public function sendHeaders() {
		// Send the 500 header
		header('HTTP/1.1 500 Internal Server Error');
	}

	/**
	 * Returns an HTML string of information about a single variable.
	 *
	 * Borrows heavily on concepts from the Debug class of {@link http://nettephp.com/ Nette}.
	 *
	 * @param   mixed    variable to dump
	 * @param   integer  maximum length of strings
	 * @return  string
	 */
	public static function dump($value, $length = 128) {
		return Eight_Exception::_dump($value, $length);
	}

	/**
	 * Helper for Eight_Exception::dump(), handles recursion in arrays and objects.
	 *
	 * @param   mixed    variable to dump
	 * @param   integer  maximum length of strings
	 * @param   integer  recursion level (internal)
	 * @return  string
	 */
	private static function _dump( & $var, $length = 128, $level = 0) {
		if($var === NULL) {
			return '<small>NULL</small>';
		} elseif(is_bool($var)) {
			return '<small>bool</small> '.($var ? 'TRUE' : 'FALSE');
		} elseif(is_float($var)) {
			return '<small>float</small> '.$var;
		} elseif(is_resource($var)) {
			if(($type = get_resource_type($var)) === 'stream' AND $meta = stream_get_meta_data($var)) {
				$meta = stream_get_meta_data($var);

				if(isset($meta['uri'])) {
					$file = $meta['uri'];

					if(function_exists('stream_is_local')) {
						// Only exists on PHP >= 5.2.4
						if(stream_is_local($file)) {
							$file = Eight_Exception::debug_path($file);
						}
					}

					return '<small>resource</small><span>('.$type.')</span> '.htmlspecialchars($file, ENT_NOQUOTES, Eight::CHARSET);
				}
			} else {
				return '<small>resource</small><span>('.$type.')</span>';
			}
		} elseif(is_string($var)) {
			if(strlen($var) > $length) {
				// Encode the truncated string
				$str = htmlspecialchars(substr($var, 0, $length), ENT_NOQUOTES, Eight::CHARSET).'&nbsp;&hellip;';
			} else {
				// Encode the string
				$str = htmlspecialchars($var, ENT_NOQUOTES, Eight::CHARSET);
			}

			return '<small>string</small><span>('.strlen($var).')</span> "'.$str.'"';
		} elseif(is_array($var)) {
			$output = array();

			// Indentation for this variable
			$space = str_repeat($s = '    ', $level);

			static $marker;

			if($marker === NULL) {
				// Make a unique marker
				$marker = uniqid("\x00");
			}

			if(empty($var)) {
				// Do nothing
			} elseif(isset($var[$marker])) {
				$output[] = "(\n$space$s*RECURSION*\n$space)";
			} elseif($level < 5) {
				$output[] = "<span>(";

				$var[$marker] = TRUE;
				foreach ($var as $key => & $val) {
					if($key === $marker) continue;
					if(!is_int($key)) {
						$key = '"'.$key.'"';
					}

					$output[] = "$space$s$key => ".Eight_Exception::_dump($val, $length, $level + 1);
				}
				unset($var[$marker]);

				$output[] = "$space)</span>";
			} else {
				// Depth too great
				$output[] = "(\n$space$s...\n$space)";
			}

			return '<small>array</small><span>('.count($var).')</span> '.implode("\n", $output);
		} elseif(is_object($var)) {
			// Copy the object as an array
			$array = (array) $var;

			$output = array();

			// Indentation for this variable
			$space = str_repeat($s = '    ', $level);

			$hash = spl_object_hash($var);

			// Objects that are being dumped
			static $objects = array();

			if(empty($var)) {
				// Do nothing
			} elseif(isset($objects[$hash])) {
				$output[] = "{\n$space$s*RECURSION*\n$space}";
			} elseif($level < 5) {
				$output[] = "<code>{";

				$objects[$hash] = TRUE;
				foreach ($array as $key => & $val) {
					if($key[0] === "\x00") {
						// Determine if the access is private or protected
						$access = '<small>'.($key[1] === '*' ? 'protected' : 'private').'</small>';

						// Remove the access level from the variable name
						$key = substr($key, strrpos($key, "\x00") + 1);
					} else {
						$access = '<small>public</small>';
					}

					$output[] = "$space$s$access $key => ".Eight_Exception::_dump($val, $length, $level + 1);
				}
				unset($objects[$hash]);

				$output[] = "$space}</code>";
			} else {
				// Depth too great
				$output[] = "{\n$space$s...\n$space}";
			}

			return '<small>object</small> <span>'.get_class($var).'('.count($array).')</span> '.implode("\n", $output);
		} else {
			return '<small>'.gettype($var).'</small> '.htmlspecialchars(print_r($var, TRUE), ENT_NOQUOTES, Eight::CHARSET);
		}
	}

	/**
	 * Removes APPPATH, SYSPATH, MODPATH, and DOCROOT from filenames, replacing
	 * them with the plain text equivalents.
	 *
	 * @param   string  path to sanitize
	 * @return  string
	 */
	public static function debug_path($file) {
		if(strpos($file, APPPATH) === 0) {
			$file = 'APPPATH/'.substr($file, strlen(APPPATH));
		} elseif(strpos($file, SYSPATH) === 0) {
			$file = 'SYSPATH/'.substr($file, strlen(SYSPATH));
		} elseif(strpos($file, MODPATH) === 0) {
			$file = 'MODPATH/'.substr($file, strlen(MODPATH));
		} elseif(strpos($file, DOCROOT) === 0) {
			$file = 'DOCROOT/'.substr($file, strlen(DOCROOT));
		}

		return $file;
	}

	/**
	 * Returns an array of lines from a file.
	 *
	 *     // Returns the current line of the current file
	 *     echo Eight_Exception::debug_source(__FILE__, __LINE__);
	 *
	 * @param   string   file to open
	 * @param   integer  line number to find
	 * @param   integer  number of padding lines
	 * @return  array
	 */
	public static function debug_source($file, $line_number, $padding = 5) {
		// Make sure we can read the source file
		if(!is_readable($file))
			return array();

		// Open the file and set the line position
		$file = fopen($file, 'r');
		$line = 0;

		// Set the reading range
		$range = array('start' => $line_number - $padding, 'end' => $line_number + $padding);

		// Set the zero-padding amount for line numbers
		$format = '% '.strlen($range['end']).'d';

		$source = array();
		while (($row = fgets($file)) !== FALSE) {
			// Increment the line number
			if(++$line > $range['end'])
				break;

			if($line >= $range['start']) {
				$source[sprintf($format, $line)] = $row;
			}
		}

		// Close the file
		fclose($file);
		
		// Prevent MySQL passwords from showing up
		$source = preg_replace("#mysql_(.*?)connect\((.*?)\)#ism", 'mysql_$1connect(REMOVED_FOR_SECURITY)' , $source);
		
		return $source;
	}

	/**
	 * Returns an array of strings that represent each step in the backtrace.
	 *
	 * @param   array  trace to analyze
	 * @return  array
	 */
	public static function trace($trace = NULL) {
		if($trace === NULL) {
			// Start a new trace
			$trace = debug_backtrace();
		}

		// Non-standard function calls
		$statements = array('include', 'include_once', 'require', 'require_once');

		$output = array();
		foreach ($trace as $step) {
			if(!isset($step['function'])) {
				// Invalid trace step
				continue;
			}

			if(isset($step['file']) AND isset($step['line'])) {
				// Include the source of this step
				$source = Eight_Exception::debug_source($step['file'], $step['line']);
			}

			if(isset($step['file'])) {
				$file = $step['file'];

				if(isset($step['line'])) {
					$line = $step['line'];
				}
			}

			// function()
			$function = $step['function'];

			if(in_array($step['function'], $statements)) {
				if(empty($step['args'])) {
					// No arguments
					$args = array();
				} else {
					// Sanitize the file path
					$args = array($step['args'][0]);
				}
			} elseif(isset($step['args'])) {
				if($step['function'] === '{closure}') {
					// Introspection on closures in a stack trace is impossible
					$params = NULL;
				} else {
					if(isset($step['class'])) {
						if(method_exists($step['class'], $step['function'])) {
							$reflection = new ReflectionMethod($step['class'], $step['function']);
						} else {
							$reflection = new ReflectionMethod($step['class'], '__call');
						}
					} else {
						$reflection = new ReflectionFunction($step['function']);
					}

					// Get the function parameters
					$params = $reflection->getParameters();
				}

				$args = array();

				foreach($step['args'] as $i => $arg) {
					if(isset($params[$i])) {
						// Assign the argument by the parameter name
						$args[$params[$i]->name] = $arg;
					} else {
						// Assign the argument by number
						$args[$i] = $arg;
					}
				}
			}

			if(isset($step['class'])) {
				// Class->method() or Class::method()
				$function = $step['class'].$step['type'].$step['function'];
			}

			$output[] = array(
				'function' => $function,
				'args'     => isset($args)   ? $args : NULL,
				'file'     => isset($file)   ? $file : NULL,
				'line'     => isset($line)   ? $line : NULL,
				'source'   => isset($source) ? $source : NULL,
			);

			unset($function, $args, $file, $line, $source);
		}

		return $output;
	}
	
	/**
	 * Returns a stacktrace in the form of a string
	 */
	public static function trace_string($trace, $arg_char_limit = 50) {
		$string = "";
		
		// Setup the stack trace
		$string .= Eight_Exception::trace_string_line("Stack Trace:");

		$x = 0;
		$error_id = 0;
		foreach (Eight_Exception::trace($trace) as $i=>$step) {
			$msg_line = "#".str_pad($x, 2, "0", STR_PAD_LEFT). "  ";
			if ($step['file']) {
				$source_id = $error_id.'source'.$i;
				$msg_line .= Eight_Exception::debug_path($step['file']).'('.$step['line']."):  ";
			} else {
				$msg_line .= "{".__('PHP internal call')."}:  ";
			}

			$msg_line .= $step['function'].'(';
			$print_able_args = array();
			if ($step['args']) {
				$args_id = $error_id.'args'.$i;
				foreach($step['args'] as $arg) {
					$arg_name = "";

					if(is_object($arg)) {
						$arg_name = get_class($arg);
					} else if(is_array($arg)) {
						$arg_name = self::arr_to_str($arg);
					} else if(is_null($arg)) {
						$arg_name = "NULL";
					} else if(is_string($arg)) {
						$arg_name = '"'.$arg.'"';
					} else {
						$arg_name = strval($arg);
					}

					$arg_name = preg_replace("#\s+#", " ", $arg_name);

					$print_able_args[] = str::limit_chars($arg_name, $arg_char_limit, "");
				}
			}

			$msg_line .= implode(", ", $print_able_args);

			$msg_line .= ")";
			$string .= Eight_Exception::trace_string_line($msg_line);
			$x++;
		}
		
		return $string;
	}
	
	public static function arr_to_str($arr) {
		return preg_replace("/array[\s]*\(/", 'array(', preg_replace("/\,[\s]*\)/", ')', preg_replace("/\s+/", " ", str_replace(array("\r\n", "\r", "\n", "\t"), '', var_export(self::obj_to_str($arr), TRUE)))));
	}
	
	public static function obj_to_str($obj) {
		if(is_array($obj)) {
			foreach($obj as $k=>$v) {
				$obj[self::obj_to_str($k)] = self::obj_to_str($v);
			}
		} elseif(is_object($obj)) {
			$obj = '**'.get_class($obj).'**';
		}

		return $obj;
	}
	
	public static function trace_string_line($str) {
		return $str."\n";
	}

	public static function trace_string_break() {
		return "\n";
	}

} // End Eight Exception
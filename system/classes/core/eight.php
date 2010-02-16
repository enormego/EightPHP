<?php
/**
 * Provides Eight-specific helper functions. This is where the magic happens!
 *
 * @package		System
 * @subpackage	Core
 * @author		EightPHP Development Team
 * @copyright	(c) 2009-2010 EightPHP
 * @license		http://license.eightphp.com
 */
final class Eight {

	const CHARSET  = 'UTF-8';
	const LOCALE = 'en_US';

	// The singleton instance of the controller
	public static $instance;

	// Output buffering level
	private static $buffer_level;

	// Will be set to YES when an exception is caught
	public static $has_error = NO;

	// The final output that will displayed by Eight
	public static $output = '';

	// The current user agent
	public static $user_agent;

	// The current locale
	public static $locale;

	// Configuration
	private static $configuration;

	// Include paths
	private static $include_paths;

	// Logged messages
	private static $log;

	// Cache lifetime
	private static $cache_lifetime;

	// Log levels
	private static $log_levels = array (
		'error' => 1,
		'alert' => 2,
		'info'  => 3,
		'debug' => 4,
	);

	// Internal caches and write status
	private static $internal_cache = array();
	private static $write_cache;
	
	// Server API that PHP is using. Allows testing of different APIs.
	public static $server_api = PHP_SAPI;

	/**
	 * Sets up the PHP environment. Adds error/exception handling, output
	 * buffering, and adds an auto-loading method for loading classes.
	 *
	 * This method is run immediately when this file is loaded, and is
	 * benchmarked as environment_setup.
	 *
	 * For security, this function also destroys the $_REQUEST global variable.
	 * Using the proper global (GET, POST, COOKIE, etc) is inherently more secure.
	 * The recommended way to fetch a global variable is using the Input library.
	 * @see http://www.php.net/globals
	 *
	 * @return  void
	 */
	public static function setup() {
		static $run;

		if(PHP_SAPI == 'cli' && in_array("--show-errors", $_SERVER['argv'])) {
			error_reporting(E_ALL ^ E_NOTICE);
			ini_set('display_errors', true);
			Eight::config_set('core.display_errors', YES);
		}

		// This function can only be run once
		if($run === YES)
			return;

		// Define Eight error constant
		define('E_EIGHT', 42);

		// Define 404 error constant
		define('E_PAGE_NOT_FOUND', 43);

		// Define database error constant
		define('E_DATABASE_ERROR', 44);

		if(self::$cache_lifetime = self::config('core.internal_cache')) {
			// Load cached configuration and language files
			self::$internal_cache['configuration'] = self::cache('configuration', self::$cache_lifetime);
			self::$internal_cache['language']      = self::cache('language', self::$cache_lifetime);

			// Load cached file paths
			self::$internal_cache['find_file_paths'] = self::cache('find_file_paths', self::$cache_lifetime);

			// Enable cache saving
			Event::add('system.shutdown', array(__CLASS__, 'internal_cache_save'));
		}

		// Set autoloader
		spl_autoload_register(array('Eight', 'auto_load'));

		// Send default text/html UTF-8 header
		header('Content-Type: text/html; charset=UTF-8');

		// Enable exception handling
		Eight_Exception::enable();

		// Enable error handling
		Eight_Exception_PHP::enable();

		if(self::$configuration['core']['log_threshold'] > 0) {
			// Set the log directory
			self::log_directory(self::$configuration['core']['log_directory']);

			// Enable log writing at shutdown
			register_shutdown_function(array(__CLASS__, 'log_save'));
		}

		// Disable notices and "strict" errors, to prevent some oddities in
		// PHP 5.2 and when using Eight under CLI
		$ER = error_reporting(~E_NOTICE & ~E_STRICT);

		// Set the user agent
		self::$user_agent = trim($_SERVER['HTTP_USER_AGENT']);

		if(!($timezone = Eight::config('locale.timezone'))) {
			// Get the default timezone
			$timezone = date_default_timezone_get();
		}
		
		// Restore error reporting
		error_reporting($ER);

		// Set the default timezone
		date_default_timezone_set($timezone);

		// Start output buffering
		ob_start(array(__CLASS__, 'output_buffer'));

		// Save buffering level
		self::$buffer_level = ob_get_level();

		// Load locales
		$locales = Eight::config('locale.language');

		// Make first locale UTF-8
		$locales[0] .= '.UTF-8';

		// Set locale information
		self::$locale = setlocale(LC_ALL, $locales);

		// Enable Eight routing
		Event::add('system.routing', array('Router', 'find_uri'));
		Event::add('system.routing', array('Router', 'setup'));

		// Enable Eight controller initialization
		Event::add('system.execute', array('Eight', 'instance'));

		// Enable Eight 404 pages
		Event::add('system.404', array('Eight_Exception_404', 'trigger'));

		// Enable Eight output handling
		Event::add('system.shutdown', array('Eight', 'shutdown'));

		if($config = Eight::config('core.enable_hooks')) {
			// Start the loading_hooks routine
			Benchmark::start(SYSTEM_BENCHMARK.'_loading_hooks');

			$hooks = array();

			if(!is_array($config)) {
				// All of the hooks are enabled, so we use list_files
				$hooks = Eight::list_files('hooks', YES);
			} else {
				// Individual hooks need to be found
				foreach($config as $name) {
					if($hook = Eight::find_file('hooks', $name, NO)) {
						// Hook was found, add it to loaded hooks
						$hooks[] = $hook;
					} else {
						// This should never happen
						Eight::log('error', 'Hook not found: '.$name);
					}
				}
			}

			// Length of extension, for offset
			$ext = -(strlen(EXT));

			foreach($hooks as $hook) {
				// Validate the filename extension
				if(substr($hook, $ext) === EXT) {
					// Hook was found, include it
					include $hook;
				} else {
					// This should never happen
					Eight::log('error', 'Hook not found: '.$hook);
				}
			}

			// Stop the loading_hooks routine
			Benchmark::stop(SYSTEM_BENCHMARK.'_loading_hooks');
		}

		// Setup is complete, prevent it from being run again
		$run = YES;
	}
	
	/**
	 * Cleans up the PHP environment. Disables error/exception handling and the
	 * auto-loading method and closes the output buffer.
	 *
	 * This method does not need to be called during normal system execution,
	 * however in some advanced situations it can be helpful.
	 */
	public static function cleanup() {
		static $run;

		// Only run this function once
		if ($run === TRUE)
			return;

		$run = TRUE;

		Eight_Exception::disable();

		Eight_Exception_PHP::disable();

		spl_autoload_unregister(array('Eight', 'auto_load'));

		Eight::close_buffers();
	}

	/**
	 * Loads the controller and initializes it. Runs the pre_controller,
	 * post_controller_constructor, and post_controller events. Triggers
	 * a system.404 event when the route cannot be mapped to a controller.
	 *
	 * This method is benchmarked as controller_setup and controller_execution.
	 *
	 * @return  object  instance of controller
	 */
	public static function &instance() {
		if(self::$instance === nil) {
			// Routing has been completed
			Event::run('system.post_routing');

			Benchmark::start(SYSTEM_BENCHMARK.'_controller_setup');

			// Log the current routing state for debugging purposes
			Eight::log('debug', 'Routing "'.Router::$current_uri.'" using the "'.Router::$current_route.'" route, '.Router::$controller.'::'.Router::$method);

			try {
				// Start validation of the controller
				$class = new ReflectionClass('Controller_'.ucfirst(Router::$controller));
			} catch(ReflectionException $e) {
				// Controller does not exist
				Event::run('system.404');
			}

			if($class->isAbstract() OR (IN_PRODUCTION AND $class->getConstant('ALLOW_PRODUCTION') == FALSE)) {
				// Controller is not allowed to run in production
				Event::run('system.404');
			}
			// Run system.pre_controller
			Event::run('system.pre_controller');

			// Create a new controller instance
			$controller = $class->newInstance();

			// Controller constructor has been executed
			Event::run('system.post_controller_constructor');

			try {
				// Load the controller method
				$method = $class->getMethod(Router::$method);

				// Method exists
				if(Router::$method[0] === '_') {
					// Do not allow access to hidden methods
					Event::run('system.404');
				}

				if($method->isProtected() or $method->isPrivate()) {
					// Do not attempt to invoke protected or private methods
					throw new ReflectionException('protected controller method');
				}

				// Default arguments
				$arguments = Router::$arguments;
			} catch (ReflectionException $e) {
				// Use __call instead
				$method = $class->getMethod('__call');

				// Use arguments in __call format
				$arguments = array(Router::$method, Router::$arguments);
			}

			// Stop the controller setup benchmark
			Benchmark::stop(SYSTEM_BENCHMARK.'_controller_setup');

			// Start the controller execution benchmark
			Benchmark::start(SYSTEM_BENCHMARK.'_controller_execution');

			// Execute the controller method
			$method->invokeArgs($controller, $arguments);

			// Controller method has been executed
			Event::run('system.post_controller');

			// Stop the controller execution benchmark
			Benchmark::stop(SYSTEM_BENCHMARK.'_controller_execution');
		}

		return Eight::$instance;
	}

	/**
	 * Get all include paths. APPPATH is the first path, followed by module
	 * paths in the order they are configured, follow by the SYSPATH.
	 *
	 * @param   boolean  re-process the include paths
	 * @return  array
	 */
	public static function include_paths($process = NO) {
		if($process === YES) {
			// Get standard PHP include paths
			// $php_paths = get_include_path();

			// Add APPPATH as the first path
			self::$include_paths = array(APPPATH);

			foreach(self::$configuration['core']['modules'] as $path) {
				if($path = str_replace('\\', '/', realpath($path))) {
					// Add a valid path
					self::$include_paths[] = $path.'/';
				}
			}

			// Add SYSPATH as the last path
			self::$include_paths[] = SYSPATH;
		}

		return self::$include_paths;
	}

	/**
	 * Get a config item or group.
	 *
	 * @param   string   item name
	 * @param   boolean  force a forward slash (/) at the end of the item
	 * @param   boolean  is the item required?
	 * @return  mixed
	 */
	public static function config($key, $slash = NO, $required = YES) {
		if(self::$configuration === nil) {
			// Load core configuration
			self::$configuration['core'] = self::config_load('core');

			// Re-parse the include paths
			self::include_paths(YES);
		}

		// Get the group name from the key
		$group = explode('.', $key, 2);
		$group = $group[0];

		if(!isset(self::$configuration[$group])) {
			// Load the configuration group
			self::$configuration[$group] = self::config_load($group, $required);
		}

		// Get the value of the key string
		$value = self::key_string(self::$configuration, $key);

		if($slash === YES and is_string($value) and $value !== '') {
			// Force the value to end with "/"
			$value = rtrim($value, '/').'/';
		}

		return $value;
	}

	/**
	 * Sets a configuration item, if allowed.
	 *
	 * @param   string   config key string
	 * @param   string   config value
	 * @return  boolean
	 */
	public static function config_set($key, $value) {
		// Do this to make sure that the config array is already loaded
		self::config($key);

		if(substr($key, 0, 7) === 'routes.') {
			// Routes cannot contain sub keys due to possible dots in regex
			$keys = explode('.', $key, 2);
		} else {
			// Convert dot-noted key string to an array
			$keys = explode('.', $key);
		}

		// Used for recursion
		$conf =& self::$configuration;
		$last = count($keys) - 1;

		foreach($keys as $i => $k) {
			if($i === $last) {
				$conf[$k] = $value;
			} else {
				$conf =& $conf[$k];
			}
		}

		if($key === 'core.modules') {
			// Reprocess the include paths
			self::include_paths(YES);
		}

		return YES;
	}

	/**
	 * Load a config file.
	 *
	 * @param   string   config filename, without extension
	 * @param   boolean  is the file required?
	 * @return  array
	 */
	public static function config_load($name, $required = YES) {
		if($name === 'core') {
			// Load the application configuration file
			require APPPATH.'config/config'.EXT;

			if(!isset($config['site_domain'])) {
				// Invalid config file
				die('Your Eight application configuration file is not valid.');
			}

			return $config;
		}

		if(isset(self::$internal_cache['configuration'][$name]))
			return self::$internal_cache['configuration'][$name];

		// Load matching configs
		$configuration = array();

		if($files = self::find_file('config', $name, $required)) {
			foreach($files as $file) {
				require $file;

				if(isset($config) and is_array($config)) {
					// Merge in configuration
					$configuration = array_merge($configuration, $config);
				}
			}
		}

		if(!isset(self::$write_cache['configuration'])) {
			// Cache has changed
			self::$write_cache['configuration'] = YES;
		}

		return self::$internal_cache['configuration'][$name] = $configuration;
	}

	/**
	 * Clears a config group from the cached configuration.
	 *
	 * @param   string  config group
	 * @return  void
	 */
	public static function config_clear($group) {
		// Remove the group from config
		unset(self::$configuration[$group], self::$internal_cache['configuration'][$group]);

		if(!isset(self::$write_cache['configuration'])) {
			// Cache has changed
			self::$write_cache['configuration'] = YES;
		}
	}

	/**
	 * Add a new message to the log.
	 *
	 * @param   string  type of message
	 * @param   string  message text
	 * @return  void
	 */
	public static function log($type, $message) {
		if(self::$log_levels[$type] <= self::$configuration['core']['log_threshold']) {
			self::$log[] = array(date('Y-m-d H:i:s P'), $type, $message);
		}
	}

	/**
	 * Save all currently logged messages.
	 *
	 * @return  void
	 */
	public static function log_save() {
		if(empty(self::$log))
			return;

		// Filename of the log
		$filename = self::log_directory().date('Y-m-d').'.log'.EXT;

		if(!is_file($filename)) {
			// Write the SYSPATH checking header
			file_put_contents($filename,
				'<?php defined(\'SYSPATH\') or die(\'No direct access.\'); ?>'.PHP_EOL.PHP_EOL);

			// Prevent external writes
			chmod($filename, 0644);
		}

		// Messages to write
		$messages = array();

		do
		{
			// Load the next mess
			list ($date, $type, $text) = array_shift(self::$log);

			// Add a new message line
			$messages[] = $date.' --- '.$type.': '.$text;
		}
		while(!empty(self::$log));

		// Write messages to log file
		file_put_contents($filename, implode(PHP_EOL, $messages).PHP_EOL, FILE_APPEND);
	}

	/**
	 * Get or set the logging directory.
	 *
	 * @param   string  new log directory
	 * @return  string
	 */
	public static function log_directory($dir = nil) {
		static $directory;

		if(!empty($dir)) {
			// Get the directory path
			$dir = realpath($dir);

			if(is_dir($dir) and is_writable($dir)) {
				// Change the log directory
				$directory = str_replace('\\', '/', $dir).'/';
			} else {
				// Log directory is invalid
				throw new Eight_Exception('core.log_dir_unwritable', $dir);
			}
		}

		return $directory;
	}

	/**
	 * Load data from a simple cache file. This should only be used internally,
	 * and is NOT a replacement for the Cache library.
	 *
	 * @param   string   unique name of cache
	 * @param   integer  expiration in seconds
	 * @return  mixed
	 */
	public static function cache($name, $lifetime) {
		if($lifetime > 0) {
			$path = APPPATH.'cache/eight_'.$name;

			if(is_file($path)) {
				// Check the file modification time
				if((time() - filemtime($path)) < $lifetime) {
					// Cache is valid
					return unserialize(file_get_contents($path));
				} else {
					// Cache is invalid, delete it
					unlink($path);
				}
			}
		}

		// No cache found
		return nil;
	}

	/**
	 * Save data to a simple cache file. This should only be used internally, and
	 * is NOT a replacement for the Cache library.
	 *
	 * @param   string   cache name
	 * @param   mixed    data to cache
	 * @param   integer  expiration in seconds
	 * @return  boolean
	 */
	public static function cache_save($name, $data, $lifetime) {
		if($lifetime < 1)
			return NO;

		$path = Eight::config('core.internal_cache_path').'/eight_'.$name;

		if($data === nil) {
			// Delete cache
			return (is_file($path) and unlink($path));
		} else {
			// Write data to cache file
			return (bool) file_put_contents($path, serialize($data));
		}
	}

	/**
	 * Eight output handler.
	 *
	 * @param   string  current output buffer
	 * @return  string
	 */
	public static function output_buffer($output) {
		if(!Event::has_run('system.send_headers')) {
			// Run the send_headers event, specifically for cookies being set
			Event::run('system.send_headers');
		}

		// Set final output
		Eight::$output = $output;

		// Set and return the final output
		return Eight::$output;
	}

	/**
	 * Closes all open output buffers, either by flushing or cleaning all
	 * open buffers, and optionally, the Eight output buffer.
	 *
	 * @param   boolean  disable to clear buffers, rather than flushing
	 * @param   boolean  close the eight output buffer
	 * @return  void
	 */
	public static function close_buffers($flush = YES, $eight_buffer = YES) {
		if(ob_get_level() >= self::$buffer_level) {
			// Set the close function
			$close = ($flush === YES) ? 'ob_end_flush' : 'ob_end_clean';

			while(ob_get_level() > self::$buffer_level) {
				// Flush or clean the buffer
				$close();
			}

			if($eight_buffer === YES) {
				// This will flush the Eight buffer, which sets self::$output
				ob_end_clean();

				// Reset the buffer level
				self::$buffer_level = ob_get_level();
			}
		}
	}

	/**
	 * Triggers the shutdown of Eight by closing the output buffer, runs the system.display event.
	 */
	public static function shutdown() {
		// Close output buffers
		self::close_buffers(YES);

		// Run the output event
		Event::run('system.display', self::$output);

		// Render the final output
		self::render(self::$output);
	}

	/**
	 * Inserts global Eight variables into the generated output and prints it.
	 *
	 * @param   string  final output that will displayed
	 */
	public static function render($output) {
		// Fetch memory usage in MB
		$memory = function_exists('memory_get_usage') ? (memory_get_usage() / 1024 / 1024) : 0;

		// Fetch benchmark for page execution time
		$benchmark = Benchmark::get(SYSTEM_BENCHMARK.'_total_execution');

		if(Eight::config('core.render_stats') === YES) {
			// Replace the global template variables
			$output = str_replace(
				array
				(
					'{eight_version}',
					'{eight_codename}',
					'{execution_time}',
					'{memory_usage}',
					'{included_files}',
				),
				array
				(
					EIGHT_VERSION,
					"",
					$benchmark['time'],
					number_format($memory, 2).'MB',
					count(get_included_files()),
				),
				$output
			);
		}

		if($level = Eight::config('core.output_compression') and ini_get('output_handler') !== 'ob_gzhandler' and (int) ini_get('zlib.output_compression') === 0) {
			if($level < 1 OR $level > 9) {
				// Normalize the level to be an integer between 1 and 9. This
				// step must be done to prevent gzencode from triggering an error
				$level = max(1, min($level, 9));
			}

			if(stripos(@$_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== NO) {
				$compress = 'gzip';
			} elseif(stripos(@$_SERVER['HTTP_ACCEPT_ENCODING'], 'deflate') !== NO) {
				$compress = 'deflate';
			}
		}

		if(isset($compress) and $level > 0) {
			switch ($compress) {
				case 'gzip':
					// Compress output using gzip
					$output = gzencode($output, $level);
				break;
				case 'deflate':
					// Compress output using zlib (HTTP deflate)
					$output = gzdeflate($output, $level);
				break;
			}

			// This header must be sent with compressed content to prevent
			// browser caches from breaking
			header('Vary: Accept-Encoding');

			// Send the content encoding header
			header('Content-Encoding: '.$compress);

			// Sending Content-Length in CGI can result in unexpected behavior
			if(stripos(PHP_SAPI, 'cgi') === NO) {
				header('Content-Length: '.strlen($output));
			}
		}

		echo $output;
	}

	/**
	 * Displays a 404 page, unless config says to ignore page not found.
	 *
	 * @param   string  URI of page
	 * @param   string  custom template
	 * @throws  Eight_Exception_404
	 */
	public static function show_404($page = NO, $template = NO) {
		if(in_array($page, arr::c(Eight::config('config.ignore_page_not_found')))) return FALSE;
		throw new Eight_Exception_404($page, $template);
	}

	/**
	 * Provides class auto-loading.
	 *
	 * @param   string  name of class
	 * @return  bool
	 */
	public static function auto_load($class) {
		if(class_exists($class, NO))
			return YES;

		// Determine class filename
		$class = preg_replace("/\_Core$/i", "", $class);
		$filename = str_replace('_', '/', strtolower($class));
		if($filename{0} == 'c' || $filename{0} == 'm') {
			$filename = str_replace(array('controller/', 'model/'), array('controllers/', 'models/'), $filename);
		}
		$prefix = reset(explode('_', $class));
		
		if($prefix == 'Eight' && $class != $prefix) {
			$filename = "core/" . str_replace("eight/", "", $filename);
		} elseif(($prefix != 'Controller' && $prefix != 'Model') || $class == $prefix) {
			if (ord($class{0}) > 96) {
				$filename = "helpers/" . $filename;
			} else {
				$filename = "libraries/" . $filename;
			}
		}

		if(!($path = Eight::find_file('classes', $filename, NO)))
			return NO;

		// Load class
		require $path;

		if(class_exists($class.'_Core', NO)) {
			if($path = Eight::find_file('classes', $filename, NO, 'ext'.EXT)) {
				// Load class extension
				require $path;
			} else {
				// Class extension to be evaluated
				$extension = 'class '.$class.' extends '.$class.'_Core { }';

				// Start class analysis
				$core = new ReflectionClass($class.'_Core');

				if($core->isAbstract()) {
					// Make the extension abstract
					$extension = 'abstract '.$extension;
				}

				// Transparent class extensions are handled using eval. This is
				// a disgusting hack, but it gets the job done.
				eval($extension);
			}
		}

		return YES;
	}

	/**
	 * Find a resource file in a given directory. Files will be located according
	 * to the order of the include paths. Configuration and i18n files will be
	 * returned in reverse order.
	 *
	 * @throws  Eight_Exception  if file is required and not found
	 * @param   string   directory to search in
	 * @param   string   filename to look for(including extension only if 4th parameter is YES)
	 * @param   boolean  file required
	 * @param   string   file extension
	 * @return  array    if the type is config, i18n or l10n
	 * @return  string   if the file is found
	 * @return  NO    if the file is not found
	 */
	public static function find_file($directory, $filename, $required = NO, $ext = NO) {
		// NOTE: This test MUST be not be a strict comparison (===), or empty
		// extensions will be allowed!
		if($ext == '') {
			// Use the default extension
			$ext = EXT;
		} else {
			// Add a period before the extension
			$ext = '.'.$ext;
		}
		
		// Search path
		$search = $directory.'/'.$filename.$ext;

		if(isset(self::$internal_cache['find_file_paths'][$search]))
			return self::$internal_cache['find_file_paths'][$search];

		// Load include paths
		$paths = self::$include_paths;

		// Nothing found, yet
		$found = nil;

		if($directory === 'config' OR $directory === 'i18n') {
			// Search in reverse, for merging
			$paths = array_reverse($paths);

			foreach($paths as $path) {
				if(is_file($path.$search)) {
					// A matching file has been found
					$found[] = $path.$search;
				}
			}
		} else {
			foreach($paths as $path) {
				if(is_file($path.$search)) {
					// A matching file has been found
					$found = $path.$search;

					// Stop searching
					break;
				}
			}
		}

		if($found === nil) {
			if($required === YES) {
				// Directory i18n key
				$directory = 'core.'.inflector::singular($directory);

				// If the file is required, throw an exception
				throw new Eight_Exception('core.resource_not_found', self::lang($directory), $filename);
			} else {
				// Nothing was found, return NO
				$found = NO;
			}
		}

		if(!isset(self::$write_cache['find_file_paths'])) {
			// Write cache at shutdown
			self::$write_cache['find_file_paths'] = YES;
		}

		return self::$internal_cache['find_file_paths'][$search] = $found;
	}

	/**
	 * Lists all files in a resource path.
	 *
	 * @param   string   directory to search
	 * @param   boolean  list all files to the maximum depth?
	 * @return  array    resolved filename paths
	 */
	public static function list_files($directory, $recursive = NO) {
		$files = array();
		$paths = array_reverse(Eight::include_paths());

		foreach($paths as $path) {
			if(is_dir($path.$directory)) {
				$dir = new DirectoryIterator($path.$directory);

				foreach($dir as $file) {
					$filename = $file->getFilename();

					if($filename[0] === '.')
						continue;

					if($file->isDir()) {
						if($recursive === YES) {
							// Recursively add files
							$files = array_merge($files, self::list_files($directory.'/'.$filename, YES));
						}
					} else {
						// Add the file to the files
						$files[$directory.'/'.$filename] = $file->getRealPath();
					}
				}
			}
		}

		return $files;
	}

	/**
	 * Lists all files and directories in a resource path.
	 *
	 * @param   string   directory to search
	 * @param   boolean  list all files to the maximum depth?
	 * @param   string   full path to search (used for recursion, *never* set this manually)
	 * @param   array    filenames to exclude
	 * @return  array    filenames and directories
	 */
	public static function new_list_files($directory = nil, $recursive = NO, $exclude = nil) {
		$files = array();
		$paths = array_reverse(Eight::include_paths());

		foreach($paths as $path) {
			if(is_dir($path.$directory)) {
				$dir = new DirectoryIterator($path.$directory);

				foreach($dir as $file) {
					$filename = $file->getFilename();

					if($filename[0] === '.' OR ($exclude == YES and in_array($filename, $exclude)))
						continue;

					if($recursive == YES and $file->isDir()) {
						// Recursively add files
						$files[$filename] = self::new_list_files($directory.'/'.$filename, YES);
					} else {
						// Add the file to the files
						$files[] = $filename;
					}
				}
			}
		}

		return $files;
	}

	/**
	 * Fetch an i18n language item.
	 *
	 * @param   string  language key to fetch
	 * @param   array   additional information to insert into the line
	 * @return  string  i18n language string, or the requested key if the i18n item is not found
	 */
	public static function lang($key, $args = array()) {
		// Extract the main group from the key
		$group = explode('.', $key, 2);
		$group = $group[0];

		// Get locale name
		$locale = Eight::config('locale.language.0');

		if(!isset(self::$internal_cache['language'][$locale][$group])) {
			// Messages for this group
			$messages = array();

			if($files = self::find_file('i18n', $locale.'/'.$group)) {
				foreach($files as $file) {
					include $file;

					// Merge in configuration
					if(!empty($lang) and is_array($lang)) {
						foreach($lang as $k => $v) {
							$messages[$k] = $v;
						}
					}
				}
			}

			if(!isset(self::$write_cache['language'])) {
				// Write language cache
				self::$write_cache['language'] = YES;
			}

			self::$internal_cache['language'][$locale][$group] = $messages;
		}

		// Get the line from cache
		$line = self::key_string(self::$internal_cache['language'][$locale], $key);

		if($line === nil) {
			Eight::log('error', 'Missing i18n entry '.$key.' for language '.$locale);

			// Return the key string as fallback
			return $key;
		}

		if(is_string($line) and func_num_args() > 1) {
			$args = array_slice(func_get_args(), 1);

			// Add the arguments into the line
			$line = vsprintf($line, is_array($args[0]) ? $args[0] : $args);
		}

		return $line;
	}

	/**
	 * Returns the value of a key, defined by a 'dot-noted' string, from an array.
	 *
	 * @param   array   array to search
	 * @param   string  dot-noted string: foo.bar.baz
	 * @return  string  if the key is found
	 * @return  NULL    if the key is not found
	 */
	public static function key_string($array, $keys) {
		if(empty($array)) return NULL;

		// Prepare for loop
		$keys = explode('.', $keys);

		while(!empty($keys)) {
			// Get the next key
			$key = array_shift($keys);

			if(isset($array[$key])) {
				if(is_array($array[$key]) and!empty($keys)) {
					// Dig down to prepare the next loop
					$array = $array[$key];
				} else {
					// Requested key was found
					return $array[$key];
				}
			} else {
				// Requested key is not set
				break;
			}
		}

		return NULL;
	}

	/**
	 * Sets values in an array by using a 'dot-noted' string.
	 *
	 * @param   array   array to set keys in (reference)
	 * @param   string  dot-noted string: foo.bar.baz
	 * @return  mixed   fill value for the key
	 * @return  void
	 */
	public static function key_string_set( & $array, $keys, $fill = nil) {
		if(is_object($array) and ($array instanceof ArrayObject)) {
			// Copy the array
			$array_copy = $array->getArrayCopy();

			// Is an object
			$array_object = YES;
		} else {
			if(!is_array($array)) {
				// Must always be an array
				$array = (array) $array;
			}

			// Copy is a reference to the array
			$array_copy =& $array;
		}

		if(empty($keys))
			return $array;

		// Create keys
		$keys = explode('.', $keys);

		// Create reference to the array
		$row =& $array_copy;

		for($i = 0, $end = count($keys) - 1; $i <= $end; $i++) {
			// Get the current key
			$key = $keys[$i];

			if(!isset($row[$key])) {
				if(isset($keys[$i + 1])) {
					// Make the value an array
					$row[$key] = array();
				} else {
					// Add the fill key
					$row[$key] = $fill;
				}
			} elseif(isset($keys[$i + 1])) {
				// Make the value an array
				$row[$key] = (array) $row[$key];
			}

			// Go down a level, creating a new row reference
			$row =& $row[$key];
		}

		if(isset($array_object)) {
			// Swap the array back in
			$array->exchangeArray($array_copy);
		}
	}

	/**
	 * Retrieves current user agent information:
	 * keys:  browser, version, platform, mobile, robot, referrer, languages, charsets
	 * tests: is_browser, is_mobile, is_robot, accept_lang, accept_charset
	 *
	 * @param   string   key or test name
	 * @param   string   used with "accept" tests: user_agent(accept_lang, en)
	 * @return  array    languages and charsets
	 * @return  string   all other keys
	 * @return  boolean  all tests
	 */
	public static function user_agent($key = 'agent', $compare = nil) {
		static $info;

		// Return the raw string
		if($key === 'agent')
			return Eight::$user_agent;

		if($info === nil) {
			// Parse the user agent and extract basic information
			$agents = Eight::config('user_agents');

			foreach($agents as $type => $data) {
				foreach($data as $agent => $name) {
					if(stripos(Eight::$user_agent, $agent) !== NO) {
						if($type === 'browser' and preg_match('|'.preg_quote($agent).'[^0-9.]*+([0-9.][0-9.a-z]*)|i', Eight::$user_agent, $match)) {
							// Set the browser version
							$info['version'] = $match[1];
						}

						// Set the agent name
						$info[$type] = $name;
						break;
					}
				}
			}
		}

		if(empty($info[$key])) {
			switch ($key) {
				case 'is_robot':
				case 'is_browser':
				case 'is_mobile':
					// A boolean result
					$return =!empty($info[substr($key, 3)]);
				break;
				case 'languages':
					$return = array();
					if(!empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
						if(preg_match_all('/[-a-z]{2,}/', strtolower(trim($_SERVER['HTTP_ACCEPT_LANGUAGE'])), $matches)) {
							// Found a result
							$return = $matches[0];
						}
					}
				break;
				case 'charsets':
					$return = array();
					if(!empty($_SERVER['HTTP_ACCEPT_CHARSET'])) {
						if(preg_match_all('/[-a-z0-9]{2,}/', strtolower(trim($_SERVER['HTTP_ACCEPT_CHARSET'])), $matches)) {
							// Found a result
							$return = $matches[0];
						}
					}
				break;
				case 'referrer':
					if(!empty($_SERVER['HTTP_REFERER'])) {
						// Found a result
						$return = trim($_SERVER['HTTP_REFERER']);
					}
				break;
			}

			// Cache the return value
			isset($return) and $info[$key] = $return;
		}

		if(!empty($compare)) {
			// The comparison must always be lowercase
			$compare = strtolower($compare);

			switch ($key) {
				case 'accept_lang':
					// Check if the lange is accepted
					return in_array($compare, Eight::user_agent('languages'));
				break;
				case 'accept_charset':
					// Check if the charset is accepted
					return in_array($compare, Eight::user_agent('charsets'));
				break;
				default:
					// Invalid comparison
					return NO;
				break;
			}
		}

		// Return the key, if set
		return isset($info[$key]) ? $info[$key] : nil;
	}

	/**
	 * Quick debugging of any variable. Any number of parameters can be set.
	 *
	 * @return  string
	 */
	public static function debug() {
		if(func_num_args() === 0)
			return;

		// Get params
		$params = func_get_args();
		$output = array();

		foreach($params as $var) {
			$output[] = '<pre>('.gettype($var).') '.html::specialchars(print_r($var, YES)).'</pre>';
		}

		return implode("\n", $output);
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
	 * Similar to print_r or var_dump, generates a string representation of
	 * any variable.
	 *
	 * @param   mixed   variable to dump
	 * @return  string
	 */
	public static function debug_var($var, $recursion = NO) {
		static $objects;

		if ($recursion === NO) {
			$objects = array();
		}

		switch (gettype($var)) {
			case 'object':
				// Unique hash of the object
				$hash = spl_object_hash($var);

				$object = new ReflectionObject($var);
				$more = NO;
				$out = 'object '.$object->getName().' { ';

				if ($recursion === YES AND in_array($hash, $objects)) {
					$out .= '*RECURSION*';
				} else {
					// Add the hash to the objects, to detect later recursion
					$objects[] = $hash;

					foreach ($object->getProperties() as $property) {
						$out .= ($more === YES ? ', ' : '').$property->getName().' => ';
						if ($property->isPublic()) {
							$out .= self::debug_var($property->getValue($var), YES);
						} elseif ($property->isPrivate()) {
							$out .= '*PRIVATE*';
						} else {
							$out .= '*PROTECTED*';
						}
						$more = YES;
					}
				}
				return $out.' }';
			case 'array':
				$more = NO;
				$out = 'array (';
				foreach ((array) $var as $key => $val) {
					if (!is_int($key)) {
						$key = self::debug_var($key, YES).' => ';
					} else {
						$key = '';
					}
					$out .= ($more ? ', ' : '').$key.self::debug_var($val, YES);
					$more = YES;
				}
				return $out.')';
			case 'string':
				return "'$var'";
			case 'float':
				return number_format($var, 6).'&hellip;';
			case 'boolean':
				return $var === YES ? 'YES' : 'NO';
			default:
				return (string) $var;
		}
	}

	/**
	 * Displays nice backtrace information.
	 * @see http://php.net/debug_backtrace
	 *
	 * @param   array   backtrace generated by an exception or debug_backtrace
	 * @return  string
	 */
	public static function backtrace($trace) {
		if ( ! is_array($trace))
			return;

		// Final output
		$output = array();

		foreach ($trace as $entry) {
			$temp = '<li>';

			if (isset($entry['file'])) {
				$temp .= self::lang('core.error_file_line', preg_replace('!^'.preg_quote(DOCROOT).'!', '', $entry['file']), $entry['line']);
			}

			$temp .= '<pre>';

			if (isset($entry['class'])) {
				// Add class and call type
				$temp .= $entry['class'].$entry['type'];
			}

			// Add function
			$temp .= $entry['function'].'( ';

			// Add function args
			if (isset($entry['args']) AND is_array($entry['args'])) {
				// Separator starts as nothing
				$sep = '';

				while ($arg = array_shift($entry['args'])) {
					if (is_string($arg) AND is_file($arg)) {
						// Remove docroot from filename
						$arg = preg_replace('!^'.preg_quote(DOCROOT).'!', '', $arg);
					}

					$temp .= $sep.html::specialchars(print_r($arg, YES));

					// Change separator to a comma
					$sep = ', ';
				}
			}

			$temp .= ' )</pre></li>';

			$output[] = $temp;
		}
		
		// Prevent MySQL passwords from showing up
		$output = preg_replace("#mysql_(.*?)connect\((.*?)\)#ism", 'mysql_$1connect(REMOVED_FOR_SECURITY)' , implode("\n", $output));
		
		return '<ul class="backtrace">'.$output.'</ul>';
	}
		/**
	 * Saves the internal caches: configuration, include paths, etc.
	 *
	 * @return  boolean
	 */
	public static function internal_cache_save() {
		if(!is_array(self::$write_cache))
			return NO;

		// Get internal cache names
		$caches = array_keys(self::$write_cache);

		// Nothing written
		$written = NO;

		foreach($caches as $cache) {
			if(isset(self::$internal_cache[$cache])) {
				// Write the cache file
				self::cache_save($cache, self::$internal_cache[$cache], self::$configuration['core']['internal_cache']);

				// A cache has been written
				$written = YES;
			}
		}

		return $written;
	}

} // End Eight

/**
 * Loads the configured driver and validates it.
 *
 * @param   string  Text to output
 * @param   array   Key/Value pairs of arguments to replace in the string
 * @return  string  Translated text
 */
function __($string, $args = NULL) {
	$localized_string = Eight::lang($string, $args);
	
	if(is_array($localized_string)) {
		$localized_string = $string;
	}
	
	foreach(arr::c($args) as $k => $v) {
		$localized_string = str_replace($k, $v, $localized_string);
	}
	
	return $localized_string;
}


<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Eight PHP Error Exceptions
 *
 * @package		System
 * @subpackage	Exceptions
 * @author		EightPHP Development Team
 * @copyright	(c) 2009-2010 EightPHP
 * @license		http://license.eightphp.com
 */

class Eight_Exception_PHP_Core extends Eight_Exception {

	public static $enabled = FALSE;

	/**
	 * Enable Eight PHP error handling.
	 *
	 * @return  void
	 */
	public static function enable() {
		if (!Eight_Exception_PHP::$enabled) {
			// Handle runtime errors
			set_error_handler(array('Eight_Exception_PHP', 'error_handler'));

			// Enable the Kohana shutdown handler, which catches E_FATAL errors.
			register_shutdown_function(array('Eight_Exception_PHP', 'shutdown_handler'));
			
			Eight_Exception_PHP::$enabled = TRUE;
		}
	}

	/**
	 * Disable Eight PHP error handling.
	 *
	 * @return  void
	 */
	public static function disable() {
		if (Eight_Exception_PHP::$enabled) {
			restore_error_handler();

			Event::clear('system.shutdown', array('Eight_Exception_PHP', 'shutdown_handler'));

			Eight_Exception_PHP::$enabled = FALSE;
		}
	}

	/**
	 * Create a new PHP error exception.
	 *
	 * @return  void
	 */
	public function __construct($code, $error, $file, $line, $context = NULL) {
		parent::__construct($error);

		// Set the error code, file, line, and context manually
		$this->code = $code;
		$this->file = $file;
		$this->line = $line;
	}

	/**
	 * PHP error handler.
	 *
	 * @throws  Eight_Exception_PHP
	 * @return  void
	 */
	public static function error_handler($code, $error, $file, $line, $context = NULL) {
		// Respect error_reporting settings
		if (error_reporting() & $code) {
			// Throw an exception
			throw new Eight_Exception_PHP($code, $error, $file, $line, $context);
		}
		
		return TRUE; // Avoid PHP Handler
	}

	/**
	 * Catches errors that are not caught by the error handler, such as E_PARSE.
	 *
	 * @uses    Eight_Exception::handle()
	 * @return  void
	 */
	public static function shutdown_handler() {
		if(!Eight::$instance) {
			// Do not execute when not active
			return;
		}

		if(Eight::$errors AND $error = error_get_last() AND in_array($error['type'], Eight::$shutdown_errors)) {
			// Clean the output buffer
			ob_get_level() && ob_clean();

			// Fake an exception for nice debugging
			Eight_Exception_PHP::handle(new Eight_Exception_PHP(0, $error['message'], $error['file'], $error['line']));

			// Shutdown now to avoid a "death loop"
			exit(1);
		}
	}

} // End Eight PHP Exception
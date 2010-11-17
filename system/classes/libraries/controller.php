<?php
/**
 * Eight Controller class. The controller class must be extended to work
 * properly, so this class is defined as abstract.
 *
 * @package		System
 * @subpackage	Libraries
 * @author		EightPHP Development Team
 * @copyright	(c) 2009-2010 EightPHP
 * @license		http://license.eightphp.com
 */
abstract class Controller_Core {

	// Allow all controllers to run in production by default
	const ALLOW_PRODUCTION = YES;

	/**
	 * Loads URI, and Input into this controller.
	 *
	 * @return  void
	 */
	public function __construct() {
		if(Eight::$instance == nil) {
			// Set the instance to the first controller loaded
			Eight::$instance = $this;
		}

		// Input should always be available
		$this->input = Input::instance();
	}

	/**
	 * Handles methods that do not exist.
	 *
	 * @param   string  method name
	 * @param   array   arguments
	 * @return  void
	 */
	public function __call($method, $args) {
		// Default to showing a 404 page
		Event::run('system.404');
	}

	/**
	 * Includes a View within the controller scope.
	 *
	 * @param   string  view filename
	 * @param   array   array of view variables
	 * @return  string
	 */
	public function _eight_load_view($eight_view_filename, array $eight_view_data) {
		// Prevent any variable collisions
		if(count($conflicts = array_intersect_key($eight_view_data, View::$_global_data)) > 0) {
			throw new Eight_Exception('View Variable Collision: The variable(s), '.implode(',', array_keys($conflicts)).' are already in-use.');
		}
		
		// Import the view variables to local namespace
		extract($eight_view_data, EXTR_SKIP);

		if (View::$_global_data) {
			// Import the global view variables to local namespace and maintain references
			extract(View::$_global_data, EXTR_REFS);
		}

		// Capture the view output
		ob_start();

		try {
			// Load the view within the current scope
			include $eight_view_filename;
		} catch (Exception $e) {
			// Delete the output buffer
			ob_end_clean();

			// Re-throw the exception
			throw $e;
		}

		// Get the captured output and close the buffer
		return ob_get_clean();
	}

} // End Controller Class
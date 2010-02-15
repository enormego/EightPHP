<?php
/**
 * Eight Controller class. The controller class must be extended to work
 * properly, so this class is defined as abstract.
 *
 * @version		$Id: controller.php 244 2010-02-11 17:14:39Z shaun $
 *
 * @package		System
 * @subpackage	Libraries
 * @author		enormego
 * @copyright	(c) 2009-2010 enormego
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
	public function _eight_load_view($eight_view_filename, $eight_input_data) {
		if($eight_view_filename == '')
			return;

		// Buffering on
		ob_start();

		// Import the view variables to local namespace
		extract($eight_input_data, EXTR_SKIP);

		// Views are straight HTML pages with embedded PHP, so importing them
		// this way insures that $this can be accessed as if the user was in
		// the controller, which gives the easiest access to libraries in views
		include $eight_view_filename;

		// Fetch the output and close the buffer
		return ob_get_clean();
	}

} // End Controller Class
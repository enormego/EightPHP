<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Creates a "Page Not Found" exception.
 *
 * @package		System
 * @subpackage	Exceptions
 * @author		EightPHP Development Team
 * @copyright	(c) 2009-2010 EightPHP
 * @license		http://license.eightphp.com
 */

class Eight_Exception_404_Core extends Eight_Exception {

	protected $code = E_PAGE_NOT_FOUND;

	/**
	 * Set internal properties.
	 *
	 * @param  string  URI of page
	 * @param  string  custom error template
	 */
	public function __construct($page = NULL) {
		if ($page === NULL) {
			// Use the complete URI
			$page = Router::$complete_uri;
		}
		
		parent::__construct(strtr('The page you requested, %page%, could not be found.', array('%page%' => $page)));
	}

	/**
	 * Throws a new 404 exception.
	 *
	 * @throws  Eight_Exception_404
	 * @return  void
	 */
	public static function trigger($page = NULL) {
		// Silence 404 errors (as matched within the ignore array) and die quietly
		if(in_array(Router::$complete_uri, arr::c(Eight::config('core.ignore_page_not_found')))) Eight::shutdown(); exit;
		
		throw new Eight_Exception_404($page);
	}

	/**
	 * Sends 404 headers, to emulate server behavior.
	 *
	 * @return void
	 */
	public function sendHeaders() {
		// Send the 404 header
		header('HTTP/1.1 404 File Not Found');
	}

} // End Eight 404 Exception
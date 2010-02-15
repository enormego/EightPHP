<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Creates a custom exception message.
 *
 * @package		System
 * @subpackage	Exceptions
 * @author		EightPHP Development Team
 * @copyright	(c) 2009-2010 EightPHP
 * @license		http://license.eightphp.com
 */

class Eight_Exception_User_Core extends Eight_Exception {

	/**
	 * Set exception title and message.
	 *
	 * @param   string  exception title string
	 * @param   string  exception message string
	 * @param   string  custom error template
	 */
	public function __construct($title, $message, array $variables = NULL) {
		parent::__construct($message, $variables);

		// Code is the error title
		$this->code = $title;
	}

} // End Eight User Exception

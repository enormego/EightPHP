<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Creates a custom exception message.
 *
 * @version		$Id: Eight_Exception_User.php 4679 2009-11-10 01:45:52Z isaiah $
 *
 * @package		System
 * @subpackage	Exceptions
 * @author		enormego
 * @copyright	(c) 2009-2010 enormego
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

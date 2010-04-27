<?php 
/**
 * @package		System
 * @subpackage	Libraries.Database
 * @author		EightPHP Development Team
 * @copyright	(c) 2009-2010 EightPHP
 * @license		http://license.eightphp.com
 */

class Database_Exception_Core extends Eight_Exception {

	protected $code = E_DATABASE_ERROR;

	public function __construct($message, $variables = NULL, $code = 0) {
		parent::__construct($message, $variables, $code);
	}

} // End Database Exception

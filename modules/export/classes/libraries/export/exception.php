<?php 
/**
 * @package		Modules
 * @subpackage	Export
 * @author		EightPHP Development Team
 * @copyright	(c) 2010 EightPHP
 * @license		http://license.eightphp.com
 */

class Export_Exception_Core extends Eight_Exception {

	protected $code = E_DATABASE_ERROR;

	public function __construct($message, $variables = NULL, $code = 0) {
		parent::__construct(Eight::lang($message, $variables), NULL, $code);
	}

} // End Export Exception

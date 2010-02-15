<?php
/**
 * Model base class.
 *
 * @package		System
 * @subpackage	Libraries
 * @author		EightPHP Development Team
 * @copyright	(c) 2009-2010 EightPHP
 * @license		http://license.eightphp.com
 */
abstract class Model_Core {

	protected $db;
	public $core; // Eight object
	public $obj; // Eight object
	
	/**
	 * Loads the database instance, if the database is not already loaded.
	 *
	 * @return  void
	 */
	public function __construct() {
		// Eight global instance
		$this->core = Eight::instance();
		$this->obj = Eight::instance();
		$this->db = Eight::instance()->db;
	}

} // End Model
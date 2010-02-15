<?php
/**
 * Model base class.
 *
 * @version		$Id: model.php 244 2010-02-11 17:14:39Z shaun $
 *
 * @package		System
 * @subpackage	Libraries
 * @author		enormego
 * @copyright	(c) 2009-2010 enormego
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
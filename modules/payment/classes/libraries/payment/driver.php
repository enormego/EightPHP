<?php
/**
 * Payment driver interface
 *
 * @package		Modules
 * @subpackage	Payments
 * @author		EightPHP Development Team
 * @copyright	(c) 2009-2010 EightPHP
 * @license		http://license.eightphp.com
 */
interface Payment_Driver {

	/**
	 * Sets driver fields and marks required fields as TRUE.
	 *
	 * @param  array  array of key => value pairs to set
	 */
	public function set_fields($fields);

	/**
	 * Runs the transaction.
	 *
	 * @return  boolean
	 */
	public function process();
	
	/**
	 * Creates a recurring profile
	 *
	 * @return  boolean
	 */
	public function create_recurring_profile();
	
} // End Payment Driver Interface
<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Payment driver interface
 *
 * $Id: Payment.php 3355 2008-08-22 19:56:31Z Shadowhand $
 *
 * @package    Payment
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
interface Payment_Driver {

	/**
	 * Sets driver fields and marks reqired fields as TRUE.
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

} // End Payment Driver Interface
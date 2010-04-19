<?php
/**
 *  Export API driver interface
 *
 * @package		Modules
 * @subpackage	Export
 * @author		EightPHP Development Team
 * @copyright	(c) 2010 EightPHP
 * @license		http://license.eightphp.com
 */
abstract class Export_Driver {

	protected $rows = array();
	protected $columns;
	
	/**
	 * Sets the columns, optional
	 */
	public function set_columns($columns) {
		$this->columns = $columns;
	}

	/**
	 * File extension for this export data type
	 *
	 * @return string
	 */
	abstract public function ext();

	/**
	 * MIME Content Type for this export data type
	 *
	 * @return string
	 */
	abstract public function content_type();

	/**
	 * Return a compiled string for this export data type
	 *
	 * @return string
	 */
	abstract public function to_string();

	/**
	 * Sends a compiled file to the browser for this export data type
	 */
	abstract public function to_browser();

	/**
	 * Add a row to the current data set
	 *
	 * @param  array $row
	 */
	public function add_row($row) {
		$this->rows[] = $row;
	}

} // End Export Driver Interface


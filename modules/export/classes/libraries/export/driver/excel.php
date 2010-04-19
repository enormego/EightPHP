<?php
/**
 *  Export Driver Excel
 *
 * @package		Modules
 * @subpackage	Export
 * @author		EightPHP Development Team
 * @copyright	(c) 2010 EightPHP
 * @license		http://license.eightphp.com
 */

class Export_Driver_Excel_Core extends Export_Driver {
	private $data = "";
	
	/**
	 * File extension for this export data type
	 *
	 * @return string
	 */
	public function ext() {
		return "xls";
	}

	/**
	 * MIME Content Type for this export data type
	 *
	 * @return string
	 */
	public function content_type() {
		return "application/vnd.ms-excel";
	}

	/**
	 * Return a compiled string for this export data type
	 *
	 * @return string
	 */
	public function to_string() {
		$writer = new Export_Driver_Excel_Writer;

		$rows = array($this->columns) + $this->rows;
		foreach($rows as $row) {
			$writer->add_row($row);
		}
		
		return $writer->to_string();
	}

	/**
	 * Sends a compiled file to the browser for this export data type
	 */
	public function to_browser() {
		echo $this->to_string();
	}

}

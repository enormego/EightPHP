<?php
/**
 *  Export Driver XML
 *
 * @package		Modules
 * @subpackage	Export
 * @author		EightPHP Development Team
 * @copyright	(c) 2010 EightPHP
 * @license		http://license.eightphp.com
 */

class Export_Driver_XML_Core extends Export_Driver {
	private $container = "entries";
	
	public function __construct($config=array()) {
		if(!str::e($config['container'])) {
			$this->container = $config['container'];
		}
	}

	/**
	 * File extension for this export data type
	 *
	 * @return string
	 */
	public function ext() {
		return "csv";
	}

	/**
	 * MIME Content Type for this export data type
	 *
	 * @return string
	 */
	public function content_type() {
		return "text/xml";
	}

	/**
	 * Return a compiled string for this export data type
	 *
	 * @return string
	 */
	public function to_string() {
		return xml::ordered2xml($this->rows, $this->container);
	}
	
	/**
	 * Add a row to the current data set
	 *
	 * @param  array $row
	 */
	public function add_row($row) {
		$keyed_row = array();
		foreach($row as $k => $v) {
			$keyed_row[inflector::camelize($this->columns[$k])] = $v;
		}
		
		$this->rows[] = $keyed_row;
	}

	/**
	 * Sends a compiled file to the browser for this export data type
	 */
	public function to_browser() {
		echo $this->to_string();
	}

	public function __destruct() {

	}
}

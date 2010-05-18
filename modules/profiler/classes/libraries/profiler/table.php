<?php
/**
 * Provides a table layout for sections in the Profiler library.
 * @internal
 *
 * @package		Modules
 * @subpackage	Profiler
 * @author		EightPHP Development Team
 * @copyright	(c) 2009-2010 EightPHP
 * @license		http://license.eightphp.com
 */
class Profiler_Table_Core {

	protected $columns = array();
	protected $rows = array();

	/**
	 * Get styles for table.
	 *
	 * @return  string
	 */
	public function styles() {
		static $styles_output;

		if(!$styles_output) {
			$styles_output = YES;
			return file_get_contents(Eight::find_file('views', 'profiler/table', NO, 'css'));
		}

		return '';
	}

	/**
	 * Add column to table.
	 *
	 * @param  string  CSS class
	 * @param  string  CSS style
	 */
	public function add_column($class = '', $style = '') {
		$this->columns[] = array('class' => $class, 'style' => $style);
	}

	/**
	 * Add row to table.
	 *
	 * @param  array   data to go in table cells
	 * @param  string  CSS class
	 * @param  string  CSS style
	 */
	public function add_row($data, $class = '', $style = '') {
		$this->rows[] = array('data' => $data, 'class' => $class, 'style' => $style);
	}
	
	/*
	 * Returns the name of the table
	 */
	public function name() {
		return $this->rows[0]['data'][0];
	}
	
	/*
	 * Returns the id of the table
	 */
	public function table_id() {
		return 'profiler_table_'.md5($this->name());
	}

	/**
	 * Render table.
	 *
	 * @return  string
	 */
	public function render($hidden=YES) {
		$data['rows'] = $this->rows;
		$data['columns'] = $this->columns;
		$data['hidden'] = $hidden;
		$data['id'] = $this->table_id();
		return View::factory('profiler/table', $data)->render();
	}
}
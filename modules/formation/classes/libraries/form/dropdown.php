<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Formation dropdown input library.
 *
 * @version		$Id: dropdown.php 244 2010-02-11 17:14:39Z shaun $
 *
 * @package		Modules
 * @subpackage	Formation
 * @author		enormego
 * @copyright	(c) 2009-2010 enormego
 * @license		http://license.eightphp.com
 */
class Form_Dropdown_Core extends Form_Input {

	protected $data = array(
		'name'  => '',
		'class' => 'dropdown',
	);

	protected $protect = array('type');

	public function __get($key) {
		if ($key == 'value') {
			return $this->selected;
		}

		return parent::__get($key);
	}

	public function html_element() {
		// Import base data
		$base_data = $this->data;
		
		unset($base_data['label']);

		if(isset($base_data['multiple']) && !!$base_data['multiple']) {
			$base_data['name'] = str_replace('[]', '', $base_data['name']).'[]';
		}
		
		// Get the options and default selection
		$options = arr::remove('options', $base_data);
		$selected = arr::remove('selected', $base_data);

		return form::dropdown($base_data, $options, $selected);
	}

	protected function load_value() {
		if (is_bool($this->valid))
			return;

		$this->data['selected'] = $this->input_value($this->name);
	}

} // End Form Dropdown
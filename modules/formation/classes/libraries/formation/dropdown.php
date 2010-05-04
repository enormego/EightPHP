<?php
/**
 * Formation dropdown input library.
 *
 * @package		Modules
 * @subpackage	Formation
 * @author		EightPHP Development Team
 * @copyright	(c) 2009-2010 EightPHP
 * @license		http://license.eightphp.com
 */
class Formation_Dropdown_Core extends Formation_Input {

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

} // End Formation Dropdown
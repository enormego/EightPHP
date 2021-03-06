<?php
/**
 * Formation textarea input library.
 *
 * @package		Modules
 * @subpackage	Formation
 * @author		EightPHP Development Team
 * @copyright	(c) 2009-2010 EightPHP
 * @license		http://license.eightphp.com
 */
class Formation_Textarea_Core extends Formation_Input {

	protected $data = array(
		'class' => 'textarea',
		'value' => '',
	);

	protected $protect = array('type');

	protected function html_element() {
		$data = $this->data;

		unset($data['label']);

		return form::textarea($data);
	}

} // End Formation Textarea
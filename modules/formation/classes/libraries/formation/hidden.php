<?php
/**
 * Formation hidden input library.
 *
 * @package		Modules
 * @subpackage	Formation
 * @author		EightPHP Development Team
 * @copyright	(c) 2009-2010 EightPHP
 * @license		http://license.eightphp.com
 */
class Formation_Hidden_Core extends Formation_Input {

	protected $data = array(
		'name'  => '',
		'value' => '',
	);

	public function render() {
		return form::hidden($this->data);
	}

} // End Formation Hidden
<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Formation hidden input library.
 *
 * @package		Modules
 * @subpackage	Formation
 * @author		EightPHP Development Team
 * @copyright	(c) 2009-2010 EightPHP
 * @license		http://license.eightphp.com
 */
class Form_Hidden_Core extends Form_Input {

	protected $data = array(
		'name'  => '',
		'value' => '',
	);

	public function render() {
		return form::hidden($this->data);
	}

} // End Form Hidden
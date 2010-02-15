<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Formation textarea input library.
 *
 * @version		$Id: textarea.php 244 2010-02-11 17:14:39Z shaun $
 *
 * @package		Modules
 * @subpackage	Formation
 * @author		enormego
 * @copyright	(c) 2009-2010 enormego
 * @license		http://license.eightphp.com
 */
class Form_Textarea_Core extends Form_Input {

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

} // End Form Textarea
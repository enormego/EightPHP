<?php
/**
 * Formation radio input library.
 *
 * @package		Modules
 * @subpackage	Formation
 * @author		EightPHP Development Team
 * @copyright	(c) 2009-2010 EightPHP
 * @license		http://license.eightphp.com
 */
class Form_Radio_Core extends Form_Checkbox {

	protected $data = array(
		'type' => 'radio',
		'class' => 'radio',
		'value' => '1',
		'checked' => NO,
	);

} // End Form_Radio
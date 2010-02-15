<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Formation password input library.
 *
 * @package		Modules
 * @subpackage	Formation
 * @author		EightPHP Development Team
 * @copyright	(c) 2009-2010 EightPHP
 * @license		http://license.eightphp.com
 */
class Form_Password_Core extends Form_Input {

	protected $data = array(
		'type'  => 'password',
		'class' => 'password',
		'value' => '',
	);

	protected $protect = array('type');

} // End Form Password
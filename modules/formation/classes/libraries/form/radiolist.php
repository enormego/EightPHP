<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Formation Radiolist input library.
 *
 * @package		Modules
 * @subpackage	Formation
 * @author		EightPHP Development Team
 * @copyright	(c) 2009-2010 EightPHP
 * @license		http://license.eightphp.com
 */
class Form_Radiolist_Core extends Form_Checklist {

	protected $data = array(
		'name'    => '',
		'type'    => 'radio',
		'class'   => 'radiolist',
		'options' => array(),
	);
		
} // End Form_Radiolist_Core
<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Formation Radiolist input library.
 *
 * @version		$Id: radiolist.php 244 2010-02-11 17:14:39Z shaun $
 *
 * @package		Modules
 * @subpackage	Formation
 * @author		enormego
 * @copyright	(c) 2009-2010 enormego
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
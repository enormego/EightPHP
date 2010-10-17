<?php

/**
 * @package		Modules
 * @subpackage	Authentication
 * @author		EightPHP Development Team
 * @copyright	(c) 2009-2010 EightPHP
 * @license		http://license.eightphp.com
 */

class Model_AuthRole extends Modeler {

	// Database table name
	protected $table_name = 'roles';

	// Table primary key
	protected $primary_key = 'role_id';

	// Column prefix
	protected $column_prefix = 'role_';
 
	// Database fields and default values
	public $data = array(
								'role_id'			=>	'',
								'role_name'			=>	'',
							);

 	// Run all queries on master db
	protected $use_master = YES;
	
} // End Auth Role Model
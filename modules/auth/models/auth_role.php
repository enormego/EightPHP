<?php defined('SYSPATH') or die('No direct script access.');

class Auth_Role_Model extends ORM {

	protected $has_and_belongs_to_many = array('users');

	/**
	 * Allows finding roles by name.
	 */
	public function unique_key($id)
	{
		if ( ! empty($id) AND is_string($id) AND ! ctype_digit($id))
		{
			return 'name';
		}

		return parent::unique_key($id);
	}

} // End Auth Role Model
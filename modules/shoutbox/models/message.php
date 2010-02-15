<?php defined('SYSPATH') or die('No direct script access.');

class Message_Model extends ORM {

	protected $belongs_to = array('user');

	public function save()
	{
		if (empty($this->object->id))
		{
			// Set the posted date to the current time
			$this->posted = time();
		}

		parent::save();
	}

} // End Message Model
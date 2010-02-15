<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Kobot Channel.
 *
 * $Id: Kobot_Channel.php 2067 2008-02-17 00:06:40Z Shadowhand $
 *
 * @package    Kobot
 * @author     Woody Gilk
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Kobot_Channel_Core {

	protected $channel;
	protected $password;
	protected $topic;
	protected $users;

	public function __construct($channel, $password = NULL)
	{
		$this->channel  = $channel;
		$this->password = $password;
	}

	public function __set($key, $value)
	{
		switch ($key)
		{
			case 'topic':
				// Set the topic
				$this->topic = $value;
			break;
			case 'users':
				if ($this->users === NULL)
				{
					// Create the initial user list
					foreach ($value as $key)
					{
						$this->users[$key] = '';
					}
				}
			break;
		}
	}

	public function __get($key)
	{
		if (isset($this->$key))
		{
			return $this->$key;
		}
	}

	public function user_join($username, $mode = '')
	{
		$this->users[$username] = $mode;
	}

	public function user_part($username)
	{
		unset($this->users[$username]);
	}

	public function user_mode($username, $mode)
	{
		isset($this->users[$username]) and $this->users[$username] = $mode;
	}

} // End Kobot Channel
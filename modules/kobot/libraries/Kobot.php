<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Kohana IRC Bot. Yah, we do that too.
 *
 * $Id: Kobot.php 3213 2008-07-27 14:28:37Z Geert $
 *
 * @package    Kobot
 * @author     Woody Gilk
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Kobot_Core {

	// The characters that represent a newline
	public static $newline = "\r\n";

	// Log level: 1 = errors, 2 = debug
	public $log_level = 1;

	// Command responses, timers, and triggers
	protected $responses = array();
	protected $timers = array();
	protected $triggers = array();

	// Responses to drop by default
	protected $dropped = array
	(
		'NOTICE',
		'001',
		'002',
		'003',
		'004',
		'005',
		'250',
		'251',
		'252',
		'254',
		'255',
		'265',
		'266',
		'366',
		'477',
	);

	// IRC socket, username, MOTD, and stats
	protected $server;
	protected $username;
	protected $motd;
	protected $stats = array
	(
		'start'              => 0,
		'last_ping'          => 0,
		'last_sent'          => 0,
		'last_received'      => 0,
	);

	// Connected channels
	protected $channels = array();

	public function __construct($host, $port = NULL, $timeout = NULL)
	{
		if (PHP_SAPI !== 'cli')
			throw new Kohana_Exception('kobot.command_line_only');

		// Close all output buffers
		while (ob_get_level()) ob_end_clean();

		// Keep-alive: TRUE
		set_time_limit(0);

		// Use internal an internal exception handler, to write logs
		set_error_handler(array($this, 'exception_handler'));
		set_exception_handler(array($this, 'exception_handler'));

		// Add the identify event
		Event::add('kobot.motd_read', array($this, 'login_identify'));

		// Set the port
		empty($port) and $port = 6667;

		// Set the timeout
		empty($timeout) and $timeout = 10;

		// Set the start time
		$this->stats['start'] = microtime(TRUE);

		// Load the server object
		$this->server = new Kobot_Server($host, $port, $timeout);

		if ($this->server->connect())
		{
			// Set the default responses
			$this->default_responses();

			// Connection is complete
			$this->log(1, 'Connected to '.$this->server->host.':'.$this->server->port);
		}
		else
		{
			// Nothing left to do if the connection fails
			$this->log(1, 'Could not to connect to '.$this->server->host.':'.$this->server->port.' in less than '.$this->server->timeout.' seconds: '.$this->server->error);
			exit;
		}
	}

	public function __get($key)
	{
		if (isset($this->$key))
		{
			return $this->$key;
		}
	}

	public function default_responses()
	{
		// Read the PING command
		$this->set_response('PING', array($this, 'response_ping'));

		// Read the MOTD command
		$this->set_response('375', array($this, 'response_motd'));
		$this->set_response('372', array($this, 'response_motd'));
		$this->set_response('376', array($this, 'response_motd'));

		// Read the JOINTOPIC command
		$this->set_response('332', array($this, 'response_topic'));

		// Read the USERS command
		$this->set_response('353', array($this, 'response_userlist'));

		// Read the TOPIC command
		$this->set_response('TOPIC', array($this, 'response_topic'));

		// Read the JOIN command
		$this->set_response('JOIN', array($this, 'response_join'));

		// Read the PART command
		$this->set_response('PART', array($this, 'response_part'));

		// Read the PRIVMSG command
		$this->set_response('PRIVMSG', array($this, 'response_privmsg'));

		// Read the "info" trigger
		$this->set_trigger('^info ([^\s]+)$', array($this, 'trigger_info'));

		// Set the "uptime" timer for 60 minutes
		$this->set_timer(60 * 60, array($this, 'timer_uptime'));

		foreach ($this->dropped as $cmd)
		{
			// Drop all requested commands
			$this->set_response($cmd, array($this, 'response_drop'));
		}
	}

	public function log($level, $message)
	{
		if ($this->log_level >= $level)
		{
			// Display the message with a timestamp, flush the output
			echo date('Y-m-d g:i:s').' --- '.$message."\n"; flush();
		}

		return TRUE;
	}

	/**
	 * Handler setters for responses, triggers, and timers.
	 * - A response executes when an IRC command is received.
	 * - A timer executes every N.N seconds.
	 * - A trigger executes when the bot is spoken to.
	 */

	public function set_response($command, $callback)
	{
		if ( ! is_callable($callback))
			throw new Kohana_Exception('kobot.invalid_callback', $command);

		// Set the response callback
		$this->responses[$command] = $callback;

		return $this;
	}

	public function remove_response($command)
	{
		// Remove the response
		unset($this->responses[$command]);

		return $this;
	}

	public function set_timer($interval, $callback)
	{
		if ( ! is_callable($callback))
			throw new Kohana_Exception('kobot.invalid_timer');

		// Add the timer to the timers, forcing the callback to be unique
		$this->timers[$this->callback_hash($callback)] = array
		(
			'callback' => $callback,
			'interval' => $interval,
			'timeout'  => microtime(TRUE) + $interval,
		);

		return $this;
	}

	public function remove_timer($callback)
	{
		// Remove the timer
		unset($this->timers[$this->callback_hash($callback)]);

		return $this;
	}

	public function set_trigger($pattern, $callback)
	{
		// Store the trigger and its callback
		$this->triggers[$pattern] = $callback;

		return $this;
	}

	public function remove_trigger($pattern)
	{
		// Remove the trigger
		unset($this->triggers[$pattern]);

		return $this;
	}

	/**
	 * Server stream reading and writing. This is where the magic happens!
	 */

	public function send($command)
	{
		if (feof($this->server->socket))
		{
			// The socket has been terminated unexpectedly. Abort, now!
			$this->log(1, 'Disconnected unexpectedly, shutting down.');
			exit;
		}

		if (fwrite($this->server->socket, $command.self::$newline))
		{
			// Log the sent command
			$this->log(3, '>>> '.$command);

			// Update the stats
			$this->stats['last_sent'] = microtime(TRUE);
		}
		else
		{
			// Log error
			$this->log(1, 'Error sending command >>> '.$command);
		}
	}

	public function read()
	{
		while ( ! feof($this->server->socket))
		{
			// Start a new read loop
			$loop_time = microtime(TRUE);

			// Read the raw server stream, up to 1024 characters
			while ($raw = fgets($this->server->socket, 1024))
			{
				// Update the last received time
				$this->stats['last_received'] = microtime(TRUE);

				// Parse the raw string into a command array
				$data = $this->parse($raw);

				if (isset($this->responses[$data['command']]))
				{
					// Call the response handler
					call_user_func($this->responses[$data['command']], $data);
				}

				// Debug the response
				$this->log(3, '<<< '.$data['command'].' <<< '.trim($raw));
			}

			// Check the timers
			$this->check_timers();

			// Detemine the amount of time spent in this loop, in microseconds
			$loop_time = (microtime(TRUE) - $loop_time) * 1000000;

			// Every loop should take one-half second
			($loop_time < 500000) and usleep(500000 - (int) $loop_time);
		}

		// Disconnect
		$this->log(1, 'Disconnected');

		// TODO: Handle reconnecting
	}

	/**
	 * Parses are raw server string into a command array.
	 *
	 * @param   string   raw server string
	 * @return  array    sender, sendhost, command, target, message
	 */
	protected function parse($raw)
	{
		// Remove the whitespace garbage
		$raw = trim($raw);

		// These values are always returned
		$data = array
		(
			'sender'   => NULL,
			'sendhost' => NULL,
			'command'  => NULL,
			'target'   => NULL,
			'message'  => NULL,
		);

		// Extract the prefix from the string
		list ($prefix, $str) = explode(' ', $raw, 2);

		if ( ! empty($prefix) AND $prefix[0] === ':')
		{
			// A user-level command, like PRIVMSG or NOTICE
			$prefix = substr($prefix, 1);

			if (strpos($prefix, '!') !== FALSE)
			{
				// sender@host, typically a user
				list ($data['sender'], $data['sendhost']) = explode('!', $prefix, 2);
			}
			else
			{
				// sender, Typically a server
				$data['sender'] = $prefix;
			}

			if (strpos($str, ' ') !== FALSE)
			{
				// CMD str, Extract the command from the remaining string
				list ($data['command'], $str) = explode(' ', $str, 2);

				if (strpos($str, ' :') !== FALSE)
				{
					// target :message, some kind of communication
					list ($data['target'], $data['message']) = explode(' :', $str, 2);
				}
				elseif ($str[0] === ':')
				{
					// :target, without a message
					$data['target'] = substr($str, 1);
				}
				else
				{
					// target, with nothing
					$data['target'] = $str;
				}
			}
			else
			{
				$data['command'] = $str;
			}
		}
		else
		{
			// A server-level command, like PING
			$data['command'] = $prefix;
			$data['message'] = empty($str) ? NULL : $str;
		}

		return $data;
	}

	protected function check_timers()
	{
		foreach ($this->timers as $key => $data)
		{
			if (microtime(TRUE) >= $data['timeout'])
			{
				// Run the callback, passing the bot as the only parameter
				call_user_func($data['callback'], $this);

				// Restart the timer, if it was not removed
				isset($this->timers[$key]) and $this->timers[$key]['timeout'] = microtime(TRUE) + $data['interval'];
			}
		}
	}

	/**
	 * Error and exception handler. Logs errors to the console rather than
	 * displaying them as HTML with Kohana.
	 */
	public function exception_handler($e, $message = NULL, $file = NULL, $line = NULL)
	{
		if (func_num_args() === 5)
		{
			if ((error_reporting() & $e) !== 0)
			{
				// PHP Error
				$this->log(1, $message.' in '.$file.' on line '.$line);
			}
		}
		else
		{
			// Exception
			$this->log(1, strip_tags($e->getMessage()).' File: '.$e->getFile().' on line '.$e->getLine());
		}
	}

	/**
	 * Generates a unique key for a callback.
	 */
	protected function callback_hash($callback)
	{
		$hash = NULL;
		if (is_array($callback))
		{
			if (is_string($callback[0]))
			{
				// Static method callback
				$hash = sha1($callback[0].'::'.$callback[1]);
			}
			else
			{
				// Object method callback
				$hash = sha1(get_class($callback[0]).'->'.$callback[1]);
			}
		}
		else
		{
			// Hash the name
			$hash = sha1($callback);
		}

		return $hash;
	}

	/**
	 * Standard IRC commands.
	 */

	public function login($username, $password = NULL, $realname = 'Kohana PHP Bot')
	{
		// Cache the current username and password
		$this->username = $username;
		$this->password = $password;

		// Send the login commands, use 8 for the mask (invisible)
		$this->send('USER '.$username.' 8 * :'.$realname);
		$this->send('NICK '.$username);

		// Update the last ping
		$this->stats['last_ping'] = microtime(TRUE);
	}

	public function login_identify()
	{
		// Send the IDENTIFY command
		$this->send('PRIVMSG NickServ :IDENTIFY '.$this->password);
	}

	public function join($channel, $password = '')
	{
		if (empty($this->channels[$channel]))
		{
			// Create a new channel
			$this->channels[$channel] = new Kobot_Channel($channel, $password);

			// Join the channel
			$this->send('JOIN '.trim($channel.' '.$password));
		}
	}

	public function part($channel)
	{
		if ( ! empty($this->channels[$channel]))
		{
			// Leave the channel
			$this->send('PART '.$channel);

			// Remove the channel
			unset($this->channels[$channel]);
		}
	}

	public function quit($message = '</Kirc> by Kohana Team')
	{
		// Send a quit message
		$this->send('QUIT :'.$message);
	}

	/**
	 * Default response handlers. You can overload these in your own extension
	 * class, or attach your own event handlers
	 */

	// *
	public function response_drop()
	{
		// Silence is golden
	}

	// PING
	public function response_ping(array $data)
	{
		// Update the stats
		$this->stats['last_ping'] = microtime(TRUE);

		// Reply with a PONG
		$this->send('PONG '.substr($data['message'], 1));
	}

	// 375, 372+, 376
	public function response_motd(array $data)
	{
		switch ($data['command'])
		{
			case '375':
				// Prepare to read the MOTD
				$this->motd = array();
			break;
			case '372':
				// Read the MOTD
				$this->motd[] = substr($data['message'], 2);
			break;
			case '376':
				// Log the number of lines in the MOTD
				$this->log(1, 'Read '.count($this->motd).' MOTD lines');

				// Make the MOTD into a string
				$this->motd = implode("\n", $this->motd);

				// Run the motd_read event
				Event::run('kobot.motd_read');
			break;
		}
	}

	// TOPIC
	public function response_topic(array $data)
	{
		if ($data['command'] === '332')
		{
			// Remove the user from the target
			list ($user, $data['target']) = explode(' ', $data['target']);
		}

		if (isset($this->channels[$data['target']]))
		{
			// Set the channel topic
			$this->channels[$data['target']]->topic = $data['message'];

			// Log the topic change
			$this->log(2, 'Topic of '.$data['target'].' changed: '.$data['message']);
		}
	}

	// 353, 366
	public function response_userlist(array $data)
	{
		if (strpos($data['target'], ' @ ') !== FALSE)
		{
			// Get the channel name from the target
			list ($bot, $channel) = explode(' @ ', $data['target'], 2);

			// Set the current users
			$this->channels[$channel]->users = ($users = explode(' ', $data['message']));

			// Log the user count
			$this->log(2, 'Found '.count($users).' users in channel');

			// Log the channel join
			$this->log(1, 'Joined '.$channel);
		}
	}

	// JOIN
	public function response_join(array $data)
	{
		// Make sure the bot is joined to the target channel
		if (isset($this->channels[$data['target']]))
		{
			// Add the user to the channel
			$this->channels[$data['target']]->user_join($data['sender']);

			// Debug the join
			$this->log(2, '> '.$data['sender'].' ('.$data['target'].')');
		}
	}

	// PART
	public function response_part(array $data)
	{
		// Make sure the bot is joined to the target channel
		if (isset($this->channels[$data['target']]))
		{
			// Remove the user from the channel
			$this->channels[$data['target']]->user_part($data['sender']);

			// Debug the join
			$this->log(2, '< '.$data['sender'].' ('.$data['target'].')');
		}
	}

	// PRIVMSG
	public function response_privmsg(array $data)
	{
		if ($data['message'] === chr(1).'VERSION'.chr(1))
		{
			// Send a CTCP VERSION response
			$this->response_version($data);
		}
		elseif
		((
			// Private messages, reply to the sender and log
			$data['target'] === $this->username
			AND $trigger = trim($data['message'])
			AND $this->log(2, 'Private message from '.$data['sender'].' received')
		)
		OR
		(
			// Channel messages
			substr($data['message'], 0, strlen($this->username)) === $this->username
			AND $trigger = trim(substr($data['message'], strlen($this->username)), ' :')
		))
		{
			// Process triggers
			foreach ($this->triggers as $pattern => $func)
			{
				if (preg_match('/'.$pattern.'/', $trigger, $matches))
				{
					// Execute the callback trigger:
					// callback($this, $command_array, $captures);
					call_user_func($func, $this, $data, $matches);
				}
			}
		}
	}

	public function response_version(array $data)
	{
		// Send a CTCP VERSION response
		$this->send('NOTICE '.$data['sender'].' :'.chr(1).'VERSION Kobot v'.KOHANA_VERSION.' Kohana Team'.chr(1));
	}

	/**
	 * Default triggers.
	 */

	protected function trigger_info(Kobot $bot, array $data, array $params)
	{
		if (isset($this->channels[$data['target']]))
		{
			switch ($params[1])
			{
				case 'topic':
					$this->send('PRIVMSG '.$data['target'].' :'.$data['sender'].': '.$this->channels[$data['target']]->topic);
				break;
				default:
					$this->log(1, var_export($data, TRUE));
				break;
			}
		}
	}

	/**
	 * Default timers.
	 */

	protected function timer_uptime()
	{
		// Uptime array
		$uptime = array();

		// Timespan array
		$timespan = date::timespan((int) $this->stats['start']);

		// Skip all the empty starting intervals
		$skip = TRUE;

		foreach ($timespan as $interval => $amount)
		{
			if ($amount > 0 OR $skip === FALSE)
			{
				// Stop skipping intervals
				$skip = FALSE;

				// Only add amounts that are greater than 0
				$uptime[$interval] = $amount.' '.$interval;
			}
		}
		// Add "and" before the last amount
		$uptime[$interval] = 'and '.$uptime[$interval];

		// Log the uptime
		$this->log(1, 'Uptime: '.implode(' ', $uptime));
	}

} // End Kobot
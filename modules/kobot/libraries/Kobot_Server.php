<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Kobot Server.
 *
 * $Id: Kobot_Server.php 2067 2008-02-17 00:06:40Z Shadowhand $
 *
 * @package    Kobot
 * @author     Woody Gilk
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Kobot_Server_Core {

	// Server settings
	public $host;
	public $port;
	public $timeout;

	// Error number and string
	public $errno;
	public $error;

	public function __construct($host, $port, $timeout)
	{
		$this->host    = $host;
		$this->port    = $port;
		$this->timeout = $timeout;
	}

	public function connect()
	{
		// Disable error reporting
		$ER = error_reporting(0);

		if ($this->socket = fsockopen($this->host, $this->port, $this->errno, $this->error, $this->timeout))
		{
			// Successful connect
			$status = TRUE;

			// Keep the response time as short as possible, for greater interactivity
			stream_set_blocking($this->socket, 0);
		}
		else
		{
			$status = FALSE;
		}

		// Enable error reporting
		error_reporting($ER);

		return $status;
	}

} // End Kobot Server
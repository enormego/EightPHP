<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Provides database access in a platform agnostic way, using simple query building blocks.
 *
 * $Id: Database.php 2302 2008-03-13 17:09:55Z Shadowhand $
 *
 * @package    Core
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Database_Core {

	// Database instances
	protected static $instances;

	// Driver instance
	protected $driver;

	// Query cache
	protected $cache = array();

	public static function instance($name = 'default', $config = NULL)
	{
		if (empty(Database::$instances[$name]))
		{
			// Create a named Database instance
			Database::$instances[$name] = new Database($config);
		}

		return Database::$instances[$name];
	}

	public function __construct($config = NULL)
	{
		if (empty($config))
		{
			// Load default configuration
			$config = Kohana::config('database.default');
		}

		if ( ! is_array($config) OR empty($config['driver']) OR empty($config['database']))
			throw new Kohana_Exception('database.invalid_configuation');

		// Driver class name
		$driver = 'Database_'.ucfirst($config['driver']).'_Driver';

		if ( ! Kohana::auto_load($driver))
			throw new Kohana_Exception('core.driver_not_found', $config['driver'], get_class($this));

		$this->driver = new $driver($config);
	}

	public function __destruct()
	{
		// Manually unset the driver to force a disconnect
		unset($this->driver);
	}

	public function select($columns = '*')
	{
		// Create an array of the columns to select
		$columns = func_num_args() ? func_get_args() : array('*');

		// Return a new SELECT statement
		return new Database_Select($columns, $this->driver);
	}

	public function insert($table, $columns = NULL)
	{
		// Return a new INSERT statement
		return new Database_Update($table, $columns, $this->driver);
	}

	public function update($table, $columns = NULL)
	{
		// Return a new UPDATE statement
		return new Database_Update($table, $columns, $this->driver);
	}

	public function delete($table)
	{
		// Return a new DELETE statement
		return new Database_Delete($table, $this->driver);
	}

	public function expression($sql)
	{
		// Return a new unescaped SQL expression
		return new Database_Expression($sql);
	}

	public function query($sql = NULL, $class = 'StdClass')
	{
		if (is_object($sql))
		{
			// Make the SQL object into a string
			$sql = (string) $sql;
		}

		if ( ! is_string($sql))
			throw new Kohana_Exception('database.invalid_query');

		return $this->driver->query($sql, $class);
	}

} // End Database

/**
 * Sets the code for a Database exception.
 */
class Kohana_Database_Exception extends Kohana_Exception {

	protected $code = E_DATABASE_ERROR;

} // End Kohana Database Exception
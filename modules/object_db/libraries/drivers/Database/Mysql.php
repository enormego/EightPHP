<?php defined('SYSPATH') or die('No direct script access.');

class Database_Mysql_Driver extends Database_Driver {

	// Database configuration
	protected $config;

	// Database link
	protected $link;

	public function __construct(array $config)
	{
		$this->config = $config;
	}

	public function __destruct()
	{
		is_resource($this->link) and mysql_close($this->link);
	}

	protected function connect()
	{
		if ( ! is_resource($this->link))
		{
			// Enable persistent connections
			$connect = empty($this->config['persistent']) ? 'mysql_connect' : 'mysql_pconnect';

			// Make connection
			$this->link = $connect($this->config['hostname'], $this->config['username'], $this->config['password'], TRUE);

			if ($this->link === FALSE)
				throw new Kohana_Database_Exception('database.connection', mysql_errno(), mysql_error());

			// Unset the password after a successful connect
			unset($this->config['password']);

			if ( ! mysql_select_db($this->config['database'], $this->link))
				throw new Kohana_Database_Exception('database.error', mysql_errno($this->link), mysql_error($this->link));

			if ( ! empty($this->config['charset']))
			{
				$this->set_charset($this->config['charset']);
			}
		}

		return $this->link;
	}

	public function query($sql, $class = 'StdClass')
	{
		is_resource($this->link) or $this->connect();

		if ( ! class_exists($class))
		{
			// Ignore the custom class
			$class = 'StdClass';
		}

		if ($this->config['caching'] === TRUE AND isset($this->cache[$this->query_hash($sql)]))
		{
			// Return the cached results
			return $this->cache[$this->query_hash($sql)];
		}

		// Create a new result set
		$result = new Mysql_Result(mysql_query($sql, $this->link), $this->link, $class, $sql);

		if ($this->config['caching'] === TRUE AND ! preg_match('#^(?:INSERT|UPDATE|REPLACE|SET)\b#i', $sql))
		{
			// Store the query results
			$this->cache[$this->query_hash($sql)] = $result;
		}

		return $result;
	}

	public function set_charset($charset)
	{
		$this->query('SET NAMES '.$this->escape_str($charset));
	}

	public function limit($limit, $offset = 0)
	{
		$limit  = (int) $limit;
		$offset = (int) $offset;

		if ($limit === 0 AND $offset === 0)
			return '';

		return 'LIMIT '.((int) $offset).', '.((int) $limit);
	}

	public function escape_str($str)
	{
		if ($this->config['escaping'] === FALSE)
			return $str;

		is_resource($this->link) or $this->connect();

		return mysql_real_escape_string($str, $this->link);
	}

	public function escape_table($table)
	{
		if ($this->config['escaping'] === FALSE)
			return $table;

		return '`'.str_replace('.', '`.`', $table).'`';
	}

	public function escape_column($column)
	{
		if ($this->config['escaping'] === FALSE)
			return $column;

		if (strtolower($column) === 'count(*)' OR $column === '*')
			return $column;

		// This matches any modifiers we support to SELECT.
		if ( ! preg_match('/\b(?:rand|all|distinct(?:row)?|high_priority|sql_(?:small_result|b(?:ig_result|uffer_result)|no_cache|ca(?:che|lc_found_rows)))\s/i', $column))
		{
			return preg_replace('/[^.*]+/', '`$0`', $column);
		}

		$parts = explode(' ', $column);
		$column = '';

		for ($i = 0, $c = count($parts); $i < $c; $i++)
		{
			// The column is always last
			if ($i == ($c - 1))
			{
				$column .= preg_replace('/[^.*]+/', '`$0`', $parts[$i]);
			}
			else // otherwise, it's a modifier
			{
				$column .= $parts[$i].' ';
			}
		}

		return $column;
	}

	public function list_tables()
	{
		$sql    = 'SHOW TABLES FROM `'.$this->db_config['connection']['database'].'`';
		$result = $this->query($sql)->result(FALSE, MYSQL_ASSOC);

		$retval = array();
		foreach($result as $row)
		{
			$retval[] = current($row);
		}

		return $retval;
	}

} // End Database MySQL Driver

/**
 * MySQL result.
 */
class Mysql_Result implements Database_Result, ArrayAccess, Iterator, Countable {

	// Result resource
	protected $result;

	// Last insert id
	protected $insert_id = FALSE;

	// Current and total rows
	protected $current_row = 0;
	protected $total_rows;

	// Result object class
	protected $result_class;

	/**
	 * Sets up the result variables.
	 *
	 * @param  resource  query result
	 * @param  resource  database link
	 * @param  boolean   return objects or arrays
	 * @param  string    SQL query that was run
	 */
	public function __construct($result, $link, $class, $sql)
	{
		if ($result === FALSE)
			throw new Kohana_Database_Exception('database.error', mysql_errno($link), mysql_error($link).' - '.$sql);

		if (is_resource($result))
		{
			// SELECT, SHOW, DESCRIBE, EXPLAIN query
			$this->total_rows  = mysql_num_rows($result);

			// Set result class
			$this->result_class = $class;
		}
		else
		{
			// DELETE, INSERT, REPLACE, or UPDATE query
			$this->insert_id  = mysql_insert_id($link);
			$this->total_rows = mysql_affected_rows($link);
		}

		// Load the result
		$this->result = $result;
	}

	/**
	 * Destruct, the cleanup crew!
	 */
	public function __destruct()
	{
		if (is_resource($this->result))
		{
			mysql_free_result($this->result);
		}
	}

	public function as_array()
	{
		return $this->result_array();
	}

	public function result_array()
	{
		$rows = array();

		if (is_resource($this->result) AND mysql_num_rows($this->result))
		{
			// Reset the pointer location to make sure things work properly
			mysql_data_seek($this->result, 0);

			while ($row = mysql_fetch_object($this->result, $this->result_class))
			{
				$rows[] = $row;
			}
		}
		
		return $rows;
	}

	public function insert_id()
	{
		return $this->insert_id;
	}

	public function list_fields()
	{
		$fields = array();

		if (is_resource($this->result))
		{
			while ($f = mysql_fetch_field($this->result))
			{
				$fields[] = $f->name;
			}
		}

		return $fields;
	}

	// End Interface
	// Interface: Countable

	/**
	 * Counts the number of rows in the result set.
	 *
	 * @return  integer
	 */
	public function count()
	{
		return $this->total_rows;
	}

	// End Interface
	// Interface: ArrayAccess

	/**
	 * Determines if the requested offset of the result set exists.
	 *
	 * @param   integer  offset id
	 * @return  boolean
	 */
	public function offsetExists($offset)
	{
		if ($this->total_rows > 0)
		{
			$min = 0;
			$max = $this->total_rows - 1;

			return ($offset < $min OR $offset > $max) ? FALSE : TRUE;
		}

		return FALSE;
	}

	/**
	 * Retreives the requested query result offset.
	 *
	 * @param   integer  offset id
	 * @return  mixed
	 */
	public function offsetGet($offset)
	{
		// Check to see if the requested offset exists.
		if ( ! $this->offsetExists($offset))
			return FALSE;

		// Go to the offset
		mysql_data_seek($this->result, $offset);

		// Return the row
		return mysql_fetch_object($this->result, $this->result_class);
	}

	/**
	 * Sets the offset with the provided value. Since you can't modify query result sets, this function just throws an exception.
	 *
	 * @param   integer  offset id
	 * @param   integer  value
	 * @throws  Kohana_Database_Exception
	 */
	public function offsetSet($offset, $value)
	{
		throw new Kohana_Database_Exception('database.result_read_only');
	}

	/**
	 * Unsets the offset. Since you can't modify query result sets, this function just throws an exception.
	 *
	 * @param   integer  offset id
	 * @throws  Kohana_Database_Exception
	 */
	public function offsetUnset($offset)
	{
		throw new Kohana_Database_Exception('database.result_read_only');
	}

	// End Interface
	// Interface: Iterator

	/**
	 * Retrieves the current result set row.
	 *
	 * @return  mixed
	 */
	public function current()
	{
		return $this->offsetGet($this->current_row);
	}

	/**
	 * Retreives the current row id.
	 *
	 * @return  integer
	 */
	public function key()
	{
		return $this->current_row;
	}

	/**
	 * Moves the result pointer ahead one step.
	 *
	 * @return  integer
	 */
	public function next()
	{
		return ++$this->current_row;
	}

	/**
	 * Moves the result pointer back one step.
	 *
	 * @return  integer
	 */
	public function prev()
	{
		return --$this->current_row;
	}

	/**
	 * Moves the result pointer to the beginning of the result set.
	 *
	 * @return  integer
	 */
	public function rewind()
	{
		return $this->current_row = 0;
	}

	/**
	 * Determines if the current result pointer is valid.
	 *
	 * @return  boolean
	 */
	public function valid()
	{
		return $this->offsetExists($this->current_row);
	}
	// End Interface

} // End MySQL Result Class
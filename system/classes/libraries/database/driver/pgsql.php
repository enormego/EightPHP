<?php
/**
 * Provides specific database items for PostgreSQL 8.1+.
 *
 * @package		System
 * @subpackage	Libraries.Database
 * @author		EightPHP Development Team
 * @copyright	(c) 2009-2010 EightPHP
 * @license		http://license.eightphp.com
 */
class Database_Driver_Pgsql extends Database_Driver {

	// Database connection link
	protected $link;
	protected $db_config;

	/**
	 * Sets up the config for the class.
	 *
	 * @param	array	database configuration
	 */
	public function __construct($config) {
		$this->db_config = $config;

		Eight::log('debug', 'PgSQL Database Driver Initialized');
	}

	public function connect() {
		// Check if link already exists
		if (is_resource($this->link)) 
			return $this->link;

		// Import the connect variables
		extract($this->db_config['connection']);

		// Persistent connections enabled?
		$connect = ($this->db_config['persistent'] == TRUE) ? 'pg_pconnect' : 'pg_connect';

		// Build the connection info
		$port = (isset($port)) ? 'port=\''.$port.'\'' : '';
		$host = (isset($host)) ? 'host=\''.$host.'\' '.$port : ''; // if no host, connect with the socket

		$connection_string = $host.' dbname=\''.$database.'\' user=\''.$user.'\' password=\''.$pass.'\'';
		// Make the connection and select the database
		if ($this->link = $connect($connection_string)) {
			if ($charset = $this->db_config['character_set']) {
				$this->set_charset($charset);
			}

			// Clear password after successful connect
			$this->db_config['connection']['pass'] = NULL;

			return $this->link;
		}

		return FALSE;
	}

	public function query($sql, $active_link = NULL, $as_master = NO) {
		if(!$active_link || !is_resource($active_link)) {
			$active_link = $this->link;
		}
		
		// Only cache if it's turned on, and only cache if it's not a write statement
		if ($this->db_config['cache'] AND ! preg_match('#\b(?:INSERT|UPDATE|SET)\b#i', $sql)) {
			$hash = $this->query_hash($sql);

			if ( ! isset(self::$query_cache[$hash])) {
				// Set the cached object
				self::$query_cache[$hash] = new Database_Pgsql_Result(pg_query($active_link, $sql), $active_link, $this->db_config['object'], $sql);
			}

			return self::$query_cache[$hash];
		}

		return new Database_Pgsql_Result(pg_query($active_link, $sql), $active_link, $this->db_config['object'], $sql);
	}

	public function set_charset($charset) {
		$this->query('SET client_encoding TO '.pg_escape_string($this->link, $charset));
	}

	public function escape_table($table) {
		return '"'.str_replace('.', '"."', $table).'"';
	}

	public function escape_column($column) {
		if (strtolower($column) == 'count(*)' OR $column == '*')
			return $column;

		// This matches any modifiers we support to SELECT.
		if ( ! preg_match('/\b(?:all|distinct)\s/i', $column)) {
			if (stripos($column, ' AS ') !== FALSE) {
				// Force 'AS' to uppercase
				$column = str_ireplace(' AS ', ' AS ', $column);

				// Runs escape_column on both sides of an AS statement
				$column = array_map(array($this, __FUNCTION__), explode(' AS ', $column));

				// Re-create the AS statement
				return implode(' AS ', $column);
			}

			return preg_replace('/[^.*]+/', '"$0"', $column);
		}

		$parts = explode(' ', $column);
		$column = '';

		for ($i = 0, $c = count($parts); $i < $c; $i++) {
			// The column is always last
			if ($i == ($c - 1)) {
				$column .= preg_replace('/[^.*]+/', '"$0"', $parts[$i]);
			} else { //  otherwise, it's a modifier 
				$column .= $parts[$i].' ';
			}
		}
		return $column;
	}

	public function regex($field, $match = '', $type = 'AND ', $num_regexs) {
		$prefix = ($num_regexs == 0) ? '' : $type;

		return $prefix.' '.$this->escape_column($field).' REGEXP \''.$this->escape_str($match).'\'';
	}

	public function notregex($field, $match = '', $type = 'AND ', $num_regexs) {
		$prefix = $num_regexs == 0 ? '' : $type;

		return $prefix.' '.$this->escape_column($field).' NOT REGEXP \''.$this->escape_str($match) . '\'';
	}

	public function merge($table, $keys, $values) {
		throw new Database_Exception('database.not_implemented', __FUNCTION__);
	}

	public function limit($limit, $offset = 0) {
		return 'LIMIT '.$limit.' OFFSET '.$offset;
	}

	public function stmt_prepare($sql = '') {
		is_object($this->link) or $this->connect();
		return new Database_Pgsql_Statement($sql, $this->link);
	}

	public function compile_select($database) {
		$sql = ($database['distinct'] == TRUE) ? 'SELECT DISTINCT ' : 'SELECT ';
		$sql .= (count($database['select']) > 0) ? implode(', ', $database['select']) : '*';

		if (count($database['from']) > 0) {
			$sql .= "\nFROM ";
			$sql .= implode(', ', $database['from']);
		}

		if (count($database['join']) > 0) {
			$sql .= ' '.implode("\n", $database['join']);
		}

		if (count($database['where']) > 0) {
			$sql .= "\nWHERE ";
		}

		$sql .= implode("\n", $database['where']);

		if (count($database['groupby']) > 0) {
			$sql .= "\nGROUP BY ";
			$sql .= implode(', ', $database['groupby']);
		}

		if (count($database['having']) > 0) {
			$sql .= "\nHAVING ";
			$sql .= implode("\n", $database['having']);
		}

		if (count($database['orderby']) > 0) {
			$sql .= "\nORDER BY ";
			$sql .= implode(', ', $database['orderby']);
		}

		if (is_numeric($database['limit'])) {
			$sql .= "\n";
			$sql .= $this->limit($database['limit'], $database['offset']);
		}

		return $sql;
	}

	public function escape_str($str) {
		is_resource($this->link) or $this->connect();

		return pg_escape_string($this->link, $str);
	}

	public function list_tables() {
		$sql    = 'SELECT table_name FROM information_schema.tables WHERE table_schema = \'public\'';
		$result = $this->query($sql)->result(FALSE, PGSQL_ASSOC);

		$retval = array();
		foreach($result as $row) {
			$retval[] = current($row);
		}

		return $retval;
	}

	public function show_error() {
		return pg_last_error($this->link);
	}

	public function list_fields($table, $query = FALSE) {
		static $tables;

		if (is_object($query)) {
			if (empty($tables[$table])) {
				$tables[$table] = array();

				foreach($query as $row) {
					$tables[$table][] = $row->Field;
				}
			}

			return $tables[$table];
		}

		// WOW...REALLY?!?
		// Taken from http://www.postgresql.org/docs/7.4/interactive/catalogs.html
		return 'SELECT
  -- Field
  pg_attribute.attname AS "Field",
  -- Type
  CASE pg_type.typname
    WHEN \'int2\' THEN \'smallint\'
    WHEN \'int4\' THEN \'int\'
    WHEN \'int8\' THEN \'bigint\'
    WHEN \'varchar\' THEN \'varchar(\' || pg_attribute.atttypmod-4 || \')\'
    ELSE pg_type.typname
  END AS "Type",
  -- Null
  CASE WHEN pg_attribute.attnotnull THEN \'\'
    ELSE \'YES\'
  END AS "Null",
  -- Default
  CASE pg_type.typname
    WHEN \'varchar\' THEN substring(pg_attrdef.adsrc from \'^\'(.*)\'.*$\')
    ELSE pg_attrdef.adsrc
  END AS "Default"
FROM pg_class
  INNER JOIN pg_attribute
    ON (pg_class.oid=pg_attribute.attrelid)
  INNER JOIN pg_type
    ON (pg_attribute.atttypid=pg_type.oid)
  LEFT JOIN pg_attrdef
    ON (pg_class.oid=pg_attrdef.adrelid AND pg_attribute.attnum=pg_attrdef.adnum)
WHERE pg_class.relname=\''.$this->escape_table($table).'\' AND pg_attribute.attnum>=1 AND NOT pg_attribute.attisdropped
ORDER BY pg_attribute.attnum';

	}

	public function field_data($table) {
		// TODO: This whole function needs to be debugged.
		$query  = pg_query('SELECT * FROM '.$this->escape_table($table).' LIMIT 1', $this->link);
		$fields = pg_num_fields($query);
		$table  = array();

		for ($i=0; $i < $fields; $i++) {
			$table[$i]['type']  = pg_field_type($query, $i);
			$table[$i]['name']  = pg_field_name($query, $i);
			$table[$i]['len']   = pg_field_prtlen($query, $i);
		}

		return $table;
	}

} // End Database_Pgsql_Driver Class

/**
 * The result class for PostgreSQL queries.
 *
 * @package		System
 * @subpackage	Libraries.Database
 * @author		EightPHP Development Team
 * @copyright	(c) 2009-2010 EightPHP
 * @license		http://license.eightphp.com
 */
class Database_Pgsql_Result implements Database_Result, ArrayAccess, Iterator, Countable {

	// Result resource
	protected $result = NULL;

	// Total rows and current row
	protected $total_rows  = FALSE;
	protected $current_row = FALSE;

	// Insert id
	protected $insert_id = FALSE;

	// Data fetching types
	protected $fetch_type  = 'pgsql_fetch_object';
	protected $return_type = PGSQL_ASSOC;

	/**
	 * Sets up the class.
	 * @internal
	 *
	 * @param	resource	result resource
	 * @param	resource	database resource link
	 * @param	bool		return objects or arrays
	 * @param	string		sql query that was run
	 */
	public function __construct($result, $link, $object = TRUE, $sql) {
		$this->result = $result;

		// If the query is a resource, it was a SELECT, SHOW, DESCRIBE, EXPLAIN query
		if (is_resource($result)) {
			$this->current_row = 0;
			$this->total_rows  = pg_num_rows($this->result);
			$this->fetch_type = ($object === TRUE) ? 'pg_fetch_object' : 'pg_fetch_array';
		} elseif (is_bool($result)) {
			if ($result == FALSE) {
				// SQL error
				throw new Database_Exception('database.error', pg_last_error().' - '.$sql);
			} else {
				// Its an DELETE, INSERT, REPLACE, or UPDATE query
				$this->insert_id  = $this->get_insert_id($link);
				$this->total_rows = pg_affected_rows($link);
			}
		}

		// Set result type
		$this->result($object);
	}

	/**
	 * Magic __destruct function, frees the result.
	 * @internal
	 */
	public function __destruct() {
		if (is_resource($this->result)) {
			pg_free_result($this->result);
		}
	}

	public function result($object = FALSE, $type = PGSQL_ASSOC) {
		$this->fetch_type = ((bool) $object) ? 'pg_fetch_object' : 'pg_fetch_array';

		// This check has to be outside the previous statement, because we do not
		// know the state of fetch_type when $object = NULL
		// NOTE - The class set by $type must be defined before fetching the result,
		// autoloading is disabled to save a lot of stupid overhead.
		if ($this->fetch_type == 'pg_fetch_object') {
			$this->return_type = class_exists($type, FALSE) ? $type : 'stdClass';
		} else {
			$this->return_type = $type;
		}

		return $this;
	}

	public function result_array($object = NULL, $type = PGSQL_ASSOC) {
		$rows = array();

		if (is_string($object)) {
			$fetch = $object;
		} elseif (is_bool($object)) {
			if ($object === TRUE) {
				$fetch = 'pg_fetch_object';

				// NOTE - The class set by $type must be defined before fetching the result,
				$type = class_exists($type, TRUE) ? $type : 'stdClass';
			} else {
				$fetch = 'pg_fetch_array';
			}
		} else {
			// Use the default config values
			$fetch = $this->fetch_type;

			if ($fetch == 'pg_fetch_object') {
				$type = class_exists($type, TRUE) ? $type : 'stdClass';
			}
		}

		while ($row = $fetch($this->result, NULL, $type)) {
			$rows[] = $row;
		}

		return $rows;
	}

	public function insert_id() {
		return $this->insert_id;
	}

	public function list_fields() {
		throw new Database_Exception('database.not_implemented', __FUNCTION__);
	}
	// End Interface

	private function get_insert_id($link) {
		$query = 'SELECT LASTVAL() as insert_id';

		$result = pg_query($link, $query);
		$insert_id = pg_fetch_array($result, NULL, PGSQL_ASSOC);

		return $insert_id['insert_id'];
	}

	// Interface: Countable
	/**
	 * Method: count
	 *  Counts the number of rows in the result set.
	 *
	 * Returns:
	 *  The number of rows in the result set
	 */
	public function count() {
		return $this->total_rows;
	}
	// End Interface

	// Interface: ArrayAccess
	/**
	 * Method: offsetExists
	 *  Determines if the requested offset of the result set exists.
	 *
	 * Parameters:
	 *  offset - offset id
	 *
	 * Returns:
	 *  TRUE if the offset exists, FALSE otherwise
	 */
	public function offsetExists($offset) {
		if ($this->total_rows > 0) {
			$min = 0;
			$max = $this->total_rows - 1;

			return ($offset < $min OR $offset > $max) ? FALSE : TRUE;
		}

		return FALSE;
	}

	/**
	 * Method: offsetGet
	 *  Retreives the requested query result offset.
	 *
	 * Parameters:
	 *  offset - offset id
	 *
	 * Returns:
	 *  The query row
	 */
	public function offsetGet($offset) {
		// Check to see if the requested offset exists.
		if (!$this->offsetExists($offset))
			return FALSE;

		// Go to the offset and return the row
		$fetch = $this->fetch_type;
		return $fetch($this->result, $offset, $this->return_type);
	}

	/**
	 * Method: offsetSet
	 *  Sets the offset with the provided value. Since you can't modify query result sets, this function just throws an exception.
	 *
	 * Parameters:
	 *  offset - offset id
	 *  value  - value to set
	 *
	 * Returns:
	 *  <Database_Exception> object
	 */
	public function offsetSet($offset, $value) {
		throw new Database_Exception('database.result_read_only');
	}

	/**
	 * Method: offsetUnset
	 *  Unsets the offset. Since you can't modify query result sets, this function just throws an exception.
	 *
	 * Parameters:
	 *  offset - offset id
	 *
	 * Returns:
	 *  <Database_Exception> object
	 */
	public function offsetUnset($offset) {
		throw new Database_Exception('database.result_read_only');
	}
	// End Interface

	// Interface: Iterator
	/**
	 * Method: current
	 *  Retreives the current result set row.
	 *
	 * Returns:
	 *  The current result row (type based on <Database_Pgsql_Result.result>)
	 */
	public function current() {
		return $this->offsetGet($this->current_row);
	}

	/**
	 * Method: key
	 *  Retreives the current row id.
	 *
	 * Returns:
	 *  The current result row id
	 */
	public function key() {
		return $this->current_row;
	}

	/**
	 * Method: next
	 *  Moves the result pointer ahead one.
	 *
	 * Returns:
	 *  The next row id
	 */
	public function next() {
		return ++$this->current_row;
	}

	/**
	 * Method: next
	 *  Moves the result pointer back one.
	 *
	 * Returns:
	 *  The previous row id
	 */
	public function prev() {
		return --$this->current_row;
	}

	/**
	 * Method: rewind
	 *  Moves the result pointer to the beginning of the result set.
	 *
	 * Returns:
	 *  0
	 */
	public function rewind() {
		return $this->current_row = 0;
	}

	/**
	 * Method: valid
	 *  Determines if the current result pointer is valid.
	 *
	 * Returns:
	 *  TRUE if the pointer is valid, FALSE otherwise
	 */
	public function valid() {
		return $this->offsetExists($this->current_row);
	}
	// End Interface
} // End Database_Pgsql_Result Class

class Database_Pgsql_Statement {

	protected $link = NULL;
	protected $stmt;

	public function __construct($sql, $link) {
		$this->link = $link;

		$this->stmt = $this->link->prepare($sql);

		return $this;
	}

	public function __destruct() {
		$this->stmt->close();
	}

	// Sets the bind parameters
	public function bind_params() {
		$argv = func_get_args();
		return $this;
	}

	// sets the statement values to the bound parameters
	public function set_vals() {
		return $this;
	}

	// Runs the statement
	public function execute() {
		return $this;
	}
} // End Database Pgsql Statement Class
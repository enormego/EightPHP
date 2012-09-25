<?php
/**
 * Provides specific database items for MSSQL.
 *
 * @package		System
 * @subpackage	Libraries.Database
 * @author		EightPHP Development Team
 * @copyright	(c) 2009-2010 EightPHP
 * @license		http://license.eightphp.com
*/
class Database_Driver_Mssql extends Database_Driver {

	/**
	 * Database connection link
	 *
	 * @var resource
	 */
	protected $link;

	/**
	 * Database connection link -- only for updates
	 *
	 * @var resource
	 */
	protected $link_master;

	/**
	 * Database configuration
	 *
	 * @var array
	 */
	protected $db_config;

	/**
	 * Construction, sets the config for the class.
	 *
	 * @param array $config
	 */
	public function __construct($config) {
		$this->db_config = $config;
		ini_set('mssql.datetimeconvert', 0);
		Eight::log('debug', 'MSSQL Database Driver Initialized');
	}

	/**
	 * Destruction, closes the database connection.
	 */
	public function __destruct() {
		is_resource($this->link) && mssql_close($this->link);
		is_resource($this->link_master) && mssql_close($this->link_master);
	}

	public function connect($master=FALSE) {
		// Check if link already exists
		if(!$master) {
			if(is_resource($this->link)) {
				return $this->link;
			}
		} else {
			if(is_resource($this->link_master)) {
				return $this->link_master;
			}
		}

		// Import the connect variables
		if($master) {
			extract($this->db_config['connection_master']);
		} else {
			extract($this->db_config['connection']);
		}

		// Persistent connections enabled?
		$connect = ($this->db_config['persistent'] == TRUE) ? 'mssql_pconnect' : 'mssql_connect';

		// Build the connection info
		$host = (isset($host)) ? $host : $socket;
		$port = (isset($port)) ? ','.$port : '';

		// Make the connection and select the database
		try {
			$active_link = $connect($host.$port, $user, $pass);
		} catch (Exception $e) {
			throw new Database_Exception($e->getMessage());
		}

		// Successful connection, proceed...
		if($active_link) {
			// Select the database
			if(!mssql_select_db('['.$database.']', $active_link)) {
				throw new Database_Exception('database.unknown_database', $database);
				return FALSE;
			}

			if($master) {
				$this->link_master = $active_link;
			} else {
				$this->link = $active_link;
			}

			if ($charset = $this->db_config['character_set']) {
				$this->set_charset($charset, $active_link);
			}

			// Clear password after successful connect
			$this->db_config['connection']['pass'] = NULL;

			return $active_link;
		}

		return FALSE;
	}

	public function has_master() {
		if(is_array($this->db_config['connection_master']) && count($this->db_config['connection_master']) > 0) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	public function query($sql, $active_link = NULL, $as_master = FALSE) {
		if(!is_resource($active_link)) {
			if($this->has_master()) {
				if($as_master) {
					is_resource($this->link_master) or $this->connect(TRUE);
					$active_link = $this->link_master;
				} else if(strtolower(substr(trim($sql), 0, 6)) == "select") {
					$active_link = $this->link;
				} else {
					is_resource($this->link_master) or $this->connect(TRUE);
					$active_link = $this->link_master;
				}
			} else {
				$active_link = $this->link;
			}
		}
		
		// Mssql specific
		if(preg_match("#LIMIT ([0-9]+)\, ([0-9]+)#i", $sql, $matches)) {
			list($replace, $offset, $limit) = $matches;
			$sql = str_replace($replace, 'LIMIT '.$limit.' OFFSET '.$offset, $sql);	
		}
		
		if(preg_match("/OFFSET ([0-9]+)/i",$sql,$matches)) {
			list($replace, $offset) = $matches;
			$sql = str_replace($replace, '', $sql);
		}
		
		if(preg_match("/LIMIT ([0-9]+)/i",$sql,$matches)) {
			list($replace, $limit) = $matches;
			$sql = str_replace($replace, '', $sql);
		}
		
		if(isset($limit) || isset($offset)) {
			if (!isset($offset)) {
				$sql = preg_replace("/^(SELECT|DELETE|UPDATE)\s/i", "$1 TOP " . $limit . ' ', $sql);
			} else {
				$ob_count = (int)preg_match_all('/ORDER BY/i', $sql,$ob_matches, PREG_OFFSET_CAPTURE);
		
				if($ob_count < 1) {
					$over = 'ORDER BY (SELECT 0)';
				} else {
					$ob_last = array_pop($ob_matches[0]);
					$orderby = strrchr($sql, $ob_last[0]);
					$over = preg_replace('/[^,\s]*\.([^,\s]*)/i', 'inner_tbl.$1', $orderby);
					
					// Remove ORDER BY clause from $sql
					$sql = substr($sql, 0, $ob_last[1]);
				}
				
				// Add ORDER BY clause as an argument for ROW_NUMBER()
				$sql = "SELECT ROW_NUMBER() OVER ($over) AS EIGHT_DB_ROWNUM, * FROM ($sql) AS inner_tbl";
			  
				$start = $offset + 1;
				$end = $offset + $limit;
		
				$sql = "WITH outer_tbl AS ($sql) SELECT * FROM outer_tbl WHERE EIGHT_DB_ROWNUM BETWEEN $start AND $end";
			}
		}

		return new Database_Mssql_Result(mssql_query($sql, $active_link), $active_link, $this->db_config['object'], $sql);
	}

	public function trans_start() {
		$this->query('SET AUTOCOMMIT=0');
		$this->query('BEGIN');
	}

	public function trans_complete() {
		$this->query('COMMIT');
		$this->query('SET AUTOCOMMIT=1');
	}

	public function trans_rollback() {
		$this->query('ROLLBACK');
		$this->query('SET AUTOCOMMIT=1');
	}

	public function set_charset($charset, $active_link = NULL) {
		return TRUE;
	}

	public function escape_table($table) {
		if (stripos($table, ' AS ') !== FALSE) {
			// Force 'AS' to uppercase
			$table = str_ireplace(' AS ', ' AS ', $table);

			// Runs escape_table on both sides of an AS statement
			$table = array_map(array($this, __FUNCTION__), explode(' AS ', $table));

			// Re-create the AS statement
			return implode(' AS ', $table);
		}
		return $table;
	}

	public function escape_column($column) {
		if (strtolower($column) == 'count(*)' OR $column == '*' OR strpos($column, '(') !== FALSE)
			return $column;

		// This matches any modifiers we support to SELECT.
		if (!preg_match('/\b(?:rand|all|distinct(?:row)?|high_priority|sql_(?:small_result|b(?:ig_result|uffer_result)|no_cache|ca(?:che|lc_found_rows)))\s/i', $column)) {
			if (stripos($column, ' AS ') !== FALSE) {
				// Force 'AS' to uppercase
				$column = str_ireplace(' AS ', ' AS ', $column);

				// Runs escape_column on both sides of an AS statement
				$column = array_map(array($this, __FUNCTION__), explode(' AS ', $column));

				// Re-create the AS statement
				return implode(' AS ', $column);
			}

			return preg_replace('/[^.*]+/', '$0', $column);
		}

		$parts = explode(' ', $column);
		$column = '';

		for ($i = 0, $c = count($parts); $i < $c; $i++) {
			// The column is always last
			if ($i == ($c - 1)) {
				$column .= preg_replace('/[^.*]+/', '$0', $parts[$i]);
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
		// Escape the column names
		foreach ($keys as $key => $value) {
			$keys[$key] = $this->escape_column($value);
		}
		return 'REPLACE INTO '.$this->escape_table($table).' ('.implode(', ', $keys).') VALUES ('.implode(', ', $values).')';
	}

	public function limit($limit, $offset = 0) {
		return 'LIMIT '.$offset.', '.$limit;
	}

	public function stmt_prepare($sql = '') {
		throw new Database_Exception('database.not_implemented', __FUNCTION__);
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
			
			// Strip ` out of where clause.
			foreach($database['where'] as $k => $v) {
				$database['where'][$k] = str_replace('`', '', $v);
			}
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
		is_resource($this->link) || $this->connect();
		return $str; //static::mssql_escape($str);
	}

	protected static function mssql_escape($data) {
		if(is_numeric($data)) {
			return $data;
		}
		$unpacked = unpack('H*hex', $data);
		return '0x'.$unpacked['hex'];
	}

	public function list_tables() {
		$sql    = 'SELECT TABLE_CATALOG , TABLE_SCHEMA , TABLE_NAME , TABLE_TYPE FROM INFORMATION_SCHEMA.TABLES';
		$result = $this->query($sql)->result(FALSE, MSSQL_ASSOC);

		$retval = array();
		foreach($result as $row) {
			$retval[] = $row['TABLE_SCHEMA'].'.'.$row['TABLE_NAME'];
		}

		return $retval;
	}

	public function show_error() {
		return mssql_get_last_message();
	}

	/**
	 * return a list of fields from $table
	 *
	 * @param  string $table
	 * @return array
	 */
	public function list_fields($table) {
		static $tables;

		if (empty($tables[$table])) {
			foreach($this->field_data($table) as $row) {
				// Make an associative array
				$tables[$table][$row['COLUMN_NAME']] = $this->sql_type($row['DATA_TYPE']);
			}
		}

		return $tables[$table];
	}

	public function field_data($table) {
		$query  = mssql_query('SELECT * FROM INFORMATION_SCHEMA.Columns where TABLE_NAME = "'.$table.'"', $this->link);

		$table  = array();
		while ($row = mssql_fetch_assoc($query)) {
			$table[] = $row;
		}

		return $table;
	}

	/**
	 * Method: insert_id
	 *  Returns last insert id
	 *
	 * @deprecated
	 */
	public function insert_id() {
		if(is_object(Database_Mssql_Result::$last_result)) {
			return Database_Mssql_Result::$last_result->insert_id();
		} else {
			Eight::log('error', "Could not find last result.");
			return FALSE;
		}
	}

	public function create_database($name) {
		mssql_query("CREATE DATABASE `".$name."`");
	}

	public function delete_database($name) {
		mssql_query("DROP DATABASE `".$name."`");
	}

} // End Database_Mysql_Driver Class


class Database_Mssql_Result implements Database_Result, ArrayAccess, Iterator, Countable {

	public static $last_result = NULL;

	/**
	 * Result resource
	 *
	 * @var resource
	 */
	protected $result = NULL;

	/**
	 * Total rows
	 *
	 * @var integer
	 */
	protected $total_rows  = FALSE;

	/**
	 * Current row
	 *
	 * @var integer
	 */
	protected $current_row = FALSE;

	/**
	 * Last insterted ID
	 *
	 * @var integer
	 */
	protected $insert_id = FALSE;

	/**
	 * Fetch type
	 *
	 * @var string
	 */
	protected $fetch_type  = 'mssql_fetch_object';

	/**
	 * Return type
	 *
	 * @var integer
	 */
	protected $return_type = MSSQL_ASSOC;

	/**
	 * Construct, sets up the class.
	 *
	 * @param resource $result
	 * @param resource $link
	 * @param boolean  $object
	 * @param string   $sql
	 */
	public function __construct($result, $link, $object = TRUE, $sql) {
		$this->result = $result;

		// If the query is a resource, it was a SELECT, SHOW, DESCRIBE, EXPLAIN query
		if (is_resource($result)) {
			$this->current_row = 0;
			$this->total_rows  = mssql_num_rows($this->result);
			$this->fetch_type = ($object === TRUE) ? 'mssql_fetch_object' : 'mssql_fetch_array';
		} elseif (is_bool($result)) {
			if ($result == FALSE) {
				// SQL error
				throw new Database_Exception('Database Error: '.mysql_error($link).' - '.$sql);
			} else {
				// Its an DELETE, INSERT, REPLACE, or UPDATE query
				$this->insert_id  = $this->insert_id($link);
				$this->total_rows = mssql_rows_affected($link);
			}
		}

		// Set result type
		$this->result($object);

		self::$last_result =& $this;
	}

	/**
	 * Destruct, the cleanup crew!
	 *
	 */
	public function __destruct() {
		if(is_resource($this->result)) {
			mssql_free_result($this->result);
		}
	}


	public function result($object = FALSE, $type = MYSQL_ASSOC) {
		$this->fetch_type = ((bool) $object) ? 'mssql_fetch_object' : 'mssql_fetch_array';

		// This check has to be outside the previous statement, because we do not
		// know the state of fetch_type when $object = NULL
		// NOTE - The class set by $type must be defined before fetching the result,
		// autoloading is disabled to save a lot of stupid overhead.
		if ($this->fetch_type == 'mssql_fetch_object') {
			$this->return_type = class_exists($type, FALSE) ? $type : 'stdClass';
		} else {
			$this->return_type = $type;
		}

		return $this;
	}

	public function result_array($object = FALSE, $type = MSSQL_ASSOC) {
		$rows = array();

		if (is_string($object)) {
			$fetch = $object;
		} elseif (is_bool($object)) {
			if ($object === TRUE) {
				$fetch = 'mssql_fetch_object';

				// NOTE - The class set by $type must be defined before fetching the result,
				$type = class_exists($type, TRUE) ? $type : 'stdClass';
			} else {
				$fetch = 'mssql_fetch_array';
			}
		} else {
			// Use the default config values
			$fetch = $this->fetch_type;

			if ($fetch == 'mssql_fetch_object') {
				$type = class_exists($type, TRUE) ? $type : 'stdClass';
			}
		}

		if(mssql_num_rows($this->result)) {
			// Reset the pointer location to make sure things work properly
			mssql_data_seek($this->result, 0);
			
			if($fetch === 'mssql_fetch_object') {
				while ($row = self::fetch_object($this->result, $type)) {
					$rows[] = $row;
				}
			} else {
				while ($row = $fetch($this->result)) {
					$rows[] = $row;
				}	
			}
		}

		return isset($rows) ? $rows : array();
	}

	public function insert_id($link = NULL) {
		if($this->insert_id) {
			return $this->insert_id;
		}

		$result = mssql_query('SELECT SCOPE_IDENTITY() AS insert_id', $link);
		$row = mssql_fetch_assoc($result);
		$this->insert_id = $row['insert_id'];
		return $this->insert_id;
	}

	public function list_fields() {
		$field_names = array();
		while ($field = mssql_fetch_field($this->result)) {
			$field_names[] = $field->name;
		}

		return $field_names;
	}
	// End Interface


	// Interface: Countable
	/**
	 * Counts the number of rows in the result set.
	 *
	 * @return integer The number of rows in the result set
	 */
	public function count() {
		return $this->total_rows;
	}

	/**
	 * Counts the number of rows in the result set.
	 *
	 * @return     integer The number of rows in the result set
	 * @deprecated Depricated, use count()
	 */
	public function num_rows() {
		return $this->total_rows;
	}
	// End Interface


	// Interface: ArrayAccess
	/**
	 * Determines if the requested offset of the result set exists.
	 *
	 * @param  integer $offset
	 * @return boolean TRUE if exists, FALSE otherwise
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
	 * Retreives the requested query result offset.
	 *
	 * @param  integer $offset
	 * @return mixed the query row
	 */
	public function offsetGet($offset) {
		// Check to see if the requested offset exists.
		if(!$this->offsetExists($offset)) {
			return FALSE;	
		}

		// Go to the offset
		mssql_data_seek($this->result, $offset);

		// Return the row
		$fetch = $this->fetch_type;
		
		if($fetch === 'mssql_fetch_object') {
			return self::fetch_object($this->result, $this->return_type);
		} else {
			return $fetch($this->result);	
		}
	}

	/**
	 * Sets the offset with the provided value. Since you can't modify query result sets, this function just throws an exception.
	 *
	 * @param  integer $offset
	 * @param  integer $value
	 * @throws Exception
	 */
	public function offsetSet($offset, $value) {
		throw new Database_Exception('database.result_read_only');
	}

	/**
	 * Unsets the offset. Since you can't modify query result sets, this function just throws an exception.
	 *
	 * @param  integer $offset
	 * @throws Exception
	 */
	public function offsetUnset($offset) {
		throw new Database_Exception('database.result_read_only');
	}
	// End Interface

	// Interface: Iterator
	/**
	 * Retreives the current result set row.
	 *
	 * @return integer The current result row (type based on <Database_Mysql_Result.result>)
	 */
	public function current() {
		return $this->offsetGet($this->current_row);
	}

	/**
	 * Retreives the current result set row.
	 *
	 * @return integer The current result row (type based on <Database_Mysql_Result.result>)
	 */
	public function row_array() {
		return $this->current();
	}

	/**
	 * Retreives the current row id.
	 *
	 * @return integer The current result row number
	 */
	public function key() {
		return $this->current_row;
	}

	/**
	 * Moves the result pointer ahead one step.
	 *
	 * @return integer The next row number
	 */
	public function next() {
		return ++$this->current_row;
	}

	/**
	 * Moves the result pointer back one step.
	 *
	 * @return integer The previous row number
	 */
	public function prev() {
		return --$this->current_row;
	}

	/**
	 * Moves the result pointer to the beginning of the result set.
	 *
	 * @return integer 0
	 */
	public function rewind() {
		return $this->current_row = 0;
	}

	/**
	 * Determines if the current result pointer is valid.
	 *
	 * @return boolean
	 */
	public function valid() {
		return $this->offsetExists($this->current_row);
	}
	// End Interface
	
	public static function fetch_object($result, $object = 'stdClass') {
		$o = new $object;
		$data = mssql_fetch_assoc($result);
		
		if(!$data) {
			return NULL;
		}
		
		if(is_array($data)) {
			foreach($data as $k => $v) {
				$o->{$k} = $v;
			}
		}
		
		return $o;
	}
	
} // End Database_MSSQL_Result Class
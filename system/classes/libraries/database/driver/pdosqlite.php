<?php
/**
 * Provides specific database items for Sqlite.
 * Connection string should be, eg: "pdosqlite://path/to/database.db"
 *
 * @version		$Id: pdosqlite.php 242 2010-02-10 23:06:09Z Shaun $
 *
 * @package		System
 * @subpackage	Libraries.Database
 * @author		enormego
 * @copyright	(c) 2009-2010 enormego
 * @license		http://license.eightphp.com
 */

class Database_Driver_Pdosqlite extends Database_Driver {

	// Database connection link
	protected $link;
	protected $db_config;

	/*
	 * Constructor: __construct
	 *  Sets up the config for the class.
	 *
	 * Parameters:
	 *  config - database configuration
	 *
	 */
	public function __construct($config) {
		$this->db_config = $config;

		Eight::log('debug', 'PDO:Sqlite Database Driver Initialized');
	}

	public function connect() {
		// Import the connect variables
		extract($this->db_config['connection']);

		try {
			$this->link = ($this->db_config['persistent'] == TRUE)
			            ? new PDO ('sqlite:'.$socket.$database, $user, $pass, array(PDO::ATTR_PERSISTENT => true))
			            : new PDO ('sqlite:'.$socket.$database, $user, $pass);
			$this->link->setAttribute(PDO::ATTR_CASE, PDO::CASE_NATURAL);
			$this->link->query('PRAGMA count_changes=1;');

			if ($charset = $this->db_config['character_set']) {
				$this->set_charset($charset);
			}
		}
		catch (PDOException $e) {
			throw new Eight_Database_Exception('database.error', $e->getMessage());
		}

		return $this->link;
	}

	public function query($sql, $active_link = NULL, $as_master = NO) {
		if(!$active_link || !is_resource($active_link)) {
			$active_link = $this->link;
		}
		
		try {
			$sth = $active_link->prepare($sql);
		}
		catch (PDOException $e) {
			throw new Eight_Database_Exception('database.error', $e->getMessage());
		}
		return new Pdosqlite_Result($sth, $active_link, $this->db_config['object'], $sql);
	}

	public function set_charset($charset) {
		$this->link->query('PRAGMA encoding = '.$this->escape_str($charset));
	}

	public function escape_table($table) {
		return '`'.str_replace('.', '`.`', $table).'`';
	}

	public function escape_column($column) {
		if (strtolower($column) == 'count(*)' OR $column == '*')
			return $column;

		// This matches any modifiers we support to SELECT.
		if ( ! preg_match('/\b(?:rand|all|distinct(?:row)?|high_priority|sql_(?:small_result|b(?:ig_result|uffer_result)|no_cache|ca(?:che|lc_found_rows)))\s/i', $column)) {
			if (stripos($column, ' AS ') !== FALSE) {
				// Force 'AS' to uppercase
				$column = str_ireplace(' AS ', ' AS ', $column);

				// Runs escape_column on both sides of an AS statement
				$column = array_map(array($this, __FUNCTION__), explode(' AS ', $column));

				// Re-create the AS statement
				return implode(' AS ', $column);
			}

			return preg_replace('/[^.*]+/', '`$0`', $column);
		}

		$parts = explode(' ', $column);
		$column = '';

		for ($i = 0, $c = count($parts); $i < $c; $i++) {
			// The column is always last
			if ($i == ($c - 1)) {
				$column .= preg_replace('/[^.*]+/', '`$0`', $parts[$i]);
			} else { //  otherwise, it's a modifier 
				$column .= $parts[$i].' ';
			}
		}
		return $column;
	}

	public function regex($field, $match = '', $type = 'AND ', $num_regexs) {
		throw new Eight_Database_Exception('database.not_implemented', __FUNCTION__);
	}

	public function notregex($field, $match = '', $type = 'AND ', $num_regexs) {
		throw new Eight_Database_Exception('database.not_implemented', __FUNCTION__);
	}

	public function merge($table, $keys, $values) {
		throw new Eight_Database_Exception('database.not_implemented', __FUNCTION__);
	}

	public function limit($limit, $offset = 0) {
		return 'LIMIT '.$offset.', '.$limit;
	}

	public function stmt_prepare($sql = '') {
		throw new Eight_Database_Exception('database.not_implemented', __FUNCTION__);
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
		if(function_exists('sqlite_escape_string')) {
			$res = sqlite_escape_string($str);
		} else {
			$res = str_replace("'", "''", $str);
		}
		return $res;
	}

	public function list_tables() {
		$sql = "SELECT `name` FROM `sqlite_master` WHERE `type`='table' ORDER BY `name`;";
		try {
			$result = $this->query($sql)->result(FALSE, PDO::FETCH_ASSOC);
			$retval = array();
			foreach($result as $row) {
				$retval[] = current($row);
			}
		}
		catch (PDOException $e) {
			throw new Eight_Database_Exception('database.error', $e->getMessage());
		}
		return $retval;
	}

	public function show_error() {
		$err = $this->link->errorInfo();
		return isset($err[2]) ? $err[2] : 'Unknown error!';
	}

	public function list_fields($table, $query = FALSE) {
		static $tables;
		if (is_object($query)) {
			if (empty($tables[$table])) {
				$tables[$table] = array();

				foreach($query->result() as $row) {
					$tables[$table][] = $row->name;
				}
			}

			return $tables[$table];
		} else {
			return 'PRAGMA table_info('.$this->escape_table($table).')';
		}
	}

	public function field_data($table) {
		Eight::log('error', 'This method is under developing');
	}
	/**
	 * Version number query string
	 *
	 * @access	public
	 * @return	string
	 */
	function version() {
		return $this->link->getAttribute(constant("PDO::ATTR_SERVER_VERSION"));
	}

} // End Database_PdoSqlite_Driver Class

/**
 * The result class for Sqlite queries.
 *
 * @package		System
 * @subpackage	Libraries.Database
 * @author		enormego
 * @copyright	(c) 2009-2010 enormego
 * @license		http://license.eightphp.com
 */
class Pdosqlite_Result implements Database_Result, ArrayAccess, Iterator, Countable {

	// Result resource
	protected $result = NULL;

	// Total rows and current row
	protected $total_rows  = FALSE;
	protected $current_row = FALSE;

	// Insert id
	protected $insert_id = FALSE;

	// Data fetching types
	protected $fetch_type  = PDO::FETCH_OBJ;
	protected $return_type = PDO::FETCH_ASSOC;

	/**
	 * Sets up the class.
	 *
	 * @param	result	result resource
	 * @param	link	database resource link
	 * @param	object	return objects or arrays
	 * @param	sql		sql query that was run
	 */
	public function __construct($result, $link, $object = TRUE, $sql) {
		if (is_object($result) OR $result = $link->prepare($sql)) {
			// run the query
			try {
				$result->execute();
			}
			catch (PDOException $e) {
				throw new Eight_Database_Exception('database.error', $e->getMessage());
			}

			if (preg_match('/^SELECT|PRAGMA|EXPLAIN/i', $sql)) {
				//Eight::log('debug','it was a SELECT, SHOW, DESCRIBE, EXPLAIN query');
				$this->result = $result;
				$this->current_row = 0;

				$this->total_rows = $this->sqlite_row_count();

				$this->fetch_type = ($object === TRUE) ? PDO::FETCH_OBJ : PDO::FETCH_ASSOC;
			} elseif (preg_match('/^DELETE|INSERT|UPDATE/i', $sql)) {
				//Eight::log('debug','Its an DELETE, INSERT, REPLACE, or UPDATE query');
				$this->insert_id  = $link->lastInsertId();
			}
		} else {
			// SQL error
			throw new Eight_Database_Exception('database.error', $link->errorInfo().' - '.$sql);
		}
		// Set result type
		$this->result($object);
	}

	private function sqlite_row_count()  {
		// workaround for PDO not supporting RowCount with SQLite - we manually count
		//TODO : can this be fixed?
		$count = 0;
		while ($this->result->fetch()) {
			$count++;
		}

		// now the really dumb part: need to re-execute the query
		$this->result->execute();

		return $count;
	}

	/*
	 *  Magic __destruct function, frees the result.
	 */
	public function __destruct() {
		if (is_object($this->result)) {
			$this->result->closeCursor();
			$this->result = NULL;
		}
	}

	public function result($object = FALSE, $type = PDO::FETCH_BOTH) {
		$rows = array();

		$this->fetch_type = (bool) $object ? PDO::FETCH_OBJ : PDO::FETCH_BOTH;

		if ($this->fetch_type == PDO::FETCH_OBJ) {
			$this->return_type = class_exists($type, FALSE) ? $type : 'stdClass';
		} else {
			$this->return_type = $type;
		}
		return $this;
	}

	public function result_array($object = NULL, $type = PDO::FETCH_ASSOC) {
		$rows = array();

		if (is_string($object)) {
			$fetch = $object;
		} elseif (is_bool($object)) {
			if ($object === TRUE) {
				$fetch = PDO::FETCH_OBJ;

				// NOTE - The class set by $type must be defined before fetching the result,
				// autoloading is disabled to save a lot of stupid overhead.
				$type = class_exists($type, FALSE) ? $type : 'stdClass';
			} else {
				$fetch = PDO::FETCH_OBJ;
			}
		} else {
			// Use the default config values
			$fetch = $this->fetch_type;

			if ($fetch == PDO::FETCH_OBJ) {
				$type = class_exists($type, FALSE) ? $type : 'stdClass';
			}
		}
		try {
			while ($row = $this->result->fetch($fetch)) {
				$rows[] = $row;
			}
		}
		catch(PDOException $e) {
			throw new Eight_Database_Exception('database.error', $e->getMessage());
			return FALSE;
		}
		return $rows;
	}

	public function insert_id() {
		return $this->insert_id;
	}

	public function list_fields() {
		//~ This function only work correctly after you execute a query,
		//~ AND BEFORE you fetch the query result!!
		//~ You should really use Database_PdoSqlite::list_fields instead of PdoSqlite_Result::list_fields()
		Eight::log('debug','If Sqlite_Result::list_fields() do NOT work as what you expect,read the method\'s comment plz');

		$field_names = array();
		for ($i = 0; $i<$this->result->columnCount(); $i++) {
			$colInfo = $this->result->getColumnMeta($i);
			$field_names[] = $colInfo['name'];
		}
		return $field_names;
	}
	// End Interface

	// Interface: Countable
	/*
	 * Method: count
	 *  Counts the number of rows in the result set.
	 *
	 * Returns:
	 *  The number of rows in the result set
	 *
	 */
	public function count() {
		//~ Now only work after calling result() or result_array();
		Eight::log('debug', 'Now only work after calling result() or result_array()');
		return $this->total_rows;
	}

	public function num_rows() {
		return $this->total_rows;
	}
	// End Interface

	// Interface: ArrayAccess
	/*
	 * Method: offsetExists
	 *  Determines if the requested offset of the result set exists.
	 *
	 * Parameters:
	 *  offset - offset id
	 *
	 * Returns:
	 *  TRUE if the offset exists, FALSE otherwise
	 *
	 */
	public function offsetExists($offset) {
		if ($this->total_rows > 0) {
			$min = 0;
			$max = $this->total_rows - 1;

			return ($offset < $min OR $offset > $max) ? FALSE : TRUE;
		}

		return FALSE;
	}

	/*
	 * Method: offsetGet
	 *  Retreives the requested query result offset.
	 *
	 * Parameters:
	 *  offset - offset id
	 *
	 * Returns:
	 *  The query row
	 *
	 */
	public function offsetGet($offset) {
		$row = array();
		try {
			$row = $this->result->fetch($this->fetch_type, PDO::FETCH_ORI_ABS, $offset);
		}
		catch(PDOException $e) {
			throw new Eight_Database_Exception('database.error', $e->getMessage());
			return FALSE;
		}
		return $row;
	}

	/*
	 * Method: offsetSet
	 *  Sets the offset with the provided value. Since you can't modify query result sets, this function just throws an exception.
	 *
	 * Parameters:
	 *  offset - offset id
	 *  value  - value to set
	 *
	 * Returns:
	 *  <Eight_Database_Exception> object
	 *
	 */
	public function offsetSet($offset, $value) {
		throw new Eight_Database_Exception('database.result_read_only');
	}

	/*
	 * Method: offsetUnset
	 *  Unsets the offset. Since you can't modify query result sets, this function just throws an exception.
	 *
	 * Parameters:
	 *  offset - offset id
	 *
	 * Returns:
	 *  <Eight_Database_Exception> object
	 *
	 */
	public function offsetUnset($offset) {
		throw new Eight_Database_Exception('database.result_read_only');
	}
	// End Interface

	// Interface: Iterator
	/*
	 * Method: current
	 *  Retreives the current result set row.
	 *
	 * Returns:
	 *  The current result row (type based on <PdoSqlite_result.result>)
	 *
	 */
	public function current() {
		return $this->offsetGet($this->current_row);
	}

	/*
	 * Method: key
	 *  Retreives the current row id.
	 *
	 * Returns:
	 *  The current result row id
	 *
	 */
	public function key() {
		return $this->current_row;
	}

	/*
	 * Method: next
	 *  Moves the result pointer ahead one.
	 *
	 * Returns:
	 *  The next row id
	 *
	 */
	public function next() {
		return ++$this->current_row;
	}

	/*
	 * Method: next
	 *  Moves the result pointer back one.
	 *
	 * Returns:
	 *  The previous row id
	 *
	 */
	public function prev() {
		return --$this->current_row;
	}

	/*
	 * Method: rewind
	 *  Moves the result pointer to the beginning of the result set.
	 *
	 * Returns:
	 *  0
	 *
	 */
	public function rewind() {
		//~ To request a scrollable cursor for your PDOStatement object,
		//~ you must set the PDO::ATTR_CURSOR attribute to PDO::CURSOR_SCROLL
		//~ when you prepare the SQL statement with PDO->prepare().
		Eight::log('error','this method do not work now,please read the comment of that.');
		//return $this->current_row = 0;
	}

	/*
	 * Method: valid
	 *  Determines if the current result pointer is valid.
	 *
	 * Returns:
	 *  TRUE if the pointer is valid, FALSE otherwise
	 *
	 */
	public function valid() {
		return $this->offsetExists($this->current_row);
	}
	// End Interface
} // End PdoSqlite_Result Class
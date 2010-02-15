<?php
/**
 *  Database API driver interface
 *
 * @version		$Id: driver.php 242 2010-02-10 23:06:09Z Shaun $
 *
 * @package		System
 * @subpackage	Libraries.Database
 * @author		enormego
 * @copyright	(c) 2009-2010 enormego
 * @license		http://license.eightphp.com
 */
abstract class Database_Driver {

	static $query_cache;

	/**
	 * Connect to our database
	 * Returns FALSE on failure or a MySQL resource
	 *
	 * @return mixed
	 */
	abstract public function connect();

	/**
	 * Perform a query based on a manually written query
	 *
	 * @param  string $sql
	 * @param  resource $active_link
	 * @param  bool $as_master
	 * @return Database_Result
	 */
	abstract public function query($sql, $active_link = NULL, $as_master = NO);

	/**
	 * Builds a DELETE query.
	 *
	 * @param  string $table
	 * @param  array $where
	 * @return string
	 */
	public function delete($table, $where) {
		return 'DELETE FROM '.$this->escape_table($table).' WHERE '.implode(' ', $where);
	}

	/**
	 * Builds an UPDATE query.
	 *
	 * @param  string $table
	 * @param  array $values
	 * @param  array $where
	 * @return string
	 */
	public function update($table, $values, $where) {
		foreach($values as $key => $val) {
			$valstr[] = $this->escape_column($key).' = '.$val;
		}
		return 'UPDATE '.$this->escape_table($table).' SET '.implode(', ', $valstr).' WHERE '.implode(' ',$where);
	}

	/**
	 * Set the charset using 'SET NAMES <charset>'
	 *
	 * @param string $charset
	 */
	abstract public function set_charset($charset);

	/**
	 * Wrap the tablename in backticks, has support for: table.field syntax.
	 *
	 * @param  string $table
	 * @return string
	 */
	abstract public function escape_table($table);

	/**
	 * Escape a column/field name, has support for special commands.
	 *
	 * @param  string $column
	 * @return string
	 */
	abstract public function escape_column($column);

	/**
	 * Builds a WHERE portion of a query.
	 *
	 * @param  mixed  $key
	 * @param  string $value
	 * @param  string $type
	 * @param  int    $num_wheres
	 * @param  bool   $quote
	 * @return string
	 */
	public function where($key, $value, $type, $num_wheres, $quote) {
		$prefix = ($num_wheres == 0) ? '' : trim($type).' ';

		if ($quote === -1) {
			$value = '';
		} else {
			if ($value === NULL) {
				if ( ! $this->has_operator($key)) {
					$key .= ' IS';
				}

				$value = ' NULL';
			} elseif (is_bool($value)) {
				if ( ! $this->has_operator($key)) {
					$key .= ' =';
				}

				$value = ($value == TRUE) ? ' 1' : ' 0';
			} else {
				if ( ! $this->has_operator($key)) {
					$key = $this->escape_column($key).' =';
				} else {
					preg_match('/^(.+?)([<>!=]+|\bIS(?:\s+NULL)|\sLIKE|\sNOT\sLIKE)\s*$/i', $key, $matches);
					$key = $this->escape_column(trim($matches[1])).' '.trim($matches[2]);
				}

				$value = ' '.(($quote == TRUE) ? $this->escape($value) : $value);
			}
		}

		return $prefix.$key.$value;
	}
	
	/**
	 * Builds a WHERE IN portion of a query.
	 *
	 * @param  string  $key
	 * @param  mixed   $value
	 * @param  string  $type
	 * @param  int     $num_wheres
	 * @param  bool    $quote
	 * @return string
	 */
	public function where_in($key, $value, $type, $num_wheres, $quote) {
		$prefix = ($num_wheres == 0) ? '' : trim($type).' ';

		if(!is_array($value)) {
			$value = array($value);
		}
		
		foreach($value as $k=> $v) {
			$value[$k] = (($quote == TRUE) ? $this->escape($v) : $v);
		}
		
		$key = $this->escape_column($key);
		return $prefix.$key." IN (".implode(",", $value).")";
	}

	/**
	 * Builds a LIKE portion of a query.
	 *
	 * @param  mixed  $field
	 * @param  string $match
	 * @param  string $type
	 * @param  int    $num_likes
	 * @return string
	 */
	public function like($field, $match = '', $type = 'AND ', $num_likes) {
		$prefix = ($num_likes == 0) ? '' : $type;

		$match = (substr($match, 0, 1) == '%' OR substr($match, (strlen($match)-1), 1) == '%')
		       ? $this->escape_str($match)
		       : '%'.$this->escape_str($match).'%';

		return $prefix.' '.$this->escape_column($field).' LIKE \''.$match . '\'';
	}

	/**
	 * Builds a NOT LIKE portion of a query.
	 *
	 * @param  mixed  $field
	 * @param  string $match
	 * @param  string $type
	 * @param  int    $num_likes
	 * @return string
	 */
	public function notlike($field, $match = '', $type = 'AND ', $num_likes) {
		$prefix = ($num_likes == 0) ? '' : $type;

		$match = (substr($match, 0, 1) == '%' OR substr($match, (strlen($match)-1), 1) == '%')
		       ? $this->escape_str($match)
		       : '%'.$this->escape_str($match).'%';

		return $prefix.' '.$this->escape_column($field).' NOT LIKE \''.$match.'\'';
	}

	/**
	 * Builds a REGEX portion of a query
	 *
	 * @param  string  $field
	 * @param  string  $match
	 * @param  string  $type
	 * @param  integer $num_regexs
	 * @return string
	 */
	abstract public function regex($field, $match, $type, $num_regexs);

	/**
	 * Builds a NOT REGEX portion of a query
	 *
	 * @param  string  $field
	 * @param  string  $match
	 * @param  string  $type
	 * @param  integer $num_regexs
	 * @return string
	 */
	abstract public function notregex($field, $match, $type, $num_regexs);

	/**
	 * Builds an INSERT query.
	 *
	 * @param  string  $table
	 * @param  array   $keys
	 * @param  array   $values
	 * @return string
	 */
	public function insert($table, $keys, $values) {
		// Escape the column names
		foreach ($keys as $key => $value) {
			$keys[$key] = $this->escape_column($value);
		}
		return 'INSERT INTO '.$this->escape_table($table).' ('.implode(', ', $keys).') VALUES ('.implode(', ', $values).')';
	}

	/**
	 * Builds a MERGE portion of a query.
	 *
	 * @param  string $table
	 * @param  array  $keys
	 * @param  array  $values
	 * @return string
	 */
	abstract public function merge($table, $keys, $values);

	/**
	 * Builds a LIMIT portion of a query.
	 *
	 * @param  integer $limit
	 * @param  integer $offset
	 * @return string
	 */
	abstract public function limit($limit, $offset = 0);

	/**
	 * Creates a prepared statement.
	 *
	 * @param  string $sql
	 * @return Database_Stmt
	 */
	abstract public function stmt_prepare($sql = '');

	/**
	 *  Compiles the SELECT statement.
	 *  Generates a query string based on which functions were used.
	 *  Should not be called directly, the get() function calls it.
	 *
	 * @param  array  $database
	 * @return string
	 */
	abstract public function compile_select($database);

	/**
	 * Determines if the string has an arithmetic operator in it.
	 *
	 * @param  string $str
	 * @return boolean
	 */
	public function has_operator($str) {
		return (bool) preg_match('/[<>!=]|\sIS\s+(?:NOT\s+)?NULL\b|\sLIKE|\sNOT\sLIKE/i', trim($str));
	}

	/**
	 * Escapes any input value
	 *
	 * @param  mixed $value
	 * @return string
	 */
	public function escape($value) {
		switch (gettype($value)) {
			case 'string':
				$value = '\''.$this->escape_str($value).'\'';
			break;
			case 'boolean':
				$value = (int) $value;
			break;
			case 'double':
				$value = sprintf('%F', $value);
			break;
			default:
				$value = ($value === NULL) ? 'NULL' : $value;
			break;
		}

		return (string) $value;
	}

	/**
	 * Escapes a string for a query
	 *
	 * @param  mixed $str but most likely string
	 * @return mixed but most likely string
	 */
	abstract public function escape_str($str);

	/**
	 * Lists all tables in the database
	 *
	 * @return array
	 */
	abstract public function list_tables();

	/**
	 * Returns the last database error
	 *
	 * @return string
	 */
	abstract public function show_error();

	/**
	 * Returns field data about a table.
	 *
	 * @param  string $table
	 * @return array
	 */
	abstract public function field_data($table);

	/**
	 * Fetches SQL type information about a field, in a generic format.
	 *
	 * @param  string $str
	 * @return array
	 */
	protected function sql_type($str) {
		static $sql_types;

		if ($sql_types === NULL) {
			// Load SQL data types
			$sql_types = Eight::config('sql_types');
		}

		$str = strtolower(trim($str));

		if (($open  = strpos($str, '(')) !== FALSE) {
			// Find closing bracket
			$close = strpos($str, ')', $open) - 1;

			// Find the type without the size
			$type = substr($str, 0, $open);
		} else {
			// No length
			$type = $str;
		}

		empty($sql_types[$type]) and exit
		(
			'Unknown field type: '.$type.'. '.
			'Please report this: http://trac.Eightphp.com/newticket'
		);

		// Fetch the field definition
		$field = $sql_types[$type];

		switch($field['type']) {
			case 'string':
			case 'float':
				if (isset($close)) {
					// Add the length to the field info
					$field['length'] = substr($str, $open + 1, $close - $open);
				}
			break;
			case 'int':
				// Add unsigned value
				$field['unsigned'] = (strpos($str, 'unsigned') !== FALSE);
			break;
		}

		return $field;
	}

	/**
	 * Clears the internal query cache
	 *
	 * @param  string $sql
	 */
	public function clear_cache($sql = NULL) {
		if (empty($sql)) {
			self::$query_cache = array();
		} else {
			unset(self::$query_cache[$this->query_hash($sql)]);
		}

		Eight::log('debug', 'Database cache cleared: '.get_class($this));
	}

	/**
	 * Creates a hash for an SQL query string. Replaces newlines with spaces,
	 * trims, and hashes.
	 *
	 * @param  string $sql
	 * @return string
	 */
	protected function query_hash($sql) {
		return sha1(trim(str_replace("\n", ' ', $sql)));
	}
	
	/**
	 * Drivers that can handle slave/master configurations will override this
	 *
	 * @return bool
	 */
	public function has_master() {
		return NO;
	}

} // End Database Driver Interface

/**
 * Database_Result
 *
 */
interface Database_Result {

	/**
	 * Prepares the query result.
	 *
	 * @param  bool  $object
	 * @param  mixed $type
	 * @return Database_Result
	 */
	public function result($object = FALSE, $type = FALSE);

	/**
	 * Builds an array of query results.
	 *
	 * @param  bool  $object
	 * @param  mixed $type
	 * @return array
	 */
	public function result_array($object = FALSE, $type = FALSE);

	/**
	 * gets the ID of the last insert statement
	 *
	 * @return int
	 */
	public function insert_id();

	/**
	 * Gets the fields of an already run query
	 *
	 * @return array
	 */
	public function list_fields();

} // End Database Result Interface
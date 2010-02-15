<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Provides database access in a platform agnostic way, using simple query building blocks.
 *
 * $Id: Database.php 2303 2008-03-14 01:00:54Z zombor $
 *
 * @package    Core
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
abstract class Database_Query_Builder_Core {

	// Un-compiled parts of the SQL query
	protected $select     = array();
	protected $set        = array();
	protected $from       = array();
	protected $join       = array();
	protected $where      = array();
	protected $orderby    = array();
	protected $order      = array();
	protected $groupby    = array();
	protected $having     = array();
	protected $distinct   = FALSE;
	protected $limit      = FALSE;
	protected $offset     = FALSE;
	
	/**
	 * Selects the column names for a database query.
	 *
	 * @chainable
	 * @param   string  string or array of column names to select
	 * @return  object  This Database object.
	 */
	public function select($sql = '*')
	{
		if (func_num_args() > 1)
		{
			$sql = func_get_args();
		}
		elseif (is_string($sql))
		{
			$sql = explode(',', $sql);
		}
		else
		{
			$sql = (array) $sql;
		}

		foreach($sql as $val)
		{
			if (($val = trim($val)) === '') continue;

			if (strpos($val, '(') === FALSE AND $val !== '*')
			{
				if (preg_match('/^DISTINCT\s++(.+)$/i', $val, $matches))
				{
					$val            = $this->config['table_prefix'].$matches[1];
					$this->distinct = TRUE;
				}
				elseif (strpos($val, '.') !== FALSE)
				{
					$val = $this->config['table_prefix'].$val;
				}

				$val = $this->driver->escape_column($val);
			}

			$this->select[] = $val;
		}

		return $this;
	}

	/**
	 * Selects the from table(s) for a database query.
	 *
	 * @chainable
	 * @param   string  string or array of tables to select
	 * @return  object  This Database object.
	 */
	public function from($sql)
	{
		foreach((array) $sql as $val)
		{
			if (($val = trim($val)) === '') continue;

			$this->from[] = $this->config['table_prefix'].$val;
		}

		return $this;
	}

	/**
	 * Generates the JOIN portion of the query.
	 *
	 * @chainable
	 * @param   string        table name
	 * @param   string|array  where key or array of key => value pairs
	 * @param   string        where value
	 * @param   string        type of join
	 * @return  object        This Database object.
	 */
	public function join($table, $key, $value = NULL, $type = '')
	{
		if ($type != '')
		{
			$type = strtoupper(trim($type));

			if ( ! in_array($type, array('LEFT', 'RIGHT', 'OUTER', 'INNER', 'LEFT OUTER', 'RIGHT OUTER'), TRUE))
			{
				$type = '';
			}
		}

		$cond = array();
		$keys = is_array($key) ? $key : array($key => $value);

		foreach ($keys as $key => $value)
		{
			$key    = (strpos($key, '.') !== FALSE) ? $this->config['table_prefix'].$key : $key;
			$cond[] = $this->driver->where($key, $this->driver->escape_column($this->config['table_prefix'].$value), 'AND ', count($cond), FALSE);
		}

		$this->join[] = $type.' JOIN '.$this->driver->escape_column($this->config['table_prefix'].$table).' ON '.implode(' ', $cond);

		return $this;
	}

	/**
	 * Selects the where(s) for a database query.
	 *
	 * @chainable
	 * @param   string|array  key name or array of key => value pairs
	 * @param   string        value to match with key
	 * @param   boolean       disable quoting of WHERE clause
	 * @return  object        This Database object.
	 */
	public function where($key, $value = NULL, $quote = TRUE)
	{
		$quote = (func_num_args() < 2 AND ! is_array($key)) ? -1 : $quote;
		$keys  = is_array($key) ? $key : array($key => $value);

		foreach ($keys as $key => $value)
		{
			$key           = (strpos($key, '.') !== FALSE) ? $this->config['table_prefix'].$key : $key;
			$this->where[] = $this->driver->where($key, $value, 'AND ', count($this->where), $quote);
		}

		return $this;
	}

	/**
	 * Selects the or where(s) for a database query.
	 *
	 * @chainable
	 * @param   string|array  key name or array of key => value pairs
	 * @param   string        value to match with key
	 * @param   boolean       disable quoting of WHERE clause
	 * @return  object        This Database object.
	 */
	public function orwhere($key, $value = NULL, $quote = TRUE)
	{
		$quote = (func_num_args() < 2 AND ! is_array($key)) ? -1 : $quote;
		$keys  = is_array($key) ? $key : array($key => $value);

		foreach ($keys as $key => $value)
		{
			$key           = (strpos($key, '.') !== FALSE) ? $this->config['table_prefix'].$key : $key;
			$this->where[] = $this->driver->where($key, $value, 'OR ', count($this->where), $quote);
		}

		return $this;
	}

	/**
	 * Selects the like(s) for a database query.
	 *
	 * @chainable
	 * @param   string|array  field name or array of field => match pairs
	 * @param   string        like value to match with field
	 * @return  object        This Database object.
	 */
	public function like($field, $match = '')
	{
		$fields = is_array($field) ? $field : array($field => $match);

		foreach ($fields as $field => $match)
		{
			$field         = (strpos($field, '.') !== FALSE) ? $this->config['table_prefix'].$field : $field;
			$this->where[] = $this->driver->like($field, $match, 'AND ', count($this->where));
		}

		return $this;
	}
	
	/**
	 * Selects the or like(s) for a database query.
	 *
	 * @chainable
	 * @param   string|array  field name or array of field => match pairs
	 * @param   string        like value to match with field
	 * @return  object        This Database object.
	 */
	public function orlike($field, $match = '')
	{
		$fields = is_array($field) ? $field : array($field => $match);

		foreach ($fields as $field => $match)
		{
			$field         = (strpos($field, '.') !== FALSE) ? $this->config['table_prefix'].$field : $field;
			$this->where[] = $this->driver->like($field, $match, 'OR ', count($this->where));
		}

		return $this;
	}

	/**
	 * Selects the not like(s) for a database query.
	 *
	 * @chainable
	 * @param   string|array  field name or array of field => match pairs
	 * @param   string        like value to match with field
	 * @return  object        This Database object.
	 */
	public function notlike($field, $match = '')
	{
		$fields = is_array($field) ? $field : array($field => $match);

		foreach ($fields as $field => $match)
		{
			$field         = (strpos($field, '.') !== FALSE) ? $this->config['table_prefix'].$field : $field;
			$this->where[] = $this->driver->notlike($field, $match, 'AND ', count($this->where));
		}

		return $this;
	}

	/**
	 * Selects the or not like(s) for a database query.
	 *
	 * @chainable
	 * @param   string|array  field name or array of field => match pairs
	 * @param   string        like value to match with field
	 * @return  object        This Database object.
	 */
	public function ornotlike($field, $match = '')
	{
		$fields = is_array($field) ? $field : array($field => $match);

		foreach ($fields as $field => $match)
		{
			$field         = (strpos($field, '.') !== FALSE) ? $this->config['table_prefix'].$field : $field;
			$this->where[] = $this->driver->notlike($field, $match, 'OR ', count($this->where));
		}

		return $this;
	}

	/**
	 * Selects the like(s) for a database query.
	 *
	 * @chainable
	 * @param   string|array  field name or array of field => match pairs
	 * @param   string        like value to match with field
	 * @return  object        This Database object.
	 */
	public function regex($field, $match = '')
	{
		$fields = is_array($field) ? $field : array($field => $match);

		foreach ($fields as $field => $match)
		{
			$field         = (strpos($field, '.') !== FALSE) ? $this->config['table_prefix'].$field : $field;
			$this->where[] = $this->driver->regex($field, $match, 'AND ', count($this->where));
		}

		return $this;
	}

	/**
	 * Selects the or like(s) for a database query.
	 *
	 * @chainable
	 * @param   string|array  field name or array of field => match pairs
	 * @param   string        like value to match with field
	 * @return  object        This Database object.
	 */
	public function orregex($field, $match = '')
	{
		$fields = is_array($field) ? $field : array($field => $match);

		foreach ($fields as $field => $match)
		{
			$field         = (strpos($field, '.') !== FALSE) ? $this->config['table_prefix'].$field : $field;
			$this->where[] = $this->driver->regex($field, $match, 'OR ', count($this->where));
		}

		return $this;
	}

	/**
	 * Selects the not regex(s) for a database query.
	 *
	 * @chainable
	 * @param   string|array  field name or array of field => match pairs
	 * @param   string        regex value to match with field
	 * @return  object        This Database object.
	 */
	public function notregex($field, $match = '')
	{
		$fields = is_array($field) ? $field : array($field => $match);

		foreach ($fields as $field => $match)
		{
			$field         = (strpos($field, '.') !== FALSE) ? $this->config['table_prefix'].$field : $field;
			$this->where[] = $this->driver->notregex($field, $match, 'AND ', count($this->where));
		}

		return $this;
	}

	/**
	 * Selects the or not regex(s) for a database query.
	 *
	 * @chainable
	 * @param   string|array  field name or array of field => match pairs
	 * @param   string        regex value to match with field
	 * @return  object        This Database object.
	 */
	public function ornotregex($field, $match = '')
	{
		$fields = is_array($field) ? $field : array($field => $match);

		foreach ($fields as $field => $match)
		{
			$field         = (strpos($field, '.') !== FALSE) ? $this->config['table_prefix'].$field : $field;
			$this->where[] = $this->driver->notregex($field, $match, 'OR ', count($this->where));
		}

		return $this;
	}

	/**
	 * Chooses the column to group by in a select query.
	 *
	 * @chainable
	 * @param   string  column name to group by
	 * @return  object  This Database object.
	 */
	public function groupby($by)
	{
		if ( ! is_array($by))
		{
			$by = explode(',', (string) $by);
		}

		foreach ($by as $val)
		{
			if (($val = trim($val)) !== '')
			{
				$this->groupby[] = $val;
			}
		}

		return $this;
	}

	/**
	 * Selects the having(s) for a database query.
	 *
	 * @chainable
	 * @param   string|array  key name or array of key => value pairs
	 * @param   string        value to match with key
	 * @param   boolean       disable quoting of WHERE clause
	 * @return  object        This Database object.
	 */
	public function having($key, $value = '', $quote = TRUE)
	{
		$this->having[] = $this->driver->where($key, $value, 'AND', count($this->having), TRUE);

		return $this;
	}

	/**
	 * Selects the or having(s) for a database query.
	 *
	 * @chainable
	 * @param   string|array  key name or array of key => value pairs
	 * @param   string        value to match with key
	 * @param   boolean       disable quoting of WHERE clause
	 * @return  object        This Database object.
	 */
	public function orhaving($key, $value = '', $quote = TRUE)
	{
		$this->having[] = $this->driver->where($key, $value, 'OR', count($this->having), TRUE);

		return $this;
	}

	/**
	 * Chooses which column(s) to order the select query by.
	 *
	 * @chainable
	 * @param   string|array  column(s) to order on, can be an array, single column, or comma seperated list of columns
	 * @param   string        direction of the order
	 * @return  object        This Database object.
	 */
	public function orderby($orderby, $direction = '')
	{
		$direction = strtoupper(trim($direction));

		if ($direction != '')
		{
			$direction = (in_array($direction, array('ASC', 'DESC', 'RAND()', 'NULL'))) ? ' '.$direction : ' ASC';
		}

		if (empty($orderby))
		{
			$this->orderby[] = $direction;
			return $this;
		}

		if ( ! is_array($orderby))
		{
			$orderby = explode(',', (string) $orderby);
		}

		$order = array();
		foreach ($orderby as $field)
		{
			if (($field = trim($field)) !== '')
			{
				$order[] = $this->driver->escape_column($field);
			}
		}
		$this->orderby[] = implode(',', $order).$direction;

		return $this;
	}

	/**
	 * Selects the limit section of a query.
	 *
	 * @chainable
	 * @param   integer  number of rows to limit result to
	 * @param   integer  offset in result to start returning rows from
	 * @return  object   This Database object.
	 */
	public function limit($limit, $offset = FALSE)
	{
		$this->limit = (int) $limit;
		$this->offset($offset);

		return $this;
	}

	/**
	 * Sets the offset portion of a query.
	 *
	 * @chainable
	 * @param   integer  offset value
	 * @return  object   This Database object.
	 */
	public function offset($value)
	{
		$this->offset = (int) $value;

		return $this;
	}

	/**
	 * Allows key/value pairs to be set for inserting or updating.
	 *
	 * @chainable
	 * @param   string|array  key name or array of key => value pairs
	 * @param   string        value to match with key
	 * @return  object        This Database object.
	 */
	public function set($key, $value = '')
	{
		if ( ! is_array($key))
		{
			$key = array($key => $value);
		}

		foreach ($key as $k => $v)
		{
			$this->set[$k] = $this->driver->escape($v);
		}

		return $this;
	}

	/**
	 * Adds an "IN" condition to the where clause
	 *
	 * @chainable
	 * @param   string  Name of the column being examined
	 * @param   mixed   An array or string to match against
	 * @param   bool    Generate a NOT IN clause instead
	 * @return  object  This Database object.
	 */
	public function in($field, $values, $not = FALSE) 
	{
		if (is_array($values))
		{
			$escaped_values = array();
			foreach ($values as $v)
			{
				if (is_numeric($v)) 
				{
					$escaped_values[] = $v;
				}
				else
				{
					$escaped_values[] = "'".$this->driver->escape_string($v)."'";
				}
			}
			$values = implode(',', $escaped_values);
		}
		$this->where($this->driver->escape_column($field).' '.($not === TRUE ? 'NOT ' : '').'IN ('.$values.')');

		return $this;
	}

	/**
	 * Adds a "NOT IN" condition to the where clause
	 *
	 * @chainable
	 * @param   string  Name of the column being examined
	 * @param   mixed   An array or string to match against
	 * @return  object  This Database object.
	 */
	public function notin($field, $values) 
	{
		return $this->in($field, $values, TRUE);
	}

	/**
	 * Compiles a merge string and runs the query.
	 *
	 * @param   string  table name
	 * @param   array   array of key/value pairs to merge
	 * @return  object  Database_Result
	 */
	public function merge($table = '', $set = NULL)
	{
		if ( ! is_null($set))
		{
			$this->set($set);
		}

		if ($this->set === NULL)
			throw new Kohana_Database_Exception('database.must_use_set');

		if ($table == '')
		{
			if ( ! isset($this->from[0]))
				throw new Kohana_Database_Exception('database.must_use_table');

			$table = $this->from[0];
		}

		$sql = $this->driver->merge($this->config['table_prefix'].$table, array_keys($this->set), array_values($this->set));

		$this->reset_write();
		return $this->query($sql);
	}

	/**
	 * Compiles the select statement based on the other functions called and returns the query string.
	 *
	 * @param   string  table name
	 * @param   string  limit clause
	 * @param   string  offset clause
	 * @return  string  sql string
	 */
	public function compile($table = '', $limit = NULL, $offset = NULL)
	{
		if ($table !== '')
		{
			$this->from($table);
		}

		if ( ! is_null($limit))
		{
			$this->limit($limit, $offset);
		}

		$sql = $this->driver->compile_select(get_object_vars($this));

		$this->reset();
		return $sql;
	}

	/**
	 * Resets all private select variables.
	 *
	 * @return  void
	 */
	protected function reset()
	{
		$this->select   = array();
		$this->from     = array();
		$this->join     = array();
		$this->where    = array();
		$this->orderby  = array();
		$this->groupby  = array();
		$this->having   = array();
		$this->distinct = FALSE;
		$this->limit    = FALSE;
		$this->offset   = FALSE;
	}
}
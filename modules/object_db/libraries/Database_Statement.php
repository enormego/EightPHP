<?php defined('SYSPATH') or die('No direct script access.');

abstract class Database_Statement_Core extends Database_Escape {

	protected $db;

	protected $where = array();
	protected $from  = array();
	protected $join  = array();

	public function __toString()
	{
		return $this->build();
	}

	public function from($tables)
	{
		$tables = func_get_args();
		
		foreach ($tables as $table)
		{
			if (is_string($table))
			{
				$table = trim($table);

				if ($table === '') continue;
			}

			$this->from[] = $table;
		}

		return $this;
	}

	public function join($table, $keys, $value = NULL, $type = NULL)
	{
		if ( ! is_array($keys))
		{
			$keys = array($keys => $value);
		}

		if ($type !== NULL)
		{
			$type = strtoupper(trim($type));

			if ( ! in_array($type, array('LEFT', 'RIGHT', 'OUTER', 'INNER', 'LEFT OUTER', 'RIGHT OUTER'), TRUE))
			{
				$type = NULL;
			}
		}

		$this->join[] = array($table, $keys, $type);

		return $this;
	}

	public function where($keys, $op = '=', $value = NULL, $type = 'AND')
	{
		if ( ! is_array($keys))
		{
			// Make keys into key/value pair
			$keys = array($keys => $value);
		}

		$this->where[] = new Database_Where($keys, $op, $type, $this->db);

		return $this;
	}

	public function or_where($keys, $op = '=', $value = NULL)
	{
		if ( ! is_array($keys))
		{
			// Make keys into key/value pair
			$keys = array($keys => $value);
		}

		$this->where[] = new Database_Where($keys, $op, 'OR', $this->db);

		return $this;
	}

} // End Database Statement
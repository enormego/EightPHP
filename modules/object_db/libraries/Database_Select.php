<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Provides database access in a platform agnostic way, using simple query building blocks.
 *
 * $Id: Database_Where.php 2303 2008-03-14 01:00:54Z zombor $
 *
 * @package    Core
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Database_Select_Core extends Database_Statement {

	protected $db;

	protected $alias = 'tbl';

	protected $order_by   = array();
	protected $group_by   = array();
	protected $having     = array();
	protected $distinct   = FALSE;
	protected $limit      = FALSE;
	protected $offset     = FALSE;

	public function __construct(array $columns, Database_Driver $db)
	{
		$this->db = $db;

		foreach($columns as $column)
		{
			if (is_string($column))
			{
				$column = trim($column);

				if ($column === '') continue;

				if (preg_match('/^DISTINCT\s++(.+)$/i', $column, $matches))
				{
					// Find distinct columns
					$this->distinct = TRUE;

					// Use only the column name
					$column = $matches[1];
				}
			}

			$this->select[] = $column;
		}
	}

	public function alias($name = NULL)
	{
		if ($name === NULL)
			return $this->alias;

		// Set the alias
		$this->alias = (string) $name;

		return $this;
	}

	public function order_by($columns, $direction = NULL)
	{
		if ( ! is_array($columns))
		{
			// Make columns into key/value pair
			$columns = array($columns => $direction);
		}

		foreach ($columns as $column => $direction)
		{
			if (is_string($column))
			{
				$column = trim($column);

				if ($column === '') continue;
			}

			if ( ! empty($direction) AND preg_match('/^(?:ASC|DESC|NULL|RAND\(\))$/i', $direction))
			{
				$direction = strtoupper($direction);
			}

			$this->order_by[$column] = $direction;
		}

		return $this;
	}

	public function group_by($columns)
	{
		if ( ! is_array($columns))
		{
			$columns = array($columns);
		}

		$this->group_by[] = $columns;

		return $this;
	}

	public function having($keys, $op = '=', $value = NULL, $type = 'AND')
	{
		if ( ! is_array($keys))
		{
			// Make keys into key/value pair
			$keys = array($keys => $value);
		}

		$this->having[] = new Database_Having($keys, $op, $type, $this->db);

		return $this;
	}

	public function limit($limit, $offset = NULL)
	{
		$this->limit = (int) $limit;

		if ( ! empty($offset))
		{
			$this->offset($offset);
		}

		return $this;
	}

	public function offset($value)
	{
		$this->offset = (int) $value;

		return $this;
	}

	public function build()
	{
		// SELECT c FROM t JOIN j WHERE w GROUP BY g HAVING h ORDER BY s LIMIT l OFFSET o
		$sql = array();

		// Add SELECT
		$sql['select'] = 'SELECT ';
		if ($this->distinct === TRUE)
		{
			$sql['select'] .= 'DISTINCT ';
		}

		// Add columns
		$data = array();
		foreach ($this->select as $val)
		{
			$data[] = $this->escape('column', $val);
		}
		$sql['select'] .= implode(', ', $data);

		// Add FROM
		$data = array();
		foreach ($this->from as $val)
		{
			$data[] = $this->escape('table', $val);
		}
		$sql['from'] = 'FROM '.implode(', ', $data);

		if ( ! empty($this->join))
		{
			// Add JOIN
			$data = array();
			foreach ($this->join as $val)
			{
				list ($table, $columns, $type) = $val;

				if ($type !== NULL)
				{
					// Add a space after the type
					$type .= ' ';
				}

				$join = array();
				foreach ($columns as $c1 => $c2)
				{
					$join[] = $this->escape('column', $c1).' = '.$this->escape('column', $c2);
				}

				$data[] = $type.'JOIN '.$this->escape('table', $table).' ON ('.implode(', ', $join).')';
			}
			$sql['join'] = implode("\n", $data);
		}

		if ( ! empty($this->where))
		{
			// Add WHERE
			$sql['where'] = 'WHERE ';

			foreach ($this->where as $i => $val)
			{
				if ($i > 0)
				{
					// Add operators after the first WHERE statement
					$sql['where'] .= $val->op();
				}

				$sql['where'] .= $val->build()."\n";
			}
			$sql['where'] = rtrim($sql['where']);
		}

		if ( ! empty($this->group_by))
		{
			// Add GROUP BY
			$sql['group_by'] ='GROUP BY ';

			$data = array();
			foreach ($this->group_by as $columns)
			{
				foreach ($columns as $i => $col)
				{
					$columns[$i] = $this->escape('column', $col);
				}

				$data[] = '('.implode(', ', $columns).')';
			}
			$sql['group_by'] .= implode(', ', $data);
		}

		if ( ! empty($this->having))
		{
			// Add HAVING
			$sql['having'] = 'HAVING ';

			foreach ($this->having as $i => $having)
			{
				if ($i > 0)
				{
					// Add operators after the first HAVING statement
					$sql['having'] .= $having->op();
				}

				$sql['having'] .= $having->build();
			}
		}

		if ( ! empty($this->order_by))
		{
			// Add ORDER BY
			$sql['order_by'] = 'ORDER BY ';

			$data = array();
			foreach ($this->order_by as $column => $direction)
			{
				$data[] = $this->escape('column', $column).' '.$direction;
			}
			$sql['order_by'] .= implode(', ', $data);
		}

		if ($this->limit > 0 OR $this->offset > 0)
		{
			// Add LIMIT and OFFSET
			$sql['limit'] = $this->db->limit($this->limit, $this->offset);
		}

		return implode("\n", $sql);


		if (is_string($group)) // group name was passed
		{
			$config = Kohana::config('database.'.$group);
			$conn = Database::parse_con_string($config['connection']);
			$driver = $conn['type'];
		}
		elseif (is_array($group)) // DB Group was passed
		{
			$conn = Database::parse_con_string($group['connection']);
			$driver = $conn['type'];
		}
		elseif ($group instanceof Database_Driver)
		{
			$driver = $group;
		}

		if (!isset($this->drivers[$driver]))
		{
			// Set driver name
			$driver_class_name = 'Database_'.ucfirst($driver).'_Driver';

			// Load the driver
			if ( ! Kohana::auto_load($driver))
				throw new Kohana_Database_Exception('database.driver_not_supported', $driver_class_name);

			// Initialize the driver
			$this->drivers[$driver] = new $driver_class_name();

			// Validate the driver
			if ( ! ($this->drivers[$driver] instanceof Database_Driver))
				throw new Kohana_Database_Exception('database.driver_not_supported', 'Database drivers must use the Database_Driver interface.');
		}

	}

} // End Database Select